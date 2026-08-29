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
                    <div class="value" id="stat-total">{{ count($athletes ?? []) }}</div>
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
                <input type="date" id="filterDate" class="clay-input-absence" onchange="renderAttendance()" value="{{ date('Y-m-d') }}">
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
            <div id="attendanceContainer">
                @forelse($athletes ?? [] as $athlete)
                    <div class="clay-table-grid clay-table-row athlete-row" data-class="{{ $athlete->kelas ?? 'Pemula' }}" data-name="{{ strtolower($athlete->nama_lengkap ?? $athlete->name) }}">
                        <div class="name-cell">
                            <input type="checkbox" name="selected_athletes[]" value="{{ $athlete->id ?? $athlete->nickname }}" class="row-checkbox athlete-checkbox" onchange="toggleAttendanceRow('{{ $athlete->id ?? $athlete->nickname }}', this.checked)">
                        </div>
                        <div class="font-semibold">{{ $athlete->nama_lengkap ?? $athlete->name ?? $athlete->nickname }}</div>
                        <div class="attendance-options">
                            <button type="button" class="btn-att btn-status-active" onclick="setAttendanceStatus('{{ $athlete->id ?? $athlete->nickname }}', 'masuk')" style="padding: 6px 12px; font-size:0.75rem; cursor:pointer; border-radius:8px; border:none; font-weight:800; background:var(--c-masuk); color:#fff; box-shadow:var(--clay-shadow-btn);"><i class="fa-solid fa-check"></i> Masuk</button>
                            <button type="button" class="btn-att btn-status-inactive" onclick="setAttendanceStatus('{{ $athlete->id ?? $athlete->nickname }}', 'tidak_masuk')" style="padding: 6px 12px; font-size:0.75rem; cursor:pointer; border-radius:8px; border:none; font-weight:800; background:var(--bg-main); color:var(--text-dark); box-shadow:var(--clay-shadow-btn);"><i class="fa-solid fa-xmark"></i> Tidak</button>
                        </div>
                        <div>{{ $athlete->kelas ?? 'Pemula' }}</div>
                        <div><span class="badge-status sb-aktif" style="background:var(--c-masuk); color:#fff; padding:4px 8px; border-radius:6px; font-weight:800; font-size:0.75rem;">{{ $athlete->status ?? 'Aktif' }}</span></div>
                    </div>
                @empty
                    <div class="text-center py-4 text-gray-500" style="padding: 20px; text-align: center; grid-column: span 5;">Belum ada data atlet terdaftar dari Appendix.</div>
                @endforelse
            </div>
        </div>
    </main>

    <!-- Injeksi Data Atlet dari Backend Laravel ke JS (Termasuk Sinkronisasi LocalStorage Appendix) -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let serverAthletes = @json($athletes ?? []);

            // Gabungkan sumber data dari Appendix localStorage seperti pada Pusat Akun
            let athletesList = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
            let athletesDataStore = JSON.parse(localStorage.getItem('athletes_data')) || [];

            let combinedMap = new Map();

            // Masukkan data dari server Laravel terlebih dahulu
            serverAthletes.forEach(usr => {
                let identifier = (usr.id || usr.nickname || usr.name || usr.nama_lengkap).toString();
                combinedMap.set(identifier, {
                    id: identifier,
                    nama_lengkap: usr.nama_lengkap || usr.name || usr.nickname,
                    kelas: usr.kelas || 'Pemula',
                    status: usr.status || 'Aktif'
                });
            });

            // Masukkan data dari KILAT_ATHLETES_LIST / KILAT_BIO_
            athletesList.forEach(nick => {
                let bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nick)) || {};
                let identifier = nick.toString();
                if (!combinedMap.has(identifier)) {
                    combinedMap.set(identifier, {
                        id: identifier,
                        nama_lengkap: bio.fullName || nick,
                        kelas: bio.kelas || 'Pemula',
                        status: bio.status || 'Aktif'
                    });
                }
            });

            // Masukkan data dari athletes_data store Appendix
            athletesDataStore.forEach(item => {
                let nick = item.name || item.nickname || item.id;
                let identifier = (item.id || nick).toString();
                if (identifier && !combinedMap.has(identifier)) {
                    combinedMap.set(identifier, {
                        id: identifier,
                        nama_lengkap: item.fullName || item.name || nick,
                        kelas: item.kelas || 'Pemula',
                        status: item.status || 'Aktif'
                    });
                }
            });

            window.allAthletes = Array.from(combinedMap.values());
            if (typeof window.renderAttendance === 'function') {
                window.renderAttendance();
            }
        });
    </script>

    <!-- JS Terpisah -->
    <script src="{{ asset('js/admin/absence.js') }}"></script>
    <script src="{{ asset('js/beranda_admin.js') }}"></script>
</body>
</html>
