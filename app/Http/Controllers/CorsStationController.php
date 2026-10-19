<?php

namespace App\Http\Controllers;

use App\Models\CorsFile;
use App\Models\CorsStation;
use App\Services\CorsDailyCatalog;
use App\Services\CorsDailyOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class CorsStationController extends Controller
{
    public function show(Request $request, CorsStation $station, CorsDailyCatalog $catalog, CorsDailyOrderService $orders)
    {
        abort_unless($station->is_active, 404);

        if ($station->is_external && $station->external_url) {
            return redirect()->away($station->external_url);
        }

        $years = $station->files()
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $days = $station->files()
            ->when($request->filled('year'), fn ($query) => $query->where('year', $request->integer('year')))
            ->when($request->filled('month'), fn ($query) => $query->where('month', $request->integer('month')))
            ->whereNotNull('day')
            ->distinct()
            ->orderByDesc('day')
            ->pluck('day');

        $files = $station->files()
            ->when($request->filled('year'), fn ($query) => $query->where('year', $request->integer('year')))
            ->when($request->filled('month'), fn ($query) => $query->where('month', $request->integer('month')))
            ->when($request->filled('day'), fn ($query) => $query->where('day', $request->integer('day')))
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderByDesc('observed_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $dailyOrder = [
            'dates' => $catalog->dateOptions($station),
            'previewUrl' => route('cors.daily.preview', $station),
            'storeUrl' => route('cors.daily.store', $station),
            'priceLabel' => $orders->priceLabel(),
            'mode' => $orders->paymentMode(),
            'downloadDays' => (int) config('cors.daily.download_days', 7),
            'canOrder' => (bool) $request->user()?->hasVerifiedEmail(),
            'authenticated' => $request->user() !== null,
        ];

        return view('cors.show', compact('station', 'files', 'years', 'days', 'dailyOrder'));
    }

    public function download(CorsFile $file): StreamedResponse
    {
        $station = $file->station;

        abort_unless($station && $station->is_active && ! $station->is_external, 404);
        abort_unless(Storage::disk('cors')->exists($file->path), 404);

        $filename = $file->ext ? "{$file->name}.{$file->ext}" : $file->name;

        return Storage::disk('cors')->download($file->path, $filename);
    }

    public function downloadZip(Request $request, CorsStation $station)
    {
        abort_unless($station->is_active && ! $station->is_external, 404);

        $validated = $request->validate([
            'file_ids' => ['required', 'array', 'min:1', 'max:50'],
            'file_ids.*' => ['integer', 'exists:cors_files,id'],
        ]);

        $files = $station->files()
            ->whereIn('id', $validated['file_ids'])
            ->get();

        abort_if($files->isEmpty(), 404);

        $zipPath = storage_path('app/temp/'.$station->slug.'-'.time().'.zip');

        if (! is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create zip archive.');
        }

        foreach ($files as $file) {
            if (! Storage::disk('cors')->exists($file->path)) {
                continue;
            }

            $entryName = $file->ext ? "{$file->name}.{$file->ext}" : $file->name;
            $zip->addFile(Storage::disk('cors')->path($file->path), $entryName);
        }

        $zip->close();

        return response()->download($zipPath, $station->slug.'-gnss-data.zip')->deleteFileAfterSend(true);
    }
}
