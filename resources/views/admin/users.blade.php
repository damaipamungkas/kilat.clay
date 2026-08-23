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
                        <option value="athlete">Athlete</option>
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

    <script src="{{ asset('js/admin/users.js') }}"></script>
    <script src="{{ asset('js/beranda_admin.js') }}"></script>
    <script>
        // --- SCRIPT PENERAPAN TEMA GLOBAL DARI SETTING ---
        document.addEventListener("DOMContentLoaded", () => {
            applyDynamicThemeSettings();
        });

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
