<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Athlete;
use App\Models\AthleteEditRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AthleteController extends Controller
{
    /**
     * Menampilkan daftar data atlet.
     */
    public function index()
    {
        $athletes = User::where('role', 'atlet')->get();
        return view('admin.athletes.index', compact('athletes'));
    }

    /**
     * Menyimpan data atlet dari halaman Appendix beserta penautan otomatis ke akun Parent.
     */
    public function storeFromAppendix(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'      => 'required|string|max:255',      // Nama Panggilan Atlet
                'fullName'  => 'required|string|max:255',      // Nama Lengkap Atlet
                'parent'    => 'nullable|string|max:255',      // Nama Wali / Parent
                'nik'       => 'nullable|string|max:30',
                'gender'    => 'nullable|string|max:20',
                'tglLahir'  => 'nullable|string|max:30',
                'alamat'    => 'nullable|string',
                'kelas'     => 'nullable|string|max:50',
                'status'    => 'nullable|string|max:50',
                'wa'        => 'nullable|string|max:30'
            ]);

            // 1. Simpan/Update data user atlet
            $identifierEmail = strtolower(trim(str_replace(' ', '', $validated['fullName']))) . '@kilat.com';

            $athleteUser = User::updateOrCreate(
                ['email' => $identifierEmail],
                [
                    'name'       => $validated['name'],
                    'namaLengkap'=> $validated['fullName'],
                    'password'   => Hash::make($validated['nik'] ?? 'password123'),
                    'role'       => 'atlet',
                    'status'     => $validated['status'] ?? 'Aktif',
                    'wa'         => $validated['wa'] ?? null,
                    'kelas'      => $validated['kelas'] ?? 'PEMULA',
                    'parentName' => $validated['parent'] ?? null,
                ]
            );

            // 2. TAUTKAN NAMA ATLET KE AKUN PARENT YANG SESUAI DI DATABASE
            if (!empty($validated['parent'])) {
                $parentNameInput = trim($validated['parent']);

                // Cari akun parent berdasarkan name, namaLengkap, atau email
                $parentUser = User::where('role', 'parent')
                    ->where(function($q) use ($parentNameInput) {
                        $q->where('name', 'LIKE', "%{$parentNameInput}%")
                          ->orWhere('namaLengkap', 'LIKE', "%{$parentNameInput}%")
                          ->orWhere('email', 'LIKE', "%{$parentNameInput}%");
                    })->first();

                if ($parentUser) {
                    $currentTautan = $parentUser->atletTautan ?? [];
                    if (is_string($currentTautan)) {
                        $currentTautan = json_decode($currentTautan, true) ?? [];
                    }

                    // Jika nama panggilan atlet belum ada di daftar, masukkan
                    if (!in_array($validated['name'], $currentTautan)) {
                        $currentTautan[] = $validated['name'];
                        $parentUser->atletTautan = $currentTautan;
                        $parentUser->save();
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Data atlet dan tautan parent berhasil disimpan ke database server.',
                'data'    => $athleteUser
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan atlet dari appendix: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan data atlet baru secara umum (reguler).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fullName' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|min:6',
        ]);

        $identifierEmail = $request->email ?? (strtolower(trim(str_replace(' ', '', $request->fullName))) . '@kilat.com');

        User::updateOrCreate(
            ['email' => $identifierEmail],
            [
                'name' => $request->name,
                'namaLengkap' => $request->fullName,
                'password' => Hash::make($request->password ?? 'password123'),
                'role' => 'atlet',
                'status' => 'Aktif'
            ]
        );

        return redirect()->back()->with('success', 'Data atlet berhasil disimpan.');
    }

    /**
     * Memproses permintaan edit data atlet.
     */
    public function updateRequest(Request $request, $id)
    {
        // Logika pemrosesan permintaan edit atlet
        $athlete = User::where('role', 'atlet')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan update berhasil diproses.'
        ]);
    }

    /**
     * Menghapus data atlet berdasarkan ID.
     */
    public function destroy($id)
    {
        $athlete = User::where('role', 'atlet')->where('id', $id)->first();

        if ($athlete) {
            $athlete->delete();
            return response()->json(['success' => true, 'message' => 'Data atlet berhasil dihapus dari database.']);
        }

        return response()->json(['success' => false, 'message' => 'Atlet tidak ditemukan.'], 404);
    }
}
