<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Login - KILAT⚡</title>

    <!-- Font & Icons -->
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
            linkTag.setAttribute('href', `${savedFolder}/${fileName}`);
        }
    });
</script>

<div class="container">

    <h1 class="hero-title">PORTAL AKSES</h1>

    @include('layouts.divider')
    @include('layouts.slider')

    <div class="tech-card">
        <div class="card-bg">
            <div class="floating-icons">
                <i class="fa-solid fa-microchip"></i>
            </div>

            <h2 class="card-title">AUTENTIKASI</h2>
            <p class="card-desc">Silakan masukkan identitas jaringan Anda</p>

            <form action="{{ route('login') }}" method="POST" id="loginForm" class="login-form">
                @csrf <!-- Wajib untuk keamanan Laravel -->

                <!-- Pesan Notifikasi Error dari Controller -->
                @if($errors->any())
                    <div style="background: rgba(255, 107, 129, 0.2); border: 1px solid #ff6b81; color: #ff6b81; padding: 10px; border-radius: 8px; font-weight: 800; font-size: 11px; margin-bottom: 15px; text-align: center;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="input-group">
                    <label for="email">ID KREDENSIAL (EMAIL)</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="sci-fi-input" placeholder="contoh: parent.nama@kilat.com" required autocomplete="username">
                    </div>
                </div>

                <!-- Penambahan margin-bottom untuk memberi jarak ke tombol -->
                <div class="input-group" style="margin-bottom: 24px;">
                    <label for="password">KODE OTORISASI (SANDI)</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" class="sci-fi-input" placeholder="Masukkan kata sandi..." required>
                        <i class="fa-solid fa-eye toggle-password" id="togglePassword" title="Lihat/Sembunyikan Sandi"></i>
                    </div>
                </div>

                <button type="submit" class="btn-neon">
                    <i class="fa-solid fa-right-to-bracket"></i> INISIALISASI LOGIN
                </button>

            </form>

            <div class="auth-links">
                <a href="https://wa.me/6285800006248?text=permintaan%20akses%20sandi%20akun%20atas%20nama%3A" target="_blank" rel="noopener noreferrer">
                    <i class="fa-solid fa-triangle-exclamation"></i> Lupa Sandi?
                </a>
                <a href="{{ route('register') }}" style="color: var(--primary-color);">Belum punya akses? <strong>Daftar</strong></a>
            </div>

        </div>
    </div>

    <div class="bottom-actions">
        <a href="{{ route('home') }}" class="btn-neon text-decoration-none">
            <i class="fa-solid fa-house-chimney-crack"></i> KEMBALI KE BERANDA
        </a>
    </div>

    <footer class="footer">
        <div>System Online.</div>
        <div class="logo-box">
            KILAT⚡ <br><span style="font-size: 0.65rem; font-weight:800; color:var(--text-gray);">&copy; 2026 - Kediri Inline Skate</span>
        </div>
        <div>Encrypted Connection</div>
    </footer>

</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk scroll"></div>

<!-- Skrip Sesi & Sinkronisasi Lokal -->
<script>
    @if(auth()->check())
        // Simpan data user aktif ke localStorage jika berhasil login ke server
        localStorage.setItem('KILAT_CURRENT_USER', JSON.stringify({
            name: "{{ auth()->user()->name }}",
            email: "{{ auth()->user()->email }}",
            role: "{{ auth()->user()->role ?? 'parent' }}"
        }));
        localStorage.setItem('userRole', "{{ strtolower(auth()->user()->role ?? 'parent') }}");
    @endif
</script>

<!-- JS Terpisah -->
<script src="{{ asset('js/auth/login.js') }}"></script>
<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
