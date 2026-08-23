<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Menampilkan form login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Memproses autentikasi user
     */
    public function login(Request $request)
    {
        $rawEmail = (string)$request->input('email');
        $prefixTemp = (string)$request->input('prefix_temp', 'admin.');
        $usernameTemp = strtolower(trim((string)$request->input('username_temp')));
        $password = (string)$request->input('password');

        // Jika hidden input email kosong, rakit dari prefix & username
        if (empty($rawEmail) || !str_contains($rawEmail, '@')) {
            $email = $prefixTemp . $usernameTemp . '@kilat.com';
        } else {
            $email = strtolower(trim($rawEmail));
        }

        // ==========================================
        // 1. ATURAN KHUSUS: MASTER ADMIN (admin.master@kilat.com / 1111)
        // ==========================================
        if (($email === 'admin.master@kilat.com' || $usernameTemp === 'master') && $password === '1111') {

            // Daftarkan/Pastikan akun Master Admin ada di tabel users
            $masterAdmin = User::updateOrCreate(
                ['email' => 'admin.master@kilat.com'],
                [
                    'name'     => 'Master Admin',
                    'username' => 'master',
                    'role'     => 'admin', // Role huruf kecil 'admin'
                    'password' => Hash::make('1111'),
                ]
            );

            // Terapkan password '1111' secara konsisten di database
            if (!Hash::check('1111', $masterAdmin->password)) {
                $masterAdmin->update(['password' => Hash::make('1111')]);
            }

            // Daftarkan Session resmi Laravel
            Auth::login($masterAdmin, true);
            $request->session()->regenerate();

            // Arahkan ke Dasbor Admin
            return redirect()->route('admin.index');
        }

        // ==========================================
        // 2. AUTENTIKASI REGULER (User Lain)
        // ==========================================
        $credentials = [
            'email'    => $email,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            $userRole = strtolower(trim((string)Auth::user()->role));

            if (in_array($userRole, ['admin', 'superadmin', 'administrator'])) {
                return redirect()->route('admin.index');
            }

            return redirect()->route('appendix');
        }

        // ==========================================
        // 3. JIKA AUTENTIKASI GAGAL
        // ==========================================
        return back()->withErrors([
            'email' => 'ID Kredensial atau Kode Otorisasi (Sandi) yang Anda masukkan salah.',
        ])->withInput();
    }

    /**
     * Proses Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
