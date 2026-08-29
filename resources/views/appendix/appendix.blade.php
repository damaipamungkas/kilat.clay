@php
    // 1. Ambil data user yang sedang login
    $user = auth()->user();

    // 2. Ambil role user & id (normalisasi ke huruf kecil & hapus spasi berlebih)
    $rawRole = $user ? strtolower(trim($user->role ?? '')) : '';
    $currentUserId = auth()->id() ?? '';

    // Jika nama atau email atau role mengandung kata admin, paksa jadi admin penuh
    $userName = $user ? strtolower(trim($user->name ?? $user->username ?? '')) : '';
    if (str_contains($userName, 'admin') || str_contains($rawRole, 'admin')) {
        $rawRole = 'admin';
    }

    // 3. Peta penyesuaian/normalisasi variasi nama role
    $roleMap = [
        'orang tua' => 'parent',
        'orangtua'  => 'parent',
        'wali'      => 'parent',
        'walimurid' => 'parent',
        'pelatih'   => 'coach',
    ];

    $role = $roleMap[$rawRole] ?? $rawRole;

    // 4. Daftar role yang diizinkan masuk
    $allowedRoles = ['admin', 'coach', 'parent'];

    // 5. Pengalihan aman menggunakan penanganan Laravel jika belum login / role tidak sesuai
    if (!$user || !in_array($role, $allowedRoles)) {
        echo "<script>window.location.href = '" . route('login') . "';</script>";
        exit();
    }

    // 6. Ambil data atlet dari database yang role-nya 'atlet' atau 'athlete' dan sudah AKTIF
    $athletes = $athletes ?? \App\Models\User::whereIn('role', ['atlet', 'athlete'])->where(function($query) {
        $query->where('status', 'Aktif')->orWhereNull('status');
    })->get();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Progress Data Atlet - KILAT (Claymorphism)</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/appendix.css') }}">
    <style>
        /* Menyesuaikan ukuran label pencarian atlet agar seukuran dengan input pencarian */
.search-title-label {
    font-size: 11px;
    font-weight: 900;
    color: var(--text-dark);
    display: inline-flex;
    align-items: center;
}

/* --- CLAYMORPHISM VARIABLES --- */
:root {
    --bg-main: #ebe5ee;
    --sidebar-bg: #7b61ff;
    --text-dark: #2a2245;
    --text-gray: #6b6288;

    --clay-purple: #c8b8ff;
    --clay-pink: #ffb8c6;
    --clay-yellow: #ffda85;
    --clay-blue: #a3d5ff;
    --clay-green: #a8e6a1;
    --clay-orange: #ffd7b5;

    --c-hadir: #50b054; /* Hijau Soft */
    --c-izin: #3b82f6;
    --c-sakit: #ffc977; /* Kuning Soft */
    --c-alpa: #ff6b81; /* Merah Soft */
    --clay-shadow-card:
        8px 8px 16px rgba(150, 140, 170, 0.5),
        inset 6px 6px 12px rgba(255, 255, 255, 0.8),
        inset -6px -6px 16px rgba(0, 0, 0, 0.08);
    --clay-shadow-btn:
        3px 3px 6px rgba(150, 140, 170, 0.5),
        inset 3px 3px 6px rgba(255, 255, 255, 0.8),
        inset -3px -3px 6px rgba(0, 0, 0, 0.08);
    --clay-shadow-inset:
        inset 6px 6px 12px rgba(150, 140, 170, 0.6),
        inset -6px -6px 12px rgba(255, 255, 255, 0.9);
    --text-timbul-dark: 1px 1px 0px #ffffff, 2px 2px 4px rgba(150, 140, 170, 0.6);
    --text-timbul-light: 1px 1px 2px rgba(0, 0, 0, 0.2);

    --bg-color-1: var(--bg-main);
    --bg-color-2: var(--clay-purple);
}

body {
    background-color: var(--bg-main);
    font-family: 'Nunito', Arial, sans-serif;
    font-size: 12px;
    margin: 0;
    padding: 10px;
    color: var(--text-dark);
    min-height: 100vh;
    overflow-x: hidden;
    overflow-y: auto;
    position: relative;
}

body::before {
    content: "";
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    z-index: -1;
    background-image: linear-gradient(135deg, var(--bg-color-1) 0%, var(--bg-color-2) 100%);
    transition: background 0.1s ease;
}

/* --- TOP BAR --- */
.top-bar {
    background-color: var(--clay-blue);
    max-width: 99%; margin: 0 auto 10px auto;
    padding: 8px 15px; border-radius: 15px;
    box-shadow: var(--clay-shadow-card);
    display: flex; justify-content: space-between;
    align-items: center; flex-wrap: wrap; gap: 10px;
}

.search-section {
    display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
}

.clay-input-top, .clay-select-top {
    background: var(--bg-main); border: none;
    padding: 4px 8px; border-radius: 8px;
    box-shadow: var(--clay-shadow-inset); outline: none;
    font-weight: 800; font-family: inherit; font-size: 11px;
    color: var(--text-dark); cursor: pointer;
}
.clay-input-top { cursor: text; width: 120px; }

.role-section { display: flex; align-items: center; gap: 10px; flex-wrap: wrap;}
.role-badge {
    background: var(--clay-purple); color: var(--sidebar-bg);
    padding: 4px 10px; border-radius: 8px;
    font-weight: 900; box-shadow: var(--clay-shadow-inset); font-size: 11px;
    display: inline-flex; align-items: center; gap: 5px;
}

.mode-toggle-btn {
    background: var(--clay-yellow); color: var(--text-dark);
    border: none; padding: 4px 10px; border-radius: 8px;
    box-shadow: var(--clay-shadow-btn); font-weight: 900; font-size: 11px; cursor: pointer;
    transition: 0.3s;
}
.mode-toggle-btn:hover { transform: scale(1.05); }
.mode-toggle-btn.mode-massal { background: var(--c-alpa); color: white; text-shadow: var(--text-timbul-light); }

.btn-settings, .btn-home {
    background: var(--clay-green); color: var(--text-dark);
    border: none; padding: 4px 10px; border-radius: 8px;
    box-shadow: var(--clay-shadow-btn); font-weight: 800; font-size: 11px; cursor: pointer; transition: 0.2s;
    text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
}
.btn-home { background: var(--clay-yellow); }
.btn-settings:hover, .btn-home:hover { transform: scale(1.05); }

/* --- FRAME UTAMA --- */
.page-container {
    background-color: var(--clay-yellow); max-width: 98%;
    margin: 0 auto; padding: 15px 20px 40px 20px;
    border-radius: 25px; box-shadow: var(--clay-shadow-card);
    max-height: 85vh; overflow-y: auto;
}
.page-container::-webkit-scrollbar { width: 5px; }
.page-container::-webkit-scrollbar-thumb { background: rgba(150, 140, 170, 0.4); border-radius: 10px; }

/* --- HEADER --- */
.header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; border-bottom: 2px dashed rgba(120, 100, 200, 0.3); padding-bottom: 10px; }
.logo-area { display: flex; flex-direction: column; align-items: flex-start; }
.logo-frame { max-height: 90px; max-width: 250px; object-fit: contain; border-radius: 5px; box-shadow: var(--clay-shadow-btn); }
.title-area { text-align: right; }
.title-area h1 { margin: 0; font-size: 18px; font-weight: 900; color: var(--text-dark); text-shadow: var(--text-timbul-dark); }
.title-area h2 { margin: 0; font-size: 12px; font-weight: 800; color: var(--text-gray); text-transform: uppercase; }

/* --- BIODATA SECTION --- */
.bio-actions { display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 5px; flex-wrap: wrap;}
.biodata-table { width: 100%; border-collapse: separate; border-spacing: 0 4px; margin-bottom: 5px; font-weight: 800; font-size: 11px; }
.biodata-table td { padding: 6px 10px; position: relative; }
.biodata-table td:first-child { width: 1%; white-space: nowrap; background-color: var(--clay-purple); border-radius: 8px 0 0 8px; box-shadow: var(--clay-shadow-inset); color: var(--sidebar-bg); padding: 4px 8px; }
.biodata-table td:last-child { background-color: var(--bg-main); border-radius: 0 8px 8px 0; box-shadow: var(--clay-shadow-inset); }

.bio-input {
    border: none; width: 100%; font-family: inherit; font-weight: 900; font-size: 11px;
    text-transform: uppercase; background: transparent; color: var(--text-dark);
    outline: none; padding: 2px; border-radius: 5px; transition: 0.3s;
}
.bio-input:focus { background: rgba(255,255,255,0.5); box-shadow: var(--clay-shadow-btn); }
.bio-input[readonly], .bio-input:disabled { cursor: not-allowed; opacity: 0.8; }
select.bio-input:disabled { appearance: none; -webkit-appearance: none; color: var(--text-gray); }

.wa-btn { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background-color: var(--c-hadir); color: white; border: none; padding: 4px 10px; border-radius: 6px; cursor: pointer; font-weight: 900; text-decoration: none; display: none; box-shadow: var(--clay-shadow-btn); font-size: 10px; }

