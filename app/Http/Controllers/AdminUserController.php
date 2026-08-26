<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    // Mengambil data user dari database server dalam format JSON untuk tabel web
    public function getUsersJson()
    {
        $users = User::all()->map(function($user) {
            // Ambil data atlet yang benar-benar tertaut berdasarkan parentName atau connectedParent jika atletTautan kosong
            $atletTautan = $user->atletTautan ?? [];
            if (empty($atletTautan) && strtolower($user->role) === 'parent') {
                $atletTautan = User::where('role', 'atlet')
                    ->where(function($q) use ($user) {
                        $q->where('parentName', $user->name)
                          ->orWhere('parentName', $user->namaLengkap)
                          ->orWhere('connectedParent', $user->name);
                    })
                    ->pluck('name')
                    ->toArray();
            }

            return [
                'id' => $user->id,
                'namaLengkap' => $user->name,
                'username' => $user->email,
                'password' => '******',
                'role' => $user->role,
                'status' => $user->status ?? 'Aktif',
                'atletTautan' => is_array($atletTautan) ? $atletTautan : (json_decode($atletTautan, true) ?? [])
            ];
        });
        return response()->json($users);
    }

    // Menyimpan akun baru dari modal web ke database server
    public function store(Request $request)
    {
        // Tangkap role secara mutlak sesuai pilihan form web
        $assignedRole = strtolower(trim($request->role ?? 'parent'));

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $assignedRole,
            'status' => $request->status ?? 'Aktif',
            'atletTautan' => $request->atletTautan ?? []
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dibuat!'
        ]);
    }

    // Memperbarui data akun via manajemen user admin
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

        $user->status = $request->status ?? $user->status;

        if ($request->has('atletTautan')) {
            $user->atletTautan = $request->atletTautan;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Data akun berhasil diperbarui!'
        ]);
    }

    // Menghapus akun dari database server (Menyelesaikan error tombol hapus)
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
