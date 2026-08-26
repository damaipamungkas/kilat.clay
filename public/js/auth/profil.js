// ===================================================
// FILE: public/js/auth/profil.js
// MANAJEMEN PROFIL & TOGGLE PASSWORD (VERSI LARAVEL)
// ===================================================

document.addEventListener("DOMContentLoaded", function () {
    // Inisialisasi tambahan jika diperlukan setelah halaman dimuat
    const eyeIcon = document.getElementById('eyeIcon');
    if (eyeIcon) {
        eyeIcon.className = 'fa-solid fa-eye';
    }
});

// --- FUNGSI NAVIGASI KE TABEL APPENDIX ---
window.navigateToAppendix = function(e) {
    if (e) e.preventDefault();

    // Ambil role dari elemen badge di halaman profil yang sudah dicetak Laravel
    const badgeEl = document.getElementById('profileRoleBadge');
    const actualRole = badgeEl ? badgeEl.innerText.toLowerCase().trim() : 'parent';

    localStorage.setItem('KILAT_ACTIVE_ROLE', actualRole);
    localStorage.setItem('userRole', actualRole);

    let targetUrl = `/appendix?role=${encodeURIComponent(actualRole)}`;
    window.location.href = targetUrl;
};

// --- FUNGSI TOGGLE EDIT PROFIL ---
window.toggleEditProfile = function() {
    const displayNama = document.getElementById('profileNamaDisplay');
    const inputNama = document.getElementById('profileNamaInput');
    const displayPass = document.getElementById('profilePasswordDisplay');
    const inputPass = document.getElementById('profilePasswordInput');

    const btnEdit = document.getElementById('btnEditProfile');
    const btnSave = document.getElementById('btnSaveProfile');

    if (inputNama && (getComputedStyle(inputNama).display === 'none')) {
        inputNama.value = displayNama ? displayNama.innerText.trim() : '';
        if (inputPass) inputPass.value = '';

        if (displayNama) displayNama.style.display = 'none';
        if (inputNama) inputNama.style.display = 'inline-block';
        if (displayPass) displayPass.style.display = 'none';
        if (inputPass) inputPass.style.display = 'inline-block';

        if (btnEdit) btnEdit.style.display = 'none';
        if (btnSave) btnSave.style.display = 'inline-block';
    }
};

// --- FUNGSI SIMPAN PERUBAHAN PROFIL ---
window.saveProfileChanges = function() {
    const inputNama = document.getElementById('profileNamaInput');
    const inputPass = document.getElementById('profilePasswordInput');

    const newName = inputNama ? inputNama.value.trim() : '';
    const newPass = inputPass ? inputPass.value : '';

    if (!newName) {
        alert("Nama lengkap tidak boleh kosong.");
        return;
    }

    // Karena menggunakan Laravel, perubahan nama/password idealnya dikirim via AJAX / Form Controller.
    // Untuk saat ini kita perbarui tampilan lokalnya dulu sementara diarahkan ke sinkronisasi backend.
    if (document.getElementById('profileNamaDisplay')) {
        document.getElementById('profileNamaDisplay').innerText = newName;
    }

    if (document.getElementById('profileNamaDisplay')) document.getElementById('profileNamaDisplay').style.display = 'inline';
    if (document.getElementById('profileNamaInput')) document.getElementById('profileNamaInput').style.display = 'none';
    if (document.getElementById('profilePasswordDisplay')) document.getElementById('profilePasswordDisplay').style.display = 'inline';
    if (document.getElementById('profilePasswordInput')) document.getElementById('profilePasswordInput').style.display = 'none';

    if (document.getElementById('btnEditProfile')) document.getElementById('btnEditProfile').style.display = 'inline-block';
    if (document.getElementById('btnSaveProfile')) document.getElementById('btnSaveProfile').style.display = 'none';

    alert("Perubahan profil berhasil diterapkan secara lokal!");
};

// --- FUNGSI TOGGLE MATA PASSWORD ---
window.toggleProfilePasswordVisibility = function(event) {
    if (event) event.preventDefault();

    const displayPass = document.getElementById('profilePasswordDisplay');
    const inputPass = document.getElementById('profilePasswordInput');
    const eyeIcon = document.getElementById('eyeIcon');

    if (!eyeIcon) return;

    const isEditing = inputPass && getComputedStyle(inputPass).display !== 'none';

    if (isEditing) {
        if (inputPass.type === 'password') {
            inputPass.type = 'text';
            eyeIcon.className = 'fa-solid fa-eye-slash';
        } else {
            inputPass.type = 'password';
            eyeIcon.className = 'fa-solid fa-eye';
        }
    } else if (displayPass) {
        // Karena data asli dari database di-hash di backend, tampilkan info placeholder aman
        if (displayPass.innerText.includes('•')) {
            displayPass.innerText = '(Terproteksi Sistem)';
            eyeIcon.className = 'fa-solid fa-eye-slash';
        } else {
            displayPass.innerText = '••••••••';
            eyeIcon.className = 'fa-solid fa-eye';
        }
    }
};
