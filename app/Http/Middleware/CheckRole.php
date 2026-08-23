<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user terautentikasi (sudah login)
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // 2. Ambil role user secara aman dari object Auth
        $user = auth()->user();
        $rawRole = strtolower(trim((string)($user->role ?? '')));

        // 3. Normalisasi nama role
        $roleMap = [
            'orang tua' => 'parent',
            'orangtua'  => 'parent',
            'wali'      => 'parent',
            'walimurid' => 'parent',
            'pelatih'   => 'coach',
            'admin'     => 'admin',
        ];

        $userRole = $roleMap[$rawRole] ?? $rawRole;

        // 4. Jika tidak ada parameter role yang ditentukan di route, izinkan lewat
        if (empty($roles)) {
            return $next($request);
        }

        // 5. Normalisasi daftar role yang diperbolehkan dari route
        $normalizedAllowedRoles = array_map(function ($r) use ($roleMap) {
            $clean = strtolower(trim($r));
            return $roleMap[$clean] ?? $clean;
        }, $roles);

        // 6. Cek apakah role user ada dalam daftar role yang diizinkan
        if (in_array($userRole, $normalizedAllowedRoles, true)) {
            return $next($request);
        }

        // 7. Jika role tidak cocok, lempar kembali ke login
        return redirect()->route('login')->with('error', 'Akses ditolak untuk role: ' . $rawRole);
    }
}
