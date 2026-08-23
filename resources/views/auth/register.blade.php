@php
    // Registrasi Akun Parent - KILAT⚡
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun Parent - KILAT⚡</title>

    <!-- Mengubah font menjadi khas Claymorphism KILAT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Script Firebase (Dipertahankan) -->
    <script src="firebase-config.js"></script>
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
    <header class="hero">
        <h1>SISTEM REGISTRASI<br>ORANG TUA (PARENT)</h1>
@include('layouts.divider')
    </header>

@include('layouts.slider')

    <div class="wrapper">
        <div class="tech-card">
            <div class="card-bg">
                <h2 class="card-title"><i class="fa-solid fa-user-plus"></i> Registrasi Akun Baru</h2>

                <form id="registrationForm" class="reg-form" onsubmit="executeSubmit(event)">

                    <!-- MEMASTIKAN ROLE OTOMATIS BERVALU "parent" -->
                    <input type="hidden" name="role" id="role" value="parent">

                    <div class="input-group">
                        <label for="nama">IDENTITAS PENGGUNA</label>
                        <div class="input-wrapper sci-fi-input-composite">
                            <i class="fa-solid fa-id-badge"></i>
                            <select id="gender" name="gender" class="sci-fi-select">
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                            </select>
                            <input type="text" id="nama" name="nama" class="sci-fi-text-inner" placeholder="Masukkan nama panggilan..." required autocomplete="off">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="username">ALAMAT EMAIL SISTEM</label>
                        <div class="input-wrapper sci-fi-input-composite">
                            <i class="fa-solid fa-envelope"></i>
                            <!-- PREFIX "parent." DIHAPUS AGAR BISA DIISI MANUAL -->
                            <input type="text" id="username" name="username" class="sci-fi-text-inner" placeholder="Masukkan email, contoh (parent.contoh@kilat.com)" required autocomplete="off">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">KUNCI KEAMANAN (PASSWORD)</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" class="sci-fi-input" placeholder="Buat kata sandi akun Anda..." required>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="agree" name="agree" required>
                        <label for="agree">
                            Otorisasi sistem: Saya menyetujui persyaratan dan telah <a href="{{ route('rule') }}" target="_blank">membaca protokol aturan</a> sekolah KILAT.
                        </label>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn-neon">
                            <i class="fa-solid fa-satellite-dish"></i> DAFTAR AKUN
                        </button>

                        <a href="{{ route('login') }}" class="btn-neon">
                            <i class="fa-solid fa-right-to-bracket"></i> SUDAH PUNYA AKUN? MASUK
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bottom-actions">
        <a href="{{ route('home') }}" class="btn-neon">
            <i class="fa-solid fa-arrow-left"></i> KEMBALI KE BERANDA
        </a>
    </div>

    <footer class="footer">
        <div>Koneksi Terenkripsi.</div>
        <div class="logo-box">
            KILAT⚡ <br><span style="font-size: 0.65rem; font-weight:800; color:var(--text-muted);">&copy; 2026 - Kediri Inline Skate School</span>
        </div>
        <div>Sistem Database v.2.0</div>
    </footer>
</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk scroll"></div>
<script src="{{ asset('js/auth/register.js') }}"></script>
<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
