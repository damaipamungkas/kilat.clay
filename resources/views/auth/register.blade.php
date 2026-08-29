@php
    // Registrasi Akun Parent - KILAT⚡
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun Parent - KILAT⚡</title>

    <!-- Font & Icon -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/auth.css') }}">
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
        <h1>SISTEM REGISTRASI<br>ORANG TUA (PARENT)</h1>
        @include('layouts.divider')
    </header>

    @include('layouts.slider')

    <div class="wrapper">
        <div class="tech-card">
            <div class="card-bg">
                <h2 class="card-title"><i class="fa-solid fa-user-plus"></i> Registrasi Akun Baru (Parent)</h2>

                <!-- TAMPILKAN PESAN ERROR JIKA ADA -->
                @if ($errors->any())
                    <div class="alert alert-danger" style="color: #ff6b6b; margin-bottom: 15px; font-size: 0.85rem;">
                        <ul style="padding-left: 15px; margin: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- FORM DIARAHKAN KE ROUTE STORE DENGAN METHOD POST -->
                <form id="registrationForm" class="reg-form" action="{{ route('register.store') }}" method="POST">
                    @csrf

                    <!-- PENGATURAN ROLE OTOMATIS: PARENT -->
                    <input type="hidden" name="role" value="parent">

                    <div class="input-group">
                        <label for="nama">IDENTITAS PENGGUNA</label>
                        <div class="input-wrapper sci-fi-input-composite">
                            <i class="fa-solid fa-id-badge"></i>
                            <select id="gender" name="gender" class="sci-fi-select">
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                            </select>
                            <input type="text" id="nama" name="name" class="sci-fi-text-inner" placeholder="Masukkan nama panggilan..." value="{{ old('name') }}" required autocomplete="off">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="username">ALAMAT EMAIL SISTEM</label>
                        <div class="input-wrapper sci-fi-input-composite">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" id="username" name="email" class="sci-fi-text-inner" placeholder="Masukkan email aktif (contoh: parent@kilat.com)" value="{{ old('email') }}" required autocomplete="off">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="password">KUNCI KEAMANAN (PASSWORD)</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" id="password" name="password" class="sci-fi-input" placeholder="Buat kata sandi akun Anda..." required>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="agree" name="agree" required>
                        <label for="agree">
                            Otorisasi sistem: Saya menyetujui persyaratan dan telah <a href="{{ route('rule') }}" target="_blank">membaca protokol aturan</a> sekolah KILAT.
                        </label>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn-neon">
                            <i class="fa-solid fa-satellite-dish"></i> DAFTAR AKUN
                        </button>

                        <a href="{{ route('login') }}" class="btn-neon">
                            <i class="fa-solid fa-right-to-bracket"></i> SUDAH PUNYA AKUN? MASUK
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bottom-actions">
        <a href="{{ route('home') }}" class="btn-neon">
            <i class="fa-solid fa-arrow-left"></i> KEMBALI KE BERANDA
        </a>
    </div>

    <footer class="footer">
        <div>Koneksi Terenkripsi.</div>
        <div class="logo-box">
            KILAT⚡ <br><span style="font-size: 0.65rem; font-weight:800; color:var(--text-muted);">&copy; 2026 - Kediri Inline Skate School</span>
        </div>
        <!-- Tombol Rahasia SQL Restore di Footer (Gaya disamakan persis dengan footer standar) -->
        <div>
            <button type="button" onclick="openRestorePasswordModalDirect()" style="background: none; border: none; padding: 0; font-family: inherit; font-size: 0.80rem; font-weight: 800; color: var(--text-muted); cursor: pointer; text-decoration: none;" title="Sistem Database v.2.0">
                Sistem Database v.2.0
            </button>
        </div>
    </footer>
</div>

<!-- Modal Input File & Password Saat Restore File SQL (Rahasia Footer) -->
<input type="file" id="uploadFileBackup" accept=".sql" style="display:none;" onchange="handleRestoreFilePrompt(event)">

<div id="restorePasswordModal" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(42, 34, 69, 0.6); backdrop-filter: blur(5px); display: none; align-items: center; justify-content: center; z-index: 999999; padding: 15px;">
    <div style="background: #2a2245; width: 100%; max-width: 420px; border-radius: 30px; padding: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);">
        <div style="text-align: center; margin-bottom: 15px;">
            <i class="fa-solid fa-key" style="font-size: 2.3rem; color: #ff6b81; margin-bottom: 10px;"></i>
            <h2 style="font-size: 1.2rem; font-weight: 900; color: #fff; margin-bottom: 5px;">Verifikasi Sandi File SQL</h2>
            <p style="font-size: 0.85rem; color: #a0aec0; font-weight: 700;" id="restoreFileNameLabel">Pilih file backup .sql terkunci yang ingin dipulihkan.</p>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="font-size: 0.85rem; font-weight: 800; color: #fff; display: block; margin-bottom: 6px;">Kata Sandi File</label>
            <input type="password" id="restoreInputPassword" placeholder="Masukkan sandi..." style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: #fff; outline: none; font-size: 0.9rem;">
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="closeRestorePasswordModal()" style="background: rgba(255,255,255,0.1); color: #a0aec0; flex: 1; padding: 12px; border-radius: 12px; border: none; font-weight: 800; cursor: pointer;">Batal</button>
            <button type="button" onclick="verifyAndExecuteRestore()" style="background: #6366f1; color: white; flex: 1; padding: 12px; border-radius: 12px; border: none; font-weight: 800; cursor: pointer;">Buka & Pulihkan</button>
        </div>
    </div>
</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk scroll"></div>

<!-- Skrip JavaScript untuk Restore Database SQL ber-Password -->
<script>
    let pendingRestoreContent = '';
    let pendingRestoreFileName = '';

    function openRestorePasswordModalDirect() {
        document.getElementById('uploadFileBackup').click();
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

    function b58DecodeWithPassword(encoded, password) {
        try {
            let decoded = decodeURIComponent(atob(encoded));
            let parts = decoded.split("::KILAT::");
            if (parts.length < 2) return null;
            if (parts[0] !== password) return null; // Sandi salah
            return parts.slice(1).join("::KILAT::");
        } catch(e) {
            return null;
        }
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
                alert(`✅ Sandi benar! Database berhasil dipulihkan (${count} entri data dimuat).`);
                location.reload();
                return;
            }

            // Fallback jika file SQL versi lama tanpa enkripsi password
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

<!-- Script JS Bawaan Auth -->
<script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
