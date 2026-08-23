// ===================================================
// FILE: public/js/profil/profil.js
// MANAJEMEN PROFIL & TOGGLE PASSWORD & ROLE ADMIN/PARENT/COACH/ATLET
// ===================================================

function getAllUsers() {
    return JSON.parse(localStorage.getItem('manageUsersData')) ||
           JSON.parse(localStorage.getItem('KILAT_USERS')) || [];
}

function saveAllUsers(users) {
    localStorage.setItem('manageUsersData', JSON.stringify(users));
    localStorage.setItem('KILAT_USERS', JSON.stringify(users));
}

function getCurrentUser() {
    return JSON.parse(localStorage.getItem('KILAT_CURRENT_USER')) ||
           JSON.parse(localStorage.getItem('kilat_user_data')) || null;
}

function saveCurrentUser(user) {
    localStorage.setItem('KILAT_CURRENT_USER', JSON.stringify(user));
    localStorage.setItem('kilat_user_data', JSON.stringify(user));
}

// --- INITIAL LOAD PROFIL ---
document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById('profileNamaDisplay')) {
        loadUserProfile();
    }
});

// --- LOAD DATA PROFIL DARI STORAGE ---
function loadUserProfile() {
    const currentUser = getCurrentUser();
    const allUsers = getAllUsers();

    if (!currentUser) {
        if (document.getElementById('profileNamaDisplay')) document.getElementById('profileNamaDisplay').innerText = 'Tidak Terautentikasi';
        if (document.getElementById('profileEmailDisplay')) document.getElementById('profileEmailDisplay').innerText = '-';
        if (document.getElementById('profileRoleBadge')) document.getElementById('profileRoleBadge').innerText = 'GUEST';
        return;
    }

    // Mencari akun terdaftar di LocalStorage
    const matchedAccount = allUsers.find(u =>
        (u.id && currentUser.id && u.id === currentUser.id) ||
        (u.username && u.username.toLowerCase() === (currentUser.username || currentUser.email || '').toLowerCase()) ||
        (u.email && u.email.toLowerCase() === (currentUser.email || '').toLowerCase())
    ) || currentUser;

    const nama = matchedAccount.namaLengkap || matchedAccount.nama || matchedAccount.name || currentUser.username || 'Pengguna';
    const email = matchedAccount.username || matchedAccount.email || currentUser.email || '-';
    const password = matchedAccount.password || '12345678';
    const role = (matchedAccount.role || currentUser.role || 'PARENT').toUpperCase();

    if (document.getElementById('profileNamaDisplay')) document.getElementById('profileNamaDisplay').innerText = nama;
    if (document.getElementById('profileEmailDisplay')) document.getElementById('profileEmailDisplay').innerText = email;

    const passDisplay = document.getElementById('profilePasswordDisplay');
    if (passDisplay) {
        passDisplay.innerText = '••••••••';
        passDisplay.setAttribute('data-real-password', password);
    }

    const badgeEl = document.getElementById('profileRoleBadge');
    if (badgeEl) {
        badgeEl.innerText = role;
        badgeEl.style.cssText = getRoleStyle(role);
    }

    const eyeIcon = document.getElementById('eyeIcon');
    if (eyeIcon) {
        eyeIcon.className = 'fa-solid fa-eye';
    }

    // TAMPILKAN TOMBOL DASHBOARD ADMIN JIKA ROLE ADALAH ADMIN
    const btnAdmin = document.getElementById('btnAdminDashboard');
    if (btnAdmin) {
        btnAdmin.style.display = (role === 'ADMIN') ? 'inline-flex' : 'none';
    }

    renderRoleSpecificContent(matchedAccount, allUsers);
}

// --- STYLING BADGE ROLE ---
function getRoleStyle(role) {
    let r = role.toLowerCase();
    switch (r) {
        case 'admin':
            return 'background: rgba(59, 130, 246, 0.2); color: #1d4ed8; border: 1px solid #93c5fd; padding: 4px 12px; border-radius: 12px; font-weight: 800;';
        case 'coach':
            return 'background: rgba(34, 197, 94, 0.2); color: #15803d; border: 1px solid #86efac; padding: 4px 12px; border-radius: 12px; font-weight: 800;';
        case 'parent':
            return 'background: rgba(239, 68, 68, 0.2); color: #b91c1c; border: 1px solid #fca5a5; padding: 4px 12px; border-radius: 12px; font-weight: 800;';
        case 'atlet':
        case 'athlete':
            return 'background: rgba(234, 179, 8, 0.2); color: #a16207; border: 1px solid #fde047; padding: 4px 12px; border-radius: 12px; font-weight: 800;';
        default:
            return 'background: rgba(107, 114, 128, 0.2); color: #374151; padding: 4px 12px; border-radius: 12px; font-weight: 800;';
    }
}

