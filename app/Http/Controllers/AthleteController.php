<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Athlete;
use App\Models\AthleteEditRequest; // Pastikan Anda membuat model & migration untuk tabel ini
use Illuminate\Support\Facades\Auth;

class AthleteController extends Controller
{
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
