<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Latihan - Sekolah Sepatu Roda (Claymorphism)</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/absence.css') }}">

    <!-- Script Sinkronisasi Tema Global -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let savedFolder = localStorage.getItem('KILAT_CSS_FOLDER') || 'css';
            const linkTag = document.getElementById('mainStylesheet');
            if (linkTag) {
                let currentHref = linkTag.getAttribute('href');
                let fileName = currentHref.split('/').pop();
                linkTag.setAttribute('href', `{{ asset('') }}${savedFolder}/${fileName}`);
            }
        });
    </script>
</head>
<body>
    <!-- SIDEBAR -->
    @include('layouts.sidebar')

    <main class="main-content" id="mainContent">
        <header class="header">
            <h1>Absensi Latihan</h1>
            <div class="header-icons">
                <div class="icon-btn" title="Cetak Laporan" onclick="window.print()"><i class="fa-solid fa-print"></i></div>
            </div>
        </header>

        <!-- KARTU STATISTIK ABSENSI BERDAMPINGAN -->
        <section class="absence-stats-grid">
            <div class="absence-stat-card">
                <div class="absence-stat-icon"><i class="fa-solid fa-users"></i></div>
                <div class="absence-stat-info">
                    <h3>Total Terdaftar</h3>
                    <div class="value" id="stat-total">0</div>
                </div>
            </div>
            <div class="absence-stat-card">
                <div class="absence-stat-icon"><i class="fa-solid fa-check"></i></div>
                <div class="absence-stat-info">
                    <h3>Masuk</h3>
                    <div class="value" id="stat-masuk" style="color: var(--c-masuk);">0</div>
                </div>
            </div>
            <div class="absence-stat-card">
                <div class="absence-stat-icon"><i class="fa-solid fa-xmark"></i></div>
                <div class="absence-stat-info">
                    <h3>Tidak Masuk</h3>
                    <div class="value" id="stat-tidak-masuk" style="color: var(--c-tidak-masuk);">0</div>
                </div>
            </div>
        </section>

        <!-- TOOLBAR / FILTER KONTROL PANEL -->
        <section class="toolbar absence-toolbar">
            <div class="absence-filter-row">
                <input type="date" id="filterDate" class="clay-input-absence" onchange="renderAttendance()">
                <select id="filterGroup" class="clay-input-absence" onchange="renderAttendance()">
                    <option value="All">Semua Kelas</option>
                    <option value="Pemula">Pemula</option>
                    <option value="Junior 1">Junior 1</option>
                    <option value="Junior 2">Junior 2</option>
                </select>
                <div class="search-bar-clay">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchAthleteInput" placeholder="Cari nama atlet..." onkeyup="renderAttendance()">
                </div>
            </div>
        </section>

        <!-- Frame Tabel Sesuai Struktur 5 Kolom -->
        <div class="table-responsive">
            <div class="clay-table-grid clay-table-header">
                <div class="name-cell">
                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this.checked)" title="Pilih Semua">
                </div>
                <div>NAMA LENGKAP</div>
                <div>STATUS KEHADIRAN</div>
                <div>KELAS</div>
                <div>STATUS</div>
            </div>
            <div id="attendanceContainer"></div>
        </div>
    </main>

    <!-- JS Terpisah -->
    <script src="{{ asset('js/admin/absence.js') }}"></script>
    <script src="{{ asset('js/beranda_admin.js') }}"></script>
</body>
</html>
