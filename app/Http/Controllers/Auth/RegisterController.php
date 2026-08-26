<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth; // <-- 1. Tambahkan baris ini di atas

class RegisterController extends Controller
{
    /**
     * Menangani proses pendaftaran akun Parent baru.
     */
    public function store(Request $request)
    {
        // 1. Validasi data yang dikirim dari form registrasi
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['required', 'in:parent'], // Memastikan role terkunci dan sah sebagai parent
        ], [
            'email.unique' => 'Alamat email ini sudah terdaftar di sistem.',
            'password.min' => 'Kunci keamanan (password) minimal harus 6 karakter.',
        ]);

        // 2. Simpan data pengguna baru ke database
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // Enskripsi password agar aman
            'role'     => $request->role,               // Otomatis bernilai 'parent'
        ]);

        // 3. AUTO-LOGIN: Masukkan user ke sesi sistem secara otomatis
        Auth::login($user);

        // 4. ARAHKAN KE PROFIL: Langsung redirect ke halaman profil setelah terdaftar
        return redirect()->route('profil')->with('success', 'Registrasi berhasil! Selamat datang di sistem KILAT.');
    }
}
