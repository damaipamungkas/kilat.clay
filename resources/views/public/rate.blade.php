@php
    // Pusat Komando Struktur Biaya - KILAT⚡
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
    <title>Struktur Biaya - KILAT</title>

    <!-- Mengubah font menjadi khas Claymorphism KILAT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/public.css') }}">
    <style>
        /* CSS Tambahan Khusus Mode Edit Admin pada Halaman Struktur Biaya */
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
            content: '\f303 Edit Kartu';
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

        /* Styling Modal Edit */
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
            max-width: 520px;
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
        loadDynamicContent();
    });
</script>

<div class="container">

    <header class="hero">
        <h1>Struktur Biaya Pelatihan</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 700; margin-top: 10px;">Transparansi tarif dan sistem iuran KILAT.</p>
        @include('layouts.divider')
    </header>

    <!-- CARD 1: TRIAL -->
    <div class="tech-card @if($isAdmin) editable-card @endif" @if($isAdmin) onclick="openEditModal('trial')" title="Klik untuk mengedit program trial" @endif>
        <div class="card-bg pastel-blue">
            <div class="card-title"><i class="fa-solid fa-bolt"></i> <span id="view-trial-title">Program Uji Coba (Trial)</span></div>
            <p id="view-trial-desc" style="color: var(--text-muted); font-weight: 700; line-height: 1.6; margin: 0; font-size: 1.05rem;">
                Kami menyediakan fasilitas latihan perdana beserta peminjaman sepatu roda <strong style="color: var(--primary-color); font-weight: 900;">tanpa biaya</strong> bagi pemula. Program ini bertujuan untuk mengevaluasi minat dan kenyamanan peserta didik sebelum melakukan pendaftaran resmi.
            </p>
        </div>
    </div>

    <!-- SLIDER WARNA HUE -->
    @include('layouts.slider')
    @include('layouts.icon-menu')

    <!-- CARD 2: TARIF BULANAN -->
    <div class="tech-card @if($isAdmin) editable-card @endif" @if($isAdmin) onclick="openEditModal('tarif')" title="Klik untuk mengedit tarif divisi" @endif>
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
                    <tbody id="view-tarif-tbody">
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
            <p id="view-tarif-note" style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted); font-weight: 800; font-style: italic; background: var(--bg-main); padding: 12px 15px; border-radius: 12px; box-shadow: var(--clay-shadow-inset);">
                <i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i> * Khusus lokasi GOR PP Al Minhaaj: Berlaku kontribusi sukarela (infaq) per sesi latihan tanpa tarif pakem.
            </p>
        </div>
    </div>

    <!-- CARD 3: REGISTRASI -->
    <div class="tech-card @if($isAdmin) editable-card @endif" @if($isAdmin) onclick="openEditModal('registrasi')" title="Klik untuk mengedit biaya pendaftaran" @endif>
        <div class="card-bg pastel-green">
            <div class="card-title"><i class="fa-solid fa-id-card-clip"></i> Biaya Pendaftaran Baru</div>
            <div style="margin-bottom: 5px; margin-top: 5px; color: var(--text-main); font-weight: 800; font-size: 1.15rem; background: var(--bg-main); padding: 15px 20px; border-radius: 16px; box-shadow: var(--clay-shadow-btn); display: inline-block;">
                Total investasi awal pendaftaran: <span id="view-reg-total" class="price" style="font-size: 1.4rem; margin-left: 10px;">Rp 150.000</span>
            </div>
            <ul style="margin-top: 15px;">
                <li><strong>Biaya Administrasi & Operasional:</strong> <span id="view-reg-admin">Rp 50.000</span> <br><span id="view-reg-admin-desc" style="font-size: 0.95rem; font-weight: 600;">(Administrasi data, perawatan alat pelindung/sepatu pinjaman, dokumentasi)</span></li>
                <li><strong>Seragam Tim (Wajib):</strong> <span id="view-reg-seragam">Rp 100.000</span> <br><span id="view-reg-seragam-desc" style="font-size: 0.95rem; font-weight: 600;">(Jersey eksklusif resmi Masing-masing Club)</span></li>
            </ul>
            <p id="view-reg-footer" style="margin-top: 10px; font-size: 0.9rem; color: var(--text-muted); font-weight: 800; font-style: italic; background: var(--bg-main); padding: 12px 15px; border-radius: 12px; box-shadow: var(--clay-shadow-inset);">
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

