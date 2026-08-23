<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function store(Request $request)
    {
        // Normalisasi input role & nama untuk mendeteksi apakah akun ini adalah admin
        $inputRole = strtolower(trim($request->role ?? 'parent'));
        $inputName = strtolower(trim($request->name ?? ''));
        $inputEmail = strtolower(trim($request->email ?? ''));

        // Jika nama, email, atau role mengindikasikan admin, paksa role menjadi 'admin'
        if (str_contains($inputName, 'admin') || str_contains($inputEmail, 'admin') || str_contains($inputRole, 'admin')) {
            $assignedRole = 'admin';
        } else {
            $assignedRole = $inputRole;
        }

        // Menyimpan data ke Database Laravel agar bisa diverifikasi oleh halaman Login & Otoritas Server
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Password wajib di-hash agar cocok dengan Auth Laravel
            'role' => $assignedRole,
            'status' => $request->status ?? 'Aktif',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil disimpan ke database server dengan otorisasi yang sesuai!'
        ]);
    }
}
