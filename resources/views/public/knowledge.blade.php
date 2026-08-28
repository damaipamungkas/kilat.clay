<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Pengetahuan - KILAT</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/public.css') }}">
</head>
<body>

<div class="container">
    <header class="hero">

        <h1>Arsip Pengetahuan KILAT</h1>

@include('layouts.divider')

        <p style="color: var(--text-gray); font-family: 'Inter', sans-serif; font-weight: 600; max-width: 700px; margin: 0 auto; line-height: 1.6;">
            Akses direktori data historis, spesifikasi teknis peralatan, serta klasifikasi kompetisi dalam olahraga Inline Skate yang terotorisasi oleh sistem KILAT.
        </p>
    </header>

    <!-- Tech Slider (Slider Warna) -->
@include('layouts.slider')

@include('layouts.icon-menu')

    <section class="article-container">

        <div class="tech-card">
            <div class="glow-spin"></div>
            <div class="top-line"></div>
            <div class="bottom-line"></div>
            <div class="card-bg">
                <div class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Kronologi Sejarah</div>
                <div class="card-desc">
                    <p>Evolusi sepatu roda (*roller skates*) dimulai jauh sebelum era modern. Cikal bakal penemuannya tercatat pada tahun <strong>1760</strong> oleh seorang penemu asal Belgia bernama <strong>John Joseph Merlin</strong>. Saat itu, ia merancang sebuah sepatu dengan roda besi kecil sejajar, meskipun prototipe awalnya sangat sulit dikendalikan dan tidak memiliki rem.</p>

                    <p>Paten resmi pertama di dunia untuk sepatu roda (*inline*) baru didaftarkan di Prancis pada tahun <strong>1819</strong> oleh <strong>Monsieur Petitbled</strong>. Namun, popularitasnya sempat tergantikan oleh sepatu roda *quad* (roda dua di depan, dua di belakang) yang ditemukan oleh James Plimpton pada tahun 1863 karena dianggap lebih stabil untuk bermanuver.</p>

                    <div class="highlight-box">
                        "Revolusi Inline Skate modern terjadi pada tahun 1980-an ketika dua bersaudara asal Minnesota, Scott dan Brennan Olson, memodifikasi sepatu roda lama untuk latihan hoki es di musim panas. Inovasi ini melahirkan perusahaan legendaris, <strong>Rollerblade</strong>, yang kemudian menjadi istilah generik untuk olahraga ini."
                    </div>
                </div>
            </div>
        </div>

        <div class="tech-card">
            <div class="glow-spin"></div>
            <div class="top-line"></div>
            <div class="bottom-line"></div>
            <div class="card-bg">
                <div class="card-title"><i class="fa-solid fa-shoe-prints"></i> Klasifikasi Perangkat (Jenis Skate)</div>
                <div class="card-desc">
                    <p>Tidak semua *inline skate* diciptakan sama. Pemilihan alat harus disesuaikan dengan medan dan tujuan penggunaannya:</p>
                    <ul>
                        <li><strong>Recreational / Fitness:</strong> Didesain untuk kenyamanan maksimal dengan *soft-boot*. Biasanya memiliki roda berukuran sedang (72mm-90mm). Sangat cocok untuk pemula dan olahraga kardio di taman.</li>
                        <li><strong>Urban / Freeskate:</strong> Menggunakan *hard-boot* berbahan plastik kokoh agar tahan terhadap benturan. Diciptakan untuk menaklukkan aspal kota, melompat, dan bermanuver di lingkungan urban.</li>
                        <li><strong>Aggressive Inline:</strong> Memiliki konstruksi paling kuat dengan roda yang sangat kecil (biasanya 55mm-60mm) dan bentuk dasar *soul-plate* lebar. Dirancang khusus untuk melompat di skatepark, *grinding* di pipa, dan melakukan trik ekstrem.</li>
                        <li><strong>Freestyle Slalom:</strong> Sepatu presisi tinggi dengan *frame* pendek. Menggunakan konfigurasi roda "banana" (*rockered*), di mana roda tengah lebih besar dari roda ujung, untuk memberikan kelincahan ekstrem saat menari melewati deretan *cone*.</li>
                        <li><strong>Speed Skating:</strong> Ciri khasnya adalah *boot* kulit karbon yang dipotong rendah di bawah mata kaki untuk fleksibilitas dorongan. Menggunakan *frame* panjang dengan ukuran roda raksasa (100mm hingga 125mm) demi mencapai kecepatan maksimal.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="tech-card">
            <div class="glow-spin"></div>
            <div class="top-line"></div>
            <div class="bottom-line"></div>
            <div class="card-bg">
                <div class="card-title"><i class="fa-solid fa-trophy"></i> Divisi Kompetisi Resmi</div>
                <div class="card-desc">
                    <p>Di bawah naungan organisasi seperti PORSEROSI (Indonesia) dan World Skate (Internasional), inline skate memiliki berbagai cabang kejuaraan resmi:</p>
                    <ul>
                        <li><strong>Speed Skating (Balap Sepatu Roda):</strong> Atlet beradu kecepatan di sirkuit velodrome, trek lintasan oval, hingga maraton jalan raya. Membutuhkan daya tahan fisik dan teknik aerodinamika tinggi.</li>
                        <li><strong>Classic & Battle Slalom:</strong> Cabang paling populer di KILAT. kompetisi di mana atlet menunjukkan koreografi, teknik keseimbangan ekstrem, dan transisi gerakan melintasi cone dalam batas waktu tertentu. Dinilai dari tingkat kesulitan, gaya, dan presisi.</li>
                        <li><strong>Skatecross:</strong> Balapan melewati lintasan halang rintang ekstrem (seperti trek motocross) dengan menggunakan sepatu urban/freeskate.</li>
                        <li><strong>Inline Hockey:</strong> Mengadaptasi aturan hoki es ke lapangan keras. Menggunakan bola khusus atau *puck* beroda.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="tech-card">
            <div class="glow-spin"></div>
            <div class="top-line"></div>
            <div class="bottom-line"></div>
            <div class="card-bg">
                <div class="card-title"><i class="fa-solid fa-screwdriver-wrench"></i> Pemeliharaan & Manfaat Klinis</div>
                <div class="card-desc">
                    <p>Agar performa sepatu roda Anda tetap optimal bagaikan kilat, perawatan berkala (Maintenance) adalah wajib hukumnya:</p>
                    <ul>
                        <li><strong>Rotasi Roda:</strong> Roda sepatu roda akan aus secara asimetris (biasanya bagian dalam lebih cepat habis). Lakukan rotasi silang (roda posisi 1 dipindah ke posisi 3 atau sebaliknya) secara berkala agar usia pakai roda lebih seimbang.</li>
                        <li><strong>Pembersihan Bearing:</strong> Jika roda mulai terdengar kasar atau berputar lambat, lepaskan *bearing*, bersihkan dengan pelarut khusus, dan berikan pelumas (*speed oil* atau *grease*). Jangan menggunakan sepatu roda menerjang genangan air karena dapat merusak *bearing*.</li>
                    </ul>
                    <p><strong>Manfaat Kesehatan:</strong> Bermain inline skate selama 1 jam dapat membakar hingga 600 kalori. Olahraga ini termasuk *low-impact*, yang berarti jauh lebih ramah terhadap persendian lutut dibandingkan olahraga lari, sekaligus membangun keseimbangan (core otot perut) dan kekuatan otot paha yang solid.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="bottom-actions">
        <a href="{{ route('home') }}">
            <button class="btn-neon"><i class="fa-solid fa-arrow-left"></i> KEMBALI KE BERANDA</button>
        </a>
    </div>

    <footer class="footer">
        <div>Technical Knowledge Base</div>
            @include('layouts.footer')
        <div>Skate Science & History</div>
    </footer>
</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk scroll"></div>

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

<!-- JS Terpisah -->
<script src="{{ asset('js/public.js') }}"></script>

</body>
</html>
