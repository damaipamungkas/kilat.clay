public function index()
{
    // Mengambil data atlet yang terdaftar via Appendix
    $athletes = Athlete::where('status', 'aktif')->get();

    // Hitung counter summary
    $totalTerdaftar = $athletes->count();

    return view('admin.absence', compact('athletes', 'totalTerdaftar'));
}
