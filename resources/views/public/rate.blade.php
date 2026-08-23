<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struktur Biaya - KILAT</title>

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
        <h1>Struktur Biaya Pelatihan</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 700; margin-top: 10px;">Transparansi tarif dan sistem iuran KILAT.</p>
        @include('layouts.divider')
    </header>

    <!-- CARD 1: TRIAL -->
    <div class="tech-card">
        <div class="card-bg pastel-blue">
            <div class="card-title"><i class="fa-solid fa-bolt"></i> Program Uji Coba (Trial)</div>
            <p style="color: var(--text-muted); font-weight: 700; line-height: 1.6; margin: 0; font-size: 1.05rem;">
                Kami menyediakan fasilitas latihan perdana beserta peminjaman sepatu roda <strong style="color: var(--primary-color); font-weight: 900;">tanpa biaya</strong> bagi pemula. Program ini bertujuan untuk mengevaluasi minat dan kenyamanan peserta didik sebelum melakukan pendaftaran resmi.
            </p>
        </div>
    </div>

    <!-- SLIDER WARNA HUE -->
    @include('layouts.slider')
    @include('layouts.icon-menu')

    <!-- CARD 2: TARIF BULANAN -->
    <div class="tech-card">
        <div class="card-bg pastel-yellow">
            <div class="card-title"><i class="fa-solid fa-map-location-dot"></i>Tarif Berdasarkan Divisi</div>
            <div style="overflow-x: auto; padding: 5px 0;">
                <table class="cyber-table">
                    <thead>
                        <tr>
                            <th>Lokasi Latihan</th>
                            <th>Paket Bulanan (8x)</th>
                            <th>Insidental / Sesi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Kediri Kota</strong><br><small style="color: var(--text-muted); font-weight: 700;">(Kediri Mall & Pasar Setono Betek)</small></td>
                            <td class="price">Rp 150.000</td>
                            <td class="price">Rp 25.000</td>
                        </tr>
                        <tr>
                            <td><strong>Wates</strong><br><small style="color: var(--text-muted); font-weight: 700;">(GOR Al-Minhaaj, Ged. Gadungan, Pasar Wates)</small></td>
                            <td class="price">Rp 100.000</td>
                            <td class="price">Rp 20.000</td>
                        </tr>
                        <tr>
                            <td><strong>Grogol</strong><br><small style="color: var(--text-muted); font-weight: 700;">(Taman Talasari - Grogol)</small></td>
                            <td class="price">Rp 100.000</td>
                            <td class="price">Rp 20.000</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted); font-weight: 800; font-style: italic; background: var(--bg-main); padding: 12px 15px; border-radius: 12px; box-shadow: var(--clay-shadow-inset);">
                <i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i> * Khusus lokasi GOR PP Al Minhaaj: Berlaku kontribusi sukarela (infaq) per sesi latihan tanpa tarif pakem.
            </p>
        </div>
    </div>

    <!-- CARD 3: REGISTRASI -->
    <div class="tech-card">
        <div class="card-bg pastel-green">
            <div class="card-title"><i class="fa-solid fa-id-card-clip"></i> Biaya Pendaftaran Baru</div>
            <div style="margin-bottom: 5px; margin-top: 5px; color: var(--text-main); font-weight: 800; font-size: 1.15rem; background: var(--bg-main); padding: 15px 20px; border-radius: 16px; box-shadow: var(--clay-shadow-btn); display: inline-block;">
                Total investasi awal pendaftaran: <span class="price" style="font-size: 1.4rem; margin-left: 10px;">Rp 150.000</span>
            </div>
            <ul style="margin-top: 15px;">
                <li><strong>Biaya Administrasi & Operasional:</strong> Rp 50.000 <br><span style="font-size: 0.95rem; font-weight: 600;">(Administrasi data, perawatan alat pelindung/sepatu pinjaman, dokumentasi)</span></li>
                <li><strong>Seragam Tim (Wajib):</strong> Rp 100.000 <br><span style="font-size: 0.95rem; font-weight: 600;">(Jersey eksklusif resmi Masing-masing Club)</span></li>
            </ul>
            <p style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted); font-weight: 800; font-style: italic; background: var(--bg-main); padding: 12px 15px; border-radius: 12px; box-shadow: var(--clay-shadow-inset);">
                <i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i>* Biaya pendaftaran dibebankan untuk masing-masing divisi
            </p>
        </div>
    </div>

    <div class="bottom-actions">
        <a href="{{ route('home') }}">
            <button class="btn-neon btn-full"><i class="fa-solid fa-arrow-left"></i> KEMBALI KE BERANDA</button>
        </a>
    </div>

    <footer class="footer">
        <div>Transparansi Biaya & Kontribusi</div>
            @include('layouts.footer')
        <div>Membership Fee Base</div>
    </footer>
</div>

<!-- Custom Scrollbar -->
<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk menggulir"></div>

<!-- JS Terpisah -->
<script src="{{ asset('js/public.js') }}"></script>

</body>
</html>
