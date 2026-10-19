<?php

use App\Http\Controllers\CorsDailyOrderController;
use App\Http\Controllers\CorsStationController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/privacy', PrivacyController::class)->name('privacy');

Route::get('/deploy/refresh/{token}', [DeployController::class, 'refresh'])->name('deploy.refresh');

Route::get('/cors/{station:slug}', [CorsStationController::class, 'show'])->name('cors.show');
Route::get('/cors/{station:slug}/24h', [CorsDailyOrderController::class, 'preview'])->name('cors.daily.preview');
Route::post('/cors/{station:slug}/24h', [CorsDailyOrderController::class, 'store'])
    ->middleware(['auth', 'verified', 'throttle:10,1'])
    ->name('cors.daily.store');
Route::post('/cors/24h-orders/{order}/confirm', [CorsDailyOrderController::class, 'confirm'])
    ->middleware(['auth', 'verified', 'throttle:20,1'])
    ->name('cors.daily.confirm');
Route::post('/webhooks/paystack', [CorsDailyOrderController::class, 'webhook'])->name('webhooks.paystack');
Route::get('/downloads/cors-24h/{token}', [CorsDailyOrderController::class, 'download'])
    ->middleware('throttle:30,1')
    ->name('cors.daily.download');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/cors/files/{file}/download', [CorsStationController::class, 'download'])->name('cors.files.download');
    Route::post('/cors/{station:slug}/download-zip', [CorsStationController::class, 'downloadZip'])->name('cors.download-zip');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
