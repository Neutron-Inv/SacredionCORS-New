<?php

use App\Mail\CorsDailyOrderPreparing;
use App\Mail\CorsDailyOrderReady;
use App\Models\CorsDailyOrder;
use App\Models\CorsFile;
use App\Models\CorsStation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Carbon::setTestNow('2026-10-16 12:00:00');

    config([
        'cors.daily.price' => 5000,
        'cors.daily.currency' => 'NGN',
        'cors.daily.download_days' => 7,
        'cors.daily.timezone' => 'Africa/Lagos',
        'services.paystack.public_key' => 'pk_test',
        'services.paystack.secret_key' => 'sk_test_secret',
    ]);

    Storage::fake('cors');
    Storage::fake('local');
    Mail::fake();
});

it('offers completed days and rejects today', function () {
    [$user, $station] = stationWithDailyFiles();

    $this->actingAs($user)
        ->get(route('cors.show', $station))
        ->assertOk()
        ->assertSee('24-hour data')
        ->assertSee('value="2026-10-15"', false)
        ->assertDontSee('value="2026-10-16"', false);

    $this->getJson(route('cors.daily.preview', $station).'?date=2026-10-15')
        ->assertOk()
        ->assertJsonPath('count', 4);

    $this->getJson(route('cors.daily.preview', $station).'?date=2026-10-16')
        ->assertStatus(422)
        ->assertJsonValidationErrors('date');
});

it('uses the station timezone when deciding that a day is still today', function () {
    Carbon::setTestNow('2026-10-15 23:30:00');
    [, $station] = stationWithDailyFiles();

    $dates = app(\App\Services\CorsDailyCatalog::class)->dateOptions($station);

    expect(collect($dates)->pluck('value')->all())->toBe(['2026-10-14', '2026-10-15']);
});

it('merges the day after payment and deletes the archive when the link expires', function () {
    [$user, $station] = stationWithDailyFiles();
    fakePaystack(500000);

    $this->actingAs($user)
        ->postJson(route('cors.daily.store', $station), ['date' => '2026-10-15'])
        ->assertOk()
        ->assertJsonPath('mode', 'paystack')
        ->assertJsonPath('price_label', '₦5,000');

    $order = CorsDailyOrder::first();

    $this->actingAs($user)
        ->postJson(route('cors.daily.confirm', $order))
        ->assertOk()
        ->assertJsonPath('status', CorsDailyOrder::READY);

    $order->refresh();

    expect($order->archive_path)->not->toBeNull()
        ->and(Storage::disk('local')->exists($order->archive_path))->toBeTrue();

    $zip = new ZipArchive;
    $zip->open(Storage::disk('local')->path($order->archive_path));
    $observation = $zip->getFromName('KOGI2880.26o');
    expect($zip->locateName('KOGI2880.26n'))->not->toBeFalse()
        ->and(substr_count($observation, 'END OF HEADER'))->toBe(1)
        ->and($observation)->toContain('EPOCH-A')
        ->and($observation)->toContain('EPOCH-B')
        ->and($observation)->not->toContain('0    59   30.0000000');
    $zip->close();

    Mail::assertSent(CorsDailyOrderPreparing::class);
    Mail::assertSent(CorsDailyOrderReady::class, function (CorsDailyOrderReady $mail) use ($order) {
        return str_contains($mail->render(), $order->download_url);
    });

    $this->get(route('cors.daily.download', $order->download_token))
        ->assertOk();

    Carbon::setTestNow('2026-10-25 12:00:00');
    $this->artisan('cors:purge-daily-orders')->assertSuccessful();

    expect($order->refresh()->status)->toBe(CorsDailyOrder::EXPIRED)
        ->and(Storage::disk('local')->exists($order->archive_path ?? 'missing'))->toBeFalse();

    $this->get(route('cors.daily.download', $order->download_token))->assertStatus(410);
});

it('does not mark an order paid when the amount does not match', function () {
    [$user, $station] = stationWithDailyFiles();
    fakePaystack(100);

    $this->actingAs($user)
        ->postJson(route('cors.daily.store', $station), ['date' => '2026-10-15'])
        ->assertOk();

    $order = CorsDailyOrder::first();

    $this->actingAs($user)
        ->postJson(route('cors.daily.confirm', $order))
        ->assertStatus(422);

    expect($order->refresh()->paid_at)->toBeNull();
    Mail::assertNothingSent();
});

it('accepts a signed paystack webhook for the stored reference', function () {
    [$user, $station] = stationWithDailyFiles();
    fakePaystack(500000);

    $this->actingAs($user)
        ->postJson(route('cors.daily.store', $station), ['date' => '2026-10-15'])
        ->assertOk();

    $order = CorsDailyOrder::first();
    $payload = json_encode([
        'event' => 'charge.success',
        'data' => ['reference' => $order->payment_reference],
    ]);

    $this->call('POST', route('webhooks.paystack'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => hash_hmac('sha512', $payload, 'sk_test_secret'),
    ], $payload)->assertOk();

    expect($order->refresh()->status)->toBe(CorsDailyOrder::READY);

    $this->call('POST', route('webhooks.paystack'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => 'invalid',
    ], $payload)->assertStatus(400);
});

function stationWithDailyFiles(): array
{
    $user = User::factory()->create();
    $station = CorsStation::create([
        'name' => 'Lokoja CORS',
        'slug' => 'lokoja',
        'code' => 'KG',
        'state_name' => 'Kogi State',
        'region' => 'North Central',
    ]);

    writeRinex($station, 15, 'KOGI288A.26o', '0    59   30.0000000', 'EPOCH-A');
    writeRinex($station, 15, 'KOGI288B.26o', '1    59   30.0000000', 'EPOCH-B');
    writeRinex($station, 15, 'KOGI288A.26n', '0    30   00.0000000', 'NAV-A');
    writeRinex($station, 15, 'KOGI288B.26n', '1    30   00.0000000', 'NAV-B');
    writeRinex($station, 16, 'KOGI289A.26o', '0    10   00.0000000', 'TODAY');
    writeRinex($station, 14, 'KOGI287A.26o', '0    10   00.0000000', 'EARLIER');

    return [$user, $station];
}

function writeRinex(CorsStation $station, int $day, string $filename, string $lastObs, string $body): void
{
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $path = sprintf('KOGI/2026/10/%02d/%s', $day, $filename);
    $contents = <<<RINEX
     3.02           OBSERVATION DATA    M (MIXED)           RINEX VERSION / TYPE
  2026    10    {$day}     0     0    0.0000000     GPS         TIME OF FIRST OBS
  2026    10    {$day}     {$lastObs}     GPS         TIME OF LAST OBS
                                                            END OF HEADER
> {$body}
RINEX;

    Storage::disk('cors')->put($path, $contents);

    CorsFile::create([
        'cors_station_id' => $station->id,
        'name' => $name,
        'ext' => $extension,
        'size' => strlen($contents),
        'path' => $path,
        'year' => 2026,
        'month' => 10,
        'day' => $day,
        'observed_at' => sprintf('2026-10-%02d 00:00:00', $day),
    ]);
}

function fakePaystack(int $amount): void
{
    Http::preventStrayRequests();
    Http::fake([
        'https://api.paystack.co/transaction/initialize' => Http::response([
            'status' => true,
            'data' => [
                'authorization_url' => 'https://checkout.paystack.com/abc',
                'access_code' => 'access-code',
                'reference' => 'ref',
            ],
        ]),
        'https://api.paystack.co/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => [
                'status' => 'success',
                'amount' => $amount,
                'currency' => 'NGN',
                'channel' => 'card',
            ],
        ]),
    ]);
}
