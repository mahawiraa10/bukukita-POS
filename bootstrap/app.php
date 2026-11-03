<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => abort(401, 'Unauthenticated.'));
        $middleware->alias(['permission' => \App\Http\Middleware\CheckPermission::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // DomainException = aturan bisnis dilanggar (over-receive, PO status salah, dll).
        // Tanpa ini Laravel balikin 500, padahal ini kesalahan input user -> 422.
        $exceptions->render(function (\DomainException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        // Jangan bocorkan SQLSTATE / stack trace / path DB ke client.
        // Detail asli tetap masuk laravel.log; client cuma dapat pesan generik.
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                report($e);
                return response()->json([
                    'message' => 'Terjadi kesalahan pada basis data. Data tidak diproses — hubungi admin.',
                ], 500);
            }
        });

        // Error tak terduga: ringkas ke client, detail asli ke laravel.log.
        $exceptions->render(function (\Throwable $e, $request) {
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return null; // halaman web: biarkan Laravel render errors/xxx.blade.php
            }

            // 404 / 401 / 403 / 419 / 429 (dan error HTTP lain): kirim status + pesan saja.
            // Penting: APP_DEBUG=true bikin default handler Laravel ikut menyertakan
            // file, line, dan trace lengkap ke client — itu yang dibocorkan.
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $pesan = match ($status) {
                    401 => 'Sesi habis atau belum login. Silakan login ulang.',
                    403 => 'Akses ditolak.',
                    404 => 'Data atau alamat yang diminta tidak ditemukan.',
                    419 => 'Sesi kedaluwarsa. Muat ulang halaman.',
                    429 => 'Terlalu banyak permintaan. Coba lagi sebentar.',
                    default => 'Permintaan tidak dapat diproses.',
                };
                return response()->json(['message' => $pesan], $status);
            }

            // Exception khusus Laravel yang sudah punya format sendiri — biarkan default.
            // (ValidationException = 422 + field errors[], AuthenticationException = 401)
            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return null;
            }

            // 500 tak terduga: jangan bocorkan SQLSTATE / trace / path server.
            report($e);
            return response()->json([
                'message' => 'Terjadi kesalahan pada server. Coba lagi atau hubungi admin.',
            ], 500);
        });
    })->create();
