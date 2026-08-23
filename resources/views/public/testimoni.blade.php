<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Testimoni - KILAT</title>

    <!-- Mengubah font menjadi khas Claymorphism KILAT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/public.css') }}">
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
        <h1>Transmisi Testimoni</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 700; margin-top: 10px;">Bantu KILAT⚡ menjadi lebih baik dari waktu ke waktu.</p>

        <div class="cyber-divider">
            <div class="wing left"></div>
            <div class="center-node"></div>
            <div class="wing right"></div>
        </div>
    </header>

    <div class="tech-slider">
        <div class="slider-borders"></div>
        <div class="slider-track" id="colorTrack" title="Geser untuk mengubah nuansa warna latar belakang">
            <div class="slider-thumb" id="colorThumb"></div>
        </div>
        <div class="slider-borders"></div>
    </div>

    <section class="icon-menu">
        <div class="icon-item" onclick="window.location.href='{{ route('knowledge') }}'"><div class="icon-circle"><i class="fa-solid fa-book"></i></div><span>Pengetahuan</span></div>
        <div class="icon-item" onclick="window.location.href='{{ route('schedule') }}'"><div class="icon-circle"><i class="fa-solid fa-calendar-days"></i></div><span>Jadwal</span></div>
        <div class="icon-item" onclick="window.location.href='{{ route('rate') }}'"><div class="icon-circle"><i class="fa-solid fa-wallet"></i></div><span>Tarif</span></div>
        <div class="icon-item" onclick="window.location.href='{{ route('faq') }}'"><div class="icon-circle"><i class="fa-solid fa-circle-question"></i></div><span>FAQ</span></div>
        <div class="icon-item" onclick="window.location.href='{{ route('feedback') }}'"><div class="icon-circle"><i class="fa-solid fa-comment-dots"></i></div><span>Umpan Balik</span></div>
    </section>

    <div class="tech-card">
        <form class="card-bg" id="testimoniForm">

            <div class="form-group">
                <label for="nama"><i class="fa-solid fa-user-astronaut"></i> IDENTITAS (NAMA)</label>
                <input type="text" id="nama" class="sci-fi-input" placeholder="Masukkan nama member Anda..." required>
            </div>

            <label class="toggle-container">
                <input type="checkbox" id="anonimToggle">
                <div class="toggle-switch"></div>
                <span class="toggle-label">Sembunyikan Identitas (Mode Anonim)</span>
            </label>

            <div class="form-group">
                <label><i class="fa-solid fa-star-half-stroke"></i> TINGKAT KEPUASAN (RATING)</label>
                <div class="rating-container">
                    <div class="stars">
                        <i class="fa-solid fa-star" data-val="1" title="Sangat Kurang"></i>
                        <i class="fa-solid fa-star" data-val="2" title="Kurang"></i>
                        <i class="fa-solid fa-star" data-val="3" title="Cukup"></i>
                        <i class="fa-solid fa-star" data-val="4" title="Sangat Baik"></i>
                        <i class="fa-solid fa-star" data-val="5" title="Sempurna"></i>
                    </div>
                    <span class="rating-text" id="ratingDisplay">0 / 5</span>
                </div>
            </div>

            <div class="alert-box">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Harap tuliskan testimoni positif mengenai pengalaman Anda. Jika ada kritik, kendala, atau saran silakan isi melalui <a href="{{ route('feedback') }}">Portal Feedback</a>.</span>
            </div>

            <div class="form-group">
                <label for="pesan"><i class="fa-solid fa-comment-dots"></i> PESAN TESTIMONI</label>
                <textarea id="pesan" class="sci-fi-input" placeholder="Apa yang membuat Anda atau Ananda bertahan di KILAT⚡?" required></textarea>
            </div>

            <button type="submit" class="btn-neon" style="margin-top: 10px;">
                KIRIM TESTIMONI <i class="fa-solid fa-paper-plane"></i>
            </button>

        </form>
    </div>

    <div class="bottom-actions">
        <a href="{{ route('home') }}" class="btn-neon">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>

    <footer class="footer">
        <div>Verified Review Hub</div>
@include('layouts.footer')
        <div>Portal Interaktif</div>
    </footer>
</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk menggulir"></div>

<div class="success-overlay" id="successOverlay">
    <div class="success-box">
        <i class="fa-solid fa-circle-check"></i>
        <h2>DATA DITERIMA</h2>
        <p>Terima kasih! Rating dan ulasan Anda akan diakumulasikan pada grafik kepuasan di sistem pusat KILAT⚡.</p>
        <button class="btn-neon" onclick="closeSuccess()" style="padding: 14px 20px; font-size:1rem;">TUTUP PESAN</button>
    </div>
</div>

<!-- JS Terpisah -->
<script src="{{ asset('js/public.js') }}"></script>

</body>
</html>
