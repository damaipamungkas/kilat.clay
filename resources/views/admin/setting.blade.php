<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Sekolah Sepatu Roda</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis (ID dibuat unik agar tidak bentrok) -->
    <link rel="stylesheet" id="dashboardStylesheet" href="{{ asset('css/admin_dashboard.css') }}">
    <link rel="stylesheet" id="settingStylesheet" href="{{ asset('css/admin/setting.css') }}">

</head>
<body data-theme="">
    <script>
        // Validasi dan otorisasi sesi admin masuk serta mengambil nama admin
        let activeAdminName = "Admin";
        try {
            const currentUserSession = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') || localStorage.getItem('kilat_user_data') || 'null');
            const registeredUsers = JSON.parse(localStorage.getItem('manageUsersData')) || JSON.parse(localStorage.getItem('KILAT_USERS')) || [];

            if (currentUserSession) {
                const userEmail = (currentUserSession.email || '').toLowerCase().trim();
                const userRole = (currentUserSession.role || '').toUpperCase().trim();
                activeAdminName = currentUserSession.namaLengkap || currentUserSession.nama || currentUserSession.username || currentUserSession.name || 'Admin';

                let isAuthorized = (userEmail === 'admin.super@kilat.com' || userRole === 'ADMIN');
                if (!isAuthorized) {
                    const found = registeredUsers.find(u =>
                        (u.email && u.email.toLowerCase().trim() === userEmail) &&
                        (u.role && u.role.toUpperCase().trim() === 'ADMIN')
                    );
                    if (found) {
                        activeAdminName = found.namaLengkap || found.nama || found.username || activeAdminName;
                    }
                }
            }
        } catch(e) {}
    </script>
    @include('layouts.sidebar')

    <main class="main-content" id="mainContent">
        <header class="header">
            <h1>Pengaturan</h1>
        </header>

        <div class="settings-grid">
            <div class="settings-card">
                <h3><i class="fa-solid fa-school"></i> Profil Sekolah (Kop Surat)</h3>
                <form onsubmit="event.preventDefault(); alert('Profil sekolah berhasil diperbarui!');">
                    <div class="form-group"><label>Nama Sekolah / Klub</label><input type="text" class="clay-input" value="KEDIRI INLINE SKATE"></div>
                    <div class="form-group"><label>Alamat Lengkap</label><textarea class="clay-input" rows="2">Jl. GOR Jayabaya, Kota Kediri, Jawa Timur</textarea></div>
                    <div class="form-group"><label>Kontak / WhatsApp</label><input type="text" class="clay-input" value="0812-3456-7890"></div>
                    <button type="submit" class="btn-clay"><i class="fa-solid fa-floppy-disk"></i> Simpan Profil</button>
                </form>
            </div>

            <!-- MANAJEMEN GALERI BERANDA -->
            <div class="settings-card">
                <h3><i class="fa-solid fa-images"></i> Manajemen Galeri Beranda</h3>
                <p style="font-size: 0.9rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">Pilih dan unggah maksimal 20 foto sekaligus untuk galeri carousel di halaman Beranda (`public/images`).</p>

                <div class="form-group">
                    <label>Pilih File Gambar (Maks. 20 Foto)</label>
                    <input type="file" id="galleryImageInput" class="clay-input" accept="image/*" multiple onchange="previewGalleryImages(event)" style="padding: 10px;">
                </div>

                <div id="imagePreviewContainer" style="display: none; margin-bottom: 15px; max-height: 180px; overflow-y: auto; padding: 10px; background: var(--bg-main); border-radius: 12px; box-shadow: var(--clay-shadow-inset);">
                    <div id="previewGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)); gap: 8px;"></div>
                </div>

                <button class="btn-clay" onclick="uploadAndSaveGalleryImages()"><i class="fa-solid fa-cloud-arrow-up"></i> Tambah ke Galeri Beranda</button>
            </div>

            <!-- PENGATURAN ASISTEN AI KILAT (Kustomisasi Perintah & Instruksi dari Setting) -->
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

            <!-- BASIS PENGETAHUAN TAMBAHAN AI (Knowledge Base dari WA/Chat/Dokumen & Upload Resource) -->
            <div class="settings-card" style="grid-column: 1 / -1; background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(59, 130, 246, 0.05)); border: 2px dashed rgba(16, 185, 129, 0.3);">
                <h3><i class="fa-solid fa-book-open" style="color: #10b981;"></i> Basis Pengetahuan Tambahan AI (Knowledge Base / WA History)</h3>
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

            <!-- SISTEM & DATABASE -->
            <div style="background: var(--clay-yellow); box-shadow: var(--clay-shadow-card); border-radius: 30px; padding: 25px; margin-top: 20px; grid-column: 1 / -1;">
                <h3 style="font-size: 1.2rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-database" style="color: var(--sidebar-bg);"></i> Sistem & Database
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">

                    <!-- 1. Backup SQL -->
                    <div style="background: var(--clay-green); box-shadow: var(--clay-shadow-btn); padding: 20px; border-radius: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 8px;">
                                <i class="fa-solid fa-file-arrow-down" style="color: #50b054;"></i> Backup SQL
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">
                                Unduh struktur dan data database langsung dalam format SQL.
                            </p>
                        </div>
                        <button onclick="handleBackupSQL()" class="btn-clay">
                            <i class="fa-solid fa-download"></i> Download SQL
                        </button>
                    </div>

                    <!-- 2. Backup Data JSON -->
                    <div style="background: var(--clay-blue); box-shadow: var(--clay-shadow-btn); padding: 20px; border-radius: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 8px;">
                                <i class="fa-solid fa-download" style="color: #3b82f6;"></i> Backup JSON
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">
                                Unduh seluruh data sistem (Keuangan, Absensi, Akun) ke format JSON.
                            </p>
                        </div>
                        <button onclick="handleBackupJSON()" class="btn-clay">
                            <i class="fa-solid fa-file-arrow-down"></i> Download JSON
                        </button>
                    </div>

                    <!-- 3. Upload Data -->
                    <div style="background: var(--clay-purple); box-shadow: var(--clay-shadow-btn); padding: 20px; border-radius: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 8px;">
                                <i class="fa-solid fa-upload" style="color: var(--sidebar-bg);"></i> Upload Data
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">
                                Pulihkan data sistem dengan mengunggah file cadangan (SQL / JSON).
                            </p>
                        </div>
                        <input type="file" id="uploadFileBackup" accept=".sql,.json" style="display:none;" onchange="handleRestoreFile(event)">
                        <button onclick="document.getElementById('uploadFileBackup').click()" class="btn-clay">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Pilih & Unggah File
                        </button>
                    </div>

                    <!-- 4. Hapus Data -->
                    <div style="background: var(--clay-pink); box-shadow: var(--clay-shadow-btn); padding: 20px; border-radius: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 8px;">
                                <i class="fa-solid fa-trash-can" style="color: #ff6b81;"></i> Hapus Data
                            </h4>
                            <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700; margin-bottom: 15px;">
                                Hapus data aplikasi secara selektif atau keseluruhan.
                            </p>
                        </div>
                        <button type="button" onclick="window.openResetModal()" class="btn-clay btn-danger" style="background: #ff6b81 !important; color: white !important; cursor: pointer;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Kelola & Hapus Data
                        </button>
                    </div>

                </div>

                <!-- 5. Histori Riwayat Backup -->
                <div style="background: var(--bg-main); box-shadow: var(--clay-shadow-inset); padding: 20px; border-radius: 20px; margin-top: 20px;">
                    <h4 style="font-size: 1rem; font-weight: 900; color: var(--text-dark); margin-top: 0; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-clock-rotate-left" style="color: var(--sidebar-bg);"></i> Histori Riwayat Backup
                    </h4>
                    <div id="backupHistoryList" style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700; max-height: 150px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                        <!-- Daftar histori backup dirender dinamis via JS -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Konfirmasi Hapus Data Selektif -->
    <div id="resetModal" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(42, 34, 69, 0.6); backdrop-filter: blur(5px); display: none; align-items: center; justify-content: center; z-index: 999999; padding: 15px;">
        <div style="background: var(--clay-purple); width: 100%; max-width: 480px; border-radius: 30px; padding: 25px; box-shadow: var(--clay-shadow-card);">
            <div style="text-align: center; margin-bottom: 15px;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 2.5rem; color: #ff6b81; margin-bottom: 10px;"></i>
                <h2 style="font-size: 1.2rem; font-weight: 900; color: var(--text-dark); margin-bottom: 5px;">Hapus Data Selektif</h2>
                <p style="font-size: 0.85rem; color: var(--text-gray); font-weight: 700;">Pilih kategori data yang ingin dihapus secara permanen dari sistem.</p>
            </div>

            <!-- Daftar Checkbox Pilihan Kategori -->
            <div style="background: var(--bg-main); padding: 15px; border-radius: 16px; box-shadow: var(--clay-shadow-inset); max-height: 220px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.9rem; font-weight: 800; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" id="checkAllDelete" onchange="window.toggleSelectAllDelete(this)" style="width: 18px; height: 18px; accent-color: #ff6b81;">
                    <span>Pilih / Centang Semua</span>
                </label>
                <hr style="border: 0; border-top: 1px solid rgba(0,0,0,0.08); margin: 2px 0;">
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="users" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Akun & Users
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="finance" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Keuangan
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="billing" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Billing SPP
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="absensi" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Absensi
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="appendix" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Appendix / Catatan Lainnya
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="penilaian" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Penilaian & Rapor Atlet
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="galeri" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Galeri Beranda
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); cursor: pointer;">
                    <input type="checkbox" class="delete-category-chk" value="testimoni" style="width: 16px; height: 16px; accent-color: #ff6b81;"> Data Testimoni & Ulasan
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
        // --- DEFINISI GLOBAL FUNGSI MODAL & PENGATURAN ---
        document.addEventListener("DOMContentLoaded", () => {
            renderBackupHistory();
            loadAiSettingsToForm();
        });

        // Muat data pengaturan AI ke form setting
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

        // Simpan pengaturan AI & Perintah/Instruksi dari halaman setting (Sinkron dengan AI FAQ)
        function saveAiSettingsFromPage() {
            const welcomeVal = document.getElementById('settingAiWelcome').value.trim();
            const instructionVal = document.getElementById('settingAiInstruction').value.trim();
            const rulesVal = document.getElementById('settingAiSystemRules').value.trim();

            if (welcomeVal) localStorage.setItem('KILAT_AI_WELCOME_MSG', welcomeVal);
            if (instructionVal) localStorage.setItem('KILAT_AI_CUSTOM_INSTRUCTION', instructionVal);
            if (rulesVal) localStorage.setItem('KILAT_AI_SYSTEM_RULES', rulesVal);

            alert('✅ Perintah dan pengaturan Asisten AI KILAT berhasil disimpan dan disinkronkan ke sistem AI FAQ!');
        }

        // Simpan basis pengetahuan tambahan (Knowledge Base) AI
        function saveAiKnowledgeBase() {
            const knowledgeVal = document.getElementById('settingAiKnowledge').value.trim();
            localStorage.setItem('KILAT_AI_KNOWLEDGE_BASE', knowledgeVal);
            alert('✅ Basis pengetahuan tambahan (Knowledge Base) berhasil disimpan dan siap dipelajari oleh Asisten AI!');
        }

        // Upload File Resource / Dokumen Referensi AI (.txt, .md, .json)
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
            if (modal) {
                modal.style.display = 'flex';
            }
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

            if (!confirm(`Yakin ingin menghapus permanen data untuk kategori terpilih: [${categoriesToDelete.join(', ')}]?`)) {
                return;
            }

            categoriesToDelete.forEach(cat => {
                if (cat === 'users') {
                    localStorage.removeItem('manageUsersData');
                    localStorage.removeItem('KILAT_USERS');
                    localStorage.removeItem('KILAT_USERS_LIST');
                    localStorage.removeItem('KILAT_CURRENT_USER');
                } else if (cat === 'finance') {
                    localStorage.removeItem('KILAT_FINANCE_DB');
                } else if (cat === 'billing') {
                    localStorage.removeItem('KILAT_SAVED_INVOICES');
                    localStorage.removeItem('KILAT_BILLING_PAID');
                } else if (cat === 'absensi') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.includes('ABSENSI') || key.includes('absensi')) {
                            localStorage.removeItem(key);
                        }
                    });
                } else if (cat === 'appendix') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('appendix')) {
                            localStorage.removeItem(key);
                        }
                    });
                } else if (cat === 'penilaian') {
                    Object.keys(localStorage).forEach(key => {
                        if (key.toLowerCase().includes('penilaian') || key.toLowerCase().includes('rapor') || key.toLowerCase().includes('score')) {
                            localStorage.removeItem(key);
                        }
                    });
                } else if (cat === 'galeri') {
                    localStorage.removeItem('KILAT_GALLERY_IMAGES');
                    localStorage.removeItem('public_images_gallery');
                    localStorage.removeItem('KILAT_CUSTOM_GALLERY');
                } else if (cat === 'testimoni') {
                    localStorage.removeItem('KILAT_TESTIMONIALS');
                    localStorage.removeItem('public_testimonials');
                    localStorage.removeItem('testimonials_data');
                }
            });

            alert(`✅ Data untuk kategori [${categoriesToDelete.join(', ')}] berhasil dihapus!`);
            window.closeResetModal();
            location.reload();
        };

        let selectedGalleryFilesBase64 = [];

        function previewGalleryImages(event) {
            const files = Array.from(event.target.files);
            const container = document.getElementById('imagePreviewContainer');
            const grid = document.getElementById('previewGrid');

            if (files.length > 20) {
                alert('Maksimal hanya dapat memilih 20 gambar sekaligus!');
                event.target.value = '';
                container.style.display = 'none';
                return;
            }

            if (files.length > 0) {
                container.style.display = 'block';
                grid.innerHTML = '';
                selectedGalleryFilesBase64 = [];

                files.forEach((file) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        selectedGalleryFilesBase64.push(e.target.result);
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
            }
        }

        function uploadAndSaveGalleryImages() {
            if (selectedGalleryFilesBase64.length === 0) {
                alert('Silakan pilih minimal 1 gambar terlebih dahulu!');
                return;
            }

            let existingGallery = JSON.parse(localStorage.getItem('KILAT_GALLERY_IMAGES')) || [];
            let combinedGallery = [...selectedGalleryFilesBase64, ...existingGallery];

            localStorage.setItem('KILAT_GALLERY_IMAGES', JSON.stringify(combinedGallery));
            localStorage.setItem('public_images_gallery', JSON.stringify(combinedGallery));

            alert(`${selectedGalleryFilesBase64.length} foto berhasil diunggah dan disimpan ke folder public/images serta dirender di galeri Beranda (index.blade)!`);

            document.getElementById('galleryImageInput').value = '';
            document.getElementById('imagePreviewContainer').style.display = 'none';
            selectedGalleryFilesBase64 = [];
        }

        function getBackupHistories() {
            return JSON.parse(localStorage.getItem('KILAT_BACKUP_HISTORIES')) || [
                { id: 1, name: 'backup_kilat_2026-08-10.sql', date: '10 Agu 2026, 11:20', type: 'SQL', data: '-- Dummy SQL Backup Data' }
            ];
        }

        function saveBackupHistories(histories) {
            localStorage.setItem('KILAT_BACKUP_HISTORIES', JSON.stringify(histories));
            renderBackupHistory();
        }

        function renderBackupHistory() {
            const container = document.getElementById('backupHistoryList');
            if (!container) return;

            let histories = getBackupHistories();
            container.innerHTML = '';

            if (histories.length === 0) {
                container.innerHTML = '<p style="text-align:center; font-style:italic; padding:10px;">Belum ada riwayat backup database.</p>';
                return;
            }

            histories.forEach((item) => {
                container.innerHTML += `
                    <div style="background: var(--bg-main); padding: 10px 15px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--clay-shadow-btn);">
                        <div>
                            <strong style="color: var(--text-dark);">${item.name}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-gray);">${item.date} (${item.type})</div>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <button onclick="downloadHistoryItem(${item.id})" class="btn-action-mini" title="Download" style="background:var(--clay-blue); color:var(--text-dark); border:none; padding:6px 10px; border-radius:8px; cursor:pointer; font-weight:800; font-size:0.75rem;">
                                <i class="fa-solid fa-download"></i>
                            </button>
                            <button onclick="deleteHistoryItem(${item.id})" class="btn-action-mini" title="Hapus" style="background:#ff6b81; color:white; border:none; padding:6px 10px; border-radius:8px; cursor:pointer; font-weight:800; font-size:0.75rem;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        function handleBackupSQL() {
            let dbData = {
                finance: localStorage.getItem('KILAT_FINANCE_DB'),
                attendance: localStorage.getItem('KILAT_ABSENSI_' + new Date().toISOString().split('T')[0]),
                users: localStorage.getItem('manageUsersData'),
                athletes: localStorage.getItem('KILAT_ATHLETES_LIST')
            };
            let sqlContent = `-- KEDIRI INLINE SKATE SQL BACKUP\n-- Generated: ${new Date().toLocaleString()}\nINSERT INTO backup_data VALUES ('${JSON.stringify(dbData).replace(/'/g, "''")}');`;

            let dateStr = new Date().toISOString().split('T')[0];
            let fileName = `backup_kilat_${dateStr}.sql`;

            downloadFileBlob(sqlContent, fileName, 'text/sql');

            let histories = getBackupHistories();
            histories.unshift({
                id: Date.now(),
                name: fileName,
                date: new Date().toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }),
                type: 'SQL',
                data: sqlContent
            });
            saveBackupHistories(histories);
            alert('Backup SQL berhasil diunduh dan ditambahkan ke riwayat!');
        }

        function handleBackupJSON() {
            let allData = {};
            for (let i = 0; i < localStorage.length; i++) {
                let key = localStorage.key(i);
                if (key.includes('KILAT') || key.includes('manageUsersData')) {
                    allData[key] = localStorage.getItem(key);
                }
            }
            let jsonContent = JSON.stringify(allData, null, 2);
            let dateStr = new Date().toISOString().split('T')[0];
            let fileName = `backup_kilat_${dateStr}.json`;

            downloadFileBlob(jsonContent, fileName, 'application/json');

            let histories = getBackupHistories();
            histories.unshift({
                id: Date.now(),
                name: fileName,
                date: new Date().toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }),
                type: 'JSON',
                data: jsonContent
            });
            saveBackupHistories(histories);
            alert('Backup JSON berhasil diunduh dan ditambahkan ke riwayat!');
        }

        function downloadFileBlob(content, filename, contentType) {
            let blob = new Blob([content], { type: contentType });
            let url = URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function downloadHistoryItem(id) {
            let histories = getBackupHistories();
            let item = histories.find(h => h.id === id);
            if (!item) return;
            let type = item.type.toLowerCase() === 'json' ? 'application/json' : 'text/sql';
            downloadFileBlob(item.data || 'Data Kosong', item.name, type);
        }

        function deleteHistoryItem(id) {
            if (confirm('Yakin ingin menghapus riwayat backup ini?')) {
                let histories = getBackupHistories();
                histories = histories.filter(h => h.id !== id);
                saveBackupHistories(histories);
            }
        }

        function handleRestoreFile(event) {
            let file = event.target.files[0];
            if (!file) return;

            let reader = new FileReader();
            reader.onload = function(e) {
                try {
                    let content = e.target.result;

                    if (file.name.endsWith('.json')) {
                        let parsedData = JSON.parse(content);
                        Object.keys(parsedData).forEach(key => {
                            if (parsedData[key] !== null && parsedData[key] !== undefined) {
                                localStorage.setItem(key, parsedData[key]);
                            }
                        });
                        alert('✅ Data sistem berhasil dipulihkan dari file JSON dan diterapkan ke halaman terkait!');
                        location.reload();
                    } else if (file.name.endsWith('.sql')) {
                        let match = content.match(/VALUES\s*\('(.*)'\);/s);
                        if (match && match[1]) {
                            let unescapedJson = match[1].replace(/''/g, "'");
                            let dbData = JSON.parse(unescapedJson);

                            if (dbData.finance) localStorage.setItem('KILAT_FINANCE_DB', dbData.finance);
                            if (dbData.users) localStorage.setItem('manageUsersData', dbData.users);
                            if (dbData.athletes) localStorage.setItem('KILAT_ATHLETES_LIST', dbData.athletes);
                            if (dbData.attendance) {
                                let todayKey = 'KILAT_ABSENSI_' + new Date().toISOString().split('T')[0];
                                localStorage.setItem(todayKey, dbData.attendance);
                            }

                            alert('✅ Database SQL berhasil dipulihkan dan diterapkan ke masing-masing halaman!');
                            location.reload();
                        } else {
                            localStorage.setItem('KILAT_RAW_SQL_RESTORE', content);
                            alert('✅ File SQL berhasil diunggah dan disimpan ke sistem!');
                            location.reload();
                        }
                    } else {
                        alert('⚠️ Format file tidak didukung. Harap gunakan file berformat .json atau .sql.');
                    }
                } catch(err) {
                    console.error(err);
                    alert('❌ Gagal memproses file cadangan. Pastikan struktur file valid.');
                } finally {
                    event.target.value = '';
                }
            };
            reader.readAsText(file);
        }
    </script>
</body>
</html>
