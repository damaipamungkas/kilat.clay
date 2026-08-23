<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AppendixController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $rawRole = strtolower(trim((string)($user->role ?? '')));
        $userName = strtolower(trim((string)($user->name ?? $user->username ?? $user->email ?? '')));

        $roleMap = [
            'orang tua'     => 'parent',
            'orangtua'      => 'parent',
            'wali'          => 'parent',
            'walimurid'     => 'parent',
            'pelatih'       => 'coach',
            'admin'         => 'admin',
            'administrator' => 'admin',
        ];

        // Lakukan pemetaan awal berdasarkan role map
        $role = $roleMap[$rawRole] ?? $rawRole;

        // Pengecekan akhir: Jika nama, email, atau username mengandung kata 'admin' ataupun rolenya admin, paksa jadi 'admin' mutlak
        if (str_contains($userName, 'admin') || str_contains($rawRole, 'admin') || $rawRole === 'administrator' || $role === 'admin') {
            $role = 'admin';
        }

        $currentUserId = $user->id ?? '';

        // Mengambil data atlet dari database dengan mencakup variasi role 'athlete' dan 'atlet'
        $athletes = User::whereIn('role', ['athlete', 'atlet', 'Atlet'])
            ->where(function($query) {
                $query->where('status', 'Aktif')->orWhereNull('status');
            })
            ->get();

        return view('appendix.appendix', compact('role', 'currentUserId', 'athletes'));
    }
}
