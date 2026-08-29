@php
    // Pusat Komando Jadwal Latihan - KILAT⚡
    $user = auth()->user();
    $rawRole = $user ? strtolower(trim($user->role ?? '')) : '';
    $userName = $user ? strtolower(trim($user->name ?? $user->username ?? '')) : '';

    if (str_contains($userName, 'admin') || str_contains($rawRole, 'admin')) {
        $rawRole = 'admin';
    }

    $isAdmin = ($rawRole === 'admin' || (isset($user) && $user));
@endphp
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
    <style>
        @if($isAdmin)
        .editable-card {
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
        }
        .editable-card:hover {
            transform: translateY(-2px);
            filter: brightness(0.98);
        }
        .editable-card::after {
            content: '\f303 Edit Jadwal';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            background: var(--sidebar-bg, #6366f1);
            color: white;
            padding: 5px 12px;
            border-radius: 10px;
            font-size: 0.75rem;
            box-shadow: var(--clay-shadow-btn);
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .editable-card:hover::after {
            opacity: 1;
        }
        @endif

        /* Styling Modal Edit Jadwal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(42, 34, 69, 0.6);
            backdrop-filter: blur(5px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 999999;
            padding: 15px;
        }
        .modal-card {
            background: var(--clay-purple, #f3e8ff);
            width: 100%;
            max-width: 540px;
            border-radius: 30px;
            padding: 25px;
            box-shadow: var(--clay-shadow-card);
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-card h2 {
            font-size: 1.2rem;
            font-weight: 900;
            color: var(--text-dark);
            margin-top: 0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: 900;
            font-size: 0.85rem;
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        .clay-input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 14px;
            border: none;
            background: var(--bg-main);
            box-shadow: var(--clay-shadow-inset);
            font-family: inherit;
            font-weight: 800;
            color: var(--text-dark);
            font-size: 0.9rem;
            box-sizing: border-box;
        }
        .modal-btns {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn-clay {
            padding: 10px 18px;
            border-radius: 14px;
            border: none;
            font-weight: 900;
            font-size: 0.9rem;
            cursor: pointer;
            box-shadow: var(--clay-shadow-btn);
            transition: 0.2s;
            text-align: center;
        }
        .btn-cancel {
            background: var(--bg-main);
            color: var(--text-gray);
        }
        .btn-save {
            background: var(--sidebar-bg, #6366f1);
            color: white;
            text-shadow: var(--text-timbul-light);
        }
        .btn-clay:hover {
            transform: scale(1.02);
            filter: brightness(0.95);
        }
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
        loadScheduleContent();
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
        <div class="tech-card @if($isAdmin) editable-card @endif" @if($isAdmin) onclick="openScheduleModal('kediri')" title="Klik untuk mengedit jadwal Kediri Kota" @endif>
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
                        <tbody id="view-kediri-tbody">
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
        <div class="tech-card @if($isAdmin) editable-card @endif" @if($isAdmin) onclick="openScheduleModal('wates')" title="Klik untuk mengedit jadwal Wates" @endif>
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
                        <tbody id="view-wates-tbody">
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

        <!-- JADWAL: GROGOL -->
        <div class="tech-card @if($isAdmin) editable-card @endif" @if($isAdmin) onclick="openScheduleModal('grogol')" title="Klik untuk mengedit jadwal Grogol" @endif>
            <div class="card-bg pastel-green">
                <div class="card-title"><i class="fa-solid fa-map-location-dot"></i> Divisi Grogol</div>

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
                        <tbody id="view-grogol-tbody">
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
        <div class="tech-card @if($isAdmin) editable-card @endif" @if($isAdmin) onclick="openScheduleModal('dewasa')" title="Klik untuk mengedit partisipan dewasa" @endif>
            <div class="card-bg pastel-pink" style="min-height: auto;">
                <div class="card-title"><i class="fa-solid fa-user-tie"></i> Partisipan Dewasa</div>
                <div class="dewasa-text" id="view-dewasa-text">
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

@if($isAdmin)
<!-- MODAL EDIT JADWAL (KHUSUS ADMIN) -->
<div class="modal-overlay" id="editScheduleModal" onclick="if(event.target === this) closeScheduleModal();">
    <div class="modal-card">
        <h2 id="scheduleModalTitle"><i class="fa-solid fa-pen-to-square" style="color:var(--sidebar-bg);"></i> Edit Jadwal</h2>
        <form id="editScheduleForm" onsubmit="saveScheduleData(event)">
            <input type="hidden" id="editCardKey">

            <!-- Form Fields untuk Kediri Kota -->
            <div id="fields-kediri" class="schedule-fields-group" style="display:none;">
                <div class="form-group"><label>Selasa: Lokasi / Tempat</label><input type="text" id="kSelasaLokasi" class="clay-input" required></div>
                <div class="form-group"><label>Selasa: Jam Pemula | Junior 1 | Junior 2</label>
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="kSelasaJam1" class="clay-input" placeholder="16.15 - 18.00" required>
                        <input type="text" id="kSelasaJam2" class="clay-input" placeholder="18.15 - 20.00" required>
                        <input type="text" id="kSelasaJam3" class="clay-input" placeholder="16.45 - 20.00" required>
                    </div>
                </div>
                <div class="form-group"><label>Jumat: Lokasi / Tempat</label><input type="text" id="kJumatLokasi" class="clay-input" required></div>
                <div class="form-group"><label>Jumat: Jam Pemula | Junior 1 | Junior 2</label>
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="kJumatJam1" class="clay-input" placeholder="16.15 - 18.00" required>
                        <input type="text" id="kJumatJam2" class="clay-input" placeholder="18.15 - 20.00" required>
                        <input type="text" id="kJumatJam3" class="clay-input" placeholder="16.45 - 20.00" required>
                    </div>
                </div>
            </div>

            <!-- Form Fields untuk Wates -->
            <div id="fields-wates" class="schedule-fields-group" style="display:none;">
                <div class="form-group"><label>Hari Latihan</label><input type="text" id="wHari" class="clay-input" value="RABU & SABTU" required></div>
                <div class="form-group"><label>Lokasi / Tempat (Pisahkan dengan baris baru)</label><textarea id="wLokasi" class="clay-input" rows="3" required></textarea></div>
                <div class="form-group"><label>Kategori Kelas</label><input type="text" id="wKategori" class="clay-input" value="Semua Kelas" required></div>
                <div class="form-group"><label>Keterangan Jam / Operasional</label><textarea id="wJam" class="clay-input" rows="3" required></textarea></div>
            </div>

            <!-- Form Fields untuk Grogol -->
            <div id="fields-grogol" class="schedule-fields-group" style="display:none;">
                <div class="form-group"><label>Hari Latihan</label><input type="text" id="gHari" class="clay-input" value="SENIN & RABU" required></div>
                <div class="form-group"><label>Lokasi / Tempat</label><input type="text" id="gLokasi" class="clay-input" required></div>
                <div class="form-group"><label>Kategori Kelas</label><input type="text" id="gKategori" class="clay-input" value="Semua Kelas" required></div>
                <div class="form-group"><label>Jam Operasional</label><input type="text" id="gJam" class="clay-input" required></div>
            </div>

            <!-- Form Fields untuk Partisipan Dewasa -->
            <div id="fields-dewasa" class="schedule-fields-group" style="display:none;">
                <div class="form-group"><label>Teks Keterangan Partisipan Dewasa</label><textarea id="dText" class="clay-input" rows="6" required></textarea></div>
            </div>

            <div class="modal-btns">
                <button type="button" class="btn-clay btn-cancel" onclick="closeScheduleModal()">Batal</button>
                <button type="submit" class="btn-clay btn-save">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endif

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk menggulir halaman"></div>

<!-- JS Terpisah -->
<script src="{{ asset('js/public.js') }}"></script>
@if($isAdmin)
<script>
    function loadScheduleContent() {
        let savedData = JSON.parse(localStorage.getItem('KILAT_SCHEDULE_CONFIG')) || {};

        // 1. Kediri
        if (savedData.kediri) {
            let k = savedData.kediri;
            document.getElementById('view-kediri-tbody').innerHTML = `
                <tr>
                    <td rowspan="3" class="highlight-day">SELASA</td>
                    <td rowspan="3" style="border-right: 2px dashed rgba(255,255,255,0.7);">${k.selasaLokasi}</td>
                    <td><span class="class-badge">Pemula (Dasar)</span></td>
                    <td style="color: var(--text-main);">${k.selasaJam1}</td>
                </tr>
                <tr>
                    <td><span class="class-badge">Junior 1 (Menengah)</span></td>
                    <td style="color: var(--text-main);">${k.selasaJam2}</td>
                </tr>
                <tr>
                    <td><span class="class-badge badge-gold">Junior 2 (Ahli)</span></td>
                    <td style="color: var(--text-main);">${k.selasaJam3}</td>
                </tr>
                <tr>
                    <td rowspan="3" class="highlight-day">JUMAT</td>
                    <td rowspan="3" style="border-right: 2px dashed rgba(255,255,255,0.7);">${k.jumatLokasi}</td>
                    <td><span class="class-badge">Pemula (Beginner)</span></td>
                    <td style="color: var(--text-main);">${k.jumatJam1}</td>
                </tr>
                <tr>
                    <td><span class="class-badge">Junior 1 (Advanced)</span></td>
                    <td style="color: var(--text-main);">${k.jumatJam2}</td>
                </tr>
                <tr>
                    <td><span class="class-badge badge-gold">Junior 2 (Expert)</span></td>
                    <td style="color: var(--text-main);">${k.jumatJam3}</td>
                </tr>
            `;
        }

        // 2. Wates
        if (savedData.wates) {
            let w = savedData.wates;
            let hariArr = w.hari.split('&').map(h => h.trim());
            document.getElementById('view-wates-tbody').innerHTML = `
                <tr>
                    <td class="highlight-day">${hariArr[0] || 'RABU'}</td>
                    <td rowspan="2" style="border-right: 2px dashed rgba(255,255,255,0.7); line-height: 1.6;">${w.lokasi.replace(/\n/g, '<br>')}</td>
                    <td rowspan="2"><span class="class-badge">${w.kategori}</span></td>
                    <td rowspan="2" style="font-style: italic; color: var(--text-muted); max-width: 200px;">${w.jam}</td>
                </tr>
                <tr>
                    <td class="highlight-day">${hariArr[1] || 'SABTU'}</td>
                </tr>
            `;
        }

        // 3. Grogol
        if (savedData.grogol) {
            let g = savedData.grogol;
            let hariG = g.hari.split('&').map(h => h.trim());
            document.getElementById('view-grogol-tbody').innerHTML = `
                <tr>
                    <td class="highlight-day">${hariG[0] || 'SENIN'}</td>
                    <td rowspan="2" style="border-right: 2px dashed rgba(255,255,255,0.7); line-height: 1.6;">${g.lokasi}</td>
                    <td rowspan="2"><span class="class-badge">${g.kategori}</span></td>
                    <td rowspan="2" style="font-style: italic; color: var(--text-muted); max-width: 200px;">${g.jam}</td>
                </tr>
                <tr>
                    <td class="highlight-day">${hariG[1] || 'RABU'}</td>
                </tr>
            `;
        }

        // 4. Dewasa
        if (savedData.dewasa) {
            document.getElementById('view-dewasa-text').innerHTML = savedData.dewasa.text;
        }
    }

    function openScheduleModal(key) {
        const modal = document.getElementById('editScheduleModal');
        const cardKeyInput = document.getElementById('editCardKey');
        const titleEl = document.getElementById('scheduleModalTitle');

        if (!modal) return;
        document.querySelectorAll('.schedule-fields-group').forEach(el => el.style.display = 'none');
        cardKeyInput.value = key;
        let savedData = JSON.parse(localStorage.getItem('KILAT_SCHEDULE_CONFIG')) || {};

        if (key === 'kediri') {
            titleEl.innerHTML = '<i class="fa-solid fa-city" style="color:var(--sidebar-bg);"></i> Edit Jadwal Divisi Kediri Kota';
            document.getElementById('fields-kediri').style.display = 'block';

            let k = savedData.kediri || {
                selasaLokasi: 'Kediri Mall, Lantai 6',
                selasaJam1: '16.15 - 18.00 WIB',
                selasaJam2: '18.15 - 20.00 WIB',
                selasaJam3: '16.45 - 20.00 WIB',
                jumatLokasi: 'Pasar Setono Betek, Lantai 2',
                jumatJam1: '16.15 - 18.00 WIB',
                jumatJam2: '18.15 - 20.00 WIB',
                jumatJam3: '16.45 - 20.00 WIB'
            };
            document.getElementById('kSelasaLokasi').value = k.selasaLokasi;
            document.getElementById('kSelasaJam1').value = k.selasaJam1;
            document.getElementById('kSelasaJam2').value = k.selasaJam2;
            document.getElementById('kSelasaJam3').value = k.selasaJam3;
            document.getElementById('kJumatLokasi').value = k.jumatLokasi;
            document.getElementById('kJumatJam1').value = k.jumatJam1;
            document.getElementById('kJumatJam2').value = k.jumatJam2;
            document.getElementById('kJumatJam3').value = k.jumatJam3;
        } else if (key === 'wates') {
            titleEl.innerHTML = '<i class="fa-solid fa-map-location-dot" style="color:var(--sidebar-bg);"></i> Edit Jadwal Divisi Wates';
            document.getElementById('fields-wates').style.display = 'block';

            let w = savedData.wates || {
                hari: 'RABU & SABTU',
                lokasi: '- GOR PP Al Minhaaj\n- Gedung Desa Gadungan\n- Pasar Wates',
                kategori: 'Semua Kelas',
                jam: 'Menyesuaikan tempat latihan yang tersedia. (Detail lokasi & waktu pasti akan di-update di Grup WhatsApp).'
            };
            document.getElementById('wHari').value = w.hari;
            document.getElementById('wLokasi').value = w.lokasi;
            document.getElementById('wKategori').value = w.kategori;
            document.getElementById('wJam').value = w.jam;
        } else if (key === 'grogol') {
            titleEl.innerHTML = '<i class="fa-solid fa-map-location-dot" style="color:var(--sidebar-bg);"></i> Edit Jadwal Divisi Grogol';
            document.getElementById('fields-grogol').style.display = 'block';

            let g = savedData.grogol || {
                hari: 'SENIN & RABU',
                lokasi: 'Taman Talasari - Grogol',
                kategori: 'Semua Kelas',
                jam: '15.15-17.00 WIB'
            };
            document.getElementById('gHari').value = g.hari;
            document.getElementById('gLokasi').value = g.lokasi;
            document.getElementById('gKategori').value = g.kategori;
            document.getElementById('gJam').value = g.jam;
        } else if (key === 'dewasa') {
            titleEl.innerHTML = '<i class="fa-solid fa-user-tie" style="color:var(--sidebar-bg);"></i> Edit Partisipan Dewasa';
            document.getElementById('fields-dewasa').style.display = 'block';

            let d = savedData.dewasa || {
                text: 'Bagi wali atlet atau partisipan dewasa, jadwal bersifat <strong>bebas dan fleksibel</strong>. Anda diperkenankan untuk hadir mengikuti jadwal latihan reguler yang tertera di atas. <br><br><span style="color: var(--primary-color); font-size: 0.95rem; font-weight: 800;">* Catatan: Kelas ini bersifat mandiri, pelatih tidak memberikan treatment atau materi kurikulum khusus untuk kategori dewasa.</span>'
            };
            // Ubah tag HTML <br> kembali menjadi newline (\n) untuk textarea
            let tempDiv = document.createElement('div');
            tempDiv.innerHTML = d.text;
            document.getElementById('dText').value = tempDiv.innerText;
        }

        modal.style.display = 'flex';
    }

    function closeScheduleModal() {
        const modal = document.getElementById('editScheduleModal');
        if (modal) modal.style.display = 'none';
    }

    function saveScheduleData(e) {
        if (e) e.preventDefault();
        let key = document.getElementById('editCardKey').value;
        let savedData = JSON.parse(localStorage.getItem('KILAT_SCHEDULE_CONFIG')) || {};

        if (key === 'kediri') {
            savedData.kediri = {
                selasaLokasi: document.getElementById('kSelasaLokasi').value.trim(),
                selasaJam1: document.getElementById('kSelasaJam1').value.trim(),
                selasaJam2: document.getElementById('kSelasaJam2').value.trim(),
                selasaJam3: document.getElementById('kSelasaJam3').value.trim(),
                jumatLokasi: document.getElementById('kJumatLokasi').value.trim(),
                jumatJam1: document.getElementById('kJumatJam1').value.trim(),
                jumatJam2: document.getElementById('kJumatJam2').value.trim(),
                jumatJam3: document.getElementById('kJumatJam3').value.trim()
            };
        } else if (key === 'wates') {
            savedData.wates = {
                hari: document.getElementById('wHari').value.trim(),
                lokasi: document.getElementById('wLokasi').value.trim(),
                kategori: document.getElementById('wKategori').value.trim(),
                jam: document.getElementById('wJam').value.trim()
            };
        } else if (key === 'grogol') {
            savedData.grogol = {
                hari: document.getElementById('gHari').value.trim(),
                lokasi: document.getElementById('gLokasi').value.trim(),
                kategori: document.getElementById('gKategori').value.trim(),
                jam: document.getElementById('gJam').value.trim()
            };
        } else if (key === 'dewasa') {
            let rawText = document.getElementById('dText').value.trim();
            let formattedText = rawText.replace(/\n/g, '<br>');
            savedData.dewasa = {
                text: `Bagi wali atlet atau partisipan dewasa, jadwal bersifat <strong>bebas dan fleksibel</strong>. Anda diperkenankan untuk hadir mengikuti jadwal latihan reguler yang tertera di atas. <br><br><span style="color: var(--primary-color); font-size: 0.95rem; font-weight: 800;">* ${formattedText}</span>`
            };
        }

        localStorage.setItem('KILAT_SCHEDULE_CONFIG', JSON.stringify(savedData));
        loadScheduleContent();
        closeScheduleModal();
        alert('✅ Jadwal latihan berhasil diperbarui!');
        return false;
    }
</script>
@endif

</body>
</html>