@if($isAdmin)
<!-- MODAL EDIT KARTU (KHUSUS ADMIN) -->
<div class="modal-overlay" id="editRateModal">
    <div class="modal-card">
        <h2 id="modalTitle"><i class="fa-solid fa-pen-to-square" style="color:var(--sidebar-bg);"></i> Edit Konten</h2>
        <form id="editRateForm" onsubmit="saveModalData(event); return false;">
            <input type="hidden" id="editCardType">

            <!-- Form Fields untuk Trial -->
            <div id="fields-trial" class="edit-fields-group" style="display:none;">
                <div class="form-group">
                    <label>Judul Program</label>
                    <input type="text" id="inputTrialTitle" class="clay-input" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi Uji Coba</label>
                    <textarea id="inputTrialDesc" class="clay-input" rows="4" required></textarea>
                </div>
            </div>

            <!-- Form Fields untuk Tarif Divisi -->
            <div id="fields-tarif" class="edit-fields-group" style="display:none;">
                <div class="form-group">
                    <label>Kediri Kota (Bulanan / Sesi)</label>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="inputKediriBulanan" class="clay-input" placeholder="Rp 150.000" required>
                        <input type="text" id="inputKediriSesi" class="clay-input" placeholder="Rp 25.000" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Wates (Bulanan / Sesi)</label>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="inputWatesBulanan" class="clay-input" placeholder="Rp 100.000" required>
                        <input type="text" id="inputWatesSesi" class="clay-input" placeholder="Rp 20.000" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Grogol (Bulanan / Sesi)</label>
                    <div style="display:flex; gap:10px;">
                        <input type="text" id="inputGrogolBulanan" class="clay-input" placeholder="Rp 100.000" required>
                        <input type="text" id="inputGrogolSesi" class="clay-input" placeholder="Rp 20.000" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Catatan Kaki / Info Khusus</label>
                    <input type="text" id="inputTarifNote" class="clay-input" required>
                </div>
            </div>

            <!-- Form Fields untuk Pendaftaran -->
            <div id="fields-registrasi" class="edit-fields-group" style="display:none;">
                <div class="form-group">
                    <label>Total Investasi Awal</label>
                    <input type="text" id="inputRegTotal" class="clay-input" required>
                </div>
                <div class="form-group">
                    <label>Biaya Administrasi & Operasional (Nominal)</label>
                    <input type="text" id="inputRegAdmin" class="clay-input" required>
                </div>
                <div class="form-group">
                    <label>Keterangan Administrasi</label>
                    <input type="text" id="inputRegAdminDesc" class="clay-input" required>
                </div>
                <div class="form-group">
                    <label>Seragam Tim / Jersey (Nominal)</label>
                    <input type="text" id="inputRegSeragam" class="clay-input" required>
                </div>
                <div class="form-group">
                    <label>Keterangan Seragam</label>
                    <input type="text" id="inputRegSeragamDesc" class="clay-input" required>
                </div>
                <div class="form-group">
                    <label>Catatan Pendaftaran</label>
                    <input type="text" id="inputRegFooter" class="clay-input" required>
                </div>
            </div>

            <div class="modal-btns">
                <button type="button" class="btn-clay btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn-clay btn-save">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Custom Scrollbar -->
<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk menggulir"></div>

