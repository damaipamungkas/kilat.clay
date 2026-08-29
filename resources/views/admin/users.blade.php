@php
    // Pusat Akun Manajemen - KILAT⚡
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pusat Akun - Sekolah Sepatu Roda (Claymorphism)</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah dengan ID Unik agar Dinamis Mengikuti Pengaturan Tema -->
    <link rel="stylesheet" id="dashboardStylesheet" href="{{ asset('css/admin_dashboard.css') }}">
    <link rel="stylesheet" id="usersStylesheet" href="{{ asset('css/admin/users.css') }}">
</head>
<body data-theme="">

    @include('layouts.sidebar')

    <main class="main-content" id="mainContent">
        <header class="header">
            <h1>Pusat Akun</h1>
            <div class="header-icons">
                <div class="icon-btn" title="Notifikasi"><i class="fa-solid fa-bell"></i></div>
                <a href="{{ route('profil') }}" class="icon-btn" title="Menuju Profil Akun"><i class="fa-solid fa-user"></i></a>
            </div>
        </header>

        <section class="stats-grid">
            <div class="stat-card c-blue"><div class="stat-icon"><i class="fa-solid fa-shield-halved"></i></div><div class="stat-info"><h3>Admin Sistem</h3><div class="value" id="count-admin">0</div></div></div>
            <div class="stat-card c-green"><div class="stat-icon"><i class="fa-solid fa-chalkboard-user"></i></div><div class="stat-info"><h3>Coach / Pelatih</h3><div class="value" id="count-coach">0</div></div></div>
            <div class="stat-card c-pink"><div class="stat-icon"><i class="fa-solid fa-users"></i></div><div class="stat-info"><h3>Akun Parent</h3><div class="value" id="count-parent">0</div></div></div>
            <div class="stat-card c-yellow"><div class="stat-icon"><i class="fa-solid fa-person-skating"></i></div><div class="stat-info"><h3>Total Atlet</h3><div class="value" id="count-athlete">0</div></div></div>
        </section>

        <section class="toolbar">
            <div class="filter-group">
                <select id="filterRole" class="clay-input" style="width: auto;" onchange="renderTable()">
                    <option value="All">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="coach">Coach</option>
                    <option value="parent">Parent</option>
                    <option value="atlet">Atlet</option>
                </select>

                <select id="sortBy" class="clay-input" style="width: auto;" onchange="renderTable()" title="Urutkan Akun">
                    <option value="role-admin">Prioritas: Admin di Atas</option>
                    <option value="role-coach">Prioritas: Coach di Atas</option>
                    <option value="role-atlet">Prioritas: Atlet di Atas</option>
                    <option value="role-parent">Prioritas: Parent di Atas</option>
                    <option value="name-asc">Nama (A - Z)</option>
                    <option value="name-desc">Nama (Z - A)</option>
                    <option value="username-asc">Username (A - Z)</option>
                    <option value="username-desc">Username (Z - A)</option>
                </select>

                <div class="search-bar-clay"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="searchInput" placeholder="Cari nama atau username..." onkeyup="renderTable()"></div>
            </div>
            <div class="action-group">
                <button class="btn-clay btn-primary" onclick="openAccountModal()"><i class="fa-solid fa-user-plus"></i> Buat Akun</button>
            </div>
        </section>

        <!-- TABEL UTAMA: INFORMASI PENGGUNA -->
        <div class="table-responsive">
            <div class="clay-table-grid clay-table-header">
                <div>INFORMASI PENGGUNA</div>
                <div style="text-align: center;">AKSI</div>
                <div>ROLE</div>
                <div>USERNAME</div>
                <div>PASSWORD</div>
                <div>ATLET TAUTAN</div>
                <div>STATUS</div>
            </div>
            <div id="accountTableBody"></div>
        </div>

        <!-- TABEL KEDUA: DATA LENGKAP BIODATA ATLET DARI APPENDIX -->
        <div class="table-responsive" style="margin-top: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div style="font-weight: 800; font-size: 1.1rem; color: var(--text-dark, #333);">
                    <i class="fa-solid fa-id-card" style="color:var(--sidebar-bg, #4f46e5); margin-right: 8px;"></i> Data Lengkap Biodata Atlet (Sinkronisasi Appendix)
                </div>
                <!-- Filter & Sorting Khusus Tabel Atlet dengan fungsionalitas & gaya disamakan -->
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <select id="filterKelasAtlet" class="clay-input" style="padding: 6px 10px; font-size: 0.85rem; width: auto;" onchange="renderBiodataAppendixTable()">
                        <option value="All">Semua Kelas</option>
                        <option value="Pemula">Pemula</option>
                        <option value="Standard">Standard</option>
                        <option value="Speed">Speed</option>
                        <option value="Freestyle">Freestyle</option>
                    </select>
                    <select id="sortAtletBy" class="clay-input" style="padding: 6px 10px; font-size: 0.85rem; width: auto;" onchange="renderBiodataAppendixTable()">
                        <option value="name-asc">Nama Panggilan (A - Z)</option>
                        <option value="name-desc">Nama Panggilan (Z - A)</option>
                        <option value="date-new">Tanggal Lahir (Terbaru)</option>
                        <option value="date-old">Tanggal Lahir (Terlama)</option>
                    </select>
                </div>
            </div>

            <!-- Grid Layout untuk 10 Kolom Atlet yang diselaraskan dengan container table-responsive -->
            <div class="clay-table-grid clay-table-header" style="grid-template-columns: 1.1fr 1.3fr 1.1fr 0.9fr 1fr 1.4fr 1.1fr 1fr 0.9fr 1fr; font-size: 0.75rem;">
                <div>NIK</div>
                <div>NAMA LENGKAP</div>
                <div>NAMA PANGGILAN</div>
                <div>GENDER</div>
                <div>TGL LAHIR</div>
                <div>ALAMAT LENGKAP</div>
                <div>NAMA WALI</div>
                <div>WHATSAPP</div>
                <div>KELAS</div>
                <div>STATUS AKUN</div>
            </div>
            <div id="biodataAtletTableBody">
                <!-- Data lengkap biodata atlet dimuat secara dinamis via JS -->
            </div>
        </div>
    </main>

    <!-- Modal Form Akun -->
    <div class="modal-overlay" id="accountModal">
        <div class="modal-card">
            <h2 id="accModalTitle"><i class="fa-solid fa-user-gear" style="color:var(--sidebar-bg);"></i> Form Akun</h2>
            <form id="accountForm" onsubmit="saveAccount(event)">
                <input type="hidden" id="accId">
                <div class="form-group"><label>Nama Lengkap</label><input type="text" id="accName" class="clay-input" placeholder="Masukkan nama..." required></div>
                <div class="form-group"><label>Username / Email</label><input type="text" id="accUsername" class="clay-input" placeholder="contoh@email.com" required></div>
                <div class="form-group">
                    <label>Kata Sandi (Password)</label>
                    <div class="input-eye-group">
                        <input type="password" id="accPassword" class="clay-input" placeholder="Masukkan kata sandi..." required>
                        <button type="button" class="btn-eye-modal" onclick="toggleModalPassword()"><i class="fa-solid fa-eye" id="modalEyeIcon"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Role / Peran</label>
                    <select id="accRole" class="clay-input" required>
                        <option value="admin">Admin</option>
                        <option value="coach">Coach</option>
                        <option value="parent">Parent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Akun</label>
                    <select id="accStatus" class="clay-input">
                        <option value="Aktif">Aktif (Dapat Login)</option>
                        <option value="Suspend">Suspend (Diblokir)</option>
                    </select>
                </div>
                <div class="modal-btns">
                    <button type="button" class="btn-clay btn-cancel" onclick="closeModal('accountModal')">Batal</button>
                    <button type="submit" class="btn-clay btn-primary btn-save">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hubungkan Atlet ke Parent -->
    <div class="modal-overlay" id="linkAthleteModal">
        <div class="modal-card">
            <h2><i class="fa-solid fa-link" style="color:#3b82f6;"></i> Hubungkan Atlet ke Parent</h2>
            <form id="linkAthleteForm" onsubmit="saveLinkAthlete(event)">
                <input type="hidden" id="targetUnlinkedAthlete">
                <p style="font-size: 0.85rem; color: var(--text-gray); margin-bottom: 15px; font-weight: 700;">Pilih akun Parent yang akan dihubungkan dengan atlet: <strong id="lblAthleteName" style="color:var(--text-dark);"></strong></p>
                <div class="form-group"><label>Pilih Akun Parent</label><select id="selectTargetParent" class="clay-input" required></select></div>
                <div class="modal-btns">
                    <button type="button" class="btn-clay btn-cancel" onclick="closeModal('linkAthleteModal')">Batal</button>
                    <button type="submit" class="btn-clay btn-primary btn-save">Hubungkan Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail / Daftar Atlet Tautan yang Terhubung -->
    <div class="modal-overlay" id="detailLinkedAthletesModal">
        <div class="modal-card">
            <h2><i class="fa-solid fa-person-skating" style="color:var(--sidebar-bg);"></i> Daftar Atlet Terhubung</h2>
            <p style="font-size: 0.85rem; color: var(--text-gray); margin-bottom: 15px; font-weight: 700;">Parent: <strong id="lblParentName" style="color:var(--text-dark);"></strong></p>
            <div id="listLinkedAthletesContent" style="max-height: 250px; overflow-y: auto; margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px;">
                <!-- Daftar atlet dirender secara dinamis -->
            </div>
            <div class="modal-btns">
                <button type="button" class="btn-clay btn-cancel" onclick="closeModal('detailLinkedAthletesModal')">Tutup</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/admin/users.js') }}"></script>
    <script src="{{ asset('js/beranda_admin.js') }}"></script>
    <script>
        // --- SCRIPT UTAMA UNTUK INTERAKTIVITAS KLIK TOTAL ATLET TAUTAN ---
        function showLinkedAthletesModal(parentName, athletesJsonEncoded) {
            let athletes = [];
            try {
                athletes = JSON.parse(decodeURIComponent(athletesJsonEncoded));
            } catch(e) {
                athletes = [];
            }

            document.getElementById('lblParentName').innerText = parentName;
            const container = document.getElementById('listLinkedAthletesContent');

            if (!athletes || athletes.length === 0) {
                container.innerHTML = `<div style="padding: 10px; text-align: center; color: var(--text-gray); font-weight: 700;">Tidak ada atlet yang terhubung dengan akun ini.</div>`;
            } else {
                let html = '';
                athletes.forEach((athName, index) => {
                    html += `
                        <div style="background: rgba(79, 70, 229, 0.05); padding: 10px 14px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 0.9rem; color: var(--text-dark);">
                            <span><i class="fa-solid fa-medal" style="color: #f59e0b; margin-right: 8px;"></i> ${athName}</span>
                            <span style="font-size: 0.75rem; background: var(--sidebar-bg); color: #fff; padding: 2px 8px; border-radius: 4px;">Atlet #${index + 1}</span>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }

            const modal = document.getElementById('detailLinkedAthletesModal');
            if (modal) {
                modal.classList.add('show');
            }
        }

        // --- PENGUKURAN STATISTIK COUNTER TOTAL ATLET YANG AKURAT ---
        async function updateStatsCounterOverride() {
            let serverUsers = [];
            try {
                let response = await fetch('/admin/users/data', { headers: { 'Accept': 'application/json' } });
                if (response.ok) {
                    serverUsers = await response.json();
                }
            } catch (e) {
                console.error('Gagal fetch data users:', e);
            }

            let adminCount = serverUsers.filter(u => ['admin', 'administrator'].includes((u.role || '').toLowerCase())).length;
            let coachCount = serverUsers.filter(u => (u.role || '').toLowerCase() === 'coach').length;
            let parentCount = serverUsers.filter(u => (u.role || '').toLowerCase() === 'parent').length;

            // Hitung total atlet dari data akun role 'atlet' ditambah sinkronisasi data appendix localStorage
            let serverAthleteCount = serverUsers.filter(u => ['atlet', 'athlete'].includes((u.role || '').toLowerCase())).length;

            let athletesList = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
            let athletesDataStore = JSON.parse(localStorage.getItem('athletes_data')) || [];

            let totalUniqueAthletes = new Set();
            athletesList.forEach(n => totalUniqueAthletes.add(n.toLowerCase()));
            athletesDataStore.forEach(item => {
                let nick = item.name || item.nickname || item.id;
                if (nick) totalUniqueAthletes.add(nick.toLowerCase());
            });

            let appendixCount = totalUniqueAthletes.size;
            let finalTotalAthlete = Math.max(serverAthleteCount, appendixCount);

            if (document.getElementById('count-admin')) document.getElementById('count-admin').innerText = adminCount;
            if (document.getElementById('count-coach')) document.getElementById('count-coach').innerText = coachCount;
            if (document.getElementById('count-parent')) document.getElementById('count-parent').innerText = parentCount;
            if (document.getElementById('count-athlete')) document.getElementById('count-athlete').innerText = finalTotalAthlete;
        }

        // --- SCRIPT UTAMA UNTUK MERENDER TABEL LENGKAP BIODATA ATLET DARI LOCALSTORAGE / APPENDIX ---
        document.addEventListener("DOMContentLoaded", () => {
            applyDynamicThemeSettings();
            renderBiodataAppendixTable();
            updateStatsCounterOverride();
        });

        function renderBiodataAppendixTable() {
            const tbody = document.getElementById('biodataAtletTableBody');
            if (!tbody) return;

            let athletesList = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
            let athletesDataStore = JSON.parse(localStorage.getItem('athletes_data')) || [];

            let combinedAthletesMap = new Map();

            athletesList.forEach(nick => {
                let bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nick)) || {};
                combinedAthletesMap.set(nick.toLowerCase(), {
                    nik: bio.nik || '-',
                    fullName: bio.fullName || nick,
                    nickname: nick,
                    gender: bio.gender || '-',
                    tglLahir: bio.tglLahir || '-',
                    alamat: bio.alamat || '-',
                    ortu: bio.ortu || bio.connectedParent || '-',
                    wa: bio.wa || '-',
                    kelas: bio.kelas || 'PEMULA',
                    status: bio.status || 'Aktif'
                });
            });

            athletesDataStore.forEach(item => {
                let nick = item.name || item.nickname || item.id;
                if (nick && !combinedAthletesMap.has(nick.toLowerCase())) {
                    combinedAthletesMap.set(nick.toLowerCase(), {
                        nik: item.nik || '-',
                        fullName: item.fullName || item.email || nick,
                        nickname: nick,
                        gender: item.gender || '-',
                        tglLahir: item.tglLahir || '-',
                        alamat: item.alamat || '-',
                        ortu: item.ortu || '-',
                        wa: item.wa || '-',
                        kelas: item.kelas || 'PEMULA',
                        status: item.status || 'Aktif'
                    });
                }
            });

            let athletesArray = Array.from(combinedAthletesMap.values());

            const filterKelas = document.getElementById('filterKelasAtlet') ? document.getElementById('filterKelasAtlet').value : 'All';
            if (filterKelas !== 'All') {
                athletesArray = athletesArray.filter(a => (a.kelas || '').toLowerCase() === filterKelas.toLowerCase());
            }

            const sortBy = document.getElementById('sortAtletBy') ? document.getElementById('sortAtletBy').value : 'name-asc';
            athletesArray.sort((a, b) => {
                if (sortBy === 'name-asc') return (a.nickname || '').localeCompare(b.nickname || '');
                if (sortBy === 'name-desc') return (b.nickname || '').localeCompare(a.nickname || '');
                if (sortBy === 'date-new') return new Date(b.tglLahir || 0) - new Date(a.tglLahir || 0);
                if (sortBy === 'date-old') return new Date(a.tglLahir || 0) - new Date(b.tglLahir || 0);
                return 0;
            });

            if (athletesArray.length === 0) {
                tbody.innerHTML = `<div style="padding: 20px; text-align: center; color: var(--text-gray); font-weight: 700; grid-column: 1 / -1;">Belum ada data biodata atlet yang tersimpan dari appendix.</div>`;
                return;
            }

            let html = '';
            athletesArray.forEach(ath => {
                let statusColor = (ath.status.toLowerCase() === 'aktif') ? '#2ec4b6' : '#e63946';
                html += `
                    <div class="clay-table-grid" style="grid-template-columns: 1.1fr 1.3fr 1.1fr 0.9fr 1fr 1.4fr 1.1fr 1fr 0.9fr 1fr; padding: 10px 12px; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: 0.8rem;">
                        <div style="font-weight: 700;">${ath.nik}</div>
                        <div style="font-weight: 800; color: var(--text-dark);">${ath.fullName}</div>
                        <div style="font-weight: 800; color: #3b82f6;"><i class="fa-solid fa-person-skating" style="margin-right:4px;"></i>${ath.nickname}</div>
                        <div>${ath.gender}</div>
                        <div>${ath.tglLahir}</div>
                        <div style="font-size: 0.75rem; color: var(--text-gray);">${ath.alamat}</div>
                        <div style="font-weight: 700;">${ath.ortu}</div>
                        <div>${ath.wa}</div>
                        <div><span style="background: rgba(245,158,11,0.15); color: #d97706; padding: 2px 6px; border-radius: 4px; font-weight: 800; font-size: 0.75rem;">${ath.kelas}</span></div>
                        <div><strong style="color:${statusColor};">${ath.status}</strong></div>
                    </div>
                `;
            });

            tbody.innerHTML = html;
        }

        function applyDynamicThemeSettings() {
            let savedFolder = localStorage.getItem('KILAT_CSS_FOLDER') || 'css';
            let savedTheme = localStorage.getItem('KILAT_THEME') || localStorage.getItem('appTheme') || '';
            if (savedTheme) {
                document.body.setAttribute('data-theme', savedTheme);
            }

            const dashboardLink = document.getElementById('dashboardStylesheet');
            const usersLink = document.getElementById('usersStylesheet');

            if (dashboardLink) {
                let href = dashboardLink.getAttribute('href');
                let fileName = href.split('/').pop();
                dashboardLink.setAttribute('href', `{{ asset('') }}${savedFolder}/${fileName}`);
            }

            if (usersLink) {
                let href = usersLink.getAttribute('href');
                let fileName = href.split('/').pop();
                if (href.includes('admin/')) {
                    usersLink.setAttribute('href', `{{ asset('') }}${savedFolder}/admin/${fileName}`);
                } else {
                    usersLink.setAttribute('href', `{{ asset('') }}${savedFolder}/${fileName}`);
                }
            }
        }
    </script>
</body>
</html>
