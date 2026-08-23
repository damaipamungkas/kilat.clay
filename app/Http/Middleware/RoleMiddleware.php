<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Pastikan user login
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Cek apakah role user ada di dalam daftar role yang diizinkan
        $user = Auth::user();
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Tolak akses jika role tidak sesuai
        return abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk tindakan ini.');
    }
}
