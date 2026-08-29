@php
    // 1. Ambil data user yang sedang login
    $user = auth()->user();

    // 2. Ambil role user & id (normalisasi ke huruf kecil & hapus spasi berlebih)
    $rawRole = $user ? strtolower(trim($user->role ?? '')) : '';
    $currentUserId = auth()->id() ?? '';

    // Jika nama atau email atau role mengandung kata admin, paksa jadi admin penuh
    $userName = $user ? strtolower(trim($user->name ?? $user->username ?? '')) : '';
    if (str_contains($userName, 'admin') || str_contains($rawRole, 'admin')) {
        $rawRole = 'admin';
    }

    // 3. Peta penyesuaian/normalisasi variasi nama role
    $roleMap = [
        'orang tua' => 'parent',
        'orangtua'  => 'parent',
        'wali'      => 'parent',
        'walimurid' => 'parent',
        'pelatih'   => 'coach',
    ];

    $role = $roleMap[$rawRole] ?? $rawRole;

    // 4. Daftar role yang diizinkan masuk
    $allowedRoles = ['admin'];

    // 5. Pengalihan aman menggunakan penanganan Laravel jika belum login / bukan admin
    if (!$user || !in_array($role, $allowedRoles)) {
        echo "<script>window.location.href = '" . route('login') . "';</script>";
        exit();
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Sekolah Sepatu Roda (KILAT)</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis -->
    <link rel="stylesheet" id="dashboardStylesheet" href="{{ asset('css/admin_dashboard.css') }}">
    <link rel="stylesheet" id="settingStylesheet" href="{{ asset('css/admin/setting.css') }}">
</head>
<body data-theme="">
    <script>
        let activeAdminName = "{{ strtoupper($user->name ?? $user->username ?? 'ADMIN') }}";
    </script>
    @include('layouts.sidebar')

    <main class="main-content" id="mainContent">
        <header class="header">
            <h1>Pengaturan (Versi 13.08)</h1>
        </header>

        <div class="settings-grid">
            <div class="settings-card">
                <h3><i class="fa-solid fa-school"></i> Profil Sekolah (Kop Surat)</h3>
                <form onsubmit="event.preventDefault(); alert('Profil sekolah berhasil diperbarui!');">
                    <div class="form-group"><label>Nama Sekolah / Klub</label><input type="text" class="clay-input" value="KEDIRI INLINE SKATE SCHOOL"></div>
                    <div class="form-group"><label>Alamat Lengkap</label><textarea class="clay-input" rows="2">Jl. GOR Jayabaya, Kota Kediri, Jawa Timur</textarea></div>
                    <div class="form-group"><label>Kontak / WhatsApp</label><input type="text" class="clay-input" value="0812-3456-7890"></div>
                    <button type="submit" class="btn-clay"><i class="fa-solid fa-floppy-disk"></i> Simpan Profil</button>
                </form>
            </div>

            <!-- MANAJEMEN GALERI BERANDA -->
            <div class="settings-card">
                <h3><i class="fa-solid fa-images"></i> Manajemen Galeri Beranda</h3>
                <p style="font-size: 0.9rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">Pilih dan unggah maksimal 10 foto sekaligus untuk galeri carousel di halaman Beranda (`public/images`).</p>

                <div class="form-group">
                    <label>Pilih File Gambar (Maks. 10 Foto)</label>
                    <input type="file" id="galleryImageInput" class="clay-input" accept="image/*" multiple onchange="previewGalleryImages(event)" style="padding: 10px;">
                </div>

                <div id="imagePreviewContainer" style="display: none; margin-bottom: 15px; max-height: 180px; overflow-y: auto; padding: 10px; background: var(--bg-main); border-radius: 12px; box-shadow: var(--clay-shadow-inset);">
                    <div id="previewGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)); gap: 8px;"></div>
                </div>

                <!-- Indikator Loading & Status Upload Galeri -->
                <div id="galleryUploadStatus" style="display: none; margin-bottom: 15px; padding: 12px 15px; background: var(--bg-main); border-radius: 12px; box-shadow: var(--clay-shadow-inset); font-size: 0.85rem; font-weight: 800; color: var(--text-dark);">
                    <div id="galleryStatusText" style="margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-spinner fa-spin" style="color: var(--sidebar-bg);"></i> Mengunggah foto ke server...
                    </div>
                    <div style="width: 100%; background: rgba(0,0,0,0.08); height: 8px; border-radius: 4px; overflow: hidden;">
                        <div id="galleryProgressBar" style="width: 0%; height: 100%; background: var(--sidebar-bg); transition: width 0.3s ease;"></div>
                    </div>
                </div>

                <button id="uploadGalleryBtn" class="btn-clay" onclick="uploadAndSaveGalleryImages()"><i class="fa-solid fa-cloud-arrow-up"></i> Tambah ke Galeri Beranda</button>
            </div>

            <!-- PENGATURAN ASISTEN AI KILAT -->
            <div class="settings-card" style="grid-column: 1 / -1; background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(168, 85, 247, 0.05)); border: 2px dashed rgba(99, 102, 241, 0.3);">
                <h3><i class="fa-solid fa-robot" style="color: #6366f1;"></i> Kustomisasi & Perintah Asisten AI KILAT</h3>
                <p style="font-size: 0.9rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">Atur pesan sambutan awal, instruksi/slogan tanda tangan, serta aturan atau perintah khusus yang harus dipatuhi oleh Asisten AI (tersinkronisasi otomatis dengan AI di halaman FAQ).</p>

                <form onsubmit="event.preventDefault(); saveAiSettingsFromPage();">
                    <div class="form-group">
                        <label>Pesan Sambutan AI (Welcome Message)</label>
                        <input type="text" id="settingAiWelcome" class="clay-input" placeholder="Contoh: Halo! Ada yang bisa saya bantu terkait KILAT?">
                    </div>
                    <div class="form-group">
                        <label>Slogan / Instruksi Tanda Tangan AI (Footer Pesan Bot)</label>
                        <input type="text" id="settingAiInstruction" class="clay-input" placeholder="Contoh: Semangat meluncur bersama KILAT!">
                    </div>
                    <div class="form-group">
                        <label>Perintah / Aturan Khusus Agen AI (System Rules / Persona)</label>
                        <textarea id="settingAiSystemRules" class="clay-input" rows="4" placeholder="Contoh: Selalu jawab dengan ramah, gunakan panggilan 'Kak' kepada pengunjung, dan arahkan agar menghubungi admin jika tidak menemukan jawaban di FAQ."></textarea>
                    </div>
                    <button type="submit" class="btn-clay" style="background: linear-gradient(135deg, #6366f1, #a855f7); color: #fff;"><i class="fa-solid fa-floppy-disk"></i> Simpan Perintah & Pengaturan AI</button>
                </form>
            </div>

            <!-- BASIS PENGETAHUAN TAMBAHAN AI -->
            <div class="settings-card" style="grid-column: 1 / -1; background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(59, 130, 246, 0.05)); border: 2px dashed rgba(16, 185, 129, 0.3);">
                <h3><i class="fa-solid fa-book-open" style="color: #10b981;"></i> Basis Pengetahuan Tambahan AI (Knowledge Base)</h3>
                <p style="font-size: 0.9rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">Tempel catatan riwayat chat WhatsApp, transkrip informasi, atau unggah file dokumen referensi agar AI dapat mempelajarinya.</p>

                <form onsubmit="event.preventDefault(); saveAiKnowledgeBase();">
                    <div class="form-group">
                        <label>Upload File Resource / Dokumen Referensi AI (.txt, .md, .json)</label>
                        <input type="file" id="aiResourceFileInput" class="clay-input" accept=".txt,.md,.json" onchange="handleAiResourceUpload(event)" style="padding: 10px;">
                        <small style="color: var(--text-gray); font-weight: 700; display: block; margin-top: 5px;">File teks yang diunggah akan otomatis dimuat ke dalam teks pengetahuan di bawah.</small>
                    </div>

                    <div class="form-group">
                        <label>Teks Pengetahuan / Riwayat Chat / Dokumen Referensi</label>
                        <textarea id="settingAiKnowledge" class="clay-input" rows="6" placeholder="Contoh:
- Coach Andi: Latihan hari rabu dipindah ke jam 4 sore karena cuaca.
- Biaya pendaftaran member baru gelombang 2 adalah Rp 250.000 sudah termasuk kaos latihan.
- Lokasi latihan tambahan di GOR Jayabaya jalur utara."></textarea>
                    </div>
                    <button type="submit" class="btn-clay" style="background: #10b981; color: #fff;"><i class="fa-solid fa-database"></i> Simpan Pengetahuan Tambahan AI</button>
                </form>
            </div>

            <!-- SISTEM & DATABASE (Backup SQL Komprehensif Ber-Password, Restore & Hapus Data Selektif) -->
            <div style="background: var(--clay-yellow); box-shadow: var(--clay-shadow-card); border-radius: 30px; padding: 25px; margin-top: 20px; grid-column: 1 / -1;">
                <h3 style="font-size: 1.2rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-database" style="color: var(--sidebar-bg);"></i> Sistem & Database (Comprehensive Secured Backup & Restore)
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">

                    <!-- 1. Backup SQL Komprehensif Ber-Password -->
                    <div style="background: var(--clay-green); box-shadow: var(--clay-shadow-btn); padding: 20px; border-radius: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 8px;">
                                <i class="fa-solid fa-file-arrow-down" style="color: #50b054;"></i> Full Backup Sistem (Ber-Password)
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">
                                Cadangkan seluruh data (Galeri, Testimoni, Jadwal, Tarif, FAQ, Umpan Balik, Aturan, Akun, Keuangan, Absensi, Asisten AI Knowledge, dll) dengan sandi pengaman.
                            </p>
                        </div>
                        <button type="button" onclick="openBackupPasswordModal()" class="btn-clay" style="cursor: pointer; text-align: center; border: none; width: 100%;">
                            <i class="fa-solid fa-lock"></i> Buat Full Backup Ber-Password
                        </button>
                    </div>

                    <!-- 2. Upload / Restore Data SQL Komprehensif -->
                    <div style="background: var(--clay-purple); box-shadow: var(--clay-shadow-btn); padding: 20px; border-radius: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 8px;">
                                <i class="fa-solid fa-upload" style="color: var(--sidebar-bg);"></i> Restore / Pulihkan Data
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">
                                Unggah file `.sql` cadangan sistem Anda dan masukkan kata sandinya untuk memulihkan seluruh data aplikasi.
                            </p>
                        </div>
                        <input type="file" id="uploadFileBackup" accept=".sql" style="display:none;" onchange="handleRestoreFilePrompt(event)">
                        <button onclick="document.getElementById('uploadFileBackup').click()" class="btn-clay" style="cursor: pointer;">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Pilih & Unggah File .SQL
                        </button>
                    </div>

                    <!-- 3. Hapus Data Selektif Lengkap -->
                    <div style="background: var(--clay-pink); box-shadow: var(--clay-shadow-btn); padding: 20px; border-radius: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 8px;">
                                <i class="fa-solid fa-trash-can" style="color: #ff6b81;"></i> Kelola & Hapus Data Selektif
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">
                                Pilih kategori spesifik komponen sistem untuk dihapus secara permanen.
                            </p>
                        </div>
                        <button type="button" onclick="window.openResetModal()" class="btn-clay btn-danger" style="background: #ff6b81 !important; color: white !important; cursor: pointer;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Pilih Kategori Hapus Data
                        </button>
                    </div>

                </div>

                <!-- 4. Histori Riwayat Backup -->
                <div style="background: var(--bg-main); box-shadow: var(--clay-shadow-inset); padding: 20px; border-radius: 20px; margin-top: 20px;">
                    <h4 style="font-size: 1rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-clock-rotate-left" style="color: var(--sidebar-bg);"></i> Histori Riwayat Aktivitas Sistem
                    </h4>
                    <div id="backupHistoryList" style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700; max-height: 180px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                        <!-- Daftar histori backup dirender dinamis via JS -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Input Password Saat Membuat Backup -->
    <div id="backupPasswordModal" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(42, 34, 69, 0.6); backdrop-filter: blur(5px); display: none; align-items: center; justify-content: center; z-index: 999999; padding: 15px;">
        <div style="background: var(--clay-purple); width: 100%; max-width: 420px; border-radius: 30px; padding: 25px; box-shadow: var(--clay-shadow-card);">
            <div style="text-align: center; margin-bottom: 15px;">
                <i class="fa-solid fa-lock" style="font-size: 2.3rem; color: var(--sidebar-bg); margin-bottom: 10px;"></i>
                <h2 style="font-size: 1.2rem; font-weight: 900; color: var(--text-dark); margin-bottom: 5px;">Atur Sandi File Full Backup</h2>
                <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700;">Masukkan kata sandi untuk mengenkripsi seluruh data cadangan sistem ini.</p>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-size: 0.85rem; font-weight: 800; color: var(--text-dark); display: block; margin-bottom: 6px;">Kata Sandi Backup</label>
                <input type="password" id="backupInputPassword" class="clay-input" placeholder="Masukkan sandi rahasia..." style="width: 100%; padding: 10px;">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn-clay" onclick="closeBackupPasswordModal()" style="background: var(--bg-main); color: var(--text-gray); flex: 1; cursor: pointer;">Batal</button>
                <button type="button" class="btn-clay" onclick="executeSecuredSqlBackup()" style="background: #50b054; color: white; flex: 1; cursor: pointer;">Proses & Download</button>
            </div>
        </div>
    </div>

    <!-- Modal Input Password Saat Restore File SQL -->
    <div id="restorePasswordModal" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(42, 34, 69, 0.6); backdrop-filter: blur(5px); display: none; align-items: center; justify-content: center; z-index: 999999; padding: 15px;">
        <div style="background: var(--clay-purple); width: 100%; max-width: 420px; border-radius: 30px; padding: 25px; box-shadow: var(--clay-shadow-card);">
            <div style="text-align: center; margin-bottom: 15px;">
                <i class="fa-solid fa-key" style="font-size: 2.3rem; color: #ff6b81; margin-bottom: 10px;"></i>
                <h2 style="font-size: 1.2rem; font-weight: 900; color: var(--text-dark); margin-bottom: 5px;">Verifikasi Sandi File SQL</h2>
                <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700;" id="restoreFileNameLabel">File ini terkunci. Masukkan sandi untuk membukanya.</p>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-size: 0.85rem; font-weight: 800; color: var(--text-dark); display: block; margin-bottom: 6px;">Kata Sandi File</label>
                <input type="password" id="restoreInputPassword" class="clay-input" placeholder="Masukkan sandi..." style="width: 100%; padding: 10px;">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn-clay" onclick="closeRestorePasswordModal()" style="background: var(--bg-main); color: var(--text-gray); flex: 1; cursor: pointer;">Batal</button>
                <button type="button" class="btn-clay" onclick="verifyAndExecuteRestore()" style="background: var(--sidebar-bg); color: white; flex: 1; cursor: pointer;">Buka & Pulihkan</button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Data Selektif Lengkap -->
    <div id="resetModal" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(42, 34, 69, 0.6); backdrop-filter: blur(5px); display: none; align-items: center; justify-content: center; z-index: 999999; padding: 15px;">
        <div style="background: var(--clay-purple); width: 100%; max-width: 520px; border-radius: 30px; padding: 25px; box-shadow: var(--clay-shadow-card);">
            <div style="text-align: center; margin-bottom: 15px;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 2.5rem; color: #ff6b81; margin-bottom: 10px;"></i>
                <h2 style="font-size: 1.2rem; font-weight: 900; color: var(--text-dark); margin-bottom: 5px;">Hapus Data Selektif Komprehensif</h2>
                <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700;">Pilih komponen data web yang ingin dihapus secara permanen dari sistem.</p>
            </div>

            <!-- Daftar Checkbox Pilihan Kategori Lengkap -->
            <div style="background: var(--bg-main); padding: 15px; border-radius: 16px; box-shadow: var(--clay-shadow-inset); max-height: 250px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.9rem; font-weight: 800; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" id="checkAllDelete" onchange="window.toggleSelectAllDelete(this)" style="width: 18px; height: 18px; accent-color: #ff6b81;">
                    <span>Pilih / Centang Semua Komponen</span>
                </label>
                <hr style="border: 0; border-top: 1px solid rgba(0,0,0,0.08); margin: 2px 0;">
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="galeri" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Galeri Beranda
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="testimoni" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Testimoni & Ulasan
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="jadwal" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Jadwal Latihan
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="tarif" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Tarif & Biaya
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="faq" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data FAQ & Pertanyaan Sering
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="feedback" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Umpan Balik (Feedback)
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="aturan" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Aturan & Protokol Klub
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="users" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Akun (Users & Atlet)
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="appendix" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Appendix & Catatan Lain
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="keuangan" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Keuangan & Billing SPP
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="pusatakun" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Pusat Akun & Profil Terkait
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="absensi" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Absensi
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="setting" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Setting / Pengaturan Sistem
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="aiknowledge" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Asisten AI Knowledge & Rules
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="allstorage" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Semua Penyimpanan Lainnya di Web Ini
                </label>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn-clay" onclick="window.closeResetModal()" style="background: var(--bg-main); color: var(--text-gray); flex: 1; cursor: pointer;">Batal</button>
                <button type="button" class="btn-clay" onclick="window.executeSelectiveDelete()" style="background: #ff6b81; color: white; flex: 1; cursor: pointer;">Hapus Terpilih</button>
            </div>
        </div>
    </div>

    <!-- JS Terpisah -->
    <script src="{{ asset('js/admin/setting.js') }}"></script>
    <script src="{{ asset('js/beranda_admin.js') }}"></script>
    <script>
        let pendingRestoreContent = '';
        let pendingRestoreFileName = '';

        document.addEventListener("DOMContentLoaded", () => {
            renderBackupHistory();
            loadAiSettingsToForm();
        });

        function loadAiSettingsToForm() {
            const welcomeInput = document.getElementById('settingAiWelcome');
            const instructionInput = document.getElementById('settingAiInstruction');
            const rulesInput = document.getElementById('settingAiSystemRules');
            const knowledgeInput = document.getElementById('settingAiKnowledge');

            if (welcomeInput) {
                welcomeInput.value = localStorage.getItem('KILAT_AI_WELCOME_MSG') || 'Halo! Selamat datang di Pusat Bantuan KILAT. Ada yang bisa saya bantu terkait jadwal latihan, biaya, atau informasi seputar sekolah sepatu roda KILAT?';
            }
            if (instructionInput) {
                instructionInput.value = localStorage.getItem('KILAT_AI_CUSTOM_INSTRUCTION') || 'Semangat meluncur bersama KILAT!';
            }
            if (rulesInput) {
                rulesInput.value = localStorage.getItem('KILAT_AI_SYSTEM_RULES') || 'Jawablah dengan ramah dan sopan. Jika informasi tidak ditemukan di FAQ, arahkan pengunjung untuk menghubungi admin melalui kontak resmi.';
            }
            if (knowledgeInput) {
                knowledgeInput.value = localStorage.getItem('KILAT_AI_KNOWLEDGE_BASE') || '';
            }
        }

        function saveAiSettingsFromPage() {
            const welcomeVal = document.getElementById('settingAiWelcome').value.trim();
            const instructionVal = document.getElementById('settingAiInstruction').value.trim();
            const rulesVal = document.getElementById('settingAiSystemRules').value.trim();

            if (welcomeVal) localStorage.setItem('KILAT_AI_WELCOME_MSG', welcomeVal);
            if (instructionVal) localStorage.setItem('KILAT_AI_CUSTOM_INSTRUCTION', instructionVal);
            if (rulesVal) localStorage.setItem('KILAT_AI_SYSTEM_RULES', rulesVal);

            alert('✅ Perintah dan pengaturan Asisten AI KILAT berhasil disimpan dan disinkronkan ke sistem AI FAQ!');
        }

        function saveAiKnowledgeBase() {
            const knowledgeVal = document.getElementById('settingAiKnowledge').value.trim();
            localStorage.setItem('KILAT_AI_KNOWLEDGE_BASE', knowledgeVal);
            alert('✅ Basis pengetahuan tambahan (Knowledge Base) berhasil disimpan dan siap dipelajari oleh Asisten AI!');
        }

        function handleAiResourceUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const fileContent = e.target.result;
                const knowledgeTextArea = document.getElementById('settingAiKnowledge');

                if (knowledgeTextArea) {
                    const currentVal = knowledgeTextArea.value.trim();
                    knowledgeTextArea.value = currentVal ? `${currentVal}\n\n--- [Sumber: ${file.name}] ---\n${fileContent}` : `--- [Sumber: ${file.name}] ---\n${fileContent}`;
                }
                alert(`✅ File resource "${file.name}" berhasil dibaca dan ditambahkan ke kotak Pengetahuan AI! Klik tombol "Simpan Pengetahuan Tambahan AI" untuk menyimpan permanen.`);
                event.target.value = '';
            };
            reader.readAsText(file);
        }

        window.openResetModal = function() {
            const modal = document.getElementById('resetModal');
            if (modal) modal.style.display = 'flex';
        };

        window.closeResetModal = function() {
            const modal = document.getElementById('resetModal');
            if (modal) {
                modal.style.display = 'none';
                const checkAll = document.getElementById('checkAllDelete');
                if (checkAll) checkAll.checked = false;
                document.querySelectorAll('.delete-category-chk').forEach(chk => chk.checked = false);
            }
        };

        window.toggleSelectAllDelete = function(master) {
            const checkboxes = document.querySelectorAll('.delete-category-chk');
            checkboxes.forEach(chk => chk.checked = master.checked);
        };

        window.executeSelectiveDelete = function() {
            const checkboxes = document.querySelectorAll('.delete-category-chk:checked');
            if (checkboxes.length === 0) {
                alert("⚠️ Silakan pilih minimal satu kategori data yang ingin dihapus!");
                return;
            }

            let categoriesToDelete = Array.from(checkboxes).map(chk => chk.value);

            if (!confirm(`Yakin ingin menghapus permanen komponen sistem terpilih: [${categoriesToDelete.join(', ')}]?`)) {
                return;
            }

            categoriesToDelete.forEach(cat => {
                if (cat === 'galeri') {
                    localStorage.removeItem('KILAT_GALLERY_IMAGES');
                    localStorage.removeItem('public_images_gallery');
                    localStorage.removeItem('KILAT_CUSTOM_GALLERY');
                } else if (cat === 'testimoni') {
                    localStorage.removeItem('KILAT_TESTIMONIALS');
                    localStorage.removeItem('public_testimonials');
                    localStorage.removeItem('testimonials_data');
                } else if (cat === 'jadwal') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('jadwal') || key.toLowerCase().includes('schedule')) localStorage.removeItem(key);
                    });
                } else if (cat === 'tarif') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('tarif') || key.toLowerCase().includes('biaya') || key.toLowerCase().includes('price')) localStorage.removeItem(key);
                    });
                } else if (cat === 'faq') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('faq') || key.toLowerCase().includes('bantuan')) localStorage.removeItem(key);
                    });
                } else if (cat === 'feedback') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('feedback') || key.toLowerCase().includes('umpan_balik')) localStorage.removeItem(key);
                    });
                } else if (cat === 'aturan') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('aturan') || key.toLowerCase().includes('rule')) localStorage.removeItem(key);
                    });
                } else if (cat === 'users') {
                    localStorage.removeItem('manageUsersData');
                    localStorage.removeItem('KILAT_USERS');
                    localStorage.removeItem('KILAT_USERS_LIST');
                    localStorage.removeItem('KILAT_ATHLETES_LIST');
                    localStorage.removeItem('athletes_data');
                    localStorage.removeItem('KILAT_CURRENT_USER');
                    localStorage.removeItem('kilat_user_data');
                    localStorage.removeItem('lastActiveAthlete');
                    localStorage.removeItem('user');
                    localStorage.removeItem('users');
                } else if (cat === 'appendix') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('appendix')) localStorage.removeItem(key);
                    });
                } else if (cat === 'keuangan') {
                    localStorage.removeItem('KILAT_FINANCE_DB');
                    localStorage.removeItem('KILAT_SAVED_INVOICES');
                    localStorage.removeItem('KILAT_BILLING_PAID');
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('finance') || key.toLowerCase().includes('billing') || key.toLowerCase().includes('keuangan')) localStorage.removeItem(key);
                    });
                } else if (cat === 'pusatakun') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('pusat_akun') || key.toLowerCase().includes('account_center')) localStorage.removeItem(key);
                    });
                } else if (cat === 'absensi') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('absensi') || key.toLowerCase().includes('attendance')) localStorage.removeItem(key);
                    });
                } else if (cat === 'setting') {
                    localStorage.removeItem('KILAT_CSS_FOLDER');
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('setting') || key.toLowerCase().includes('profil_sekolah')) localStorage.removeItem(key);
                    });
                } else if (cat === 'aiknowledge') {
                    localStorage.removeItem('KILAT_AI_WELCOME_MSG');
                    localStorage.removeItem('KILAT_AI_CUSTOM_INSTRUCTION');
                    localStorage.removeItem('KILAT_AI_SYSTEM_RULES');
                    localStorage.removeItem('KILAT_AI_KNOWLEDGE_BASE');
                } else if (cat === 'allstorage') {
                    localStorage.clear();
                }
            });

            alert(`✅ Komponen sistem [${categoriesToDelete.join(', ')}] berhasil dibersihkan dan dihapus permanen!`);
            window.closeResetModal();
            location.reload();
        };

        let selectedGalleryFiles = [];

        function previewGalleryImages(event) {
            const files = Array.from(event.target.files);
            const container = document.getElementById('imagePreviewContainer');
            const grid = document.getElementById('previewGrid');

            if (files.length > 10) {
                alert('Maksimal hanya dapat memilih 10 gambar sekaligus!');
                event.target.value = '';
                container.style.display = 'none';
                return;
            }

            if (files.length > 0) {
                container.style.display = 'block';
                grid.innerHTML = '';
                selectedGalleryFiles = files;

                files.forEach((file) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        grid.innerHTML += `
                            <div style="position: relative;">
                                <img src="${e.target.result}" style="width: 100%; height: 70px; object-fit: cover; border-radius: 8px; box-shadow: var(--clay-shadow-btn);">
                            </div>
                        `;
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                container.style.display = 'none';
                selectedGalleryFiles = [];
            }
        }

        // --- FUNGSI UPLOAD KE FOLDER SERVER (public/images) VIA LARAVEL ---
        function uploadAndSaveGalleryImages() {
            if (selectedGalleryFiles.length === 0) {
                alert('⚠️ Silakan pilih minimal 1 gambar terlebih dahulu!');
                return;
            }

            const totalFiles = selectedGalleryFiles.length;
            const estimatedSeconds = Math.max(1, Math.ceil(totalFiles * 0.8));

            const statusContainer = document.getElementById('galleryUploadStatus');
            const statusText = document.getElementById('galleryStatusText');
            const progressBar = document.getElementById('galleryProgressBar');
            const uploadBtn = document.getElementById('uploadGalleryBtn');

            statusContainer.style.display = 'block';
            uploadBtn.disabled = true;
            uploadBtn.style.opacity = '0.6';
            uploadBtn.style.cursor = 'not-allowed';

            let currentProgress = 0;
            statusText.innerHTML = `<i class="fa-solid fa-spinner fa-spin" style="color: var(--sidebar-bg);"></i> Mengunggah ${totalFiles} foto ke server... (Estimasi waktu: ± ${estimatedSeconds} detik)`;
            progressBar.style.width = '20%';

            let formData = new FormData();
            for (let i = 0; i < selectedGalleryFiles.length; i++) {
                formData.append('images[]', selectedGalleryFiles[i]);
            }

            let intervalTime = (estimatedSeconds * 1000) / 7;
            let progressInterval = setInterval(() => {
                currentProgress += 12;
                if (currentProgress > 85) currentProgress = 85;
                progressBar.style.width = currentProgress + '%';
            }, intervalTime);

            // Kirim request ke backend route Laravel
            fetch("{{ route('admin.gallery.upload') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';

                if (data.success) {
                    statusText.innerHTML = `<i class="fa-solid fa-circle-check" style="color: #10b981;"></i> Berhasil mengunggah ${totalFiles} foto ke public/images!`;
                    setTimeout(() => {
                        alert('✅ ' + data.message);

                        // Reset input file & bersihkan preview agar siap upload baru
                        document.getElementById('galleryImageInput').value = '';
                        document.getElementById('imagePreviewContainer').style.display = 'none';
                        document.getElementById('previewGrid').innerHTML = '';
                        selectedGalleryFiles = [];

                        statusContainer.style.display = 'none';
                        progressBar.style.width = '0%';
                        uploadBtn.disabled = false;
                        uploadBtn.style.opacity = '1';
                        uploadBtn.style.cursor = 'pointer';
                    }, 400);
                } else {
                    throw new Error(data.message || 'Gagal menyimpan ke server');
                }
            })
            .catch(error => {
                clearInterval(progressInterval);
                console.error(error);
                statusText.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color: #ff6b81;"></i> Gagal mengunggah foto ke server!`;
                alert('❌ Gagal mengunggah foto ke folder public/images server.');
                uploadBtn.disabled = false;
                uploadBtn.style.opacity = '1';
                uploadBtn.style.cursor = 'pointer';
            });
        }

        function getBackupHistories() {
            return JSON.parse(localStorage.getItem('KILAT_BACKUP_HISTORIES')) || [];
        }

        function saveBackupHistories(histories) {
            localStorage.setItem('KILAT_BACKUP_HISTORIES', JSON.stringify(histories));
            renderBackupHistory();
        }

        function openBackupPasswordModal() {
            document.getElementById('backupInputPassword').value = '';
            document.getElementById('backupPasswordModal').style.display = 'flex';
        }

        function closeBackupPasswordModal() {
            document.getElementById('backupPasswordModal').style.display = 'none';
        }

        function executeSecuredSqlBackup() {
            let password = document.getElementById('backupInputPassword').value;
            if (!password) {
                alert("⚠️ Masukkan kata sandi keamanan terlebih dahulu!");
                return;
            }
            closeBackupPasswordModal();

            // Mengambil seluruh entri data secara komprehensif tanpa terkecuali
            let allDataObj = {};
            for (let i = 0; i < localStorage.length; i++) {
                let key = localStorage.key(i);
                if (key !== 'KILAT_BACKUP_HISTORIES') {
                    allDataObj[key] = localStorage.getItem(key);
                }
            }

            let payloadString = JSON.stringify(allDataObj);
            let encodedPayload = b58EncodeWithPassword(payloadString, password);

            let sqlContent = `-- KILAT SEKOLAH SEPATU RODA - COMPREHENSIVE SECURED SQL BACKUP\n`;
            sqlContent += `-- Generated at: ${new Date().toISOString()}\n`;
            sqlContent += `-- INCLUDES: Gallery, Testimonials, Schedule, Tariffs, FAQ, Feedback, Rules, Accounts, Appendix, Finance, Account Center, Attendance, Settings, AI Knowledge & All Web Storage\n\n`;
            sqlContent += `BEGIN TRANSACTION;\n\n`;
            sqlContent += `INSERT OR REPLACE INTO secure_storage (key, value) VALUES ('kilat_secured_payload', '${encodedPayload}');\n\n`;
            sqlContent += `COMMIT;\n`;

            let dateObj = new Date();
            let dateStrFormatted = dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            let timeStrFormatted = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            let fileName = `backup_kilat_comprehensive_${dateObj.getTime()}.sql`;

            const blob = new Blob([sqlContent], { type: 'text/sql;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            let histories = getBackupHistories();
            histories.unshift({
                id: Date.now(),
                name: fileName,
                date: `${dateStrFormatted}, ${timeStrFormatted}`,
                type: 'FULL SECURE SQL BACKUP',
                password: password,
                data: sqlContent
            });
            saveBackupHistories(histories);

            alert("✅ Berhasil! Full Backup Database SQL komprehensif ber-password berhasil dibuat dan diunduh.");
        }

        function b58EncodeWithPassword(text, password) {
            let combined = password + "::KILAT::" + text;
            return btoa(encodeURIComponent(combined));
        }

        function b58DecodeWithPassword(encoded, password) {
            try {
                let decoded = decodeURIComponent(atob(encoded));
                let parts = decoded.split("::KILAT::");
                if (parts.length < 2) return null;
                if (parts[0] !== password) return null;
                return parts.slice(1).join("::KILAT::");
            } catch(e) {
                return null;
            }
        }

        function downloadHistoryItem(id) {
            let histories = getBackupHistories();
            let item = histories.find(h => h.id === id);
            if (!item || !item.data) {
                alert("⚠️ Data backup isi file SQL tidak ditemukan pada histori ini.");
                return;
            }

            const blob = new Blob([item.data], { type: 'text/sql;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', item.name);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function renderBackupHistory() {
            const container = document.getElementById('backupHistoryList');
            if (!container) return;

            let histories = getBackupHistories();
            container.innerHTML = '';

            if (histories.length === 0) {
                container.innerHTML = '<p style="text-align:center; font-style:italic; padding:10px;">Belum ada riwayat aktivitas backup.</p>';
                return;
            }

            histories.forEach((item) => {
                let downloadBtnHtml = item.data ? `
                    <button onclick="downloadHistoryItem(${item.id})" class="btn-action-mini" title="Download SQL" style="background:#10b981; color:white; border:none; padding:6px 10px; border-radius:8px; cursor:pointer; font-weight:800; font-size:0.75rem;">
                        <i class="fa-solid fa-download"></i> Download
                    </button>
                ` : '';

                container.innerHTML += `
                    <div style="background: var(--bg-main); padding: 10px 15px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--clay-shadow-btn); gap: 10px; flex-wrap: wrap;">
                        <div>
                            <strong style="color: var(--text-dark);">${item.name}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-gray);">${item.date} (${item.type})</div>
                        </div>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            ${downloadBtnHtml}
                            <button onclick="deleteHistoryItem(${item.id})" class="btn-action-mini" title="Hapus" style="background:#ff6b81; color:white; border:none; padding:6px 10px; border-radius:8px; cursor:pointer; font-weight:800; font-size:0.75rem;">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        function deleteHistoryItem(id) {
            if (confirm('Yakin ingin menghapus riwayat ini dari daftar?')) {
                let histories = getBackupHistories();
                histories = histories.filter(h => h.id !== id);
                saveBackupHistories(histories);
            }
        }

        function handleRestoreFilePrompt(event) {
            let file = event.target.files[0];
            if (!file) return;

            let reader = new FileReader();
            reader.onload = function(e) {
                pendingRestoreContent = e.target.result;
                pendingRestoreFileName = file.name;
                document.getElementById('restoreFileNameLabel').innerText = `File "${file.name}" terkunci. Masukkan sandi untuk membukanya.`;
                document.getElementById('restoreInputPassword').value = '';
                document.getElementById('restorePasswordModal').style.display = 'flex';
                event.target.value = '';
            };
            reader.readAsText(file);
        }

        function closeRestorePasswordModal() {
            document.getElementById('restorePasswordModal').style.display = 'none';
            pendingRestoreContent = '';
            pendingRestoreFileName = '';
        }

        function verifyAndExecuteRestore() {
            let password = document.getElementById('restoreInputPassword').value;
            if (!password) {
                alert("⚠️ Harap masukkan kata sandi file!");
                return;
            }

            try {
                let content = pendingRestoreContent;
                let encodedPayload = '';

                let lines = content.split('\n');
                lines.forEach(line => {
                    if (line.includes('INSERT OR REPLACE INTO secure_storage') || line.includes('INSERT OR REPLACE INTO local_storage')) {
                        try {
                            let match = line.match(/VALUES \('([^']+)',\s*'([^']*)'\);/);
                            if (match && match[1] && match[2] !== undefined) {
                                if (match[1] === 'kilat_secured_payload') {
                                    encodedPayload = match[2];
                                }
                            }
                        } catch(ex) {}
                    }
                });

                if (encodedPayload) {
                    let decryptedJson = b58DecodeWithPassword(encodedPayload, password);
                    if (!decryptedJson) {
                        alert("❌ Kata sandi salah atau file rusak!");
                        return;
                    }

                    let dataObj = JSON.parse(decryptedJson);
                    let count = 0;
                    for (let k in dataObj) {
                        localStorage.setItem(k, dataObj[k]);
                        count++;
                    }

                    closeRestorePasswordModal();
                    alert(`✅ Sandi benar! Seluruh data sistem berhasil dipulihkan (${count} entri data dimuat).`);

                    let histories = getBackupHistories();
                    let dateObj = new Date();
                    histories.unshift({
                        id: Date.now(),
                        name: pendingRestoreFileName,
                        date: `${dateObj.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}, ${dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`,
                        type: 'RESTORE FULL SECURE SQL',
                        data: content
                    });
                    saveBackupHistories(histories);
                    location.reload();
                    return;
                }

                // Fallback jika format backup lama
                let restoredCount = 0;
                lines.forEach(line => {
                    if (line.includes('INSERT OR REPLACE INTO local_storage')) {
                        try {
                            let match = line.match(/VALUES \('([^']+)',\s*'([^']*)'\);/);
                            if (match && match[1] && match[2] !== undefined) {
                                localStorage.setItem(match[1], match[2].replace(/''/g, "'"));
                                restoredCount++;
                            }
                        } catch(ex) {}
                    }
                });

                if (restoredCount > 0) {
                    closeRestorePasswordModal();
                    alert(`✅ File backup lama berhasil dipulihkan (${restoredCount} entri data dimuat)!`);
                    location.reload();
                } else {
                    alert("❌ Sandi salah atau struktur file SQL tidak dikenali.");
                }

            } catch(err) {
                console.error(err);
                alert('❌ Gagal memproses pemulihan data.');
            }
        }
    </script>
</body>
</html>
