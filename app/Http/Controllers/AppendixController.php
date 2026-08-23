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

        $roleMap = [
            'orang tua' => 'parent',
            'orangtua'  => 'parent',
            'wali'      => 'parent',
            'walimurid' => 'parent',
            'pelatih'   => 'coach',
            'admin'     => 'admin',
        ];

        $role = $roleMap[$rawRole] ?? $rawRole;
        $currentUserId = $user->id ?? '';

        // Ambil data atlet dari database yang sudah AKTIF (bukan pending verifikasi)
        $athletes = User::where('role', 'athlete')
            ->where(function($query) {
                $query->where('status', 'Aktif')->orWhereNull('status');
            })
            ->get();

        return view('appendix.appendix', compact('role', 'currentUserId', 'athletes'));
    }
}
