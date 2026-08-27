<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Athlete;
use App\Models\AthleteEditRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AthleteController extends Controller
{
    // Method untuk menyimpan data atlet baru sekaligus membuat akun User dengan role 'atlet'
    public function store(Request $request)
    {
        // Validasi input sesuai kebutuhan
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        // 1. Buat akun di tabel users terlebih dahulu
        $user = User::create([
            'namalengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'username' => $request->email, // atau sesuaikan dengan input username jika ada
            'password' => Hash::make($request->password),
            'role' => 'atlet',
            'status' => 'Aktif',
        ]);

        // 2. Simpan data ke tabel athletes (hubungkan dengan user_id jika diperlukan)
        Athlete::create([
            'user_id' => $user->id,
            'nama_lengkap' => $request->nama_lengkap,
            // Tambahkan field lain sesuai tabel Anda
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun dan data atlet berhasil dibuat!'
        ]);
    }

    // Method untuk memproses klik "Simpan" pada Edit Data Atlet
    public function updateRequest(Request $request, $id)
    {
        $user = Auth::user();

        // Jika ADMIN, langsung update data utama
        if ($user->role === 'admin') {
            $athlete = Athlete::findOrFail($id);
            $athlete->update($request->all());
            return response()->json(['message' => 'Data berhasil diupdate!']);
        }

        // Jika PARENT, simpan ke tabel antrean (staging)
        if ($user->role === 'parent') {
            AthleteEditRequest::create([
                'athlete_id' => $id,
                'requested_by' => $user->id,
                'requested_data' => json_encode($request->except(['_token'])), // Simpan data baru sebagai JSON
                'status' => 'pending'
            ]);

            return response()->json([
                'message' => 'Perubahan data berhasil dikirim. Menunggu verifikasi Admin.'
            ]);
        }
    }

    // Method khusus ADMIN untuk menyetujui perubahan dari Parent
    public function approveEdit($requestId)
    {
        $editRequest = AthleteEditRequest::findOrFail($requestId);

        if ($editRequest->status === 'pending') {
            $athlete = Athlete::findOrFail($editRequest->athlete_id);

            // Decode JSON dari tabel request dan terapkan ke tabel utama
            $newData = json_decode($editRequest->requested_data, true);
            $athlete->update($newData);

            // Ubah status request menjadi approved
            $editRequest->update(['status' => 'approved']);

            return back()->with('success', 'Perubahan data atlet dari Parent telah disetujui.');
        }

        return back()->with('error', 'Request sudah diproses sebelumnya.');
    }
}