/* --- TABS --- */
.curriculum-select-container { display: flex; justify-content: center; gap: 5px; margin-bottom: 5px; flex-wrap: wrap; }
.curr-btn { padding: 6px 12px; background-color: var(--bg-main); color: var(--text-gray); border: none; border-radius: 100px; font-weight: 900; font-size: 11px; cursor: pointer; transition: 0.3s; text-transform: uppercase; box-shadow: var(--clay-shadow-btn); }
.curr-btn.active { background-color: var(--sidebar-bg); color: white; box-shadow: var(--clay-shadow-inset); text-shadow: var(--text-timbul-light); }
#btnToggleLock { background: var(--c-hadir); color: white; margin-left: 5px; }

/* --- MAIN MATRIX TABLE --- */
.table-responsive { width: 100%; overflow-x: auto; margin-bottom: 5px; padding-bottom: 5px; }
.table-responsive::-webkit-scrollbar { height: 5px; width: 5px;}
.table-responsive::-webkit-scrollbar-thumb { background: rgba(150, 140, 170, 0.4); border-radius: 10px; }
.table-responsive.is-locked { max-height: 55vh; overflow-y: auto; }

.matrix-table { width: 100%; border-collapse: separate; border-spacing: 3px; text-align: center; text-transform: uppercase; font-weight: 800; table-layout: auto; }

.matrix-table th {
    background-color: var(--sidebar-bg); color: white !important; font-size: 10px; font-weight: 900;
    box-shadow: var(--clay-shadow-btn); text-shadow: var(--text-timbul-light);
    border: none; border-radius: 5px; padding: 6px 4px; vertical-align: middle;
    resize: horizontal;
    overflow: hidden;
    min-width: 45px;
    max-width: 300px;
    white-space: normal;
    word-wrap: break-word;
}

.matrix-table td {
    border: none; border-radius: 5px; padding: 6px 4px; vertical-align: middle; min-height: 24px; font-size: 10px;
    color: var(--text-dark) !important; box-shadow: var(--clay-shadow-inset); background: var(--bg-main); transition: all 0.1s ease;
    white-space: normal;
    word-break: break-word;
}

.matrix-table.is-locked th { position: sticky; top: -1px; z-index: 10; }
.col-num { width: 35px; background-color: var(--clay-pink) !important; font-weight: 900; box-shadow: var(--clay-shadow-btn) !important;}
.col-family { width: 60px !important; background-color: var(--clay-blue) !important; font-weight: 900; box-shadow: var(--clay-shadow-btn) !important;}
.level-boundary td { position: relative; }
.level-boundary td::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: rgba(120, 100, 200, 0.3); border-radius: 1px; }
.clickable-cell { cursor: pointer; box-shadow: var(--clay-shadow-btn) !important; }
.clickable-cell:hover { transform: scale(1.03); z-index: 5; position: relative;}
.speed-table td { font-size: 12px; padding: 8px; }

/* === FOOTER === */
.footer {
    display: flex; justify-content: space-between; align-items: center;
    padding: 20px 25px; border-radius: 25px; background: var(--bg-main);
    border: none; box-shadow: var(--clay-shadow-card);
    font-size: 0.9rem; color: var(--text-muted); font-weight: 800;
    margin-top: 5px; margin-bottom: 5px;
    flex-shrink: 0;
}
.logo-box { padding: 6px 16px; font-weight: 900; font-size: 1.2rem; color: var(--primary-color); background: var(--bg-main); border-radius: 15px; text-align: center; border: none; box-shadow: var(--clay-shadow-inset); }

/* --- FOOTER / KETERANGAN --- */
.footer-note { background-color: var(--clay-green); font-weight: 900; font-size: 10px; text-align: center; padding: 8px; color: var(--text-dark); border-radius: 10px; box-shadow: var(--clay-shadow-inset); margin-top: 5px; margin-bottom: 5px; flex-shrink: 0; }
.bottom-section { display: flex; gap: 15px; align-items: stretch; flex-wrap: wrap; margin-top: 5px; flex-shrink: 0; }
.legend-table { border-collapse: separate; border-spacing: 2px; width: 200px; font-weight: 900; text-align: center; font-size: 10px; }
.legend-table td { padding: 6px; border-radius: 6px; box-shadow: var(--clay-shadow-btn); }
.legend-title { width: 90px; background-color: var(--clay-purple); color: var(--sidebar-bg); }
.bg-belum { background-color: #ffffff !important; box-shadow: var(--clay-shadow-inset) !important;}
.bg-ulangi { background-color: var(--c-alpa) !important; color: white !important; }
.bg-progress { background-color: var(--c-sakit) !important; color: var(--text-dark) !important;}
.bg-lancar { background-color: var(--clay-blue) !important; }
.bg-master { background-color: var(--c-hadir) !important; color: white !important;}

.analysis-box { flex-grow: 1; padding: 10px 14px; border-radius: 15px; background: var(--clay-pink); box-shadow: var(--clay-shadow-inset); display: flex; flex-direction: column; min-width: 250px;}
.analysis-box span { font-weight: 900; margin-bottom: 5px; color: var(--sidebar-bg); font-size: 11px;}
#analysisTextarea { width: 100%; flex-grow: 1; border: none; resize: none; font-weight: 700; font-family: inherit; font-size: 11px; background: transparent; color: var(--text-dark); }
#analysisTextarea:focus { outline: none; }

/* --- MODAL POPUP --- */
.modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(42, 34, 69, 0.4); backdrop-filter: blur(5px); }
.modal-content { background-color: var(--clay-blue); margin: 5% auto; padding: 20px; width: 90%; max-width: 400px; border-radius: 20px; box-shadow: var(--clay-shadow-card); font-family: 'Nunito', sans-serif; font-size: 11px; position: relative; }
.close-btn { position: absolute; top: 15px; right: 20px; color: var(--text-gray); font-size: 22px; font-weight: bold; cursor: pointer; transition: 0.2s; }
.close-btn:hover { color: var(--c-alpa); transform: scale(1.1);}
.modal-content h3 { margin-top: 0; border-bottom: 2px dashed rgba(150, 140, 170, 0.5); padding-bottom: 10px; font-size: 15px; font-weight: 900; color: var(--sidebar-bg);}

.form-group { margin-bottom: 10px; }
.form-group label { display: block; font-weight: 800; margin-bottom: 5px; color: var(--text-gray);}
.form-group input, .form-group select { width: 100%; padding: 8px 10px; box-sizing: border-box; border: none; border-radius: 8px; font-family: inherit; font-weight: 800; font-size:11px; background: var(--bg-main); box-shadow: var(--clay-shadow-inset); outline: none; color: var(--text-dark); }
.yt-input-group { display: flex; gap: 8px; }
.yt-input-group input { flex-grow: 1; }
.yt-view-btn { background-color: var(--c-alpa); color: white; border: none; border-radius: 8px; padding: 0 10px; cursor: pointer; font-weight: 900; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: var(--clay-shadow-btn); font-size: 10px; }
.submit-btn { background-color: var(--c-hadir); color: white; padding: 10px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-weight: 900; font-size: 12px; margin-top: 5px; box-shadow: var(--clay-shadow-btn); transition: 0.2s; text-shadow: var(--text-timbul-light); }
.submit-btn:hover { filter: brightness(1.05); transform: translateY(-2px); }

/* History Items */
.history-container { margin-top: 5px; border-top: 2px dashed rgba(150, 140, 170, 0.5); padding-top: 10px; }
.history-list { max-height: 120px; overflow-y: auto; padding-right: 5px; }
.history-list::-webkit-scrollbar { width: 4px; }
.history-list::-webkit-scrollbar-thumb { background: rgba(150, 140, 170, 0.4); border-radius: 10px; }
.history-item { padding: 8px; margin-bottom: 5px; border-radius: 10px; background: var(--bg-main); box-shadow: var(--clay-shadow-inset); display: flex; justify-content: space-between; align-items: center; }
.history-details { flex-grow: 1; font-weight: 700; color: var(--text-dark);}
.history-details strong { font-weight: 900; }
.yt-link { color: var(--c-alpa); text-decoration: none; font-weight: 900; display: inline-flex; align-items: center; margin-top: 5px; font-size: 9px; background: white; padding: 3px 6px; border-radius: 6px; box-shadow: var(--clay-shadow-btn); }
.delete-history-btn { background-color: var(--c-alpa); color: white; border: none; padding: 4px 8px; border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 900; margin-left: 5px; box-shadow: var(--clay-shadow-btn); transition: 0.2s; }
.delete-history-btn:hover { transform: scale(1.05); }

/* Speed History */
.speed-history-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 5px; }
.speed-history-box { background: var(--clay-green); padding: 10px; border-radius: 15px; box-shadow: var(--clay-shadow-inset); }
.speed-history-box h4 { margin: 0 0 10px 0; font-size: 11px; color: var(--sidebar-bg); border-bottom: 1px dashed rgba(120, 100, 200, 0.3); padding-bottom: 5px; }

/* --- STYLES MASS ASSESSMENT --- */
.mass-athlete-list { max-height: 45vh; overflow-y: auto; margin-top: 5px; border-top: 2px dashed rgba(150, 140, 170, 0.5); padding-top: 10px; }
.mass-athlete-item { display: flex; flex-direction: column; background: var(--clay-yellow); padding: 10px; margin-bottom: 5px; border-radius: 12px; box-shadow: var(--clay-shadow-inset); gap: 8px; }
.mass-athlete-header { display: flex; align-items: center; gap: 8px; font-weight: 900; color: var(--text-dark); font-size: 11px; }
.mass-check { width: 14px; height: 14px; cursor: pointer; }

