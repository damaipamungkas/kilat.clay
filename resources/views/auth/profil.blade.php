<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Komando - KILAT⚡</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

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

<div class="container">

    <header class="hero-section">
        <h1>PUSAT DATA AKUN</h1>
        @include('layouts.divider')
    </header>

    @include('layouts.slider')

    <div class="tech-card">
        <div class="card-bg">
            <div class="floating-icons">
                <i class="fa-solid fa-id-card-clip" id="profileIcon"></i>
            </div>

            <h1 class="card-title">Profil Pengguna</h1>
            <p class="card-desc">Transmisi data diinisialisasi.<br>Berikut adalah ringkasan status sistem Anda:</p>

            <div class="data-box">
                <p>
                    <i class="fa-solid fa-fingerprint"></i>
                    <strong>NAMA LENGKAP:</strong>
                    <span id="profileNamaDisplay" style="color: var(--text-main); font-weight: 900;">Mencari Data...</span>
                    <input type="text" id="profileNamaInput" class="editable-input" style="display:none;" placeholder="Nama Lengkap">
                </p>
                <p>
                    <i class="fa-solid fa-envelope"></i>
                    <strong>EMAIL SISTEM:</strong>
                    <span id="profileEmailDisplay" style="color: var(--text-main); font-weight: 800;">Mencari Data...</span>
                    <input type="text" id="profileEmailInput" class="editable-input" style="display:none;" placeholder="Email / Username" readonly>
                </p>

                <div class="data-row-password" style="margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-lock"></i>
                    <strong>PASSWORD:</strong>
                    <div class="password-container" style="display: inline-flex; align-items: center; gap: 8px;">
                        <span id="profilePasswordDisplay" style="color: var(--text-main); font-weight: 800;">••••••••</span>
                        <input type="password" id="profilePasswordInput" class="editable-input" style="display:none;" placeholder="Password Baru">
                        <button type="button" class="toggle-password-btn" id="btnTogglePassword" onclick="toggleProfilePasswordVisibility(event)" title="Lihat/Sembunyikan Password" style="cursor: pointer; background: transparent; border: none; outline: none; padding: 4px;">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <p style="border-bottom: none; padding-bottom: 0;">
                    <i class="fa-solid fa-network-wired"></i>
                    <strong>OTORITAS (ROLE):</strong>
                    <span id="profileRoleBadge">Mencari Data...</span>
                </p>

                <!-- Area Konten Lampiran/Tautan Dinamis Berdasarkan Role -->
                <div id="roleSpecificContent" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border-color, #e5e7eb);"></div>
            </div>

            <div class="btn-group" id="roleSpecificActions" style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                <!-- Tombol Halaman Admin (Disembunyikan secara default, ditampilkan via JS jika role Admin) -->
                <a href="{{ route('admin.index') }}" id="btnAdminDashboard" class="btn-neon" style="display: none; background: #8b5cf6; color: #fff;">
                    <i class="fa-solid fa-user-shield"></i> DASHBOARD ADMIN
                </a>

                <button type="button" class="btn-neon" id="btnEditProfile" onclick="toggleEditProfile()">
                    <i class="fa-solid fa-user-pen"></i> EDIT PROFIL
                </button>
                <button type="button" class="btn-neon" id="btnSaveProfile" onclick="saveProfileChanges()" style="display: none; background: #10b981; color: #fff;">
                    <i class="fa-solid fa-floppy-disk"></i> SIMPAN PERUBAHAN
                </button>
            </div>
        </div>
    </div>

    <div class="bottom-actions">
        <a href="{{ route('home') }}" class="btn-neon">
            <i class="fa-solid fa-house-chimney-crack"></i> KEMBALI KE BERANDA
        </a>

        <button type="button" class="btn-neon" onclick="handleLogout()" style="background: var(--c-alpa, #ff6b81); color: white;">
            <i class="fa-solid fa-right-from-bracket"></i> KELUAR / LOGOUT
        </button>
    </div>

    <footer class="footer">
        <div>System Online.</div>
        <div class="logo-box">
            KILAT⚡ <br><span style="font-size: 0.65rem; font-weight:800; color:var(--text-muted);">&copy; 2026 - Kediri Inline Skate School</span>
        </div>
        <div>Encrypted Connection</div>
    </footer>
</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk menggulir"></div>

<!-- Script Auth & Profil -->
<script src="{{ asset('js/auth/profil.js') }}"></script>
<script src="{{ asset('js/auth.js') }}"></script>

</body>
</html>
