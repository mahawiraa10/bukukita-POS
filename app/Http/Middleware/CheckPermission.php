<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Owner bypass semua permission
        if ($user->role && $user->role->name === 'owner') {
            return $next($request);
        }

        // Cek relasi role -> permissions
        $hasPermission = $user->role && $user->role->permissions()->where('name', $permission)->exists();

        if (!$hasPermission) {
            return response()->json(['message' => 'Forbidden: Anda tidak memiliki hak akses ['.$permission.']'], 403);
        }

        return $next($request);
    }
}
