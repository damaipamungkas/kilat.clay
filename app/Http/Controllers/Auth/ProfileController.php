use App\Models\User;

public function index()
{
    $currentUser = auth()->user(); // atau mengambil user yang sedang login

    // Ambil data atlet yang terhubung dengan nama lengkap atau username parent ini
    // Misalnya berdasarkan kolom parentName atau connectedParent pada tabel user/atlet
    $linkedAthletes = User::where('role', 'atlet')
                          ->where(function($query) use ($currentUser) {
                              $query->where('parentName', $currentUser->name)
                                    ->orWhere('parentName', $currentUser->namaLengkap);
                          })
                          ->pluck('name') // Mengambil nama panggilan atlet
                          ->toArray();

    return view('admin.profil', [
        'user' => $currentUser,
        'atletTautan' => $linkedAthletes // Kirim ke view
    ]);
}
