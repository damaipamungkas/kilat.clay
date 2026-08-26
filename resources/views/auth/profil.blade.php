@php
    // Pusat Komando Profil Akun - KILAT⚡
    $user = Auth::user();

    // Logika tambahan untuk mendeteksi atlet yang terhubung ke user dari database server
    $linkedAthletes = [];
    if (isset($user)) {
        if (method_exists($user, 'athletes') && $user->athletes) {
            $linkedAthletes = $user->athletes;
        } elseif (!empty($user->atletTautan)) {
            $linkedAthletes = is_array($user->atletTautan) ? $user->atletTautan : json_decode($user->atletTautan, true);
        }
    }
@endphp
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

            <!-- PESAN SUKSES / NOTIFIKASI -->
            @if(session('success'))
                <div class="alert alert-success" style="color: #10b981; font-weight: 700; margin-bottom: 15px; text-align: center;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="data-box">
                <p>
                    <i class="fa-solid fa-fingerprint"></i>
                    <strong>NAMA LENGKAP:</strong>
                    <span id="profileNamaDisplay" style="color: var(--text-main); font-weight: 900;">{{ $user->name }}</span>
                </p>
                <p>
                    <i class="fa-solid fa-envelope"></i>
                    <strong>EMAIL SISTEM:</strong>
                    <span id="profileEmailDisplay" style="color: var(--text-main); font-weight: 800;">{{ $user->email }}</span>
                </p>

                <div class="data-row-password" style="margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-lock"></i>
                    <strong>PASSWORD:</strong>
                    <div class="password-container" style="display: inline-flex; align-items: center; gap: 8px;">
                        <span id="profilePasswordDisplay" style="color: var(--text-main); font-weight: 800;">••••••••</span>
                        <button type="button" class="toggle-password-btn" id="btnTogglePassword" onclick="toggleProfilePasswordVisibility(event)" title="Lihat/Sembunyikan Password" style="cursor: pointer; background: transparent; border: none; outline: none; padding: 4px;">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <p style="border-bottom: none; padding-bottom: 0;">
                    <i class="fa-solid fa-network-wired"></i>
                    <strong>OTORITAS (ROLE):</strong>
                    <span id="profileRoleBadge" style="text-transform: uppercase; font-weight: 800; color: var(--accent-color, #8b5cf6);">{{ $user->role }}</span>
                </p>

                <!-- Area Konten Lampiran/Tautan Berdasarkan Role & Daftar Atlet Terdaftar -->
                <div id="roleSpecificContent" style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border-color, #e5e7eb);">

                    <!-- BAGIAN DAFTAR ATLET HANYA MUNCUL JIKA ROLE ADALAH PARENT / WALI -->
                    @if(in_array(strtolower($user->role), ['parent', 'wali', 'orang tua', 'orangtua']))
                        <div class="profile-athletes-section" style="margin-bottom: 15px;">
                            <div style="font-weight: 800; font-size: 0.85rem; margin-bottom: 8px; color: var(--text-main, #333); display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-child-reaching"></i> DAFTAR ATLET TERTAUT:
                            </div>

                            <!-- Wadah penampung atlet (Sinkronisasi Database & LocalStorage) -->
                            <div id="dynamicLinkedAthletesContainer">
                                @if(!empty($linkedAthletes) && count($linkedAthletes) > 0)
                                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                        @foreach($linkedAthletes as $athlete)
                                            <span style="background: rgba(139, 92, 246, 0.15); color: #7c3aed; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-user-ninja" style="font-size: 0.75rem;"></i> {{ is_object($athlete) ? ($athlete->name ?? $athlete->nickname) : $athlete }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div id="fallbackEmptyBox" style="background: rgba(239, 68, 68, 0.08); border: 1px dashed rgba(239, 68, 68, 0.3); padding: 10px; border-radius: 8px; text-align: center;">
                                        <p style="margin: 0 0 8px 0; font-size: 0.8rem; color: #b91c1c; font-weight: 700;">Belum ada atlet yang terdaftar/tertaut pada akun ini.</p>
                                        <a href="{{ route('appendix') }}" style="display: inline-block; background: #ef4444; color: #fff; padding: 5px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; text-decoration: none;">
                                            <i class="fa-solid fa-plus"></i> Tambahkan atlet ke appendix
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    <!-- AKHIR BAGIAN DAFTAR ATLET -->

                    @if(in_array(strtolower($user->role), ['admin', 'administrator']))
                        <a href="{{ route('admin.index') }}" class="btn-neon" style="display: inline-block; background: #8b5cf6; color: #fff; text-align: center; width: 100%; margin-bottom: 10px;">
                            <i class="fa-solid fa-user-shield"></i> DASHBOARD ADMIN
                        </a>
                    @endif
                </div>
            </div>

            <!-- TOMBOL AKSI UTAMA DIARAHKAN KE TABEL APPENDIX UNTUK DATA ATLET -->
            <div class="btn-group" id="roleSpecificActions" style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                <a href="{{ route('appendix') }}" class="btn-neon" style="background: var(--btn-bg, #3b82f6); color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 12px; font-weight: 700;">
                    <i class="fa-solid fa-table-list"></i> BUKA TABEL APPENDIX (DATA ATLET)
                </a>
            </div>
        </div>
    </div>

    <div class="bottom-actions">
        <a href="{{ route('home') }}" class="btn-neon">
            <i class="fa-solid fa-house-chimney-crack"></i> KEMBALI KE BERANDA
        </a>

        <!-- TOMBOL LOGOUT AMAN MENGGUNAKAN FORM POST -->
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn-neon" style="background: var(--c-alpa, #ff6b81); color: white; cursor: pointer;">
                <i class="fa-solid fa-right-from-bracket"></i> KELUAR / LOGOUT
            </button>
        </form>
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

<!-- Skrip Integrasi Otomatis Pembacaan LocalStorage Tautan Atlet -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const currentParentName = "{{ $user->name ?? '' }}".toLowerCase().trim();

        // Ambil data users dari localStorage (mengikuti fungsi helper dari appendix.js)
        const manageUsers = JSON.parse(
            localStorage.getItem('manageUsersData') ||
            localStorage.getItem('KILAT_USERS') ||
            '[]'
        );

        // Cari user yang sesuai dengan nama parent aktif saat ini
        const foundUser = manageUsers.find(u => {
            let uName = (u.name || u.username || u.namaLengkap || '').toLowerCase().trim();
            return uName === currentParentName;
        });

        if (foundUser && foundUser.atletTautan && foundUser.atletTautan.length > 0) {
            const container = document.getElementById('dynamicLinkedAthletesContainer');
            if (container) {
                let badgesHTML = '<div style="display: flex; flex-wrap: wrap; gap: 6px;">';
                foundUser.atletTautan.forEach(ath => {
                    badgesHTML += `
                        <span style="background: rgba(139, 92, 246, 0.15); color: #7c3aed; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-user-ninja" style="font-size: 0.75rem;"></i> ${ath}
                        </span>
                    `;
                });
                badgesHTML += '</div>';
                container.innerHTML = badgesHTML;
            }
        }
    });
</script>

<!-- Script Auth & Profil -->
<script src="{{ asset('js/auth/profil.js') }}"></script>
<script src="{{ asset('js/auth.js') }}"></script>

</body>
</html>
