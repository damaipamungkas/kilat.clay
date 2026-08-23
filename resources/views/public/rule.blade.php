<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tata Tertib & Peraturan - KILAT</title>

    <!-- Mengubah font menjadi khas Claymorphism KILAT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/public.css') }}">
    <style>
        .table-rule-wrapper { width: 100%; overflow-x: auto; margin-top: 10px; }
        .rule-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left; }
        .rule-table th, .rule-table td { padding: 10px 12px; border: 1px solid rgba(0,0,0,0.1); }
        .rule-table th { background: rgba(0,0,0,0.05); font-weight: 800; }
        .rule-table td input[type="text"] { width: 100%; padding: 6px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; font-size: 0.85rem; }
        .card-admin-action { display: flex; justify-content: flex-end; gap: 8px; margin-top: 12px; }
        .btn-rule-edit { background: #3b82f6; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 0.8rem; }
        .btn-rule-save { background: #22c55e; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 0.8rem; display: none; }
        .btn-rule-add { background: #8b5cf6; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 0.75rem; margin-top: 8px; display: none; }
        .btn-rule-del { background: #ef4444; color: #fff; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem; }
    </style>
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
        <h1>TATA TERTIB & PERATURAN</h1>

        <div class="cyber-divider">
            <div class="wing left"></div>
            <div class="center-node"></div>
            <div class="wing right"></div>
        </div>

        <p style="color: var(--text-muted); font-weight: 700; max-width: 700px; margin: 0 auto; line-height: 1.6;">
            Panduan kepatuhan dan sistem regulasi resmi yang berlaku bagi seluruh anggota, pelatih, dan wali atlet di area operasional Kediri Inline Skate School.
        </p>
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

    <section class="article-container">

        <!-- CARD 1 -->
        <div class="tech-card" data-card-id="1">
            <div class="card-bg">
                <div class="card-title"><i class="fa-solid fa-location-crosshairs"></i> Peraturan Area Latihan/Umum</div>
                <div class="table-rule-wrapper">
                    <table class="rule-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Ketentuan / Peraturan</th>
                                <th class="admin-col" style="width: 70px; display: none;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td class="editable-cell">Menjaga ketenangan, kenyamanan, dan keamanan</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td class="editable-cell">Barang bawaan hilang/rusak tanggung jawab masing-masing.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td class="editable-cell">Menjaga kebersihan di tempat latihan.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn-rule-add" onclick="addTableRow(this)"><i class="fa-solid fa-plus"></i> Tambah Baris</button>
                <div class="card-admin-action admin-container" style="display: none;">
                    <button type="button" class="btn-rule-edit" onclick="toggleEditCard(this)">Edit Card</button>
                    <button type="button" class="btn-rule-save" onclick="saveCardData(this)">Simpan</button>
                </div>
            </div>
        </div>

        <!-- CARD 2 -->
        <div class="tech-card" data-card-id="2">
            <div class="card-bg">
                <div class="card-title"><i class="fa-solid fa-user-astronaut"></i> Peraturan Atlet</div>
                <div class="table-rule-wrapper">
                    <table class="rule-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Ketentuan / Peraturan</th>
                                <th class="admin-col" style="width: 70px; display: none;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td class="editable-cell">Mengikuti trial sebelum registrasi/pendaftaran.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td class="editable-cell">Saat trial boleh pinjam sepatu roda maksimal 30 menit (jika ada antrian trial).</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td class="editable-cell">Diwajibkan membawa sepatu running/sejenisnya dan memakai kaos kaki.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td class="editable-cell">Mengisi formulir pendaftaran, (mendaftar akun, dan mengisi identitas atlet pada website)</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td class="editable-cell">Membayar biaya pendaftaran dan Biaya bulanan/insidental.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td class="editable-cell">Membawa air minum (selain air putih harap tidak dibawa saat latihan).</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>7</td>
                                <td class="editable-cell">Memfasilitasi diri dengan perlengkapan latihan (sepatu roda, pelindung, helm, P3K).</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>8</td>
                                <td class="editable-cell">Disarankan berangkat lebih awal dari jadwal yang telah ditentukan.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn-rule-add" onclick="addTableRow(this)"><i class="fa-solid fa-plus"></i> Tambah Baris</button>
                <div class="card-admin-action admin-container" style="display: none;">
                    <button type="button" class="btn-rule-edit" onclick="toggleEditCard(this)">Edit Card</button>
                    <button type="button" class="btn-rule-save" onclick="saveCardData(this)">Simpan</button>
                </div>
            </div>
        </div>

        <!-- CARD 3 -->
        <div class="tech-card" data-card-id="3">
            <div class="card-bg">
                <div class="card-title"><i class="fa-solid fa-file-invoice-dollar"></i> Administrasi Pembayaran</div>
                <div class="table-rule-wrapper">
                    <table class="rule-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Ketentuan / Peraturan</th>
                                <th class="admin-col" style="width: 70px; display: none;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td class="editable-cell">Biaya pendaftaran senilai Rp. 150.000 dengan fasilitas jersey, akses raport, merchandise, dll.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td class="editable-cell">Iuran bulanan: Satu atlet Rp. 150.000/bulan, Dua atlet atau lebih Rp. 125.000/bulan (8x pertemuan).</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td class="editable-cell">Atlet baru daftar tengah/akhir bulan dikenakan Rp. 20.000 x sisa pertemuan (dibayar di awal).</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td class="editable-cell">Insidental dikenakan Rp. 25.000/latihan.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td class="editable-cell">Iuran bulanan dibayar tanggal 1-10 pada awal bulan, berakhir maksimal tanggal 31.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td class="editable-cell">Peserta insidental jika ingin akses raport dikenakan Rp. 25.000 (data latihan masuk arsip).</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn-rule-add" onclick="addTableRow(this)"><i class="fa-solid fa-plus"></i> Tambah Baris</button>
                <div class="card-admin-action admin-container" style="display: none;">
                    <button type="button" class="btn-rule-edit" onclick="toggleEditCard(this)">Edit Card</button>
                    <button type="button" class="btn-rule-save" onclick="saveCardData(this)">Simpan</button>
                </div>
            </div>
        </div>

        <!-- CARD 4 -->
        <div class="tech-card" data-card-id="4">
            <div class="card-bg">
                <div class="card-title"><i class="fa-solid fa-triangle-exclamation"></i> Peraturan Lain</div>
                <div class="table-rule-wrapper">
                    <table class="rule-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Ketentuan / Peraturan</th>
                                <th class="admin-col" style="width: 70px; display: none;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td class="editable-cell">Jadwal latihan diatur sesuai kelas, bergabung jadwal lain berisiko kehilangan materi/nilai.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td class="editable-cell">Libur karena event lomba atau izin wali atlet tidak wajib diganti jadwalnya oleh KILAT.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td class="editable-cell">Setiap event lomba ada biaya pendampingan transport pelatih jika didampingi.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td class="editable-cell">Wali atlet (dewasa) yang ikut berlatih gratis namun tidak mendapat treatment khusus.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td class="editable-cell">Cedera saat latihan ditangani P3K pelatih; lanjutan medis menjadi kewajiban wali atlet.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td class="editable-cell">Peraturan dapat berubah sewaktu-waktu dan diumumkan melalui grup WhatsApp.</td>
                                <td class="admin-col" style="display: none;"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn-rule-add" onclick="addTableRow(this)"><i class="fa-solid fa-plus"></i> Tambah Baris</button>
                <div class="card-admin-action admin-container" style="display: none;">
                    <button type="button" class="btn-rule-edit" onclick="toggleEditCard(this)">Edit Card</button>
                    <button type="button" class="btn-rule-save" onclick="saveCardData(this)">Simpan</button>
                </div>
            </div>
        </div>

    </section>

    <div class="bottom-actions">
        <a href="{{ route('register') }}" class="btn-neon">
            <i class="fa-solid fa-arrow-left"></i>SUDAH MEMBACA DAN MENYETUJUI
        </a>
    </div>
    <div class="bottom-actions">
        <a href="{{ route('home') }}" class="btn-neon">
            <i class="fa-solid fa-home"></i> BERANDA
        </a>
    </div>

    <footer class="footer">
        <div>UI Web Design</div>
            @include('layouts.footer')
        <div>Sistem Regulasi</div>
    </footer>

</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk scroll"></div>

<!-- JS Terpisah -->
<script src="{{ asset('js/public.js') }}"></script>
<script src="{{ asset('js/public/rule.js') }}"></script>

</body>
</html>
