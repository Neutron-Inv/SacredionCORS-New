<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class DeployController extends Controller
{
    public function refresh(Request $request, string $token): Response
    {
        $secret = config('services.deploy.secret');

        if (! $secret || ! hash_equals($secret, $token)) {
            abort(404);
        }

        $results = [];

        Artisan::call('optimize:clear');
        $results['optimize:clear'] = trim(Artisan::output()) ?: 'Done.';

        try {
            Artisan::call('storage:link', ['--force' => true]);
            $results['storage:link'] = trim(Artisan::output()) ?: 'Done.';
        } catch (\Throwable $e) {
            $results['storage:link'] = 'Failed: '.$e->getMessage();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'results' => $results,
            ]);
        }

        $body = collect($results)
            ->map(fn (string $output, string $command) => "{$command}\n{$output}")
            ->implode("\n\n");

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