<!-- JS Terpisah -->
<script src="{{ asset('js/public.js') }}"></script>
@if($isAdmin)
<script>
    function loadDynamicContent() {
        let savedData = JSON.parse(localStorage.getItem('KILAT_RATE_CONFIG')) || {};

        // 1. Trial
        if (savedData.trial) {
            document.getElementById('view-trial-title').innerText = savedData.trial.title;
            document.getElementById('view-trial-desc').innerHTML = savedData.trial.desc;
        }

        // 2. Tarif
        if (savedData.tarif) {
            let t = savedData.tarif;
            document.getElementById('view-tarif-tbody').innerHTML = `
                <tr>
                    <td><strong>Kediri Kota</strong><br><small style="color: var(--text-muted); font-weight: 700;">(Kediri Mall & Pasar Setono Betek)</small></td>
                    <td class="price">${t.kediriBulanan}</td>
                    <td class="price">${t.kediriSesi}</td>
                </tr>
                <tr>
                    <td><strong>Wates</strong><br><small style="color: var(--text-muted); font-weight: 700;">(GOR Al-Minhaaj, Ged. Gadungan, Pasar Wates)</small></td>
                    <td class="price">${t.watesBulanan}</td>
                    <td class="price">${t.watesSesi}</td>
                </tr>
                <tr>
                    <td><strong>Grogol</strong><br><small style="color: var(--text-muted); font-weight: 700;">(Taman Talasari - Grogol)</small></td>
                    <td class="price">${t.grogolBulanan}</td>
                    <td class="price">${t.grogolSesi}</td>
                </tr>
            `;
            document.getElementById('view-tarif-note').innerHTML = `<i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i> ${t.note}`;
        }

        // 3. Registrasi
        if (savedData.registrasi) {
            let r = savedData.registrasi;
            document.getElementById('view-reg-total').innerText = r.total;
            document.getElementById('view-reg-admin').innerText = r.admin;
            document.getElementById('view-reg-admin-desc').innerText = `(${r.adminDesc})`;
            document.getElementById('view-reg-seragam').innerText = r.seragam;
            document.getElementById('view-reg-seragam-desc').innerText = `(${r.seragamDesc})`;
            document.getElementById('view-reg-footer').innerHTML = `<i class="fa-solid fa-circle-info" style="color: var(--primary-color);"></i>* ${r.footer}`;
        }
    }

    function openEditModal(type) {
        const modal = document.getElementById('editRateModal');
        const cardTypeInput = document.getElementById('editCardType');
        const titleEl = document.getElementById('modalTitle');

        document.querySelectorAll('.edit-fields-group').forEach(el => el.style.display = 'none');

        cardTypeInput.value = type;
        let savedData = JSON.parse(localStorage.getItem('KILAT_RATE_CONFIG')) || {};

        if (type === 'trial') {
            titleEl.innerHTML = '<i class="fa-solid fa-bolt" style="color:var(--sidebar-bg);"></i> Edit Program Uji Coba (Trial)';
            document.getElementById('fields-trial').style.display = 'block';

            document.getElementById('inputTrialTitle').value = savedData.trial?.title || document.getElementById('view-trial-title').innerText;
            document.getElementById('inputTrialDesc').value = savedData.trial?.descRaw || "Kami menyediakan fasilitas latihan perdana beserta peminjaman sepatu roda tanpa biaya bagi pemula. Program ini bertujuan untuk mengevaluasi minat dan kenyamanan peserta didik sebelum melakukan pendaftaran resmi.";
        } else if (type === 'tarif') {
            titleEl.innerHTML = '<i class="fa-solid fa-map-location-dot" style="color:var(--sidebar-bg);"></i> Edit Tarif Berdasarkan Divisi';
            document.getElementById('fields-tarif').style.display = 'block';

            let t = savedData.tarif || {};
            document.getElementById('inputKediriBulanan').value = t.kediriBulanan || 'Rp 150.000';
            document.getElementById('inputKediriSesi').value = t.kediriSesi || 'Rp 25.000';
            document.getElementById('inputWatesBulanan').value = t.watesBulanan || 'Rp 100.000';
            document.getElementById('inputWatesSesi').value = t.watesSesi || 'Rp 20.000';
            document.getElementById('inputGrogolBulanan').value = t.grogolBulanan || 'Rp 100.000';
            document.getElementById('inputGrogolSesi').value = t.grogolSesi || 'Rp 20.000';
            document.getElementById('inputTarifNote').value = t.note || '* Khusus lokasi GOR PP Al Minhaaj: Berlaku kontribusi sukarela (infaq) per sesi latihan tanpa tarif pakem.';
        } else if (type === 'registrasi') {
            titleEl.innerHTML = '<i class="fa-solid fa-id-card-clip" style="color:var(--sidebar-bg);"></i> Edit Biaya Pendaftaran Baru';
            document.getElementById('fields-registrasi').style.display = 'block';

            let r = savedData.registrasi || {};
            document.getElementById('inputRegTotal').value = r.total || 'Rp 150.000';
            document.getElementById('inputRegAdmin').value = r.admin || 'Rp 50.000';
            document.getElementById('inputRegAdminDesc').value = r.adminDesc || 'Administrasi data, perawatan alat pelindung/sepatu pinjaman, dokumentasi';
            document.getElementById('inputRegSeragam').value = r.seragam || 'Rp 100.000';
            document.getElementById('inputRegSeragamDesc').value = r.seragamDesc || 'Jersey eksklusif resmi Masing-masing Club';
            document.getElementById('inputRegFooter').value = r.footer || 'Biaya pendaftaran dibebankan untuk masing-masing divisi';
        }

        modal.style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('editRateModal').style.display = 'none';
    }

    function saveModalData(e) {
        if (e) e.preventDefault();
        let type = document.getElementById('editCardType').value;
        let savedData = JSON.parse(localStorage.getItem('KILAT_RATE_CONFIG')) || {};

        if (type === 'trial') {
            let title = document.getElementById('inputTrialTitle').value.trim();
            let descRaw = document.getElementById('inputTrialDesc').value.trim();
            let descFormatted = descRaw.replace(/tanpa biaya/gi, '<strong style="color: var(--primary-color); font-weight: 900;">tanpa biaya</strong>');

            savedData.trial = { title, desc: descFormatted, descRaw };
        } else if (type === 'tarif') {
            savedData.tarif = {
                kediriBulanan: document.getElementById('inputKediriBulanan').value.trim(),
                kediriSesi: document.getElementById('inputKediriSesi').value.trim(),
                watesBulanan: document.getElementById('inputWatesBulanan').value.trim(),
                watesSesi: document.getElementById('inputWatesSesi').value.trim(),
                grogolBulanan: document.getElementById('inputGrogolBulanan').value.trim(),
                grogolSesi: document.getElementById('inputGrogolSesi').value.trim(),
                note: document.getElementById('inputTarifNote').value.trim()
            };
        } else if (type === 'registrasi') {
            savedData.registrasi = {
                total: document.getElementById('inputRegTotal').value.trim(),
                admin: document.getElementById('inputRegAdmin').value.trim(),
                adminDesc: document.getElementById('inputRegAdminDesc').value.trim(),
                seragam: document.getElementById('inputRegSeragam').value.trim(),
                seragamDesc: document.getElementById('inputRegSeragamDesc').value.trim(),
                footer: document.getElementById('inputRegFooter').value.trim()
            };
        }

        localStorage.setItem('KILAT_RATE_CONFIG', JSON.stringify(savedData));
        loadDynamicContent();
        closeEditModal();
        alert('✅ Perubahan struktur biaya berhasil disimpan!');
    }
</script>
@endif

</body>
</html>
