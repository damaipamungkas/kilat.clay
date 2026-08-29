<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
<<<<<<< HEAD
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AthleteController extends Controller
{
    public function index()
=======
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
>>>>>>> 18a593e9daed664e8703aac1c40824fc1d2ce11c
    {
        $athletes = User::where('role', 'atlet')->get();
        return view('admin.athletes.index', compact('athletes'));
    }

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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fullName' => 'required|string|max:255',
        ]);

        $identifierEmail = strtolower(trim(str_replace(' ', '', $request->fullName))) . '@kilat.com';

        User::updateOrCreate(
            ['email' => $identifierEmail],
            [
                'name' => $request->name,
                'namaLengkap' => $request->fullName,
                'password' => Hash::make('password123'),
                'role' => 'atlet',
                'status' => 'Aktif'
            ]
        );

        return redirect()->back()->with('success', 'Data atlet berhasil disimpan.');
    }

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