// --- RENDER CONTENT TERTAUT & TOMBOL KHUSUS PARENT / ATLET ---
function renderRoleSpecificContent(account, allUsers) {
    const container = document.getElementById('roleSpecificContent');
    if (!container) return;

    let role = (account.role || '').toLowerCase();
    let html = '';

    if (role === 'parent') {
        let athletes = account.atletTautan || account.athletes || [];
        let athleteTags = '';

        if (Array.isArray(athletes) && athletes.length > 0) {
            athleteTags = athletes.map(ath => {
                let name = typeof ath === 'object' ? (ath.name || ath.nickname || ath.fullName) : ath;
                return `<span style="background: rgba(234, 179, 8, 0.2); color: #a16207; border: 1px solid #fde047; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; margin: 3px 2px; display: inline-block;">
                    <i class="fa-solid fa-person-skating"></i> ${name}
                </span>`;
            }).join(' ');
        } else {
            athleteTags = `<div style="margin-top: 6px; padding: 10px; background: rgba(239, 68, 68, 0.08); border: 1px dashed #fca5a5; border-radius: 10px; color: #b91c1c; font-size: 0.85rem; font-weight: 600;">
                <i class="fa-solid fa-circle-exclamation"></i> Belum ada atlet tertaut. Silakan <strong>tambahkan atlet pada tombol tabel appendix di bawah</strong>.
            </div>`;
        }

        html = `
            <div style="margin-bottom: 15px;">
                <i class="fa-solid fa-children"></i>
                <strong>ATLET TERTAUT:</strong>
                <div style="margin-top: 6px;">${athleteTags}</div>
            </div>

            <!-- TOMBOL KHUSUS MENUJU TABEL APPENDIX -->
            <div style="margin-top: 15px; text-align: center;">
                <a href="/appendix" onclick="navigateToAppendix(event)" class="btn-neon" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px 16px; background: var(--btn-bg, #3b82f6); color: #fff; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.9rem;">
                    <i class="fa-solid fa-table-list"></i> BUKA TABEL APPENDIX (MANAJEMEN ATLET)
                </a>
            </div>
        `;
    } else if (role === 'atlet' || role === 'athlete') {
        let currentName = account.namaLengkap || account.nama || account.username;
        let parentUsers = allUsers.filter(u => (u.role || '').toLowerCase() === 'parent');
        let matchedParents = [];

        parentUsers.forEach(p => {
            let pAthletes = p.atletTautan || p.athletes || [];
            let isLinked = pAthletes.some(ath => {
                let name = typeof ath === 'object' ? (ath.name || ath.nickname || ath.fullName) : ath;
                return name === currentName || name === account.username;
            });
            if (isLinked) {
                matchedParents.push(p.namaLengkap || p.nama || p.username);
            }
        });

        let parentTags = matchedParents.length > 0 ? matchedParents.map(pName => `
            <span style="background: rgba(239, 68, 68, 0.2); color: #b91c1c; border: 1px solid #fca5a5; padding: 3px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; margin: 2px; display: inline-block;">
                <i class="fa-solid fa-user-tie"></i> ${pName}
            </span>
        `).join(' ') : '<span style="color:#9ca3af; font-style:italic;">Belum Tertaut ke Parent</span>';

        html = `
            <p>
                <i class="fa-solid fa-user-shield"></i>
                <strong>PARENT TERTAUT:</strong><br>
                <div style="margin-top: 5px;">${parentTags}</div>
            </p>

            <!-- TOMBOL AKSES APPENDIX KHUSUS ATLET -->
            <div style="margin-top: 15px; text-align: center;">
                <a href="/appendix?role=atlet" onclick="navigateToAppendix(event)" class="btn-neon" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px 16px; background: var(--btn-bg, #3b82f6); color: #fff; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.9rem;">
                    <i class="fa-solid fa-table-list"></i> BUKA TABEL APPENDIX
                </a>
            </div>
        `;
    } else {
        html = `
            <p style="color: var(--text-muted); font-size: 0.85rem; text-align: center; margin: 0;">
                <i class="fa-solid fa-circle-check" style="color:#10b981;"></i> Akun terverifikasi dengan akses penuh sistem.
            </p>

            <div style="margin-top: 15px; text-align: center;">
                <a href="/appendix" onclick="navigateToAppendix(event)" class="btn-neon" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 10px 16px; background: var(--btn-bg, #3b82f6); color: #fff; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.9rem;">
                    <i class="fa-solid fa-table-list"></i> BUKA TABEL APPENDIX
                </a>
            </div>
        `;
    }

    container.innerHTML = html;
}

