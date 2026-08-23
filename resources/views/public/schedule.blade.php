<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Latihan - KILAT</title>

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
        <h1>SISTEM JADWAL LATIHAN</h1>

        <div class="cyber-divider">
            <div class="wing left"></div>
            <div class="center-node"></div>
            <div class="wing right"></div>
        </div>

        <div class="alert-box">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>Jadwal latihan bersifat dinamis dan dapat berubah sewaktu-waktu. Segala bentuk pembaruan (update) harian akan diinformasikan secara langsung melalui <strong>Grup WhatsApp Resmi KILAT</strong>.</div>
        </div>
    </header>

    <!-- Tech Slider / Slider Warna Tema -->
    <div class="tech-slider">
        <div class="slider-track" id="colorTrack" title="Geser untuk mengubah nuansa warna latar belakang">
            <div class="slider-thumb" id="colorThumb"></div>
        </div>
    </div>

@include('layouts.icon-menu')

    <section class="article-container">

        <!-- JADWAL: KEDIRI KOTA -->
        <div class="tech-card">
            <div class="card-bg pastel-blue">
                <div class="card-title"><i class="fa-solid fa-city"></i> Divisi Kediri Kota</div>

                <div class="table-responsive">
                    <table class="clay-table">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Lokasi / Tempat</th>
                                <th>Kategori Kelas</th>
                                <th>Jam Operasional</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Sesi Selasa -->
                            <tr>
                                <td rowspan="3" class="highlight-day">SELASA</td>
                                <td rowspan="3" style="border-right: 2px dashed rgba(255,255,255,0.7);">Kediri Mall, Lantai 6</td>
                                <td><span class="class-badge">Pemula (Dasar)</span></td>
                                <td style="color: var(--text-main);">16.15 - 18.00 WIB</td>
                            </tr>
                            <tr>
                                <td><span class="class-badge">Junior 1 (Menengah)</span></td>
                                <td style="color: var(--text-main);">18.15 - 20.00 WIB</td>
                            </tr>
                            <tr>
                                <td><span class="class-badge badge-gold">Junior 2 (Ahli)</span></td>
                                <td style="color: var(--text-main);">16.45 - 20.00 WIB</td>
                            </tr>

                            <!-- Sesi Jumat -->
                            <tr>
                                <td rowspan="3" class="highlight-day">JUMAT</td>
                                <td rowspan="3" style="border-right: 2px dashed rgba(255,255,255,0.7);">Pasar Setono Betek, Lantai 2</td>
                                <td><span class="class-badge">Pemula (Beginner)</span></td>
                                <td style="color: var(--text-main);">16.15 - 18.00 WIB</td>
                            </tr>
                            <tr>
                                <td><span class="class-badge">Junior 1 (Advanced)</span></td>
                                <td style="color: var(--text-main);">18.15 - 20.00 WIB</td>
                            </tr>
                            <tr>
                                <td><span class="class-badge badge-gold">Junior 2 (Expert)</span></td>
                                <td style="color: var(--text-main);">16.45 - 20.00 WIB</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- JADWAL: WATES -->
        <div class="tech-card">
            <div class="card-bg pastel-green">
                <div class="card-title"><i class="fa-solid fa-map-location-dot"></i> Divisi Wates</div>

                <div class="table-responsive">
                    <table class="clay-table">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Lokasi / Tempat</th>
                                <th>Kategori Kelas</th>
                                <th>Jam Operasional</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="highlight-day">RABU</td>
                                <td rowspan="2" style="border-right: 2px dashed rgba(255,255,255,0.7); line-height: 1.6;">
                                    - GOR PP Al Minhaaj<br>
                                    - Gedung Desa Gadungan<br>
                                    - Pasar Wates
                                </td>
                                <td rowspan="2"><span class="class-badge">Semua Kelas</span></td>
                                <td rowspan="2" style="font-style: italic; color: var(--text-muted); max-width: 200px;">
                                    Menyesuaikan tempat latihan yang tersedia. (Detail lokasi & waktu pasti akan di-update di Grup WhatsApp).
                                </td>
                            </tr>
                            <tr>
                                <td class="highlight-day">SABTU</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- JADWAL: WATES -->
        <div class="tech-card">
            <div class="card-bg pastel-green">
                <div class="card-title"><i class="fa-solid fa-map-location-dot"></i> Divisi Wates</div>

                <div class="table-responsive">
                    <table class="clay-table">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Lokasi / Tempat</th>
                                <th>Kategori Kelas</th>
                                <th>Jam Operasional</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="highlight-day">SENIN</td>
                                <td rowspan="2" style="border-right: 2px dashed rgba(255,255,255,0.7); line-height: 1.6;">
                                    Taman Talasari - Grogol
                                </td>
                                <td rowspan="2"><span class="class-badge">Semua Kelas</span></td>
                                <td rowspan="2" style="font-style: italic; color: var(--text-muted); max-width: 200px;">
                                    15.15-17.00 WIB
                                </td>
                            </tr>
                            <tr>
                                <td class="highlight-day">RABU</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- JADWAL: KELAS DEWASA -->
        <div class="tech-card">
            <div class="card-bg pastel-pink" style="min-height: auto;">
                <div class="card-title"><i class="fa-solid fa-user-tie"></i> Partisipan Dewasa</div>
                <div class="dewasa-text">
                    Bagi wali atlet atau partisipan dewasa, jadwal bersifat <strong>bebas dan fleksibel</strong>. Anda diperkenankan untuk hadir mengikuti jadwal latihan reguler yang tertera di atas. <br><br>
                    <span style="color: var(--primary-color); font-size: 0.95rem; font-weight: 800;">
                        * Catatan: Kelas ini bersifat mandiri, pelatih tidak memberikan treatment atau materi kurikulum khusus untuk kategori dewasa.
                    </span>
                </div>
            </div>
        </div>

    </section>

    <div style="text-align: center;">
        <a href="{{ route('home') }}" class="btn-neon">
            <i class="fa-solid fa-arrow-left"></i> KEMBALI KE BERANDA
        </a>
    </div>

    <footer class="footer">
        <div>Disiplin Waktu & Latihan</div>
            @include('layouts.footer')
        <div>Official Training Schedule</div>
    </footer>

</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk menggulir halaman"></div>

<!-- JS Terpisah -->
<script src="{{ asset('js/public.js') }}"></script>

</body>
</html>
