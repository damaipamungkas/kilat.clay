<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    /**
     * Mengambil data user dan biodata atlet dari database server dalam format JSON untuk tabel web.
     */
    public function getUsersJson()
    {
        $users = User::all()->map(function($user) {
            // 1. Ambil data atlet tautan yang tertaut dari kolom database (jika ada)
            $atletTautan = [];
            if (Schema::hasColumn('users', 'atletTautan')) {
                $atletTautan = $user->atletTautan ?? [];
            }

            // Normalisasi jika berbentuk string JSON/array tersimpan
            if (is_string($atletTautan)) {
                $atletTautan = json_decode($atletTautan, true) ?? [];
            }

            // 2. Jika role adalah parent dan atletTautan masih kosong, cari otomatis ke tabel atlets & users
            if (empty($atletTautan) && strtolower($user->role ?? '') === 'parent') {
                $cleanParentName = trim(strtolower($user->name));
                $cleanParentFullName = trim(strtolower($user->namaLengkap ?? ''));

                // A. Cari dari tabel 'atlets' di database berdasarkan kolom 'ortu' atau 'parentName'
                if (Schema::hasTable('atlets')) {
                    $atletTautan = DB::table('atlets')
                        ->get()
                        ->filter(function($ath) use ($cleanParentName, $cleanParentFullName) {
                            $ortu = trim(strtolower($ath->ortu ?? $ath->parentName ?? ''));
                            return $ortu !== '' && (
                                $ortu === $cleanParentName ||
                                $ortu === $cleanParentFullName ||
                                str_contains($ortu, $cleanParentName) ||
                                str_contains($cleanParentName, $ortu)
                            );
                        })
                        ->pluck('nickname')
                        ->toArray();
                }

                // B. Jika masih kosong, cari dari tabel User ber-role 'atlet'
                if (empty($atletTautan)) {
                    $atletTautan = User::where('role', 'atlet')
                        ->get()
                        ->filter(function($athlete) use ($cleanParentName, $cleanParentFullName) {
                            $pName = trim(strtolower($athlete->parentName ?? ''));
                            $cParent = trim(strtolower($athlete->connectedParent ?? ''));
                            return ($pName !== '' && ($pName === $cleanParentName || $pName === $cleanParentFullName)) ||
                                   ($cParent !== '' && ($cParent === $cleanParentName || $cParent === $cleanParentFullName));
                        })
                        ->pluck('name')
                        ->toArray();
                }
            }

            // 3. Ambil data khusus biodata atlet dari tabel appendix/atlet jika tersedia di database
            $biodataAtlet = null;
            if (strtolower($user->role ?? '') === 'atlet' || !empty($atletTautan)) {
                if (Schema::hasTable('atlets')) {
                    $biodataAtlet = DB::table('atlets')
                        ->where('name', $user->name)
                        ->orWhere('nickname', $user->name)
                        ->orWhere('user_id', $user->id)
                        ->first();
                }
            }

            $statusUser = 'Aktif';
            if (Schema::hasColumn('users', 'status')) {
                $statusUser = $user->status ?? 'Aktif';
            }

            return [
                'id' => $user->id,
                'namaLengkap' => $user->name,
                'username' => $user->email,
                'password' => '******',
                'role' => $user->role,
                'status' => $statusUser,
                'atletTautan' => is_array($atletTautan) ? array_values(array_unique($atletTautan)) : [],
                // Data khusus biodata atlet tambahan dari appendix
                'biodata_atlet' => $biodataAtlet ? [
                    'nik' => $biodataAtlet->nik ?? '-',
                    'gender' => $biodataAtlet->gender ?? '-',
                    'tglLahir' => $biodataAtlet->tglLahir ?? '-',
                    'alamat' => $biodataAtlet->alamat ?? '-',
                    'ortu' => $biodataAtlet->ortu ?? '-',
                    'wa' => $biodataAtlet->wa ?? '-',
                    'kelas' => $biodataAtlet->kelas ?? $biodataAtlet->kategori ?? 'PEMULA',
                    'nis' => $biodataAtlet->nis ?? '-',
                    'kategori' => $biodataAtlet->kategori ?? 'Freestyle Slalom',
                    'coach' => $biodataAtlet->coach ?? '-',
                    'jadwal' => $biodataAtlet->jadwal ?? '-'
                ] : null
            ];
        });

        return response()->json($users);
    }

    /**
     * Menyimpan akun baru dari modal web ke database server.
     */
    public function store(Request $request)
    {
        $assignedRole = strtolower(trim($request->role ?? 'parent'));

        $dataToCreate = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $assignedRole,
        ];

        if (Schema::hasColumn('users', 'status')) {
            $dataToCreate['status'] = $request->status ?? 'Aktif';
        }

        if (Schema::hasColumn('users', 'atletTautan')) {
            $dataToCreate['atletTautan'] = $request->atletTautan ?? [];
        }

        User::create($dataToCreate);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dibuat!'
        ]);
    }

    /**
     * Memperbarui data akun via manajemen user admin.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->filled('role')) {
            $rawRole = strtolower(trim($request->role));
            $user->role = ($rawRole === 'athlete') ? 'atlet' : $rawRole;
        }

        if (Schema::hasColumn('users', 'status')) {
            $user->status = $request->status ?? ($user->status ?? 'Aktif');
        }

        if ($request->has('atletTautan') && Schema::hasColumn('users', 'atletTautan')) {
            $user->atletTautan = $request->atletTautan;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Data akun berhasil diperbarui!'
        ]);
    }

    /**
     * Menghapus akun dari database server.
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil dihapus dari database!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus akun: ' . $e->getMessage()
            ], 500);
        }
    }
}