/* Perbaikan Kotak Filter Massal agar Selalu Sejajar Mendatar / Horizontal */
.mass-filter-controls {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 6px;
    width: 100%;
    box-sizing: border-box;
}
.mass-filter-controls .clay-select-top {
    flex: 1;
    min-width: 0;
    width: auto;
}

.quick-btn-group-10 { display: flex; flex-wrap: wrap; gap: 5px; }
.quick-btn-10 {
    border: none; width: 26px; height: 26px; border-radius: 6px;
    cursor: pointer; box-shadow: var(--clay-shadow-btn); transition: 0.2s;
    font-weight: 900; font-size: 11px; display: flex; justify-content: center; align-items: center;
}
.quick-btn-10:active { transform: scale(0.9); }
.quick-btn-10.selected {
    transform: scale(1.15);
    box-shadow: inset 2px 2px 4px rgba(0,0,0,0.3), inset -2px -2px 4px rgba(255,255,255,0.7);
    border: 2px solid var(--text-dark);
}

.quick-btn-qual {
    border: none; padding: 5px 10px; border-radius: 6px;
    cursor: pointer; box-shadow: var(--clay-shadow-btn); transition: 0.2s;
    font-weight: 900; font-size: 10px; display: inline-flex; justify-content: center; align-items: center;
}
.quick-btn-qual.selected {
    transform: scale(1.05);
    box-shadow: inset 2px 2px 4px rgba(0,0,0,0.3), inset -2px -2px 4px rgba(255,255,255,0.7);
    border: 2px solid var(--text-dark);
}

