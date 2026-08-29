@php
    // Pusat Komando Keuangan - KILAT⚡
    $user = Auth::user();

    // Logika tambahan untuk mendeteksi atlet dan daftar admin terdaftar dari database server (Tanpa Master Admin System statis jika tidak ada di DB)
    $linkedAthletes = [];
    $systemAdmins = [];

    if (isset($user)) {
        if (method_exists($user, 'athletes') && $user->athletes) {
            $linkedAthletes = $user->athletes;
        } elseif (!empty($user->atletTautan)) {
            $linkedAthletes = is_array($user->atletTautan) ? $user->atletTautan : json_decode($user->atletTautan, true);
        }

        // Mengambil daftar admin asli yang benar-benar terdaftar di database server
        if (class_exists(\App\Models\User::class)) {
            $dbAdmins = \App\Models\User::whereIn('role', ['admin', 'administrator', 'Master Admin', 'master', 'admin 1'])
                ->orWhere(function($query) {
                    $query->where('name', 'LIKE', '%admin%')
                          ->orWhere('role', 'LIKE', '%admin%');
                })
                ->pluck('name')
                ->unique()
                ->toArray();

            if (!empty($dbAdmins)) {
                $systemAdmins = $dbAdmins;
            }
        }
    }

    // Jika database kosong atau belum mendeteksi admin lain, gunakan user aktif yang sedang login sebagai admin utama yang valid
    if (empty($systemAdmins)) {
        $systemAdmins = [$user->name ?? 'Admin'];
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keuangan - Sekolah Sepatu Roda (Claymorphism)</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah -->
    <link rel="stylesheet" href="{{ asset('css/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/finance.css') }}">
    <style>
        /* ==========================================================================
           CSS KEUANGAN (PERBAIKAN TATA LETAK, PIUTANG, HELD BY, & TABEL MODUL)
           ========================================================================== */

        /* 1. Header & Status Role */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header h1 { font-size: 2.2rem; font-weight: 900; }
        .header-icons { display: flex; gap: 15px; align-items: center; }

        /* Badge Indikator Role */
        .role-indicator {
            background: var(--clay-yellow);
            padding: 8px 15px;
            border-radius: 15px;
            font-weight: 900;
            font-size: 0.85rem;
            box-shadow: var(--clay-shadow-btn);
            color: var(--text-dark);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* 2. Summary Banner (Saldo Bersih, Pemasukan, Pengeluaran, & Piutang) */
        .summary-banner {
            background: var(--clay-pink);
            border-radius: 35px;
            padding: 25px 35px;
            box-shadow: var(--clay-shadow-card);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .saldo-box h2 { font-size: 1.05rem; color: var(--text-gray); font-weight: 800; margin-bottom: 5px; text-shadow: none; }
        .saldo-box .grand-total { font-size: 2.2rem; font-weight: 900; color: var(--text-dark); }

        .summary-stats {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .stat-item {
            background: var(--bg-main);
            padding: 12px 18px;
            border-radius: 20px;
            box-shadow: var(--clay-shadow-btn);
            text-align: center;
            min-width: 140px;
        }
        .stat-item h3 {
            font-size: 0.75rem;
            color: var(--text-gray);
            text-shadow: none;
            font-weight: 800;
            margin-bottom: 4px;
        }
        .stat-item .val {
            font-size: 1.1rem;
            font-weight: 900;
        }
        .val.income { color: var(--income-color); }
        .val.expense { color: var(--expense-color); }
        .val.piutang { color: #f50b0b; }

        /* Kotak Piutang Tambahan */
        .piutang-box {
            background: rgba(255, 165, 0, 0.15) !important;
            border: 2px dashed orange;
            padding: 12px 18px !important;
            border-radius: 20px !important;
            text-align: center;
            box-shadow: var(--clay-shadow-btn);
            min-width: 140px;
        }

        /* 3. Card Total Saldo & Saldo "Held By" Masing-Masing Admin */
        .treasurer-breakdown {
            background: var(--clay-yellow);
            width: 100%;
            border-radius: 25px;
            padding: 20px 25px;
            box-shadow: var(--clay-shadow-card);
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .treasurer-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .treasurer-title {
            font-size: 0.95rem;
            font-weight: 900;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 8px;
            text-shadow: none;
        }
        .btn-transfer-saldo {
            background: var(--sidebar-bg);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 900;
            font-size: 0.8rem;
            cursor: pointer;
            box-shadow: var(--clay-shadow-btn);
            text-shadow: var(--text-timbul-light);
            transition: 0.2s;
        }
        .btn-transfer-saldo:hover { transform: scale(1.03); }

        .treasurer-badges {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .treasurer-badge {
            background: var(--bg-main);
            padding: 8px 16px;
            border-radius: 15px;
            font-weight: 800;
            font-size: 0.85rem;
            box-shadow: var(--clay-shadow-inset);
            color: var(--text-dark);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .badge-amount { color: var(--sidebar-bg); font-weight: 900; }

        /* 4. Finance Grid (Card Modul Tabel Keuangan) */
        .finance-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            padding-bottom: 40px;
        }
        .finance-grid > div:last-child:nth-child(odd) { grid-column: 1 / -1; }

        .finance-card {
            background: var(--clay-blue);
            border-radius: 30px;
            padding: 25px;
            box-shadow: var(--clay-shadow-card);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .finance-card:nth-child(2) { background: var(--clay-green); }
        .finance-card:nth-child(3) { background: var(--clay-purple); }
        .finance-card:nth-child(4) { background: var(--clay-orange); }
        .finance-card:nth-child(5) { background: var(--clay-pink); }

        .finance-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(120, 100, 200, 0.3);
        }
        .card-title-group { display: flex; align-items: center; gap: 12px; }
        .card-icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            box-shadow: var(--clay-shadow-inset);
            color: white;
        }
        .ic-bulanan { background: var(--sidebar-bg); }
        .ic-harian { background: #3b82f6; }
        .ic-daftar { background: #ffaa00; }
        .ic-lain { background: #ff6b81; }
        .ic-keluar { background: #e63946; }

        .finance-card-header h3 { font-size: 1.05rem; font-weight: 900; margin: 0; }
        .btn-input-arus {
            background: var(--bg-main);
            border: none;
            padding: 7px 12px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.8rem;
            cursor: pointer;
            color: var(--sidebar-bg);
            box-shadow: var(--clay-shadow-btn);
            display: flex; align-items: center; gap: 5px;
            text-decoration: none;
            transition: 0.2s;
        }
        .btn-input-arus:hover { filter: brightness(0.95); transform: scale(1.02); }

        /* 5. Tabel di dalam Card Keuangan */
        .table-container {
            flex: 1;
            overflow-x: auto;
            max-height: 260px;
            overflow-y: auto;
        }
        .table-container::-webkit-scrollbar { width: 4px; height: 4px; }
        .table-container::-webkit-scrollbar-thumb { background: rgba(120, 100, 200, 0.3); border-radius: 10px; }

        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 6px;
            font-size: 0.8rem;
        }
        .custom-table th {
            background: var(--bg-main);
            color: var(--text-gray);
            font-weight: 900;
            padding: 8px 10px;
            text-align: left;
            box-shadow: var(--clay-shadow-btn);
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .custom-table td {
            background: var(--bg-main);
            padding: 8px 10px;
            font-weight: 800;
            color: var(--text-dark);
            box-shadow: var(--clay-shadow-inset);
        }
        .custom-table tr td:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
        .custom-table tr td:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }

        .action-btns { display: flex; gap: 5px; }
        .btn-action-mini {
            width: 26px; height: 26px;
            border-radius: 6px;
            border: none;
            background: var(--bg-main);
            box-shadow: var(--clay-shadow-btn);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
            transition: 0.2s;
        }
        .btn-action-mini.edit { color: #3b82f6; }
        .btn-action-mini.btn-delete { color: var(--expense-color); }
        .btn-action-mini:hover { transform: scale(1.1); }

        /* Status Bayar & Badge Held By */
        .status-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 900;
            display: inline-block;
            box-shadow: var(--clay-shadow-btn);
        }
        .status-paid { background: var(--income-color); color: white; }
        .status-unpaid { background: var(--expense-color); color: white; }
        .badge-account {
            background: var(--clay-purple);
            color: var(--sidebar-bg);
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 800;
            box-shadow: var(--clay-shadow-btn);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Footer Total per Card */
        .finance-card-footer {
            margin-top: 15px;
            padding-top: 12px;
            border-top: 2px dashed rgba(120, 100, 200, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .finance-card-footer span { font-weight: 800; color: var(--text-gray); font-size: 0.85rem; }
        .finance-card-footer .frame-total { font-size: 1.15rem; font-weight: 900; color: var(--text-dark); text-shadow: var(--text-timbul-dark); }
        .frame-total.income { color: var(--income-color); }
        .frame-total.expense { color: var(--expense-color); }

        /* ==========================================================================
           CUSTOM SELECT / DROPDOWN & INPUT Kustomisasi Tema Claymorphism
           ========================================================================== */
        .custom-select-wrapper {
            position: relative;
            width: 100%;
        }
        .custom-select-wrapper select.clay-input {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-color: var(--bg-main);
            color: var(--text-dark);
            border: none;
            padding-right: 40px;
            cursor: pointer;
            width: 100%;
        }
        .custom-select-wrapper::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
            pointer-events: none;
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }
        .custom-select-wrapper.open::after {
            transform: translateY(-50%) rotate(180deg);
        }

        /* CSS Tambahan untuk Toggle Switch Diskon pada Modal Bulanan */
        .clay-checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-main);
            padding: 10px 15px;
            border-radius: 14px;
            box-shadow: var(--clay-shadow-inset);
        }
        .toggle-switch-checkbox {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
            background-color: #cbd5e1;
            border-radius: 12px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: var(--clay-shadow-btn);
        }
        .toggle-switch-checkbox::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background-color: #ffffff;
            border-radius: 50%;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .toggle-real:checked + .toggle-switch-checkbox {
            background-color: var(--sidebar-bg, #6366f1);
        }
        .toggle-real:checked + .toggle-switch-checkbox::after {
            transform: translateX(24px);
        }

        /* ==========================================================================
           PERBAIKAN TATA LETAK TOMBOL MODAL (AGAR RAPI & BERJEJER HORIZONTAL)
           ========================================================================== */
        .modal-btns {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .modal-btns .btn-clay,
        .modal-btns button {
            flex: 1;
            padding: 10px 16px;
            border-radius: 14px;
            border: none;
            font-weight: 900;
            font-size: 0.9rem;
            cursor: pointer;
            box-shadow: var(--clay-shadow-btn);
            transition: 0.2s;
            text-align: center;
        }
        .modal-btns .btn-cancel {
            background: var(--bg-main);
            color: var(--text-gray);
        }
        .modal-btns .btn-save {
            background: var(--sidebar-bg, #6366f1);
            color: white;
            text-shadow: var(--text-timbul-light);
        }
        .modal-btns button:hover {
            transform: scale(1.02);
            filter: brightness(0.95);
        }

        /* Media Queries Responsif */
        @media (max-width: 992px) {
            .summary-banner { flex-direction: column; text-align: center; }
            .summary-stats { width: 100%; justify-content: space-around; }
            .finance-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .main-content { padding: 15px; }
            .summary-stats { flex-direction: column; width: 100%; }
            .stat-item { width: 100%; }
            .piutang-box { width: 100%; }
            .treasurer-breakdown { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body data-theme="">

    <!-- SIDEBAR -->
    @include('layouts.sidebar')

    <main class="main-content" id="mainContent">
        <header class="header">
            <h1>Keuangan</h1>
            <div class="header-icons">
                <div class="role-indicator" id="currentRoleDisplay"><i class="fa-solid fa-user-shield"></i> Role: <span id="activeRoleName">{{ $user->name ?? 'Admin' }}</span></div>
                <button class="icon-btn" title="Cetak Laporan" onclick="window.print()"><i class="fa-solid fa-print"></i></button>
                <button class="icon-btn" title="Ekspor ke Excel / Spreadsheet" onclick="exportToExcel()"><i class="fa-solid fa-file-excel"></i></button>
            </div>
        </header>

        <section class="summary-banner">
            <div class="saldo-box">
                <h2>Saldo Bersih Saat Ini</h2>
                <div class="grand-total" id="grand-total-val">Rp 0</div>
            </div>
            <div class="summary-stats">
                <div class="stat-item"><h3>Total Pemasukan</h3><div class="val income" id="global-income">+ Rp 0</div></div>
                <div class="stat-item"><h3>Total Pengeluaran</h3><div class="val expense" id="global-expense">- Rp 0</div></div>
                <div class="summary-box piutang-box">
                    <span style="font-size: 0.9rem; font-weight: bold; color: #d97706;">Total Piutang (SPP Harian Belum Lunas)</span>
                    <h3 id="global-piutang" style="color: #b45309; margin-top: 5px;">Rp 0</h3>
                </div>
            </div>
        </section>

        <!-- Pemisahan Saldo Held By & Fitur Pindah Tangan Saldo -->
        <div class="treasurer-breakdown">
            <div class="treasurer-top">
                <div class="treasurer-title"><i class="fa-solid fa-users-gear" style="color:var(--sidebar-bg);"></i> Total Saldo & Saldo "Held By" Masing-Masing Admin:</div>
                <button class="btn-transfer-saldo" onclick="openTransferModal()"><i class="fa-solid fa-right-left"></i> Pindah Tangankan Saldo</button>
            </div>
            <div class="treasurer-badges" id="treasurer-badges-container"></div>
        </div>

        <section class="finance-grid">
            <!-- 1. SPP Bulanan (Otomatis dari Billing) -->
            <div class="finance-card">
                <div class="finance-card-header">
                    <div class="card-title-group"><div class="card-icon ic-bulanan"><i class="fa-solid fa-calendar-check"></i></div><h3>SPP Bulanan (Otomatis)</h3></div>
                    <div style="display: flex; gap: 6px;">
                        <button class="btn-input-arus" style="background: var(--text-white); font-size: 0.75rem; padding: 5px 8px;" onclick="ubahNominalDefault('bulanan')" title="Ubah Nominal Default Bulanan"><i class="fa-solid fa-coins"></i>Nominal Default</button>
                        <button class="btn-input-arus" onclick="openBulananModal()"><i class="fa-solid fa-plus"></i> Input Manual</button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Nominal</th>
                                <th>Held By</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-bulanan"></tbody>
                    </table>
                </div>
                <div class="finance-card-footer"><span>Total Bulanan:</span><div class="frame-total income" id="total-bulanan">Rp 0</div></div>
            </div>

            <!-- 2. SPP Harian (Absensi & Manual) -->
            <div class="finance-card">
                <div class="finance-card-header">
                    <div class="card-title-group"><div class="card-icon ic-harian"><i class="fa-solid fa-stopwatch"></i></div><h3>SPP Harian (Absensi & Manual)</h3></div>
                    <div style="display: flex; gap: 6px;">
                        <button class="btn-input-arus" style="background: var(--text-white); font-size: 0.75rem; padding: 5px 8px;" onclick="ubahNominalDefault('harian')" title="Ubah Nominal Default Harian"><i class="fa-solid fa-coins"></i>Nominal Default</button>
                        <button class="btn-input-arus" onclick="openModal('harian')"><i class="fa-solid fa-plus"></i> Input Arus</button>
                    </div>
                </div>
                <div style="background: rgba(255, 218, 133, 0.4); padding: 8px 12px; border-radius: 10px; margin-bottom: 10px; font-size: 0.75rem; font-weight: 800; color: var(--text-dark);">
                    <i class="fa-solid fa-circle-info" style="color: var(--sidebar-bg);"></i> <strong>Catatan:</strong> Klik status bayar untuk mengubah status menjadi "Terbayar" dan pilih admin eksekutor pemegang uang agar saldo "Held By" akurat.
                </div>

                <!-- Kotak Pencarian Atlet SPP Harian -->
                <div style="margin-bottom: 12px;">
                    <input type="text" id="searchAtletHarian" class="clay-input" placeholder="🔍 Cari nama atlet di SPP Harian..." oninput="renderFinanceTables()" style="width: 100%; padding: 8px 12px; font-size: 0.85rem;">
                </div>

                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Nominal</th>
                                <th>Status Bayar</th>
                                <th>Held By</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-harian"></tbody>
                    </table>
                </div>
                <div class="finance-card-footer"><span>Total Harian (Lunas):</span><div class="frame-total income" id="total-harian">Rp 0</div></div>
            </div>

            <!-- 3. Pendaftaran (Manual) -->
            <div class="finance-card">
                <div class="finance-card-header">
                    <div class="card-title-group"><div class="card-icon ic-daftar"><i class="fa-solid fa-user-plus"></i></div><h3>Pendaftaran (Manual)</h3></div>
                    <div style="display: flex; gap: 6px;">
                        <button class="btn-input-arus" style="background: var(--text-white); font-size: 0.75rem; padding: 5px 8px;" onclick="ubahNominalDefault('daftar')" title="Ubah Nominal Default Pendaftaran"><i class="fa-solid fa-coins"></i>Nominal Default</button>
                        <button class="btn-input-arus" onclick="openModal('daftar')"><i class="fa-solid fa-plus"></i> Input Arus</button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Nama</th>
                                <th>Nominal</th>
                                <th>Held By</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-daftar"></tbody>
                    </table>
                </div>
                <div class="finance-card-footer"><span>Total Pendaftaran:</span><div class="frame-total income" id="total-daftar">Rp 0</div></div>
            </div>

            <!-- 4. Lain-lain (Manual) -->
            <div class="finance-card">
                <div class="finance-card-header">
                    <div class="card-title-group"><div class="card-icon ic-lain"><i class="fa-solid fa-gifts"></i></div><h3>Lain-lain (Manual)</h3></div>
                    <div style="display: flex; gap: 6px;">
                        <button class="btn-input-arus" style="background: var(--text-white); font-size: 0.75rem; padding: 5px 8px;" onclick="ubahNominalDefault('lain')" title="Ubah Nominal Default Lain-lain"><i class="fa-solid fa-coins"></i>Nominal Default</button>
                        <button class="btn-input-arus" onclick="openModal('lain')"><i class="fa-solid fa-plus"></i> Input Arus</button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Catatan</th>
                                <th>Nominal</th>
                                <th>Held By</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-lain"></tbody>
                    </table>
                </div>
                <div class="finance-card-footer"><span>Total Lain-lain:</span><div class="frame-total income" id="total-lain">Rp 0</div></div>
            </div>

            <!-- 5. Pengeluaran (Manual) -->
            <div class="finance-card">
                <div class="finance-card-header">
                    <div class="card-title-group"><div class="card-icon ic-keluar"><i class="fa-solid fa-receipt"></i></div><h3>Pengeluaran (Manual)</h3></div>
                    <div style="display: flex; gap: 6px;">
                        <button class="btn-input-arus" style="background: var(--text-white); font-size: 0.75rem; padding: 5px 8px;" onclick="ubahNominalDefault('keluar')" title="Ubah Nominal Default Pengeluaran"><i class="fa-solid fa-coins"></i>Nominal Default</button>
                        <button class="btn-input-arus" onclick="openModal('keluar')"><i class="fa-solid fa-plus"></i> Input Keluar</button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tanggal</th>
                                <th>Catatan</th>
                                <th>Nominal</th>
                                <th>Held By</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-keluar"></tbody>
                    </table>
                </div>
                <div class="finance-card-footer"><span>Total Pengeluaran:</span><div class="frame-total expense" id="total-keluar">Rp 0</div></div>
            </div>
        </section>
    </main>

    <!-- Datalist global untuk pilihan nama atlet dari users -->
    <datalist id="atletList"></datalist>

    <!-- Modals -->
    <!-- Modal Input SPP Bulanan (Dengan Diskon & Toggle Diskon) -->
    <div class="modal-overlay" id="bulananModal" style="display: none;">
        <div class="modal-card">
            <h2><i class="fa-solid fa-calendar-check" style="color:var(--sidebar-bg);"></i> Input Arus SPP Bulanan</h2>
            <form id="bulananForm" onsubmit="handleBulananSubmit(event); return false;">
                <div class="form-group">
                    <label for="inputDateBulanan">Tanggal Pembayaran</label>
                    <input type="date" id="inputDateBulanan" class="clay-input" required>
                </div>
                <div class="form-group">
                    <label for="inputAtletBulanan">Nama Atlet</label>
                    <input list="atletList" id="inputAtletBulanan" class="clay-input" placeholder="Ketik atau pilih nama atlet..." autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="inputAmountBulanan">Nominal Bayar (Rp)</label>
                    <input type="number" id="inputAmountBulanan" class="clay-input" placeholder="Contoh: 150000" required>
                </div>
                <div class="form-group">
                    <label for="inputAccountBulanan">Held By (Admin Eksekutor)</label>
                    <div class="custom-select-wrapper">
                        <select id="inputAccountBulanan" class="clay-input" required></select>
                    </div>
                </div>

                <!-- Pengaturan Diskon dengan Toggle & Input Nilai -->
                <div class="form-group">
                    <label>Fungsi Diskon</label>
                    <div class="clay-checkbox-wrapper">
                        <label for="toggleDiscountBulanan" style="cursor:pointer; margin:0; font-weight:700; font-size:0.85rem; color:var(--text-dark);">Aktifkan Diskon (Potong Nominal)</label>
                        <input type="checkbox" id="toggleDiscountBulanan" class="toggle-real" checked style="display:none;" onchange="toggleDiscountField(this)">
                        <label for="toggleDiscountBulanan" class="toggle-switch-checkbox"></label>
                    </div>
                </div>
                <div class="form-group" id="groupDiscountBulanan">
                    <label for="inputDiscountBulanan">Besar Diskon (Rp)</label>
                    <input type="number" id="inputDiscountBulanan" class="clay-input" value="25000" placeholder="Contoh: 25000">
                </div>

                <div class="form-group">
                    <label for="inputKeteranganBulanan">Keterangan</label>
                    <input type="text" id="inputKeteranganBulanan" class="clay-input" placeholder="Misal: Lunas Bulan Ini">
                </div>
                <div class="modal-btns">
                    <button type="button" class="btn-clay btn-cancel" onclick="closeBulananModal()">Batal</button>
                    <button type="submit" class="btn-clay btn-save">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal General Transaksi (Harian, Daftar, Lain-lain, Pengeluaran) -->
    <div class="modal-overlay" id="transactionModal" style="display: none;">
        <div class="modal-card">
            <h2 id="modalTitle">Input Arus Keuangan</h2>
            <form id="transactionForm" onsubmit="handleFormSubmit(event); return false;">
                <input type="hidden" id="editCategory">
                <input type="hidden" id="editIndex">
                <div class="form-group">
                    <label for="inputDate">Tanggal</label>
                    <input type="date" id="inputDate" class="clay-input" required>
                </div>
                <div class="form-group" id="groupNameLabel">
                    <label id="labelNameInput" for="inputName">Nama / Catatan</label>
                    <input list="atletList" type="text" id="inputName" class="clay-input" placeholder="Masukkan nama atau catatan..." autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label for="inputAmount">Nominal (Rp)</label>
                    <input type="number" id="inputAmount" class="clay-input" placeholder="Contoh: 25000" required>
                </div>
                <div class="form-group" id="groupStatusBayar" style="display:none;">
                    <label for="inputStatusBayar">Status Bayar</label>
                    <div class="custom-select-wrapper">
                        <select id="inputStatusBayar" class="clay-input">
                            <option value="Belum Bayar">Belum Bayar</option>
                            <option value="Terbayar">Terbayar</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputAccount">Held By (Admin Eksekutor)</label>
                    <div class="custom-select-wrapper">
                        <select id="inputAccount" class="clay-input" required></select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputKeterangan">Keterangan</label>
                    <input type="text" id="inputKeterangan" class="clay-input" placeholder="Keterangan tambahan...">
                </div>
                <div class="modal-btns">
                    <button type="button" class="btn-clay btn-cancel" onclick="closeModal('transactionModal')">Batal</button>
                    <button type="submit" class="btn-clay btn-save">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pindah Tangankan Saldo Held By -->
    <div class="modal-overlay" id="transferSaldoModal" style="display: none;">
        <div class="modal-card">
            <h2><i class="fa-solid fa-right-left" style="color:var(--sidebar-bg);"></i> Pindah Tangankan Saldo Held By</h2>
            <form id="transferSaldoForm" onsubmit="handleTransferSaldo(event); return false;">
                <div class="form-group">
                    <label for="transferFromAdmin">Pilih Admin Pengirim (Sumber Saldo)</label>
                    <div class="custom-select-wrapper">
                        <select id="transferFromAdmin" class="clay-input" required></select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="transferToAdmin">Pilih Admin Tujuan (Penerima Saldo)</label>
                    <div class="custom-select-wrapper">
                        <select id="transferToAdmin" class="clay-input" required></select>
                    </div>
                </div>
                <div style="background: rgba(255, 184, 198, 0.3); padding: 12px; border-radius: 12px; margin-bottom: 15px; font-size: 0.8rem; font-weight: 800; color: var(--text-dark); line-height: 1.4;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: var(--expense-color);"></i> <strong>Catatan:</strong> Memindahkan saldo held by kepada admin terpilih wajib memindahkan <u>seluruh saldo/data</u> yang tercatat pada held by yang menyerahkan.
                </div>
                <div class="modal-btns">
                    <button type="button" class="btn-clay btn-cancel" onclick="closeTransferModal()">Batal</button>
                    <button type="submit" class="btn-clay btn-save">Pindah Tangankan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Khusus Pemilihan Admin (Held By) saat Mengubah Status Pembayaran Harian Menjadi Terbayar -->
    <div class="modal-overlay" id="modalStatusHarianAdmin" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div class="modal-card" style="background: var(--clay-pink); padding: 25px; border-radius: 30px; width: 350px; max-width: 90%; box-shadow: var(--clay-shadow-card);">
            <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 1.1rem; color: var(--text-dark); font-weight: 900;">Pilih Admin Penerima (Held By)</h3>
            <p style="font-size: 0.85rem; color: var(--text-gray); margin-bottom: 15px; font-weight: 800;">Tentukan siapa admin yang membawa/menerima pembayaran uang harian ini:</p>
            <form id="formStatusHarianAdmin" onsubmit="confirmToggleStatusBayar(event); return false;">
                <input type="hidden" id="statusHarianIndexTarget">
                <input type="hidden" id="statusHarianTargetValue">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="selectAdminPenerimaHarian" style="display: block; margin-bottom: 5px; font-weight: 900; color: var(--text-dark);">Admin Eksekutor</label>
                    <div class="custom-select-wrapper">
                        <select id="selectAdminPenerimaHarian" class="clay-input" style="width: 100%; padding: 8px;" required></select>
                    </div>
                </div>
                <div class="modal-btns">
                    <button type="button" class="btn-clay btn-cancel" onclick="closeStatusHarianAdminModal()">Batal</button>
                    <button type="submit" class="btn-clay btn-save">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JS Terpisah -->
    <script src="{{ asset('js/admin/finance.js') }}"></script>
    <script src="{{ asset('js/beranda_admin.js') }}"></script>
    <script>
        // Sinkronisasi data server Laravel ke JavaScript
        const serverAdminList = @json($systemAdmins);

        // --- SCRIPT PENERAPAN TEMA OTOMATIS DARI SETTING ---
        function applyAppTheme() {
            let savedTheme = localStorage.getItem('KILAT_THEME') ||
                             localStorage.getItem('appTheme') ||
                             localStorage.getItem('theme') ||
                             localStorage.getItem('KILAT_ACTIVE_THEME');

            if (savedTheme) {
                document.body.setAttribute('data-theme', savedTheme);
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            applyAppTheme();
            sanitizeHeldByData();
            initFinanceModule();
            applyActiveRoleDisplay();

            // Setup Custom Select Wrapper Event Listeners untuk panah dropdown selalu interaktif & responsif
            document.querySelectorAll('.custom-select-wrapper select').forEach(select => {
                select.addEventListener('focus', () => select.parentElement.classList.add('open'));
                select.addEventListener('blur', () => select.parentElement.classList.remove('open'));
                select.addEventListener('change', () => select.parentElement.classList.remove('open'));
            });

            const targetNode = document.getElementById('activeRoleName');
            if (targetNode) {
                const observer = new MutationObserver((mutations) => {
                    applyActiveRoleDisplay();
                });
                observer.observe(targetNode, { childList: true, characterData: true, subtree: true });
            }
        });

        function formatRp(angka) {
            return "Rp " + parseInt(angka || 0).toLocaleString("id-ID");
        }

        let financeDB = JSON.parse(localStorage.getItem('KILAT_FINANCE_DB')) || {
            bulanan: [],
            harian: [],
            daftar: [],
            lain: [],
            keluar: []
        };

        let defaultNominals = JSON.parse(localStorage.getItem('KILAT_DEFAULT_NOMINALS')) || {
            bulanan: 125000,
            harian: 25000,
            daftar: 50000,
            lain: 0,
            keluar: 0
        };

        if (!defaultNominals.harian || defaultNominals.harian < 25000) {
            defaultNominals.harian = 25000;
        }

        function saveFinanceDB() {
            localStorage.setItem('KILAT_FINANCE_DB', JSON.stringify(financeDB));
        }

        function saveDefaultNominals() {
            localStorage.setItem('KILAT_DEFAULT_NOMINALS', JSON.stringify(defaultNominals));
        }

        window.ubahNominalDefault = function(category) {
            let currentVal = defaultNominals[category] !== undefined ? defaultNominals[category] : (category === 'harian' ? 25000 : 0);
            let inputStr = prompt(`Masukkan nominal default baru untuk kategori [${category.toUpperCase()}]:`, currentVal);
            if (inputStr !== null) {
                let newNominal = parseInt(inputStr);
                if (!isNaN(newNominal) && newNominal >= 0) {
                    defaultNominals[category] = newNominal;
                    saveDefaultNominals();
                    alert(`✅ Nominal default untuk kategori "${category.toUpperCase()}" berhasil diubah menjadi ${formatRp(newNominal)}.`);
                } else {
                    alert("⚠️ Masukkan angka nominal yang valid!");
                }
            }
        };

        function getActiveRole() {
            let activeRole = localStorage.getItem('KILAT_ACTIVE_ROLE') ||
                             localStorage.getItem('KILAT_CURRENT_ROLE') ||
                             localStorage.getItem('activeRole') ||
                             localStorage.getItem('role');

            if (!activeRole) {
                try {
                    let userData = JSON.parse(
                        localStorage.getItem('KILAT_CURRENT_USER') ||
                        localStorage.getItem('kilat_user_data') ||
                        localStorage.getItem('currentUser') || '{}'
                    );
                    if (userData.role) {
                        activeRole = userData.role;
                    } else if (userData.nama || userData.name) {
                        activeRole = userData.nama || userData.name;
                    }
                } catch(e) {}
            }

            if (!activeRole) {
                let sidebarUserEl = document.querySelector('aside div, .sidebar-user, .user-profile, [class*="user"]');
                if (sidebarUserEl && sidebarUserEl.innerText) {
                    let lines = sidebarUserEl.innerText.split('\n');
                    activeRole = lines[0].trim();
                }
            }

            if (!activeRole || activeRole.toLowerCase() === 'coach' || activeRole === 'admin') {
                activeRole = serverAdminList[0] || 'Admin';
            }

            return activeRole;
        }

        function applyActiveRoleDisplay() {
            const roleNameEl = document.getElementById('activeRoleName');
            if (roleNameEl) {
                const correctRole = getActiveRole();
                if (roleNameEl.innerText !== correctRole) {
                    roleNameEl.innerText = correctRole;
                }
            }
        }

        // --- MENYELARASKAN PERSIS DENGAN JALUR AKUN ADMIN ASLI (SERVER & LOCALSTORAGE) ---
        function getValidAdminList() {
            let adminSet = new Set(serverAdminList);

            let registeredUsers = [];
            try {
                registeredUsers = JSON.parse(
                    localStorage.getItem('manageUsersData') ||
                    localStorage.getItem('KILAT_USERS') ||
                    localStorage.getItem('KILAT_USERS_LIST') || '[]'
                );
            } catch(e) {}

            if (registeredUsers.length > 0) {
                registeredUsers.forEach(u => {
                    let roleStr = (u.role || '').toLowerCase().trim();
                    let nameVal = u.namaLengkap || u.name || u.nama_lengkap || u.username;
                    if (roleStr === 'admin' || roleStr.includes('admin') || roleStr.includes('master')) {
                        if (nameVal && !nameVal.includes('@')) {
                            adminSet.add(nameVal);
                        }
                    }
                });
            }

            let currentActive = getActiveRole();
            if (currentActive && !currentActive.includes('@')) {
                adminSet.add(currentActive);
            }

            // Bersihkan sisa data dummy bawaan seperti Master Admin System jika tidak ada di database/local storage asli
            adminSet.delete("Master Admin System");

            if (adminSet.size === 0) {
                adminSet.add("admin 1");
                adminSet.add("damai");
            }

            return Array.from(adminSet);
        }

        function sanitizeHeldByData() {
            let validAdmins = getValidAdminList();
            let defaultAdmin = validAdmins[0] || 'Admin';

            ['bulanan', 'harian', 'daftar', 'lain', 'keluar'].forEach(cat => {
                if (financeDB[cat]) {
                    financeDB[cat].forEach(item => {
                        let currentAcc = (item.account || '').trim();
                        if (currentAcc === 'Master Admin System' || currentAcc.includes('@') || !validAdmins.includes(currentAcc)) {
                            item.account = defaultAdmin;
                        }
                    });
                }
            });
            saveFinanceDB();
        }

        function initFinanceModule() {
            const todayStr = new Date().toISOString().split('T')[0];
            if (document.getElementById('inputDateBulanan')) document.getElementById('inputDateBulanan').value = todayStr;
            if (document.getElementById('inputDate')) document.getElementById('inputDate').value = todayStr;

            applyActiveRoleDisplay();

            populateAdminSelects();
            populateAthleteDatalists();

            if (financeDB.harian && financeDB.harian.length > 0) {
                financeDB.harian.forEach(item => {
                    if (!item.amount || item.amount < 25000) {
                        item.amount = defaultNominals.harian || 25000;
                    }
                });
                saveFinanceDB();
            }

            syncBillingInvoicesToFinance(todayStr);

            renderFinanceTables();
            updateFinanceSummary();
            renderTreasurerBadges();
        }

        function getRegisteredAthletesList() {
            let storedAthletes = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
            if (storedAthletes.length === 0) {
                let users = JSON.parse(localStorage.getItem('KILAT_USERS')) || JSON.parse(localStorage.getItem('manageUsersData')) || [];
                users.forEach(u => {
                    let r = (u.role || '').toLowerCase();
                    if (r.includes('atlet') || r.includes('siswa') || !u.role) {
                        let name = u.namaLengkap || u.nama || u.username || u.name;
                        if (name && !storedAthletes.includes(name)) storedAthletes.push(name);
                    }
                });
            }
            return storedAthletes;
        }

        function populateAthleteDatalists() {
            let athletes = getRegisteredAthletesList();
            let datalist = document.getElementById('atletList');
            if (!datalist) {
                datalist = document.createElement('datalist');
                datalist.id = 'atletList';
                document.body.appendChild(datalist);
            }
            datalist.innerHTML = '';
            athletes.forEach(name => {
                datalist.innerHTML += `<option value="${name}">`;
            });

            ['inputAtletBulanan', 'inputNameBulanan', 'inputName'].forEach(id => {
                let el = document.getElementById(id);
                if (el) {
                    el.setAttribute('list', 'atletList');
                }
            });
        }

        function populateAdminSelects() {
            let validAdmins = getValidAdminList();

            let selectAccountEl = document.getElementById('inputAccount');
            if (selectAccountEl) {
                selectAccountEl.innerHTML = '';
                validAdmins.forEach(adm => {
                    selectAccountEl.innerHTML += `<option value="${adm}">${adm}</option>`;
                });
            }

            let selectBulananAccount = document.getElementById('inputAccountBulanan');
            if (selectBulananAccount) {
                selectBulananAccount.innerHTML = '';
                validAdmins.forEach(adm => {
                    selectBulananAccount.innerHTML += `<option value="${adm}">${adm}</option>`;
                });
            }

            let selectFrom = document.getElementById('transferFromAdmin');
            let selectTo = document.getElementById('transferToAdmin');
            if (selectFrom && selectTo) {
                selectFrom.innerHTML = '';
                selectTo.innerHTML = '';
                validAdmins.forEach(adm => {
                    selectFrom.innerHTML += `<option value="${adm}">${adm}</option>`;
                    selectTo.innerHTML += `<option value="${adm}">${adm}</option>`;
                });
            }
        }

        function cleanDuplicateHarianRecords() {
            if (!financeDB.harian || financeDB.harian.length === 0) return;

            financeDB.harian = financeDB.harian.filter(harianItem => {
                let hDate = harianItem.date || new Date().toISOString().split('T')[0];
                let hPeriod = hDate.substring(0, 7);
                let hName = (harianItem.name || '').toLowerCase().trim();

                let isAlreadyInBulanan = (financeDB.bulanan || []).some(b => {
                    let bName = (b.name || '').toLowerCase().trim();
                    let bPeriod = b.period || (b.date ? b.date.substring(0, 7) : '');
                    return bName === hName && bPeriod === hPeriod;
                });

                if (isAlreadyInBulanan) return false;

                let savedInvoices = JSON.parse(localStorage.getItem('KILAT_SAVED_INVOICES')) || [];
                let billingPaid = JSON.parse(localStorage.getItem('KILAT_BILLING_PAID')) || [];

                let isPaidInBilling = savedInvoices.some(inv => {
                    let invName = (inv.athlete?.name || inv.name || '').toLowerCase().trim();
                    let invPeriod = inv.period || (inv.dueDate ? inv.dueDate.substring(0, 7) : '');
                    let isPaidStatus = inv.status && inv.status.toLowerCase() === 'paid';
                    return invName === hName && invPeriod === hPeriod && isPaidStatus;
                }) || billingPaid.some(item => {
                    let itemName = (item.name || item.nickname || '').toLowerCase().trim();
                    let itemPeriod = item.period || (item.date ? item.date.substring(0, 7) : '');
                    return itemName === hName && itemPeriod === hPeriod;
                });

                if (isPaidInBilling) return false;

                return true;
            });
            saveFinanceDB();
        }

        function syncBillingInvoicesToFinance(todayStr) {
            let validAdmins = getValidAdminList();
            let defaultAdmin = validAdmins[0] || 'Admin';

            let savedInvoices = JSON.parse(localStorage.getItem('KILAT_SAVED_INVOICES')) || [];
            let billingPaid = JSON.parse(localStorage.getItem('KILAT_BILLING_PAID')) || [];

            let paidSourceItems = [];

            if (savedInvoices.length > 0) {
                let paidInvoices = savedInvoices.filter(inv => inv.status && inv.status.toLowerCase() === 'paid');
                paidSourceItems = paidInvoices.map(inv => ({
                    date: inv.dueDate || todayStr,
                    period: inv.period,
                    name: inv.athlete?.name || inv.name || 'Atlet',
                    amount: inv.total || 0,
                    account: validAdmins.includes(inv.account) ? inv.account : defaultAdmin
                }));
            }

            if (paidSourceItems.length === 0 && billingPaid.length > 0) {
                paidSourceItems = billingPaid.map(item => ({
                    date: item.date || todayStr,
                    period: item.period,
                    name: item.name || item.nickname || 'Atlet',
                    amount: item.amount || 0,
                    account: validAdmins.includes(item.account) ? item.account : defaultAdmin
                }));
            }

            let existingBulanan = financeDB.bulanan || [];
            let manualBulananItems = existingBulanan.filter(item => item.isManual === true);

            let newBulananList = [...manualBulananItems];

            paidSourceItems.forEach((item, idx) => {
                let periodVal = item.period || (item.date ? item.date.substring(0, 7) : todayStr.substring(0, 7));
                let exists = newBulananList.some(b => (b.name || '').toLowerCase().trim() === (item.name || '').toLowerCase().trim() && (b.period || '') === periodVal);
                if (!exists) {
                    let ketStr = item.period ? `Periode ${item.period}` : 'SPP Bulanan';
                    newBulananList.push({
                        id: item.id || 'bill_' + (idx + 1),
                        date: item.date || todayStr,
                        name: item.name || 'Atlet',
                        amount: parseInt(item.amount || 0),
                        account: validAdmins.includes(item.account) ? item.account : defaultAdmin,
                        keterangan: ketStr,
                        period: periodVal,
                        isManual: false
                    });
                }
            });

            financeDB.bulanan = newBulananList;
            cleanDuplicateHarianRecords();
        }

        function renderFinanceTables() {
            sanitizeHeldByData();
            cleanDuplicateHarianRecords();
            renderTableCategory('bulanan', 'list-bulanan', 'total-bulanan');
            renderTableHarianCustom();
            renderTableCategory('daftar', 'list-daftar', 'total-daftar');
            renderTableCategory('lain', 'list-lain', 'total-lain');
            renderTableCategory('keluar', 'list-keluar', 'total-keluar');
        }

        function renderTableHarianCustom() {
            const tbody = document.getElementById('list-harian');
            const totalEl = document.getElementById('total-harian');
            if (!tbody) return;

            tbody.innerHTML = '';
            let items = financeDB['harian'] || [];

            let searchInputEl = document.getElementById('searchAtletHarian');
            let keyword = searchInputEl ? searchInputEl.value.toLowerCase().trim() : '';

            let filteredItems = items.filter(item => {
                if (!keyword) return true;
                let nameMatch = (item.name || '').toLowerCase().includes(keyword);
                let ketMatch = (item.keterangan || '').toLowerCase().includes(keyword);
                return nameMatch || ketMatch;
            });

            let sumTotalLunas = 0;

            if (filteredItems.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--text-gray); padding: 15px; font-weight:800;">Tidak ada data transaksi harian yang cocok.</td></tr>`;
                if (totalEl) totalEl.innerText = formatRp(0);
                return;
            }

            filteredItems.forEach((item) => {
                let originalIndex = items.indexOf(item);
                let currentAmount = parseInt(item.amount || 0);
                if (item.statusBayar === 'Terbayar') {
                    sumTotalLunas += currentAmount;
                }

                let tr = document.createElement('tr');
                let statusBadgeClass = item.statusBayar === 'Terbayar' ? 'status-paid' : 'status-unpaid';
                let currentHeldBy = item.account || getValidAdminList()[0];
                tr.innerHTML = `
                    <td>${originalIndex + 1}</td>
                    <td>${item.date || '-'}</td>
                    <td><strong>${item.name || '-'}</strong></td>
                    <td>${formatRp(item.amount)}</td>
                    <td><span class="status-badge ${statusBadgeClass}" style="cursor:pointer;" onclick="promptStatusBayarAdmin(${originalIndex})" title="Klik untuk ubah status & pilih admin">${item.statusBayar || 'Belum Bayar'}</span></td>
                    <td><span class="badge-account"><i class="fa-solid fa-user-shield"></i> ${currentHeldBy}</span></td>
                    <td>
                        <button type="button" class="btn-action-mini btn-edit" onclick="editTransaction('harian', ${originalIndex})" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button type="button" class="btn-action-mini btn-delete" onclick="deleteTransaction('harian', ${originalIndex})" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            if (totalEl) totalEl.innerText = formatRp(sumTotalLunas);
        }

        function renderTableCategory(catKey, tbodyId, totalId) {
            if (catKey === 'harian') {
                renderTableHarianCustom();
                return;
            }

            const tbody = document.getElementById(tbodyId);
            const totalEl = document.getElementById(totalId);
            if (!tbody) return;

            tbody.innerHTML = '';
            let items = financeDB[catKey] || [];
            let sumTotal = 0;

            if (items.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:var(--text-gray); padding: 15px; font-weight:800;">Belum ada data transaksi.</td></tr>`;
                if (totalEl) totalEl.innerText = formatRp(0);
                return;
            }

            items.forEach((item, index) => {
                let currentAmount = parseInt(item.amount || 0);
                sumTotal += currentAmount;

                let tr = document.createElement('tr');
                let currentHeldBy = item.account || getValidAdminList()[0];
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${item.date || '-'}</td>
                    <td><strong>${item.name || item.catatan || '-'}</strong></td>
                    <td>${formatRp(item.amount)}</td>
                    <td><span class="badge-account"><i class="fa-solid fa-user-shield"></i> ${currentHeldBy}</span></td>
                    <td>${item.keterangan || '-'}</td>
                    <td>
                        <button type="button" class="btn-action-mini btn-edit" onclick="editTransaction('${catKey}', ${index})" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button type="button" class="btn-action-mini btn-delete" onclick="deleteTransaction('${catKey}', ${index})" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            if (totalEl) totalEl.innerText = formatRp(sumTotal);
        }

        function updateFinanceSummary() {
            let totalBulanan = (financeDB.bulanan || []).reduce((acc, curr) => acc + parseInt(curr.amount || 0), 0);
            let totalHarian = (financeDB.harian || []).reduce((acc, curr) => curr.statusBayar === 'Terbayar' ? acc + parseInt(curr.amount || 0) : acc, 0);
            let totalDaftar = (financeDB.daftar || []).reduce((acc, curr) => acc + parseInt(curr.amount || 0), 0);
            let totalLain = (financeDB.lain || []).reduce((acc, curr) => acc + parseInt(curr.amount || 0), 0);
            let totalKeluar = (financeDB.keluar || []).reduce((acc, curr) => acc + parseInt(curr.amount || 0), 0);

            let totalPiutang = (financeDB.harian || []).reduce((acc, curr) => (!curr.statusBayar || curr.statusBayar !== 'Terbayar') ? acc + parseInt(curr.amount || 0) : acc, 0);

            let grandIncome = totalBulanan + totalHarian + totalDaftar + totalLain;
            let grandNet = grandIncome - totalKeluar;

            const grandTotalVal = document.getElementById('grand-total-val') || document.getElementById('dashboardSaldo');
            const globalIncome = document.getElementById('global-income');
            const globalExpense = document.getElementById('global-expense');
            const globalPiutang = document.getElementById('global-piutang');

            if (grandTotalVal) grandTotalVal.innerText = formatRp(grandNet);
            if (globalIncome) globalIncome.innerText = `+ ${formatRp(grandIncome)}`;
            if (globalExpense) globalExpense.innerText = `- ${formatRp(totalKeluar)}`;
            if (globalPiutang) globalPiutang.innerText = formatRp(totalPiutang);
        }

        function renderTreasurerBadges() {
            const container = document.getElementById('treasurer-badges-container');
            if (!container) return;

            let validAdmins = getValidAdminList();
            let adminMap = {};

            populateAdminSelects();

            validAdmins.forEach(adm => { adminMap[adm] = 0; });

            ['bulanan', 'harian', 'daftar', 'lain'].forEach(cat => {
                (financeDB[cat] || []).forEach(item => {
                    let adminName = (item.account || '').trim();
                    if (!adminName || adminName === 'Master Admin System' || !adminMap.hasOwnProperty(adminName)) {
                        adminName = validAdmins[0] || 'Admin';
                    }
                    if (!adminMap.hasOwnProperty(adminName)) {
                        adminMap[adminName] = 0;
                    }
                    let amt = (cat === 'harian' && item.statusBayar !== 'Terbayar') ? 0 : parseInt(item.amount || 0);
                    adminMap[adminName] += amt;
                });
            });

            (financeDB.keluar || []).forEach(item => {
                let adminName = (item.account || '').trim();
                if (!adminName || adminName === 'Master Admin System' || !adminMap.hasOwnProperty(adminName)) {
                    adminName = validAdmins[0] || 'Admin';
                }
                if (!adminMap.hasOwnProperty(adminName)) {
                    adminMap[adminName] = 0;
                }
                let amt = parseInt(item.amount || 0);
                adminMap[adminName] -= amt;
            });

            let html = '';
            let adminsList = Object.keys(adminMap);
            if (adminsList.length === 0) {
                html = '<span style="font-size:0.8rem; color:var(--text-gray); font-weight:800;">Belum ada data Held By admin tercatat.</span>';
            } else {
                adminsList.forEach(adm => {
                    html += `
                        <div class="treasurer-badge">
                            <span class="badge-name"><i class="fa-solid fa-user-shield"></i> ${adm}</span>
                            <span class="badge-amount">${formatRp(adminMap[adm])}</span>
                        </div>
                    `;
                });
            }
            container.innerHTML = html;
        }

        window.openBulananModal = function() {
            const modal = document.getElementById('bulananModal');
            if (modal) {
                modal.classList.add('show');
                modal.style.display = 'flex';
            }

            const todayStr = new Date().toISOString().split('T')[0];
            let dateBulananEl = document.getElementById('inputDateBulanan');
            if (dateBulananEl) dateBulananEl.value = todayStr;

            let formEl = document.getElementById('bulananForm');
            if (formEl) formEl.reset();
            if (dateBulananEl) dateBulananEl.value = todayStr;

            let amountBulananEl = document.getElementById('inputAmountBulanan');
            if (amountBulananEl) amountBulananEl.value = defaultNominals.bulanan;

            let editIdxEl = document.getElementById('editBulananIndex');
            if (editIdxEl) editIdxEl.value = '';

            populateAdminSelects();
            populateAthleteDatalists();
        };

        window.closeBulananModal = function() {
            const modal = document.getElementById('bulananModal');
            if (modal) {
                modal.classList.remove('show');
                modal.style.display = 'none';
            }
        };

        window.handleBulananSubmit = function(e) {
            if (e) e.preventDefault();
            let validAdmins = getValidAdminList();
            let activeRole = getActiveRole();
            let defaultAdmin = validAdmins.includes(activeRole) ? activeRole : (validAdmins[0] || 'Admin');

            let dateInputEl = document.getElementById('inputDateBulanan');
            let nameInputEl = document.getElementById('inputAtletBulanan') || document.getElementById('inputNameBulanan');
            let amountInputEl = document.getElementById('inputAmountBulanan');
            let accountInputEl = document.getElementById('inputAccountBulanan');
            let ketInputEl = document.getElementById('inputKeteranganBulanan');
            let editIdxEl = document.getElementById('editBulananIndex');

            let dateVal = dateInputEl ? dateInputEl.value : new Date().toISOString().split('T')[0];
            let nameVal = nameInputEl ? nameInputEl.value.trim() : '';
            let amountVal = amountInputEl ? parseInt(amountInputEl.value) || defaultNominals.bulanan : defaultNominals.bulanan;
            let accountVal = accountInputEl ? (accountInputEl.value || defaultAdmin) : defaultAdmin;
            let periodVal = dateVal ? dateVal.substring(0, 7) : '';

            if (!nameVal) {
                alert("⚠️ Silakan masukkan atau pilih nama atlet terlebih dahulu.");
                if (nameInputEl) nameInputEl.focus();
                return false;
            }

            if (!financeDB.bulanan) financeDB.bulanan = [];

            let editIndex = editIdxEl && editIdxEl.value !== '' ? parseInt(editIdxEl.value) : -1;

            let existingIndex = financeDB.bulanan.findIndex(b => {
                let bName = (b.name || '').toLowerCase().trim();
                let bPeriod = b.period || (b.date ? b.date.substring(0, 7) : '');
                return bName === nameVal.toLowerCase() && bPeriod === periodVal;
            });

            if (existingIndex >= 0 && existingIndex !== editIndex) {
                alert(`⚠️ Peringatan:\nAtlet "${nameVal}" sudah tercatat melakukan pembayaran SPP Bulanan pada periode bulan ini (${periodVal}). Anda tidak dapat menginput pemasukan ganda pada bulan yang sama.`);
                return false;
            }

            if (editIndex >= 0 && financeDB.bulanan[editIndex]) {
                financeDB.bulanan[editIndex].date = dateVal;
                financeDB.bulanan[editIndex].name = nameVal;
                financeDB.bulanan[editIndex].amount = amountVal;
                financeDB.bulanan[editIndex].account = accountVal;
                financeDB.bulanan[editIndex].keterangan = ketInputEl ? (ketInputEl.value.trim() || 'SPP Bulanan Manual') : 'SPP Bulanan Manual';
                financeDB.bulanan[editIndex].period = periodVal;
            } else {
                let newItem = {
                    id: 'manual_' + Date.now(),
                    date: dateVal,
                    name: nameVal,
                    amount: amountVal,
                    account: accountVal,
                    keterangan: ketInputEl ? (ketInputEl.value.trim() || 'SPP Bulanan Manual') : 'SPP Bulanan Manual',
                    period: periodVal,
                    isManual: true
                };
                financeDB.bulanan.unshift(newItem);
            }

            cleanDuplicateHarianRecords();
            saveFinanceDB();
            renderFinanceTables();
            updateFinanceSummary();
            renderTreasurerBadges();
            closeBulananModal();

            let formEl = document.getElementById('bulananForm');
            if (formEl) formEl.reset();
            return false;
        };

        window.openModal = function(category) {
            const modal = document.getElementById('transactionModal');
            const titleEl = document.getElementById('modalTitle');
            const catInput = document.getElementById('editCategory');
            const idxInput = document.getElementById('editIndex');
            const groupStatus = document.getElementById('groupStatusBayar');
            const labelName = document.getElementById('labelNameInput');
            const inputAmountEl = document.getElementById('inputAmount');
            const inputAccount = document.getElementById('inputAccount');
            const inputDateEl = document.getElementById('inputDate');
            const inputNameEl = document.getElementById('inputName');

            if (catInput) catInput.value = category;
            if (idxInput) idxInput.value = '';

            if (modal) {
                modal.classList.add('show');
                modal.style.display = 'flex';
            }

            const todayStr = new Date().toISOString().split('T')[0];
            if (inputDateEl) inputDateEl.value = todayStr;

            populateAdminSelects();
            populateAthleteDatalists();

            if (inputNameEl) {
                inputNameEl.setAttribute('list', 'atletList');
            }

            let activeRole = getActiveRole();
            let validAdmins = getValidAdminList();
            if (inputAccount && inputAccount.tagName === 'SELECT') {
                if (validAdmins.includes(activeRole)) {
                    inputAccount.value = activeRole;
                } else {
                    inputAccount.value = validAdmins[0] || '';
                }
            }

            let formEl = document.getElementById('transactionForm');
            if (formEl) formEl.reset();
            if (catInput) catInput.value = category;
            if (inputDateEl) inputDateEl.value = todayStr;

            let defaultFallback = defaultNominals[category] !== undefined ? defaultNominals[category] : (category === 'harian' ? 25000 : 0);

            if (category === 'harian') {
                if (titleEl) titleEl.innerText = 'Input Arus SPP Harian';
                if (groupStatus) groupStatus.style.display = 'block';
                if (labelName) labelName.innerText = 'Nama Atlet';
                if (inputAmountEl) inputAmountEl.value = defaultFallback;
            } else if (category === 'daftar') {
                if (titleEl) titleEl.innerText = 'Input Arus Pendaftaran';
                if (groupStatus) groupStatus.style.display = 'none';
                if (labelName) labelName.innerText = 'Nama Calon Atlet';
                if (inputAmountEl) inputAmountEl.value = defaultFallback;
            } else if (category === 'lain') {
                if (titleEl) titleEl.innerText = 'Input Arus Lain-lain';
                if (groupStatus) groupStatus.style.display = 'none';
                if (labelName) labelName.innerText = 'Catatan / Sumber Pemasukan';
                if (inputAmountEl) inputAmountEl.value = defaultFallback;
            } else if (category === 'keluar') {
                if (titleEl) titleEl.innerText = 'Input Arus Pengeluaran';
                if (groupStatus) groupStatus.style.display = 'none';
                if (labelName) labelName.innerText = 'Keperluan / Catatan Pengeluaran';
                if (inputAmountEl) inputAmountEl.value = defaultFallback;
            }
        };

        window.closeModal = function(modalId = 'transactionModal') {
            const modalEl = document.getElementById(modalId);
            if (modalEl) {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
            }
        };

        window.handleFormSubmit = function(e) {
            if (e) e.preventDefault();

            let catInputEl = document.getElementById('editCategory');
            let idxInputEl = document.getElementById('editIndex');
            let category = catInputEl ? catInputEl.value : 'harian';
            let editIndex = idxInputEl && idxInputEl.value !== '' ? parseInt(idxInputEl.value) : -1;

            let dateInputEl = document.getElementById('inputDate');
            let nameInputEl = document.getElementById('inputName');
            let amountInputEl = document.getElementById('inputAmount');
            let statusBayarEl = document.getElementById('inputStatusBayar');
            let accountInputEl = document.getElementById('inputAccount');
            let ketInputEl = document.getElementById('inputKeterangan');

            let validAdmins = getValidAdminList();
            let dateVal = dateInputEl ? dateInputEl.value : new Date().toISOString().split('T')[0];
            let nameVal = nameInputEl ? nameInputEl.value.trim() : '';
            let defaultFallback = defaultNominals[category] !== undefined ? defaultNominals[category] : (category === 'harian' ? 25000 : 0);
            let amountVal = amountInputEl ? parseInt(amountInputEl.value) || defaultFallback : defaultFallback;
            let accountVal = accountInputEl ? (accountInputEl.value.trim() || validAdmins[0]) : validAdmins[0];
            let statusBayarVal = statusBayarEl ? statusBayarEl.value : 'Belum Bayar';
            let ketVal = ketInputEl ? (ketInputEl.value.trim() || '-') : '-';

            if (!nameVal) {
                alert("⚠️ Nama atlet atau catatan wajib diisi.");
                if (nameInputEl) nameInputEl.focus();
                return false;
            }

            if (category === 'harian') {
                let harianMonthPeriod = dateVal ? dateVal.substring(0, 7) : new Date().toISOString().substring(0, 7);
                let isAlreadyInBulanan = (financeDB.bulanan || []).some(b => {
                    let bName = (b.name || '').toLowerCase().trim();
                    let bPeriod = b.period || (b.date ? b.date.substring(0, 7) : '');
                    return bName === nameVal.toLowerCase() && bPeriod === harianMonthPeriod;
                });

                let savedInvoices = JSON.parse(localStorage.getItem('KILAT_SAVED_INVOICES')) || [];
                let billingPaid = JSON.parse(localStorage.getItem('KILAT_BILLING_PAID')) || [];
                let isPaidInBilling = savedInvoices.some(inv => {
                    let invName = (inv.athlete?.name || inv.name || '').toLowerCase().trim();
                    let invPeriod = inv.period || (inv.dueDate ? inv.dueDate.substring(0, 7) : '');
                    let isPaidStatus = inv.status && inv.status.toLowerCase() === 'paid';
                    return invName === nameVal.toLowerCase() && invPeriod === harianMonthPeriod && isPaidStatus;
                }) || billingPaid.some(item => {
                    let itemName = (item.name || item.nickname || '').toLowerCase().trim();
                    let itemPeriod = item.period || (item.date ? item.date.substring(0, 7) : '');
                    return itemName === nameVal.toLowerCase() && itemPeriod === harianMonthPeriod;
                });

                if (isAlreadyInBulanan || isPaidInBilling) {
                    alert(`⚠️ Aturan Filter Silang Mutlak:\nAtlet "${nameVal}" sudah tercatat melunasi SPP Bulanan/Billing pada periode bulan ini (${harianMonthPeriod}). Data harian/piutang tidak boleh dimasukkan.`);
                    return false;
                }
            }

            if (!financeDB[category]) financeDB[category] = [];

            if (editIndex >= 0 && financeDB[category][editIndex]) {
                financeDB[category][editIndex].date = dateVal;
                financeDB[category][editIndex].name = nameVal;
                financeDB[category][editIndex].amount = amountVal;
                financeDB[category][editIndex].account = accountVal;
                financeDB[category][editIndex].keterangan = ketVal;
                if (category === 'harian') {
                    financeDB[category][editIndex].statusBayar = statusBayarVal;
                }
            } else {
                let newItem = {
                    date: dateVal,
                    name: nameVal,
                    amount: amountVal,
                    account: accountVal,
                    keterangan: ketVal
                };

                if (category === 'harian') {
                    newItem.statusBayar = statusBayarVal;
                }

                financeDB[category].unshift(newItem);
            }

            saveFinanceDB();
            renderFinanceTables();
            updateFinanceSummary();
            renderTreasurerBadges();
            closeModal('transactionModal');

            let formEl = document.getElementById('transactionForm');
            if (formEl) formEl.reset();
            return false;
        };

        window.editTransaction = function(category, index) {
            let item = financeDB[category] && financeDB[category][index];
            if (!item) return;

            const todayStr = new Date().toISOString().split('T')[0];

            if (category === 'bulanan') {
                openBulananModal();
                let nameEl = document.getElementById('inputAtletBulanan') || document.getElementById('inputNameBulanan');
                let dateEl = document.getElementById('inputDateBulanan');
                let amountEl = document.getElementById('inputAmountBulanan');
                let accountEl = document.getElementById('inputAccountBulanan');
                let ketEl = document.getElementById('inputKeteranganBulanan');

                if (nameEl) nameEl.value = item.name || '';
                if (dateEl) dateEl.value = item.date || todayStr;
                if (amountEl) amountEl.value = item.amount || 0;
                if (accountEl) accountEl.value = item.account || getValidAdminList()[0];
                if (ketEl) ketEl.value = item.keterangan || '';

                let bulananForm = document.getElementById('bulananForm');
                let editIdxEl = document.getElementById('editBulananIndex');
                if (!editIdxEl && bulananForm) {
                    editIdxEl = document.createElement('input');
                    editIdxEl.type = 'hidden';
                    editIdxEl.id = 'editBulananIndex';
                    bulananForm.appendChild(editIdxEl);
                }
                if (editIdxEl) editIdxEl.value = index;

            } else {
                openModal(category);
                let dateEl = document.getElementById('inputDate');
                let nameEl = document.getElementById('inputName');
                let amountEl = document.getElementById('inputAmount');
                let statusEl = document.getElementById('inputStatusBayar');
                let accountEl = document.getElementById('inputAccount');
                let ketEl = document.getElementById('inputKeterangan');
                let idxEl = document.getElementById('editIndex');

                if (dateEl) dateEl.value = item.date || todayStr;
                if (nameEl) nameEl.value = item.name || item.catatan || '';
                if (amountEl) amountEl.value = item.amount || 0;
                if (statusEl) statusEl.value = item.statusBayar || 'Belum Bayar';
                if (accountEl) accountEl.value = item.account || getValidAdminList()[0];
                if (ketEl) ketEl.value = item.keterangan || '';
                if (idxEl) idxEl.value = index;
            }
        };

        window.promptStatusBayarAdmin = function(index) {
            let item = financeDB.harian[index];
            if (!item) return;

            let targetStatus = item.statusBayar === 'Terbayar' ? 'Belum Bayar' : 'Terbayar';

            if (targetStatus === 'Belum Bayar') {
                item.statusBayar = targetStatus;
                saveFinanceDB();
                renderFinanceTables();
                updateFinanceSummary();
                renderTreasurerBadges();
                return;
            }

            let modal = document.getElementById('modalStatusHarianAdmin');
            let indexInput = document.getElementById('statusHarianIndexTarget');
            let valInput = document.getElementById('statusHarianTargetValue');
            let selectAdmin = document.getElementById('selectAdminPenerimaHarian');

            if (indexInput) indexInput.value = index;
            if (valInput) valInput.value = targetStatus;

            if (selectAdmin) {
                let validAdmins = getValidAdminList();
                selectAdmin.innerHTML = '';
                validAdmins.forEach(adm => {
                    let selectedAttr = (adm === item.account) ? 'selected' : '';
                    selectAdmin.innerHTML += `<option value="${adm}" ${selectedAttr}>${adm}</option>`;
                });
            }

            if (modal) {
                modal.classList.add('show');
                modal.style.setProperty('display', 'flex', 'important');
            }
        };

        window.closeStatusHarianAdminModal = function() {
            let modal = document.getElementById('modalStatusHarianAdmin');
            if (modal) {
                modal.classList.remove('show');
                modal.style.setProperty('display', 'none', 'important');
            }
        };

        window.confirmToggleStatusBayar = function(e) {
            if (e) e.preventDefault();
            let indexInput = document.getElementById('statusHarianIndexTarget');
            let selectAdmin = document.getElementById('selectAdminPenerimaHarian');

            if (indexInput && selectAdmin) {
                let idx = parseInt(indexInput.value);
                let chosenAdmin = selectAdmin.value;

                let item = financeDB.harian[idx];
                if (item) {
                    item.statusBayar = 'Terbayar';
                    item.account = chosenAdmin;

                    saveFinanceDB();
                    renderFinanceTables();
                    updateFinanceSummary();
                    renderTreasurerBadges();
                }
            }

            closeStatusHarianAdminModal();
            return false;
        };

        window.deleteTransaction = function(category, index) {
            if (confirm("Yakin ingin menghapus catatan transaksi ini?")) {
                financeDB[category].splice(index, 1);
                saveFinanceDB();
                renderFinanceTables();
                updateFinanceSummary();
                renderTreasurerBadges();
            }
        };

        window.openTransferModal = function() {
            const modal = document.getElementById('transferSaldoModal');
            if (modal) {
                modal.classList.add('show');
                modal.style.display = 'flex';
            }
            populateAdminSelects();
        };

        window.closeTransferModal = function() {
            const modal = document.getElementById('transferSaldoModal');
            if (modal) {
                modal.classList.remove('show');
                modal.style.display = 'none';
            }
        };

        window.handleTransferSaldo = function(e) {
            if (e) e.preventDefault();
            let fromAdmin = document.getElementById('transferFromAdmin').value;
            let toAdmin = document.getElementById('transferToAdmin').value;

            if (fromAdmin === toAdmin) {
                alert("⚠️ Admin pengirim dan penerima tidak boleh sama!");
                return false;
            }

            let countTransferred = 0;
            ['bulanan', 'harian', 'daftar', 'lain', 'keluar'].forEach(cat => {
                (financeDB[cat] || []).forEach(item => {
                    if (item.account === fromAdmin) {
                        item.account = toAdmin;
                        countTransferred++;
                    }
                });
            });

            saveFinanceDB();
            renderFinanceTables();
            updateFinanceSummary();
            renderTreasurerBadges();
            closeTransferModal();

            alert(`✅ Berhasil memindahtangankan ${countTransferred} data transaksi dari "${fromAdmin}" ke "${toAdmin}"!`);
            return false;
        };

        // --- FUNGSI EKSPOR KE EXCEL / SPREADSHEET (CSV) DENGAN PADDING KOLOM SERAGAM ---
        window.exportToExcel = function() {
            let csvRows = [];
            const maxCols = 6;

            function addRow(arr) {
                let row = [...arr];
                while (row.length < maxCols) {
                    row.push("");
                }
                csvRows.push(row);
            }

            addRow(["REKAPITULASI KEUANGAN SEKOLAH SEPATU RODA"]);
            addRow([]);

            addRow(["=== SPP BULANAN (OTOMATIS & MANUAL) ==="]);
            addRow(["No", "Tanggal", "Nama", "Nominal", "Held By", "Keterangan"]);
            let totalBulananVal = 0;
            (financeDB.bulanan || []).forEach((item, idx) => {
                let amt = parseInt(item.amount || 0);
                totalBulananVal += amt;
                addRow([
                    idx + 1,
                    item.date || '-',
                    `"${(item.name || '').replace(/"/g, '""')}"`,
                    amt,
                    `"${(item.account || '-').replace(/"/g, '""')}"`,
                    `"${(item.keterangan || '').replace(/"/g, '""')}"`
                ]);
            });
            addRow(["Total Bulanan", "", "", totalBulananVal, "", ""]);
            addRow([]);

            addRow(["=== SPP HARIAN (ABSENSI & MANUAL) ==="]);
            addRow(["No", "Tanggal", "Nama", "Nominal", "Status Bayar", "Held By"]);
            let totalHarianVal = 0;
            (financeDB.harian || []).forEach((item, idx) => {
                let amt = parseInt(item.amount || 0);
                if (item.statusBayar === 'Terbayar') totalHarianVal += amt;
                addRow([
                    idx + 1,
                    item.date || '-',
                    `"${(item.name || '').replace(/"/g, '""')}"`,
                    amt,
                    `"${(item.statusBayar || 'Belum Bayar').replace(/"/g, '""')}"`,
                    `"${(item.account || '-').replace(/"/g, '""')}"`
                ]);
            });
            addRow(["Total Harian (Lunas)", "", "", totalHarianVal, "", ""]);
            addRow([]);

            addRow(["=== PENDAFTARAN (MANUAL) ==="]);
            addRow(["No", "Tanggal", "Nama", "Nominal", "Held By", "Keterangan"]);
            let totalDaftarVal = 0;
            (financeDB.daftar || []).forEach((item, idx) => {
                let amt = parseInt(item.amount || 0);
                totalDaftarVal += amt;
                addRow([
                    idx + 1,
                    item.date || '-',
                    `"${(item.name || '').replace(/"/g, '""')}"`,
                    amt,
                    `"${(item.account || '-').replace(/"/g, '""')}"`,
                    `"${(item.keterangan || '').replace(/"/g, '""')}"`
                ]);
            });
            addRow(["Total Pendaftaran", "", "", totalDaftarVal, "", ""]);
            addRow([]);

            addRow(["=== LAIN-LAIN (MANUAL) ==="]);
            addRow(["No", "Tanggal", "Catatan", "Nominal", "Held By", "Keterangan"]);
            let totalLainVal = 0;
            (financeDB.lain || []).forEach((item, idx) => {
                let amt = parseInt(item.amount || 0);
                totalLainVal += amt;
                addRow([
                    idx + 1,
                    item.date || '-',
                    `"${(item.name || item.catatan || '').replace(/"/g, '""')}"`,
                    amt,
                    `"${(item.account || '-').replace(/"/g, '""')}"`,
                    `"${(item.keterangan || '').replace(/"/g, '""')}"`
                ]);
            });
            addRow(["Total Lain-lain", "", "", totalLainVal, "", ""]);
            addRow([]);

            addRow(["=== PENGELUARAN (MANUAL) ==="]);
            addRow(["No", "Tanggal", "Catatan", "Nominal", "Held By", "Keterangan"]);
            let totalKeluarVal = 0;
            (financeDB.keluar || []).forEach((item, idx) => {
                let amt = parseInt(item.amount || 0);
                totalKeluarVal += amt;
                addRow([
                    idx + 1,
                    item.date || '-',
                    `"${(item.name || item.catatan || '').replace(/"/g, '""')}"`,
                    amt,
                    `"${(item.account || '-').replace(/"/g, '""')}"`,
                    `"${(item.keterangan || '').replace(/"/g, '""')}"`
                ]);
            });
            addRow(["Total Pengeluaran", "", "", totalKeluarVal, "", ""]);
            addRow([]);

            let grandIncome = totalBulananVal + totalHarianVal + totalDaftarVal + totalLainVal;
            let grandNet = grandIncome - totalKeluarVal;
            addRow(["RINGKASAN KEUANGAN"]);
            addRow(["Total Pemasukan", grandIncome]);
            addRow(["Total Pengeluaran", totalKeluarVal]);
            addRow(["Saldo Bersih", grandNet]);

            let csvString = "\uFEFF" + csvRows.map(row => row.join(",")).join("\n");

            const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            let dateStr = new Date().toISOString().split('T')[0];
            link.setAttribute('download', `Laporan_Keuangan_SekolahSepatuRoda_${dateStr}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            alert("✅ Laporan Keuangan berhasil diekspor ke file Excel/Spreadsheet secara rapi sesuai kolom dan baris!");
        };

        function toggleDiscountField(checkbox) {
            const groupDisc = document.getElementById('groupDiscountBulanan');
            if (groupDisc) {
                groupDisc.style.display = checkbox.checked ? 'block' : 'none';
            }
        }

        const originalHandleBulananSubmit = window.handleBulananSubmit;
        window.handleBulananSubmit = function(e) {
            const toggleDisc = document.getElementById('toggleDiscountBulanan');
            const amountInput = document.getElementById('inputAmountBulanan');
            const discountInput = document.getElementById('inputDiscountBulanan');

            if (toggleDisc && toggleDisc.checked && amountInput && discountInput) {
                let baseAmount = parseInt(amountInput.value) || 0;
                let discountInputVal = parseInt(discountInput.value) || 0;
                let finalAmount = Math.max(0, baseAmount - discountInputVal);
                amountInput.value = finalAmount;
            }

            if (typeof originalHandleBulananSubmit === 'function') {
                return originalHandleBulananSubmit(e);
            }
            return true;
        };
    </script>
</body>
</html>
