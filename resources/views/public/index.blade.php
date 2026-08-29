<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kediri Inline Skate School - KILAT</title>

    <!-- Font & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Utama dengan ID mainStylesheet untuk sinkronisasi folder /css atau /css-theme -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/public.css') }}">

    <script>
        // Sinkronisasi otomatis folder CSS global (public/css atau public/css-surealist) saat halaman dimuat
        document.addEventListener("DOMContentLoaded", () => {
            let savedFolder = localStorage.getItem('KILAT_CSS_FOLDER') || 'css';
            const linkTag = document.getElementById('mainStylesheet');
            if (linkTag) {
                let href = linkTag.getAttribute('href');
                let filename = href.split('/').pop();
                linkTag.setAttribute('href', `{{ asset('') }}${savedFolder}/${filename}`);
            }
        });
    </script>
</head>
<body>

<div class="container">
    @include('layouts.navbar')
    @include('layouts.icon-menu')

    <!-- HERO / TENTANG KLUB -->
    <header class="hero position-relative">
        <h1>SELAMAT DATANG DI<br>Kediri Inline Skate School</h1>

        @include('layouts.divider')

        <div class="hero-content-wrapper">
            <h2 id="target-tentang" class="section-title-tentang">Tentang KILAT</h2>
            <div id="dynamicTentangContent">
                <p class="text-bold-muted mt-0"><strong>KILAT (Kediri Inline Skate School)</strong> secara resmi didirikan pada 25 Oktober 2015. Bermula sebagai komunitas kreatif wadah para pemuda pecinta olahraga sepatu roda di Kediri, KILAT sempat mengalami lonjakan popularitas yang signifikan pada tahun 2017. Kendati sempat vakum total akibat tantangan pandemi COVID-19, semangat komunitas ini tidak pernah padam.</p>
                <p class="text-bold-muted">Memasuki tahun 2024, KILAT melakukan reorganisasi dan bertransformasi secara struktural menjadi sebuah Klub Olahraga resmi. Kami menjalin kemitraan strategis dengan berbagai instansi pemerintahan serta berafiliasi penuh di bawah naungan induk organisasi <strong>PORSEROSI</strong> yang dibina langsung oleh <strong>KONI</strong>.</p>
                <p class="text-bold-muted mb-0">Kami berkomitmen untuk menyediakan modul pelatihan sepatu roda yang terstandarisasi bagi berbagai kelompok usia dan tingkat keahlian. Bersama kami, Anda dapat menguasai teknik fundamental, menyalurkan hobi positif, hingga mengembangkan potensi untuk menjadi atlet profesional berprestasi.</p>
            </div>
        </div>
    </header>

    @include('layouts.slider')

    <!-- GALERI (Sinkronisasi PHP & JS ke public/images) -->
    <section class="gallery-section position-relative" id="target-galeri">
        <h2 class="gallery-title">Galeri Dokumentasi KILAT</h2>

        @php
            // 1. Ambil data dari session server (hasil upload admin)
            $serverGallery = session()->get('server_gallery_images', []);

            // 2. Scan folder fisik public/images secara otomatis
            $publicImagesPath = public_path('images');
            $scannedImages = [];

            if (is_dir($publicImagesPath)) {
                $files = scandir($publicImagesPath);
                foreach ($files as $file) {
                    if (!in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)) {
                        $scannedImages[] = asset('images/' . $file);
                    }
                }
            }

            // Gabungkan gambar dari session server dan scan folder public/images
            $combinedGallery = array_unique(array_merge($serverGallery, $scannedImages));
        @endphp

        <!-- Injeksi data gambar server ke variabel JS global agar terbaca public.js -->
        <script>
            window.SERVER_GALLERY_IMAGES = @json($combinedGallery);
        </script>

        <div class="carousel-wrapper" id="carousel-wrapper" style="display: flex; overflow-x: auto; gap: 15px; scroll-behavior: smooth; padding: 10px 0;">
            @forelse($combinedGallery as $imgUrl)
                <div class="gallery-item" style="min-width: 280px; flex: 0 0 auto;">
                    <img src="{{ $imgUrl }}" alt="Galeri KILAT" style="width: 100%; height: 260px; object-fit: cover; border-radius: 16px; box-shadow: var(--clay-shadow-btn);" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1565992441121-4367c2967103?auto=format&fit=crop&w=600&q=80'">
                </div>
            @empty
                <!-- Fallback jika folder kosong -->
                <div class="gallery-item" style="min-width: 280px; flex: 0 0 auto;">
                    <img src="{{ asset('images/default-galeri.jpg') }}" alt="Galeri KILAT" style="width: 100%; height: 260px; object-fit: cover; border-radius: 16px; box-shadow: var(--clay-shadow-btn);" onerror="this.src='https://images.unsplash.com/photo-1565992441121-4367c2967103?auto=format&fit=crop&w=600&q=80'">
                </div>
            @endforelse
        </div>

        <div class="gallery-controls">
            <button class="gallery-btn" onclick="moveSlide(-1)" title="Sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="gallery-btn" onclick="moveSlide(1)" title="Selanjutnya"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </section>

    <!-- KURSUS & TESTIMONI -->
    <section class="grid-container">
        <!-- FRAME 1: KELAS KURSUS -->
        <div class="tech-card">
            <div class="card-bg pastel-blue">
                <div class="floating-icons">
                    <i class="fa-solid fa-fingerprint"></i>
                    <i class="fa-solid fa-heart"></i>
                    <i class="fa-solid fa-thumbs-up"></i>
                </div>
                <div class="card-title" id="target-kursus">Program Kelas Pelatihan</div>
                <div class="card-desc" id="dynamicProgramContent">
                    <h2>Kategori Kelas Tersedia:</h2>
                    <ul>
                        <li><strong>Pemula:</strong> Fokus pada pembentukan fondasi fisik dasar, mencakup fleksibilitas, daya tahan, keseimbangan, dan kelincahan. Tahap ini wajib diselesaikan oleh setiap peserta sebelum naik ke jenjang lanjutan.</li>
                        <li><strong>Junior 1:</strong> Pendalaman teknik tingkat lanjut berdasarkan kurikulum instruksional standar <em>classic slalom</em> regulasi Appendix Grade E. Peserta wajib menguasai daftar penguasaan trik level E sebelum melanjutkan ke kelas berikutnya.</li>
                        <li><strong>Junior 2:</strong> Modul pemantapan kompetisi dan persiapan kejuaraan profesional. Peserta diwajibkan telah lulus penguasaan materi Appendix tingkat E.</li>
                        <li><strong>Privat:</strong> Sesi pelatihan eksklusif dengan kuota terbatas maksimal 4 peserta per pertemuan. Diutamakan bagi peserta yang telah menguasai standar materi Appendix tingkat E.</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <button class="btn-neon" onclick="window.location.href='{{ route('login') }}'">Gabung Kelas</button>
                </div>
            </div>
        </div>

        <!-- FRAME 2: TESTIMONI -->
        <div class="tech-card">
            <div class="card-bg pastel-yellow">
                <div class="floating-icons">
                    <i class="fa-solid fa-star text-amber"></i>
                </div>
                <div class="card-title" id="target-testimoni">Ulasan & Testimoni Member</div>

                <div class="card-desc mb-2 w-100">
                    <div class="testi-summary">
                        <h2 id="testiAverageScore">0.0</h2>
                        <div>
                            <div class="testi-summary-stars" id="testiStarsContainer">
                                <i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i>
                            </div>
                            <div class="testi-summary-count" id="testiCountText">Belum ada ulasan terverifikasi</div>
                        </div>
                    </div>

                    <div class="testi-container" id="dynamicTestimonialsContainer"></div>
                </div>

                <div class="card-footer mt-auto">
                    <button class="btn-neon" onclick="window.location.href='{{ route('testimoni') }}'">Beri Ulasan <i class="fa-solid fa-pen-nib"></i></button>
                    <span class="member-badge"><i class="fa-solid fa-users"></i> Member Terverifikasi</span>
                </div>
            </div>
        </div>
    </section>

    <!-- PROSEDUR & KONTAK -->
    <section class="grid-container">
        <!-- FRAME 3: PROSEDUR PENDAFTARAN -->
        <div class="tech-card">
            <div class="card-bg pastel-green">
                <div class="floating-icons">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div class="card-title" id="target-prosedur">Prosedur Pendaftaran</div>
                <div class="card-desc" id="dynamicProsedurContent">
                    <ol class="prosedur-list">
                        <li><strong>Konsultasi & Uji Coba:</strong> Menghubungi admin guna memperoleh informasi lengkap serta penjadwalan sesi latihan pengenalan (<em>trial</em>).</li>
                        <li><strong>Registrasi Akun:</strong> Melakukan pendaftaran akun orang tua (<em>parent</em>), mendaftarkan data atlet, serta melengkapi formulir biodata secara daring melalui situs web.</li>
                        <li><strong>Penyelesaian Administrasi:</strong> Membayar biaya pendaftaran via transfer bank atau secara tunai saat sesi latihan berlangsung, dilanjutkan dengan konfirmasi ukuran seragam resmi.</li>
                        <li><strong>Penentuan Kelas:</strong> Penempatan jenjang kelas (Pemula, Junior 1, Junior 2) ditentukan berdasarkan hasil asesmen dan evaluasi dari tim pelatih.</li>
                        <li><strong>Memulai Latihan:</strong> Bergabung bersama komunitas dan mengikuti jadwal sesi latihan reguler yang telah ditentukan.</li>
                    </ol>
                </div>
                <div class="card-footer">
                    <button class="btn-neon" onclick="window.location.href='{{ route('register') }}'">Daftar Sekarang</button>
                </div>
            </div>
        </div>

        <!-- FRAME 4: KONTAK KAMI -->
        <div class="tech-card">
            <div class="card-bg pastel-pink">
                <div class="floating-icons">
                    <i class="fa-solid fa-address-book"></i>
                </div>
                <div class="card-title" id="target-kontak">Informasi Kontak</div>
                <div class="card-desc contact-list" id="dynamicContactList">
                    <a href="https://vt.tiktok.com/ZSXLrdc19/" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-tiktok"></i> TikTok KILAT
                    </a>
                    <a href="https://www.instagram.com/kilat.school?igsh=MXE3MWt4Y3diNjdueQ==" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-instagram"></i> IG: KILAT
                    </a>
                    <a href="https://www.instagram.com/damai.pamungkas?igsh=MTNreG8yMzFwMXBsZA==" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-instagram"></i> IG: Coach Damai
                    </a>
                    <a href="https://www.facebook.com/share/194FFyAwDB/" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-facebook-f"></i> Facebook KILAT
                    </a>
                    <a href="mailto:kilat.school@gmail.com">
                        <i class="fa-solid fa-envelope"></i> kilat.school@gmail.com
                    </a>
                    <a href="https://wa.me/6285800006248" target="_blank" rel="noopener noreferrer">
                        <i class="fa-brands fa-whatsapp"></i> WA: Coach Damai (085800006248)
                    </a>
                </div>
                <div class="card-footer">
                    <a id="whatsappDirectBtn" href="https://wa.me/6285800006248" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <button class="btn-neon">Hubungi via WhatsApp</button>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div>Membangun Generasi Juara</div>
            @include('layouts.footer')
        <div>Professional Training Standard</div>
    </footer>
</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk menggulir halaman"></div>

<script src="{{ asset('js/public.js') }}"></script>
</body>
</html>