// --- FUNGSI NAVIGASI PAKSA KE TABEL APPENDIX BERDASARKAN ROLE USER TERDAFTAR ---
window.navigateToAppendix = function(e) {
    if (e) e.preventDefault();

    const currentUser = getCurrentUser();
    const allUsers = getAllUsers();

    if (!currentUser) {
        alert("Sesi login tidak ditemukan. Silakan login kembali.");
        window.location.href = '/login';
        return;
    }

    // Mengambil profil paling akurat dari database/LocalStorage
    const matchedAccount = allUsers.find(u =>
        (u.id && currentUser.id && u.id === currentUser.id) ||
        (u.username && u.username.toLowerCase() === (currentUser.username || currentUser.email || '').toLowerCase()) ||
        (u.email && u.email.toLowerCase() === (currentUser.email || '').toLowerCase())
    ) || currentUser;

    const actualRole = (matchedAccount.role || currentUser.role || 'PARENT').toLowerCase();
    const userId = matchedAccount.id || currentUser.id || '';
    const userIdentifier = matchedAccount.username || matchedAccount.email || '';

    // Sinkronisasi penuh ke LocalStorage agar halaman Appendix membaca role resmi
    localStorage.setItem('KILAT_ACTIVE_ROLE', actualRole);
    localStorage.setItem('userRole', actualRole);

    // Membangun URL tujuan dengan parameter role & identifier pengguna
    let targetUrl = `/appendix?role=${encodeURIComponent(actualRole)}`;
    if (userId) {
        targetUrl += `&id=${encodeURIComponent(userId)}`;
    }
    if (actualRole === 'parent' && userIdentifier) {
        targetUrl += `&parent=${encodeURIComponent(userIdentifier)}`;
    }

    // Pindah ke halaman Appendix
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
        inputNama.value = displayNama ? displayNama.innerText : '';
        inputPass.value = displayPass ? (displayPass.getAttribute('data-real-password') || '') : '';

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

    let currentUser = getCurrentUser();
    let allUsers = getAllUsers();

    if (currentUser) {
        currentUser.namaLengkap = newName;
        saveCurrentUser(currentUser);
    }

    let userIdx = allUsers.findIndex(u =>
        (u.username && u.username.toLowerCase() === (currentUser.username || currentUser.email || '').toLowerCase()) ||
        (u.email && u.email.toLowerCase() === (currentUser.email || '').toLowerCase())
    );

    if (userIdx !== -1) {
        allUsers[userIdx].namaLengkap = newName;
        if (newPass) allUsers[userIdx].password = newPass;
        saveAllUsers(allUsers);
    }

    if (document.getElementById('profileNamaDisplay')) document.getElementById('profileNamaDisplay').style.display = 'inline';
    if (document.getElementById('profileNamaInput')) document.getElementById('profileNamaInput').style.display = 'none';
    if (document.getElementById('profilePasswordDisplay')) document.getElementById('profilePasswordDisplay').style.display = 'inline';
    if (document.getElementById('profilePasswordInput')) document.getElementById('profilePasswordInput').style.display = 'none';

    if (document.getElementById('btnEditProfile')) document.getElementById('btnEditProfile').style.display = 'inline-block';
    if (document.getElementById('btnSaveProfile')) document.getElementById('btnSaveProfile').style.display = 'none';

    loadUserProfile();
    alert("Profil berhasil diperbarui!");
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
        const realPass = displayPass.getAttribute('data-real-password') || '12345678';
        if (displayPass.innerText.includes('•')) {
            displayPass.innerText = realPass;
            eyeIcon.className = 'fa-solid fa-eye-slash';
        } else {
            displayPass.innerText = '••••••••';
            eyeIcon.className = 'fa-solid fa-eye';
        }
    }
};
    function handleLogout() {
        if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
            localStorage.removeItem('KILAT_CURRENT_USER');
            localStorage.removeItem('kilat_user_data');
            window.location.href = "{{ route('login') }}";
        }
    }