.c-0 { background-color: #ffffff; color: var(--text-dark); }
.c-red { background-color: var(--c-alpa); color: white; }
.c-yel { background-color: var(--c-sakit); color: var(--text-dark); text-shadow: 1px 1px 1px rgba(255,255,255,0.5);}
.c-grn { background-color: var(--c-hadir); color: white; }

/* --- GLOBAL STYLING UNTUK TOMBOL CETAK & AKSI ATLET DI HTML ANDA --- */
.bio-actions a,
.bio-actions button {
    font-family: 'Nunito', sans-serif;
    font-size: 11px;
    font-weight: 900;
    padding: 6px 12px;
    border-radius: 100px;
    border: none;
    cursor: pointer;
    box-shadow: var(--clay-shadow-btn);
    transition: 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.bio-actions a:hover,
.bio-actions button:hover {
    transform: scale(1.05);
    filter: brightness(1.05);
}

.bio-actions a:active,
.bio-actions button:active {
    box-shadow: var(--clay-shadow-inset);
    transform: scale(0.98);
}

.bio-actions button[onclick*="print"] {
    background-color: var(--clay-blue);
    color: var(--text-dark);
}

@media (max-width: 600px) {
    .speed-history-wrapper { grid-template-columns: 1fr; }
    .quick-btn-10 { width: 22px; height: 22px; font-size: 9px;}
}

@media print {
    body * {
        visibility: hidden;
    }
    #print-area, #print-area * {
        visibility: visible;
    }
    #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}

.analysis-legend-container {
    display: flex;
    flex-direction: row;
    gap: 15px;
    width: 100%;
    align-items: stretch;
    margin-top: 5px;
    margin-bottom: 5px;
    box-sizing: border-box;
    flex-shrink: 0;
}

.legend-wrapper {
    display: flex;
    flex-direction: column;
    width: fit-content;
    flex-shrink: 0;
}

.legend-table {
    width: auto;
    border-collapse: collapse;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--clay-shadow-card);
    font-size: 11px;
    white-space: nowrap;
}

.legend-table th {
    background: var(--sidebar-bg);
    color: white;
    text-align: center;
    font-weight: 900;
    padding: 6px 10px;
    font-size: 11px;
}

.legend-table td {
    padding: 6px 10px;
    font-weight: 800;
    border: 1px solid #eee;
}

.dot {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    margin-right: 5px;
    vertical-align: middle;
}
.dot-white { background: #ffffff; border: 1px solid #ccc; }
.dot-red   { background: #ff6b81; border: 1px solid #fff; }
.dot-yellow{ background: #ffc977; border: 1px solid #fff; }
.dot-green { background: #50b054; border: 1px solid #fff; }

.bg-white  { background: #ffffff; color: #333; }
.bg-red    { background: #ff6b81; color: white; }
.bg-yellow { background: #ffc977; color: #333; }
.bg-green  { background: #50b054; color: white; }

.analysis-box {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.analysis-box span {
    font-size: 11px;
    font-weight: 800;
    margin-bottom: 5px;
}

.analysis-textarea {
    width: 100%;
    flex: 1;
    min-height: 60px;
    font-size: 11px;
    padding: 8px 12px;
    resize: vertical;
    box-sizing: border-box;
}

.analysis-textarea:read-only {
    background-color: #f5f5f5;
    cursor: not-allowed;
}
    </style>
</head>
<body>

<div class="top-bar">
    <div class="search-section">
        <span class="search-title-label" id="searchLabelTitle">🔍 PENCARIAN ATLET:</span>
        <input type="text" id="searchAthlete" class="clay-input-top" placeholder="Ketik Nama...">

        <!-- SELECT NAMA LENGKAP -->
        <select id="athleteSelectFullName" title="Pilih Nama Lengkap" class="clay-select-top">
            <option value="">-- NAMA LENGKAP --</option>
            @foreach($athletes as $athlete)
                <option value="{{ $athlete->id }}" data-fullname="{{ $athlete->email }}" data-nickname="{{ $athlete->name }}">
                    {{ strtoupper($athlete->email) }}
                </option>
            @endforeach
        </select>

        <!-- SELECT NAMA PANGGILAN -->
        <select id="athleteSelect" title="Pilih Nama Panggilan" class="clay-select-top" style="display:none;">
            <option value="">-- PANGGILAN --</option>
            @foreach($athletes as $athlete)
                <option value="{{ $athlete->id }}">
                    {{ strtoupper($athlete->name) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="role-section">
        <span class="role-badge" id="roleInfoBadge">👤 AKSES AKUN: <span id="roleLabelDisplay" class="role-display-text">{{ strtoupper($role) }}</span></span>

        @if($role === 'admin')
            <a href="{{ route('admin.index') }}" class="btn-home btn-orange" title="Menu Admin" id="btnPanelAdmin"><i class="fa-solid fa-gauge-high"></i> Panel Admin</a>
        @endif

        @if(in_array($role, ['admin', 'coach']))
            <button id="btnModeToggle" class="mode-toggle-btn mode-massal">📋 MODE: MASSAL AKTIF</button>
        @endif

        <a href="{{ route('profil') }}" class="btn-home" title="Profil Akun"><i class="fa-solid fa-user"></i> Profil Akun</a>

        @if($role === 'admin')
            <button id="btnSettings" class="btn-settings">⚙️ Pengaturan</button>
        @endif
    </div>
</div>

<div class="page-container" id="mainContainer">

    <div class="header">
        <div class="logo-area" id="logoClubContainer">
            <img id="displayLogo" src="https://via.placeholder.com/250x90/7b61ff/ffffff?text=LOGO+KILAT" alt="Logo Club" class="logo-frame">
        </div>
        <div class="title-area">
            <h1>PROGRESS DATA ATLET</h1>
            <h2 id="displayClubNameSub">KEDIRI INLINE SKATE SCHOOL</h2>
        </div>
    </div>

    <!-- TOMBOL AKSI: TERSEDIA UNTUK ADMIN DAN PARENT, BESERTA TOMBOL CETAK YANG SELALU MUNCUL -->
    <div class="bio-actions" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
        @if(in_array($role, ['admin', 'parent']))
            <button type="button" onclick="openAthleteFormModal('add')" class="curr-btn btn-green" style="background-color: var(--c-hadir); color: white;"><i class="fa-solid fa-user-plus"></i> Tambah Atlet</button>
            <button type="button" onclick="openAthleteFormModal('edit')" class="curr-btn btn-yellow" style="background-color: var(--clay-yellow); color: var(--text-dark);"><i class="fa-solid fa-user-pen"></i> Edit Atlet</button>
            <button type="button" onclick="deleteActiveAthlete()" class="curr-btn btn-red" style="background-color: var(--c-alpa); color: white;"><i class="fa-solid fa-user-minus"></i> Hapus Atlet</button>
        @endif
        <button onclick="window.print()" class="curr-btn btn-blue" style="display: inline-block !important;">🖨️ Cetak</button>
    </div>

    <div id="print-area">
        <table class="biodata-table">
            <tr><td>NIK</td><td> <input type="text" id="bioNIK" class="bio-input" placeholder="-" readonly></td></tr>
            <tr><td>NAMA LENGKAP</td><td> <input type="text" id="athleteFullName" class="bio-input" placeholder="-" readonly></td></tr>
            <tr><td>NAMA PANGGILAN</td><td> <input type="text" id="athleteName" class="bio-input" placeholder="-" readonly></td></tr>
            <tr>
                <td>JENIS KELAMIN</td>
                <td>
                    <select id="bioGender" class="bio-input" disabled>
                        <option value="">- PILIH JENIS KELAMIN -</option>
                        <option value="L">LAKI-LAKI</option>
                        <option value="P">PEREMPUAN</option>
                    </select>
                </td>
            </tr>
            <tr><td>TANGGAL LAHIR</td><td> <input type="date" id="bioTglLahir" class="bio-input" readonly></td></tr>
            <tr><td>ALAMAT LENGKAP</td><td> <input type="text" id="bioAlamat" class="bio-input" placeholder="-" readonly></td></tr>
            <tr><td>NAMA WALI</td><td> <input type="text" id="bioOrtu" class="bio-input" value="{{ strtoupper($user->name ?? $user->username ?? '') }}" placeholder="-" readonly></td></tr>
            <tr>
                <td>WHATSAPP</td>
                <td>
                    <input type="text" id="bioWA" class="bio-input" placeholder="Cth: 08123456789" readonly>
                    <a href="#" id="waBtn" class="wa-btn" target="_blank" style="display:none;">Hubungi WA</a>
                </td>
            </tr>
            <tr>
                <td>KELAS</td>
                <td>
                    <select id="bioKelas" class="bio-input" disabled>
                        <option value="">- PILIH KELAS -</option>
                        <option value="PEMULA">PEMULA</option>
                        <option value="JUNIOR 1">JUNIOR 1</option>
                        <option value="JUNIOR 2">JUNIOR 2</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>STATUS AKUN</td>
                <td>
                    <select id="bioStatus" class="bio-input" disabled>
                        <option value="Aktif">AKTIF</option>
                        <option value="Arsip">ARSIP</option>
                    </select>
                </td>
            </tr>
        </table>

        <div class="curriculum-select-container">
            <button class="curr-btn active" data-target="classicSlalomView">CLASSIC SLALOM</button>
            <button class="curr-btn" data-target="freestyleSlideView">FREESTYLE SLIDE</button>
            <button class="curr-btn" data-target="speedSlalomView">SPEED SLALOM</button>
            <button class="curr-btn" data-target="beginner'sTestView">BEGINNER</button>
            <button id="btnToggleLock" class="curr-btn">🔒 Header Terkunci</button>
        </div>

        <!-- CLASSIC SLALOM VIEW -->
        <div id="classicSlalomView" class="curriculum-view">
            <div class="table-responsive is-locked">
                <table class="matrix-table is-locked">
                    <thead><tr><th colspan="2">MARK & FAMILIES</th><th colspan="2">OTHER</th><th colspan="2">SITTING</th><th colspan="2">JUMPING</th><th colspan="2">WHEELINGS</th><th colspan="2">SPINNING</th></tr></thead>
                    <tbody>
                        <tr><td rowspan="11" class="col-family">A (50-60)</td>
                        <tr><td class="col-num">10</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">TOE FOOTGUN SPIN</td></tr>
                        <tr><td class="col-num">9</td><td colspan="2"></td><td colspan="2">BACK TOE CHRISTIE SPIN</td><td colspan="2">TOE FOOTGUN WIPER</td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr><td class="col-num">8</td><td colspan="2"></td><td colspan="2">TOE CHRISTIE SPIN</td><td colspan="2">HEEL WIPER</td><td>BACKWARD SQUARE</td><td>LEAF SERIES</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">7</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">BACK SEVEN INT/EXT</td></tr>
                        <tr><td class="col-num">6</td><td colspan="2"></td><td colspan="2">BACK TOE FOOTGUN SPIN</td><td colspan="2">TOE WIPER</td><td>FORWARD SQUARE</td><td>LEAF SERIES</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">5</td><td colspan="2"></td><td colspan="2">TOE FOOTGUN SPIN</td><td colspan="2"></td><td colspan="2"></td><td colspan="2">ONE CONE BACK SEVEN INT/EXT</td></tr>
                        <tr><td class="col-num">4</td><td colspan="2"></td><td colspan="2">BACK TOE WHEELING JUMP</td><td colspan="2"></td><td colspan="2">STAR</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">3</td><td colspan="2"></td><td colspan="2">BACK TOE CHRISTIE</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr><td class="col-num">2</td><td colspan="2"></td><td colspan="2"></td><td colspan="2">TOE WHEELING JUMP</td><td>SIDE SQUARE</td><td>LEAF SERIES</td><td colspan="2">SEVEN INT/EXT</td></tr>
                        <tr class="level-boundary"><td class="col-num">1</td><td colspan="2"></td><td colspan="2">TOE CHRISTIE</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td></tr>

                        <tr><td rowspan="11" class="col-family">B (40-50)</td>
                        <tr><td class="col-num">10</td><td colspan="2">BUTTERFLY CROSS</td><td colspan="2">1 CONE TOE FOOTGUN SPIN</td><td colspan="2"></td><td>WHEELING SHIFTS</td><td>COUNTERSHIFTS SERIES</td><td colspan="2">ONE CONE SEVEN INT/EXT</td></tr>
                        <tr><td class="col-num">9</td><td colspan="2"></td><td colspan="2"></td><td colspan="2">FOOTGUN SPIN JUMP</td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr><td class="col-num">8</td><td colspan="2"></td><td>BACK TEAPOT</td><td>BACK SUPERMAN</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr><td class="col-num">7</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td>WHEELING FLIP</td><td>DAYNIGHT SERIES</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">6</td><td colspan="2">BUTTERFLY</td><td colspan="2">BACK TOE FOOTGUN</td><td colspan="2">FOOTGUN WIPER</td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr><td class="col-num">5</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">FLAT BACK SEVEN INT/EXT</td></tr>
                        <tr><td class="col-num">4</td><td colspan="2"></td><td>TEAPOT</td><td>SUPERMAN</td><td colspan="2"></td><td colspan="2">FRENCH SHIFTS SERIES</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">3</td><td colspan="2"></td><td colspan="2"></td><td colspan="2">BACK KAZAKCHOK</td><td colspan="2"></td><td colspan="2">FLAT SEVEN INT/EXT</td></tr>
                        <tr><td class="col-num">2</td><td colspan="2">TOE TOE REVERSE EAGLE</td><td colspan="2">TOE FOOTGUN</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr class="level-boundary"><td class="col-num">1</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">TOE SEWING MACHINE</td><td colspan="2">ONE CONE BACK KOREAN SPIN</td></tr>

                        <tr><td rowspan="11" class="col-family">C (30-40)</td>
                        <tr><td class="col-num">10</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">BACK KOREAN SPIN</td></tr>
                        <tr><td class="col-num">9</td><td colspan="2">COBRA BACK</td><td colspan="2">BACK SITTING COBRA</td><td colspan="2"></td><td colspan="2">BACK WHEELING</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">8</td><td colspan="2"></td><td colspan="2">BACK CHRISTIE</td><td colspan="2">KAZAKCHOK</td><td colspan="2"></td><td colspan="2">ONE CONE KOREAN SPIN</td></tr>
                        <tr><td class="col-num">7</td><td colspan="2">COBRA</td><td colspan="2">SITTING COBRA</td><td colspan="2"></td><td colspan="2"></td><td colspan="2">KOREAN SPIN</td></tr>
                        <tr><td class="col-num">6</td><td colspan="2">REVERSE EAGLE</td><td colspan="2">CHRISTIE</td><td colspan="2">KAZAKSPIN</td><td>FLAT SHIFT</td><td>COUNTERSHIFTS SERIES</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">5</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">BACK TWO WHEELS SPIN</td></tr>
                        <tr><td class="col-num">4</td><td colspan="2">TOE WHEELS EAGLE</td><td colspan="2">BACK FOOTGUN</td><td colspan="2">FRONT WIPER</td><td colspan="2">TWO WHEELING</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">3</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">TWO WHEEL SPIN</td></tr>
                        <tr><td class="col-num">2</td><td colspan="2"></td><td colspan="2">FOOTGUN</td><td colspan="2">WIPER</td><td>FLAT FLIP</td><td>DAYNIGHT SERIES</td><td colspan="2"></td></tr>
                        <tr class="level-boundary"><td class="col-num">1</td><td colspan="2">Z-EAGLE</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">BACK J-TURN</td></tr>

                        <tr><td rowspan="11" class="col-family">D (20-30)</td>
                        <tr><td class="col-num">10</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr><td class="col-num">9</td><td colspan="2">TOE TOE SPECIAL</td><td colspan="2">SITTING HEEL TOE BACK CROSS</td><td colspan="2">FOOTSPIN JUMP</td><td colspan="2"></td><td colspan="2">J-TURN</td></tr>
                        <tr><td class="col-num">8</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">TWO FEET SPIN</td></tr>
                        <tr><td class="col-num">7</td><td colspan="2">BRUSH</td><td colspan="2">SITTING HEEL TOE CROSS</td><td colspan="2"></td><td colspan="2">TWO WHEELS BACK CROSS</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">6</td><td colspan="2">HEEL TOE SPECIAL</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">TOTAL CROSS</td></tr>
                        <tr><td class="col-num">5</td><td>EAGLE</td><td>EAGLE CROSS</td><td colspan="2"></td><td colspan="2"></td><td colspan="2">TWO WHEELS BACK SNAKE</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">4</td><td colspan="2"></td><td colspan="2">BACK SITTING HEEL TOE SNAKE</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr><td class="col-num">3</td><td colspan="2">SWEEPERS</td><td colspan="2"></td><td colspan="2"></td><td colspan="2">TWO WHEELS CROSS</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">2</td><td colspan="2"></td><td colspan="2">SITTING HEEL TOE SNAKE</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr class="level-boundary"><td class="col-num">1</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2">TWO WHEELS SNAKE</td><td colspan="2"></td></tr>

                        <tr><td rowspan="12" class="col-family">E (10-20)</td>
                        <tr><td class="col-num">10</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr><td class="col-num">9</td><td colspan="2">EIGHT</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td>ITALIAN</td><td>VOLTE</td></tr>
                        <tr><td class="col-num">8</td><td colspan="2"></td><td>SMALL CAR</td><td>5 WHEELS SITTING</td><td colspan="2">X JUMP</td><td colspan="2">BACK ONE FOOT</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">7</td><td colspan="2">BACK EIGHT</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr><td class="col-num">6</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td>CRAZY SUN</td><td>MEXICAN</td></tr>
                        <tr><td class="col-num">5</td><td colspan="2"></td><td colspan="2"></td><td colspan="2">CRAB CROSS</td><td colspan="2">ONE FOOT</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">4</td><td>STROLL</td><td>BACK STROLL</td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td><td>SUN</td><td>MABROUK</td></tr>
                        <tr><td class="col-num">3</td><td>CRAZY</td><td>DOUBLE CRAZY SERIES</td><td colspan="2"></td><td colspan="2"></td><td>BACK SNAKE</td><td>BACK CROSS</td><td colspan="2"></td></tr>
                        <tr><td class="col-num">2</td><td>CHAP CHAP</td><td>X</td><td colspan="2"></td><td colspan="2">CRAB SERIES</td><td colspan="2"></td><td colspan="2"></td></tr>
                        <tr><td class="col-num">1</td><td colspan="2">MEGA SERIES</td><td colspan="2"></td><td colspan="2"></td><td>SNAKE</td><td>CROSS</td><td colspan="2"></td></tr>
                        <tr class="level-boundary"><td class="col-num">0</td><td>NELSON</td><td>BACK NELSON SERIES</td><td colspan="2">SITTING FISH</td><td colspan="2"></td><td colspan="2">FISH</td><td colspan="2"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="footer-note">UPDATE APPENDIX 2026 CLASSIC SLALOM TRICK MATRIX</div>
        </div>

        <!-- FREESTYLE SLIDE VIEW -->
        <div id="freestyleSlideView" class="curriculum-view" style="display:none;">
            <div class="table-responsive is-locked">
                <table class="matrix-table is-locked">
                    <thead><tr><th colspan="2">MARK & FAMILIES</th><th>FAMILY 1</th><th>FAMILY 2</th><th>FAMILY 3</th><th>FAMILY 4</th><th>FAMILY 5</th></tr></thead>
                    <tbody>
                        <tr><td rowspan="13" class="col-family">A</td>
                        <tr><td class="col-num">1</td><td>V - Toe Toe</td><td>Cowboy Heel Heel</td><td>8 Cross Heel Heel</td><td></td><td></td></tr>
                        <tr><td class="col-num">2</td><td></td><td>Cowboy Toe Toe</td><td>8 Cross Toe Toe</td><td></td><td></td></tr>
                        <tr><td class="col-num">3</td><td></td><td></td><td>8 Cross Toe Heel</td><td></td><td></td></tr>
                        <tr><td class="col-num">4</td><td></td><td>Cowboy Toe Heel</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">5</td><td></td><td></td><td>8 Cross 8 Wheels</td><td></td><td></td></tr>
                        <tr><td class="col-num">6</td><td>Cross Ern Sui Heel Heel</td><td>Cowboy 8 Wheels</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">7</td><td></td><td></td><td>Cross UFO Heel Heel</td><td>FastSlide Heel</td><td></td></tr>
                        <tr><td class="col-num">8</td><td>Cross Ern Sui Heel Toe</td><td>Backslide Toe</td><td>Cross UFO Toe Toe</td><td>FastSlide Toe</td><td></td></tr>
                        <tr><td class="col-num">9</td><td>Cross Ern Sui Toe Toe</td><td>Backslide Heel</td><td>Cross UFO Toe heel</td><td></td><td></td></tr>
                        <tr><td class="col-num">10</td><td>Cross Ern Sui Heel</td><td></td><td>Cross UFO 8 Wheels</td><td></td><td>Cross Parallel Heel Heel</td></tr>
                        <tr><td class="col-num">11</td><td>Cross Ern Sui Toe</td><td></td><td>Cross Parallel Toe Heel</td><td></td><td></td></tr>
                        <tr class="level-boundary"><td class="col-num">12</td><td></td><td></td><td></td><td></td><td>Cross Parallel Toe Toe</td></tr>

                        <tr><td rowspan="16" class="col-family">B</td>
                        <tr><td class="col-num">1</td><td>Cross Ern Sui 4 wheels</td><td></td><td>Eagle Toe Toe</td><td></td><td></td></tr>
                        <tr><td class="col-num">2</td><td></td><td></td><td>Eagle Toe Heel</td><td></td><td></td></tr>
                        <tr><td class="col-num">3</td><td></td><td></td><td>Eagle 8 Wheels</td><td></td><td></td></tr>
                        <tr><td class="col-num">4</td><td></td><td></td><td>Eagle Heel Heel</td><td></td><td></td></tr>
                        <tr><td class="col-num">5</td><td>Ern Sui Heel Heel</td><td></td><td>Ufo special heel heel</td><td></td><td></td></tr>
                        <tr><td class="col-num">6</td><td>Ern Sui Toe Heel</td><td></td><td>Ufo special toe toe</td><td>FastSlide 4 Wheels</td><td></td></tr>
                        <tr><td class="col-num">7</td><td>Ern Sui Heel Toe</td><td></td><td>Ufo special toe heel</td><td></td><td>Unity / Savannah Heel Heel</td></tr>
                        <tr><td class="col-num">8</td><td>Ern Sui Toe Toe</td><td></td><td>Ufo special toe toe</td><td></td><td>Unity / Savannah Toe Toe</td></tr>
                        <tr><td class="col-num">9</td><td></td><td>Backslide 4 Wheels</td><td>UFO Toe Toe</td><td></td><td>Unity / Savannah Toe Heel</td></tr>
                        <tr><td class="col-num">10</td><td></td><td></td><td>UFO Toe Heel</td><td>Magic Toe Toe</td><td></td></tr>
                        <tr><td class="col-num">11</td><td></td><td></td><td>UFO Heel Heel</td><td>Magic Heel Heel</td><td></td></tr>
                        <tr><td class="col-num">12</td><td></td><td></td><td></td><td>Magic Toe Heel</td><td>Cross Parallel 8 Wheels</td></tr>
                        <tr><td class="col-num">13</td><td></td><td></td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">14</td><td></td><td></td><td>Ufo special 8 wheels</td><td></td><td></td></tr>
                        <tr class="level-boundary"><td class="col-num">15</td><td></td><td></td><td>UFO 8 Wheels</td><td></td><td></td></tr>

                        <tr><td rowspan="8" class="col-family">C</td>
                        <tr><td class="col-num">1</td><td></td><td>Cross Acid Toe Heel</td><td></td><td></td><td>Parallel Toe Toe</td></tr>
                        <tr><td class="col-num">2</td><td>Ern Sui 4 Wheels</td><td>Cross Acid Heel Heel</td><td></td><td>FastWheel Heel</td><td>Parallel Heel Toe</td></tr>
                        <tr><td class="col-num">3</td><td></td><td>Cross Acid Heel Toe</td><td></td><td>FastWheel Heel Heel</td><td>Parallel Heel Heel</td></tr>
                        <tr><td class="col-num">4</td><td></td><td>Cross Acid Toe Toe</td><td></td><td>FastWheel Toe Toe</td><td>Unity / Savannah 8 Wheels</td></tr>
                        <tr><td class="col-num">5</td><td></td><td></td><td></td><td>FastWheel Toe Heel</td><td></td></tr>
                        <tr><td class="col-num">6</td><td></td><td></td><td></td><td>FastWheel Heel Toe</td><td></td></tr>
                        <tr class="level-boundary"><td class="col-num">7</td><td></td><td></td><td></td><td></td><td></td></tr>

                        <tr><td rowspan="13" class="col-family">D</td>
                        <tr><td class="col-num">1</td><td></td><td>Barrow Heel Toe</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">2</td><td>Soyale Heel Heel</td><td>Barrow Toe Heel</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">3</td><td>Soyale Heel Toe</td><td>Barrow Toe</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">4</td><td>Soyale Toe Heel</td><td>Barrow Toe</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">5</td><td>Soyale Toe Toe</td><td>Barrow 4 Wheels</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">6</td><td>Soyale Heel</td><td>Cross Acid Toe</td><td></td><td></td><td>Parallel 8 Wheels</td></tr>
                        <tr><td class="col-num">7</td><td>Soyale Toe</td><td>Cross Acid Heel</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">8</td><td>Soyale 4 Wheels</td><td>Acid Toe Heel</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">9</td><td></td><td>Acid Heel Toe</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">10</td><td></td><td>Acid Toe Toe</td><td></td><td>Magic 8 Wheels</td><td></td></tr>
                        <tr><td class="col-num">11</td><td></td><td></td><td></td><td>FastWheel 4 Wheels heel</td><td></td></tr>
                        <tr class="level-boundary"><td class="col-num">12</td><td></td><td>Acid toe 4 wheels</td><td></td><td>FastWheel 4 Wheels toe</td><td></td></tr>

                        <tr><td rowspan="13" class="col-family">E</td>
                        <tr><td class="col-num">1</td><td>Soyale 8 wheels</td><td>Barrow 8 wheels</td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">2</td><td></td><td></td><td></td><td></td><td></td></tr>
                        <tr><td class="col-num">3</td><td></td><td></td><td></td><td>Powerslide Toe</td><td></td></tr>
                        <tr><td class="col-num">4</td><td></td><td></td><td></td><td>Powerslide Heel</td><td></td></tr>
                        <tr><td class="col-num">5</td><td></td><td>Cross Acid 8 Wheels</td><td></td><td>Powerslide Toe Toe</td><td></td></tr>
                        <tr><td class="col-num">6</td><td></td><td></td><td></td><td>Powerslide Heel Heel</td><td></td></tr>
                        <tr><td class="col-num">7</td><td></td><td></td><td></td><td>Powerslide Heel Toe</td><td></td></tr>
                        <tr><td class="col-num">8</td><td></td><td>Acid Toe</td><td></td><td>Soul Toe (Fastwheel Toe)</td><td></td></tr>
                        <tr><td class="col-num">9</td><td></td><td>Acid Heel</td><td></td><td>Soul Heel (Faswheel Heel)</td><td></td></tr>
                        <tr><td class="col-num">10</td><td></td><td>Acid 4 Wheels</td><td></td><td>Soul 4 Wheels (Fastwheel 4 wheels)</td><td></td></tr>
                        <tr><td class="col-num">11</td><td></td><td></td><td></td><td></td><td></td></tr>
                        <tr class="level-boundary"><td class="col-num">12</td><td></td><td></td><td></td><td>Powerslide</td><td></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="footer-note">UPDATE APPENDIX FREESTYLE SLIDE MATRIX</div>
        </div>

        <!-- SPEED SLALOM VIEW -->
        <div id="speedSlalomView" class="curriculum-view" style="display:none;">
            <div class="table-responsive is-locked">
                <table class="matrix-table is-locked speed-table">
                     <thead>
                        <tr><th colspan="2">SPEED SLALOM TRACK</th><th>WAKTU TERBAIK SAAT INI</th></tr>
                    </thead>
                    <tbody>
                        <tr class="level-boundary">
                            <td class="col-family text-size-14">A</td>
                            <td class="speed-header-label">ON SKATE</td>
                            <td id="speed-on-skate" class="clickable-speed clickable-cell" data-type="on-skate">0.000 Detik</td>
                        </tr>
                        <tr class="level-boundary">
                            <td class="col-family text-size-14">B</td>
                            <td class="speed-header-label">OFF SKATE</td>
                            <td id="speed-off-skate" class="clickable-speed clickable-cell" data-type="off-skate">0.000 Detik</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="speed-history-wrapper">
                <div class="speed-history-box">
                    <h4>📜 Histori ON SKATE (Maks. 10)</h4>
                    <div class="history-list" id="globalHistoryOnSkate"><div class="history-item flex-center-gray"><em>Belum ada rekor.</em></div></div>
                </div>
                <div class="speed-history-box">
                    <h4>📜 Histori OFF SKATE (Maks. 10)</h4>
                    <div class="history-list" id="globalHistoryOffSkate"><div class="history-item flex-center-gray"><em>Belum ada rekor.</em></div></div>
                </div>
            </div>
            <div class="footer-note">SPEED SLALOM APPENDIX - (1 CONE FAULT = +0.2 DETIK | >4 FAULT = GAGAL)</div>
        </div>

        <!-- BEGINNER'S TEST VIEW -->
        <div id="beginner'sTestView" class="curriculum-view" style="display:none;">
            <div class="table-responsive is-locked">
                <table class="matrix-table is-locked">
                     <thead><tr><th colspan="2">TINGKATAN</th><th>FLEXIBILITY</th><th>DURABILITY</th><th>STABILITY</th><th>AGILITY</th></tr></thead>
                    <tbody>
                        <tr><td rowspan="3" class="col-family">LEVEL 3<br>(Pengembangan)</td>
                        <tr><td class="col-num">1</td><td>T-STOP</td><td>MELUNCUR MUNDUR</td><td>MELUNCUR 1 KAKI</td><td>MAJU PUTAR 180⁰</td></tr>
                        <tr class="level-boundary"><td class="col-num">2</td><td>PLOW STOP</td><td>SCOOTER KANAN & KIRI</td><td>SNAKE</td><td>FORWARD CROSSOVERS</td></tr>
                        <tr><td rowspan="3" class="col-family">LEVEL 2<br>(Lanjutan)</td>
                        <tr><td class="col-num">1</td><td>MAJU BUKA TUTUP</td><td>SCOOTER</td><td>FISH</td><td>ZIG-ZAG</td></tr>
                        <tr class="level-boundary"><td class="col-num">2</td><td>MAJU SIMPLE S</td><td>MELUNCUR MAJU</td><td>MELANGKAH KANAN & KIRI</td><td>AMBIL CONE</td></tr>
                        <tr><td rowspan="3" class="col-family">LEVEL 1<br>(Dasar)</td>
                        <tr><td class="col-num">1</td><td>POSISI KAKI V / A</td><td>JALAN DI TEMPAT</td><td>MELANGKAH KANAN / KIRI</td><td>MELANGKAH KESAMPING</td></tr>
                        <tr class="level-boundary"><td class="col-num">2</td><td>MAJU SIMPLE S</td><td>MAJU 5M</td><td>JALAN DI TEMPAT</td><td>MELANGKAH CEPAT</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="footer-note">BEGINNER CURICULUM - FOKUS PADA PENGUASAAN DASAR SEPATU RODA UNTUK PEMULA</div>
        </div>

        <!-- CONTAINER LEGEND & ANALISA -->
        <div class="analysis-legend-container">
            <!-- LEGENDA STATUS PENILAIAN -->
            <div class="legend-wrapper">
                <table class="legend-table" id="legendTableDynamic">
                </table>
            </div>

            <!-- ANALISA / CATATAN KESELURUHAN -->
            <div class="analysis-box">
                <span class="analysis-label">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"></path></svg>
                    <span id="analysisLabelText">ANALISA / CATATAN KESELURUHAN:</span>
                </span>
                <textarea id="analysisTextarea" class="analysis-textarea" placeholder="Ketik analisa/catatan atlet di sini... (Langsung tersimpan otomatis)"></textarea>
            </div>
        </div>

    </div>
</div>

@if($role === 'admin')
<!-- MODAL PENGATURAN -->
<div id="settingsModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModalSafely(document.getElementById('settingsModal'))">&times;</span>
        <h3>⚙️ Pengaturan Aplikasi</h3>
        <form id="settingsForm">
            <div class="form-group">
                <label for="settingClubName">Nama Club / Sekolah:</label>
                <input type="text" id="settingClubName" placeholder="Contoh: KEDIRI INLINE SKATE SCHOOL" required>
            </div>
            <div class="form-group">
                <label for="settingLogoUrl">URL Logo (Gambar):</label>
                <input type="url" id="settingLogoUrl" placeholder="https://contoh.com/logo.png">
                <small class="help-text">Kosongkan untuk memakai logo default.</small>
            </div>
            <div class="form-group">
                <label for="settingLogoFile">Atau Upload Logo Terbaru (File):</label>
                <input type="file" id="settingLogoFile" accept="image/*" class="clay-input-top file-input-full">
                <small class="help-text">Pilih file dari perangkat untuk logo baru.</small>
            </div>
            <button type="submit" class="submit-btn">Simpan Pengaturan</button>
        </form>
    </div>
</div>
@endif

<!-- MODAL FORM BIODATA ATLET (TAMBAH / EDIT) -->
<div id="athleteFormModal" class="modal">
    <div class="modal-content" style="max-width: 450px;">
        <span class="close-btn" onclick="closeModalSafely(document.getElementById('athleteFormModal'))">&times;</span>
        <h3 id="athleteFormModalTitle">Form Biodata Atlet</h3>
        <form id="athleteCustomForm" onsubmit="saveAthleteBiodata(event)">
            <input type="hidden" id="formAthleteId">
            <div class="form-group">
                <label>NIK:</label>
                <input type="text" id="inputNik" placeholder="Nomor NIK Atlet" required>
            </div>
            <div class="form-group">
                <label>Nama Lengkap:</label>
                <input type="text" id="inputFullName" placeholder="Nama Lengkap Atlet" required>
            </div>
            <div class="form-group">
                <label>Nama Panggilan:</label>
                <input type="text" id="inputNickname" placeholder="Nama Panggilan Atlet" required>
            </div>
            <div class="form-group">
                <label>Jenis Kelamin:</label>
                <select id="inputGender" required>
                    <option value="">- PILIH JENIS KELAMIN -</option>
                    <option value="L">LAKI-LAKI</option>
                    <option value="P">PEREMPUAN</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Lahir:</label>
                <input type="date" id="inputTglLahir" required>
            </div>
            <div class="form-group">
                <label>Alamat Lengkap:</label>
                <input type="text" id="inputAlamat" placeholder="Alamat Lengkap" required>
            </div>
            <div class="form-group">
                <label>Nama Wali / Parent:</label>
                <input type="text" id="inputWali" placeholder="Nama Wali" required>
            </div>
            <div class="form-group">
                <label>WhatsApp:</label>
                <input type="text" id="inputWa" placeholder="Cth: 08123456789" required>
            </div>
            <div class="form-group">
                <label>Kelas:</label>
                <select id="inputKelas" required>
                    <option value="PEMULA">PEMULA</option>
                    <option value="JUNIOR 1">JUNIOR 1</option>
                    <option value="JUNIOR 2">JUNIOR 2</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status Akun:</label>
                <select id="inputStatus" required>
                    <option value="Aktif">AKTIF</option>
                    <option value="Arsip">ARSIP</option>
                </select>
            </div>
            <button type="submit" class="submit-btn">Simpan Biodata Atlet</button>
        </form>
    </div>
</div>

<!-- MODAL TRICK INDIVIDU -->
<div id="trickModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModalSafely(document.getElementById('trickModal'))">&times;</span>
        <h3 id="trickModalTitle">Penilaian Trik</h3>
        <form id="assessmentForm">
            <div class="form-group custom-trick-group">
                <label for="modalTrickName">Nama Trik/Materi (Admin Edit):</label>
                @if($role === 'admin')
                    <div style="display: flex !important; gap: 6px; visibility: visible !important; opacity: 1 !important;" id="adminTrickEditContainer">
                        <input type="text" id="modalTrickName" class="form-control" style="flex: 1; display: block !important;" readonly>
                        <button type="button" id="btnEditTrickName" class="curr-btn btn-yellow" style="display: inline-flex !important; align-items: center; padding: 4px 8px; font-size: 10px;" title="Edit Nama Trik"><i class="fa-solid fa-pen"></i> Edit</button>
                        <button type="button" id="btnSaveTrickName" class="curr-btn btn-success" style="display: none !important; align-items: center; padding: 4px 8px; font-size: 10px;" title="Simpan Nama Trik"><i class="fa-solid fa-check"></i> Simpan</button>
                    </div>
                @else
                    <input type="text" id="modalTrickName" class="form-control" style="width: 100%; display: block !important;" readonly>
                @endif
            </div>

            <div class="form-group">
                <label for="modalDate">Tanggal Penilaian:</label>
                <input type="date" id="modalDate" required>
            </div>

            <div class="form-group" id="modalScoreInputWrapper">
                <label id="modalScoreLabel" for="assessmentScore">Hasil Penilaian:</label>
                <select id="assessmentScore" class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc; font-weight: bold; margin-top: 5px;">
                </select>
            </div>

            <div class="form-group">
                <div class="yt-input-group">
                    <a href="#" id="modalYtViewBtn" class="yt-view-btn" target="_blank" style="display:none;">Lihat Video</a>
                </div>
            </div>
            <button type="submit" class="submit-btn">Simpan Penilaian</button>
        </form>
        <div class="history-container">
            <h4>Histori Penilaian:</h4>
            <div class="history-list" id="trickHistoryList"></div>
        </div>
    </div>
</div>

<!-- MODAL MASSAL -->
<div id="massModal" class="modal">
    <div class="modal-content modal-mass-width">
        <span class="close-btn" onclick="closeModalSafely(document.getElementById('massModal'))">&times;</span>
        <h3 class="modal-title-purple">📋 Penilaian Massal Trik</h3>

        <div class="form-group custom-trick-group">
            <label for="massTrickName">Nama Trik/Materi (Admin Edit):</label>
            @if($role === 'admin')
                <div style="display: flex !important; gap: 6px; visibility: visible !important; opacity: 1 !important;" id="adminMassTrickEditContainer">
                    <input type="text" id="massTrickName" readonly class="input-mass-trick" style="flex: 1; padding: 8px 10px; border: none; border-radius: 8px; background: var(--bg-main); box-shadow: var(--clay-shadow-inset); font-weight: 800; font-size: 11px; outline: none; color: var(--text-dark); display: block !important;">
                    <button type="button" id="btnEditMassTrickName" class="curr-btn btn-yellow" style="display: inline-flex !important; align-items: center; padding: 4px 8px; font-size: 10px;" title="Edit Nama Trik"><i class="fa-solid fa-pen"></i> Edit</button>
                    <button type="button" id="btnSaveMassTrickName" class="curr-btn btn-success" style="display: none !important; align-items: center; padding: 4px 8px; font-size: 10px;" title="Simpan Nama Trik"><i class="fa-solid fa-check"></i> Simpan</button>
                </div>
            @else
                <input type="text" id="massTrickName" readonly class="input-mass-trick" style="width: 100%; padding: 8px 10px; border: none; border-radius: 8px; background: var(--bg-main); box-shadow: var(--clay-shadow-inset); font-weight: 800; font-size: 11px; outline: none; color: var(--text-dark); display: block !important;">
            @endif
        </div>
        <div class="form-group">
            <label for="massDate">Tanggal Penilaian (Untuk Semua):</label>
            <input type="date" id="massDate" required>
        </div>

        <div class="mass-filter-box">
            <p class="mass-filter-title">🔍 FILTER ATLET UNTUK PENILAIAN MASSAL:</p>
            <div class="mass-filter-controls">
                <select id="filterKelasMass" class="clay-select-top flex-1">
                    <option value="">-- SEMUA KELAS --</option>
                    <option value="PEMULA">PEMULA</option>
                    <option value="JUNIOR 1">JUNIOR 1</option>
                    <option value="JUNIOR 2">JUNIOR 2</option>
                </select>
                <select id="filterStatusMass" class="clay-select-top flex-1">
                    <option value="">-- SEMUA STATUS --</option>
                    <option value="Aktif">AKTIF</option>
                    <option value="Arsip">ARSIP</option>
                </select>
                <select id="filterHasilMass" class="clay-select-top flex-1">
                    <option value="">-- SEMUA HASIL --</option>
                    <option value="Kosong">KOSONG</option>
                    <option value="Merah">MERAH</option>
                    <option value="Kuning">KUNING</option>
                    <option value="Hijau">HIJAU</option>
                </select>
            </div>

            <label class="check-all-label">
                <input type="checkbox" id="checkAllMass" class="mass-check mass-check-large">
                PILIH SEMUA / CENTANG SEMUA ATLET DI BAWAH
            </label>
            <p class="mass-apply-text">Terapkan nilai yang sama ke atlet yg dicentang:</p>
            <div class="quick-btn-group-10" id="massActionBtns"></div>
        </div>

        <p class="mass-manual-label">Atau nilai satu per satu di bawah ini:</p>
        <div class="mass-athlete-list" id="massAthleteContainer"></div>

        <button type="button" class="submit-btn btn-bg-sidebar" onclick="closeMassModal()">✅ Selesai & Tutup</button>
    </div>
</div>

<div id="speedModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModalSafely(document.getElementById('speedModal'))">&times;</span>
        <h3 id="speedModalTitle">Input Speed Slalom</h3>
        <form id="speedForm">
            <div class="form-group"><label for="speedDate">Tanggal Penilaian:</label><input type="date" id="speedDate" required></div>
            <div class="form-group"><label for="speedTime">Waktu Asli (Detik):</label><input type="number" id="speedTime" step="0.001" min="0" placeholder="Contoh: 5.432" required></div>
            <div class="form-group" id="coneFaultGroup">
                <label for="speedFault">Cone Fault (Kesalahan Cone):</label>
                <input type="number" id="speedFault" min="0" value="0" placeholder="Jumlah cone tersenggol/terlewat">
                <small class="help-text">Setiap 1 fault = +0.2 detik</small>
            </div>
            <button type="submit" class="submit-btn">Simpan Rekor</button>
        </form>
    </div>
</div>

<!-- Modal Pending / Verifikasi Pendaftaran Atlet -->
<div id="pendingModal" class="clay-modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div class="clay-modal-content" style="background: #fff; margin: 10% auto; padding: 20px; width: 90%; max-width: 600px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f1f1; padding-bottom: 10px; margin-bottom: 15px;">
            <h3 style="margin: 0; color: #333;"><i class="fa-solid fa-user-clock"></i> Daftar Verifikasi Atlet Baru</h3>
            <button type="button" onclick="closeModalSafely(document.getElementById('pendingModal'))" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #777;">&times;</button>
        </div>

        <div id="pendingListContainer" style="max-height: 400px; overflow-y: auto;">
        </div>
    </div>
</div>

@include('layouts.footer')
</div>

<script>
    window.USER_ROLE = "{{ strtolower($role) }}";
    window.CURRENT_USER_ID = "{{ $currentUserId }}";
    window.CURRENT_USER_NAME = "{{ strtoupper($user->name ?? $user->username ?? '') }}";

    localStorage.setItem('userRole', window.USER_ROLE);
    if(window.USER_ROLE === 'admin') {
        localStorage.setItem('isAdmin', 'true');
    }
</script>

<script src="{{ asset('js/appendix.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allTrickCells = document.querySelectorAll('.matrix-table td:not(.col-num):not(.col-family)');

        allTrickCells.forEach((cell, index) => {
            const uniqueCellId = 'trick_custom_name_' + index;
            cell.dataset.storageId = uniqueCellId;

            const savedCustomName = localStorage.getItem(uniqueCellId);
            if (savedCustomName) {
                cell.innerText = savedCustomName;
            }
        });

        window.activeTrickCell = null;

        document.querySelector('.page-container').addEventListener('click', function(e) {
            let cell = e.target.closest('.matrix-table td:not(.col-num):not(.col-family)');

            if (cell) {
                window.activeTrickCell = cell;

                const modalTrickNameInput = document.getElementById('modalTrickName');
                const massTrickNameInput = document.getElementById('massTrickName');

                if (modalTrickNameInput) {
                    modalTrickNameInput.value = cell.innerText.trim();
                }
                if (massTrickNameInput) {
                    massTrickNameInput.value = cell.innerText.trim();
                }
            }
        });

        const btnEditTrickName = document.getElementById('btnEditTrickName');
        const btnSaveTrickName = document.getElementById('btnSaveTrickName');
        const modalTrickNameInput = document.getElementById('modalTrickName');
        const trickModalTitle = document.getElementById('trickModalTitle');

        if (btnEditTrickName && btnSaveTrickName && modalTrickNameInput) {
            btnEditTrickName.addEventListener('click', function(e) {
                e.preventDefault();

                if (!modalTrickNameInput.value && trickModalTitle) {
                    modalTrickNameInput.value = trickModalTitle.innerText.replace('Penilaian Trik: ', '').trim();
                }

                modalTrickNameInput.removeAttribute('readonly');
                modalTrickNameInput.focus();

                btnEditTrickName.style.setProperty('display', 'none', 'important');
                btnSaveTrickName.style.setProperty('display', 'inline-flex', 'important');
            });

            btnSaveTrickName.addEventListener('click', function(e) {
                e.preventDefault();
                const newName = modalTrickNameInput.value.trim();

                if (newName !== '') {
                    if (trickModalTitle) {
                        trickModalTitle.innerText = 'Penilaian Trik: ' + newName;
                    }

                    if (window.activeTrickCell) {
                        window.activeTrickCell.innerText = newName;

                        const storageId = window.activeTrickCell.dataset.storageId;
                        if (storageId) {
                            localStorage.setItem(storageId, newName);
                        }

                        let originalBg = window.activeTrickCell.style.backgroundColor;
                        window.activeTrickCell.style.backgroundColor = 'var(--clay-green)';
                        setTimeout(() => {
                            window.activeTrickCell.style.backgroundColor = originalBg;
                        }, 800);
                    }

                    if (typeof window.saveTrickNameChanges === 'function') {
                        window.saveTrickNameChanges(window.currentCellKey, newName, window.activeTrickCell);
                    }
                }

                modalTrickNameInput.setAttribute('readonly', true);
                btnSaveTrickName.style.setProperty('display', 'none', 'important');
                btnEditTrickName.style.setProperty('display', 'inline-flex', 'important');
            });
        }

        const btnEditMassTrickName = document.getElementById('btnEditMassTrickName');
        const btnSaveMassTrickName = document.getElementById('btnSaveMassTrickName');
        const massTrickNameInput = document.getElementById('massTrickName');

        if (btnEditMassTrickName && btnSaveMassTrickName && massTrickNameInput) {
            btnEditMassTrickName.addEventListener('click', function(e) {
                e.preventDefault();
                massTrickNameInput.removeAttribute('readonly');
                massTrickNameInput.focus();

                btnEditMassTrickName.style.setProperty('display', 'none', 'important');
                btnSaveMassTrickName.style.setProperty('display', 'inline-flex', 'important');
            });

            btnSaveMassTrickName.addEventListener('click', function(e) {
                e.preventDefault();
                const newName = massTrickNameInput.value.trim();

                if (newName !== '') {
                    if (window.activeTrickCell) {
                        window.activeTrickCell.innerText = newName;

                        const storageId = window.activeTrickCell.dataset.storageId;
                        if (storageId) {
                            localStorage.setItem(storageId, newName);
                        }

                        let originalBg = window.activeTrickCell.style.backgroundColor;
                        window.activeTrickCell.style.backgroundColor = 'var(--clay-green)';
                        setTimeout(() => {
                            window.activeTrickCell.style.backgroundColor = originalBg;
                        }, 800);
                    }

                    if (typeof window.saveTrickNameChanges === 'function') {
                        window.saveTrickNameChanges(window.currentCellKey, newName, window.activeTrickCell);
                    }
                }

                massTrickNameInput.setAttribute('readonly', true);
                btnSaveMassTrickName.style.setProperty('display', 'none', 'important');
                btnEditMassTrickName.style.setProperty('display', 'inline-flex', 'important');
            });
        }
    });

    // --- MANAJEMEN MODAL BIODATA ATLET (TAMBAH / EDIT / HAPUS) ---
    window.openAthleteFormModal = function(mode) {
        const modal = document.getElementById('athleteFormModal');
        const titleEl = document.getElementById('athleteFormModalTitle');
        const inputKelas = document.getElementById('inputKelas');
        const inputStatus = document.getElementById('inputStatus');
        const userRole = "{{ $role }}";

        // Terapkan batasan hak akses edit kelas & status jika user adalah parent
        if (userRole === 'parent') {
            inputKelas.setAttribute('disabled', 'true');
            inputStatus.setAttribute('disabled', 'true');
        } else {
            inputKelas.removeAttribute('disabled');
            inputStatus.removeAttribute('disabled');
        }

        if (mode === 'add') {
            titleEl.innerText = 'Tambah Data Atlet Baru';
            document.getElementById('athleteCustomForm').reset();
            document.getElementById('formAthleteId').value = '';
            document.getElementById('inputWali').value = "{{ strtoupper($user->name ?? $user->username ?? '') }}";
        } else if (mode === 'edit') {
            titleEl.innerText = 'Edit Biodata Atlet Aktif';
            // Isi form dengan data yang sedang aktif tampil di layar
            document.getElementById('inputNik').value = document.getElementById('bioNIK').value !== '-' ? document.getElementById('bioNIK').value : '';
            document.getElementById('inputFullName').value = document.getElementById('athleteFullName').value !== '-' ? document.getElementById('athleteFullName').value : '';
            document.getElementById('inputNickname').value = document.getElementById('athleteName').value !== '-' ? document.getElementById('athleteName').value : '';
            document.getElementById('inputGender').value = document.getElementById('bioGender').value;
            document.getElementById('inputTglLahir').value = document.getElementById('bioTglLahir').value;
            document.getElementById('inputAlamat').value = document.getElementById('bioAlamat').value !== '-' ? document.getElementById('bioAlamat').value : '';
            document.getElementById('inputWali').value = document.getElementById('bioOrtu').value !== '-' ? document.getElementById('bioOrtu').value : '';
            document.getElementById('inputWa').value = document.getElementById('bioWA').value !== '-' ? document.getElementById('bioWA').value : '';
            document.getElementById('inputKelas').value = document.getElementById('bioKelas').value;
            document.getElementById('inputStatus').value = document.getElementById('bioStatus').value;
        }

        if (modal) modal.style.display = 'block';
    };

    window.closeModalSafely = function(modalEl) {
        if (modalEl) modalEl.style.display = 'none';
    };

    window.saveAthleteBiodata = function(e) {
        if (e) e.preventDefault();

        const nickname = document.getElementById('inputNickname').value.trim().toUpperCase();
        if (!nickname) {
            alert('Nama Panggilan atlet wajib diisi!');
            return;
        }

        const bioData = {
            nik: document.getElementById('inputNik').value,
            fullName: document.getElementById('inputFullName').value,
            nickname: nickname,
            gender: document.getElementById('inputGender').value,
            tglLahir: document.getElementById('inputTglLahir').value,
            alamat: document.getElementById('inputAlamat').value,
            ortu: document.getElementById('inputWali').value,
            wa: document.getElementById('inputWa').value,
            kelas: document.getElementById('inputKelas').value,
            status: document.getElementById('inputStatus').value
        };

        // Simpan ke localStorage spesifik berdasarkan nama panggilan
        localStorage.setItem('KILAT_BIO_' + nickname, JSON.stringify(bioData));

        // Perbarui daftar list atlet jika belum ada
        let athletesList = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
        if (!athletesList.includes(nickname)) {
            athletesList.push(nickname);
            localStorage.setItem('KILAT_ATHLETES_LIST', JSON.stringify(athletesList));
        }

        alert('✅ Biodata atlet berhasil disimpan!');
        closeModalSafely(document.getElementById('athleteFormModal'));

        // Muat ulang tampilan biodata di halaman utama
        if (typeof window.loadAthleteBioData === 'function') {
            window.loadAthleteBioData(nickname);
        } else {
            location.reload();
        }
    };

    window.deleteActiveAthlete = function() {
        const nickname = document.getElementById('athleteName').value.trim();
        if (!nickname || nickname === '-') {
            alert('Pilih atlet yang ingin dihapus terlebih dahulu.');
            return;
        }

        if (confirm(`Yakin ingin menghapus biodata atlet "${nickname}" dari sistem lokal?`)) {
            localStorage.removeItem('KILAT_BIO_' + nickname);
            let athletesList = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
            athletesList = athletesList.filter(n => n.toUpperCase() !== nickname.toUpperCase());
            localStorage.setItem('KILAT_ATHLETES_LIST', JSON.stringify(athletesList));

            alert('🗑️ Data atlet berhasil dihapus.');
            location.reload();
        }
    };
</script>

</body>
</html>
