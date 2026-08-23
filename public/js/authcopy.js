document.addEventListener("DOMContentLoaded", () => {
    initAuthOrProfile();
    initColorSlider();
    initSkateScrollbar();
});

// --- 1. INISIALISASI HALAMAN LOGIN / PROFIL / REGISTRASI ---
function initAuthOrProfile() {
    // Tombol Mata Password (Login)
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    // Inisialisasi Tampilan Profil Jika Berada di Halaman Profil
    setupProfilePage();
}

// --- 2. LOGIKA HALAMAN PROFIL & HELPER ---
window.lihatBiodataAtlet = function(nickname) {
    localStorage.setItem('lastActiveAthlete', nickname);
    window.location.href = '/appendix';
};

window.daftarkanAtletBaru = function() {
    localStorage.removeItem('lastActiveAthlete');
    window.location.href = '/appendix';
};

let isPasswordVisible = false;
window.togglePasswordVisibility = function() {
    isPasswordVisible = !isPasswordVisible;
    const passDisplay = document.getElementById('profilePasswordDisplay');
    const passInput = document.getElementById('profilePasswordInput');
    const eyeIcon = document.getElementById('eyeIcon');
    const rawPass = passDisplay ? passDisplay.dataset.rawPass || '' : '';

    if (isPasswordVisible) {
        if (eyeIcon) {
            eyeIcon.className = 'fa-solid fa-eye-slash';
        }
        // Jika sedang dalam mode edit (input password terlihat)
        if (passInput && window.getComputedStyle(passInput).display !== 'none') {
            passInput.type = 'text';
        } else if (passDisplay) {
            // Jika dalam mode baca biasa, tampilkan teks aslinya
            passDisplay.textContent = rawPass;
        }
    } else {
        if (eyeIcon) {
            eyeIcon.className = 'fa-solid fa-eye';
        }
        // Jika sedang dalam mode edit
        if (passInput && window.getComputedStyle(passInput).display !== 'none') {
            passInput.type = 'password';
        } else if (passDisplay) {
            // Jika dalam mode baca biasa, sembunyikan kembali menjadi titik-titik
            passDisplay.textContent = '••••••••';
        }
    }
};

let isEditingProfile = false;
window.toggleEditProfil = function() {
    isEditingProfile = !isEditingProfile;
    const namaDisplay = document.getElementById('profileNamaDisplay');
    const namaInput = document.getElementById('profileNamaInput');
    const emailDisplay = document.getElementById('profileEmailDisplay');
    const emailInput = document.getElementById('profileEmailInput');
    const passDisplay = document.getElementById('profilePasswordDisplay');
    const passInput = document.getElementById('profilePasswordInput');
    const editBtn = document.getElementById('editProfileBtn');

    if (!namaDisplay || !namaInput) return;

    if (isEditingProfile) {
        namaInput.value = namaDisplay.textContent;
        if (emailInput && emailDisplay) emailInput.value = emailDisplay.textContent;
        if (passInput && passDisplay) {
            passInput.value = passDisplay.dataset.rawPass || '';
            passInput.type = isPasswordVisible ? 'text' : 'password';
        }

        namaDisplay.style.display = 'none';
        namaInput.style.display = 'inline-block';

        if (emailDisplay && emailInput) {
            emailDisplay.style.display = 'none';
            emailInput.style.display = 'inline-block';
        }

        if (passDisplay && passInput) {
            passDisplay.style.display = 'none';
            passInput.style.display = 'inline-block';
        }

        if (editBtn) {
            editBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> SIMPAN PERUBAHAN';
            editBtn.style.background = 'var(--clay-green)';
        }
    } else {
        let savedData = localStorage.getItem('kilat_user_data');
        if (savedData) {
            let userData = JSON.parse(savedData);
            userData.namaLengkap = namaInput.value.trim() || userData.namaLengkap;
            userData.nama = userData.namaLengkap;
            if (emailInput) userData.email = emailInput.value.trim() || userData.email;
            if (passInput && passInput.value.trim() !== '') {
                userData.password = passInput.value.trim();
            }

            localStorage.setItem('kilat_user_data', JSON.stringify(userData));

            namaDisplay.textContent = userData.namaLengkap;
            if (emailDisplay && emailInput) emailDisplay.textContent = userData.email;
            if (passDisplay && passInput) {
                passDisplay.dataset.rawPass = userData.password || '';
                passDisplay.textContent = isPasswordVisible ? (userData.password || '') : '••••••••';
            }
        }

        namaInput.style.display = 'none';
        namaDisplay.style.display = 'inline-block';

        if (emailInput && emailDisplay) {
            emailInput.style.display = 'none';
            emailDisplay.style.display = 'inline-block';
        }

        if (passInput && passDisplay) {
            passInput.style.display = 'none';
            passDisplay.style.display = 'inline-block';
        }

        if (editBtn) {
            editBtn.innerHTML = '<i class="fa-solid fa-user-pen"></i> EDIT PROFIL';
            editBtn.style.background = 'var(--primary-color)';
        }
        alert('Profil berhasil diperbarui!');
    }
};

window.logoutSistem = function() {
    if(confirm("Apakah Anda yakin ingin keluar dari akun?")) {
        localStorage.removeItem('kilat_user_data');
        window.location.href = '/login';
    }
};

function setupProfilePage() {
    const namaDisplay = document.getElementById('profileNamaDisplay');
    if (!namaDisplay) return;

    let savedData = localStorage.getItem('kilat_user_data');
    if (!savedData) {
        alert("⚠️ Akses Ditolak! Transmisi data tidak ditemukan. Silakan Login atau Register terlebih dahulu.");
        window.location.href = '/login';
        return;
    }

    const userData = JSON.parse(savedData);
    const userEmail = userData.email || userData.username || 'Tidak Terdeteksi';
    const userName = userData.namaLengkap || userData.nama || 'Anonim';
    const userPassword = userData.password || '';
    let userRoleRaw = userData.role || '';

    let registeredUsers = JSON.parse(localStorage.getItem('manageUsersData')) || JSON.parse(localStorage.getItem('KILAT_USERS')) || [];
    let foundUser = registeredUsers.find(u => u.username === userEmail || u.email === userEmail || u.name === userName);
    if (foundUser && foundUser.role) {
        userRoleRaw = foundUser.role;
    }

    const userRole = userRoleRaw.toLowerCase().trim();
    if (userRole !== 'admin' && userRole !== 'parent' && userRole !== 'coach') {
        alert("⚠️ Akses Ditolak! Halaman ini hanya dapat diakses oleh akun dengan role Admin, Parent, atau Coach.");
        window.location.href = '/login';
        return;
    }

    const emailDisplay = document.getElementById('profileEmailDisplay');
    const passDisplay = document.getElementById('profilePasswordDisplay');
    const badgeSpan = document.getElementById('profileRoleBadge');
    const profileIcon = document.getElementById('profileIcon');

    if (namaDisplay) namaDisplay.textContent = userName;
    if (emailDisplay) emailDisplay.textContent = userEmail;
    if (passDisplay) {
        passDisplay.dataset.rawPass = userPassword;
        passDisplay.textContent = '••••••••';
    }
    if (badgeSpan) badgeSpan.textContent = userRoleRaw.toUpperCase();

    if (userRole === 'admin') {
        if (badgeSpan) badgeSpan.className = 'admin-badge';
        if (profileIcon) profileIcon.className = 'fa-solid fa-shield-halved';
    } else if (userRole === 'coach') {
        if (badgeSpan) badgeSpan.className = 'coach-badge';
        if (profileIcon) profileIcon.className = 'fa-solid fa-stopwatch';
    } else {
        if (badgeSpan) badgeSpan.className = 'parent-badge';
        if (profileIcon) profileIcon.className = 'fa-solid fa-id-card-clip';
    }

    const roleContent = document.getElementById('roleSpecificContent');
    const roleActions = document.getElementById('roleSpecificActions');

    if (userRole === 'admin' && roleContent && roleActions) {
        roleContent.innerHTML = `
            <div style="margin-top: 15px; border-top: 2px dashed var(--border-color); padding-top: 15px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <i class="fa-solid fa-user-shield" style="color: var(--primary-color); width: 25px; text-align: center;"></i>
                    <strong style="font-family: 'Nunito', sans-serif; font-size: 0.9rem; font-weight: 900; color: var(--text-muted);">STATUS HAK AKSES SISTEM:</strong>
                </div>
                <div style="background: var(--bg-main); box-shadow: var(--clay-shadow-btn); padding: 20px; border-radius: 20px; text-align: center;">
                    <span style="display: block; font-size: 0.95rem; font-weight: 800; color: var(--text-muted);">Kontrol Penuh Panel Utama</span>
                    <span style="font-size: 1.2rem; font-weight: 900; color: var(--primary-color); text-shadow: var(--text-timbul-light);">SUPERVISOR / ADMIN AKTIF</span>
                </div>
            </div>
        `;
        roleActions.innerHTML = `
            <button type="button" id="editProfileBtn" onclick="toggleEditProfil()" class="btn-neon" style="background: var(--primary-color);">
                <i class="fa-solid fa-user-pen"></i> EDIT PROFIL
            </button>
            <a href="/admin" class="btn-neon" style="background: var(--sidebar-bg);">
                <i class="fa-solid fa-gauge-high"></i> BUKA DASHBOARD ADMIN
            </a>
        `;
    } else if (userRole === 'coach' && roleContent && roleActions) {
        const totalAtlet = userData.totalAtletBinaan || (userData.atlet ? userData.atlet.length : 0);
        roleContent.innerHTML = `
            <div style="margin-top: 15px; border-top: 2px dashed var(--border-color); padding-top: 15px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <i class="fa-solid fa-users" style="color: var(--primary-color); width: 25px; text-align: center;"></i>
                    <strong style="font-family: 'Nunito', sans-serif; font-size: 0.9rem; font-weight: 900; color: var(--text-muted);">INFORMASI ATLET BINAAN:</strong>
                </div>
                <div style="background: var(--bg-main); box-shadow: var(--clay-shadow-btn); padding: 20px; border-radius: 20px; text-align: center;">
                    <span style="display: block; font-size: 0.95rem; font-weight: 800; color: var(--text-muted);">Jumlah Total Atlet:</span>
                    <span style="font-size: 2rem; font-weight: 900; color: var(--text-main); text-shadow: var(--text-timbul-light);">${totalAtlet} <span style="font-size: 1rem; color: var(--primary-color);">ATLET</span></span>
                </div>
            </div>
        `;
        roleActions.innerHTML = `
            <a href="/appendix" class="btn-neon"><i class="fa-solid fa-clipboard-check"></i> MULAI MENILAI</a>
        `;
    } else if (roleContent && roleActions) {
        let linkedAthletes = [];
        let globalAthletesList = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];

        globalAthletesList.forEach(nickname => {
            let bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nickname)) || {};
            if (bio.connectedParent === userEmail || bio.connectedParent === userName || bio.ortu === userName || bio.ortu === userData.namaLengkap) {
                linkedAthletes.push(nickname);
            }
        });

        if (linkedAthletes.length === 0 && userData.atlet && Array.isArray(userData.atlet)) {
            userData.atlet.forEach(atlet => {
                const nick = typeof atlet === 'string' ? atlet : (atlet.namaPanggilan || atlet.nama || '');
                if (nick && !linkedAthletes.includes(nick)) linkedAthletes.push(nick);
            });
        }

        let atletHTML = '';
        linkedAthletes.forEach((nickname) => {
            atletHTML += `
                <div onclick="lihatBiodataAtlet('${nickname}')" style="background: var(--bg-main); box-shadow: var(--clay-shadow-btn); padding: 14px 18px; border-radius: 16px; color: var(--text-main); font-weight: 800; font-size: 1rem; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: 0.3s;" onmouseover="this.style.boxShadow='var(--clay-shadow-inset)'" onmouseout="this.style.boxShadow='var(--clay-shadow-btn)'">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-person-skating" style="color: var(--primary-color);"></i>
                        <span>${nickname}</span>
                    </div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--primary-color); font-size: 0.9rem;"></i>
                </div>
            `;
        });

        roleContent.innerHTML = `
            <div style="margin-top: 15px; border-top: 2px dashed var(--border-color); padding-top: 15px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <i class="fa-solid fa-users" style="color: var(--primary-color); width: 25px; text-align: center;"></i>
                    <strong style="font-family: 'Nunito', sans-serif; font-size: 0.9rem; font-weight: 900; color: var(--text-muted);">DAFTAR ATLET ANDA:</strong>
                </div>
                <div style="display: flex; flex-direction: column; gap: 12px;">${atletHTML}</div>
            </div>
        `;
        roleActions.innerHTML = `
            <button type="button" id="editProfileBtn" onclick="toggleEditProfil()" class="btn-neon" style="background: var(--primary-color);">
                <i class="fa-solid fa-user-pen"></i> EDIT PROFIL
            </button>
            <a onclick="daftarkanAtletBaru()" class="btn-neon"><i class="fa-solid fa-user-plus"></i> DAFTARKAN ATLET BARU</a>
        `;
    }
}

// --- 3. LOGIKA REGISTRASI PARENT ---
window.executeSubmit = function(event) {
    event.preventDefault();
    const btnSubmit = document.querySelector('button[type="submit"]');
    const gender = document.getElementById('gender').value;
    const nama = document.getElementById('nama').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    const fullEmail = "parent." + username + "@kilat.com";
    const fullName = gender + " " + nama;

    if (btnSubmit) {
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> MEMPROSES...';
        btnSubmit.disabled = true;
    }

    let registeredUsers = JSON.parse(localStorage.getItem('manageUsersData')) || JSON.parse(localStorage.getItem('KILAT_USERS')) || [];
    const existingUser = registeredUsers.find(u => u.email === fullEmail || u.username === fullEmail);
    if (existingUser) {
        alert("⚠️ Gagal Registrasi: Email/Username tersebut sudah terdaftar di sistem.");
        if (btnSubmit) {
            btnSubmit.innerHTML = '<i class="fa-solid fa-satellite-dish"></i> DAFTAR AKUN';
            btnSubmit.disabled = false;
        }
        return;
    }

    const newUserRecord = {
        id: Date.now(),
        username: fullEmail,
        email: fullEmail,
        name: fullName,
        namaLengkap: fullName,
        password: password,
        role: "parent",
        createdAt: new Date().toISOString()
    };

    registeredUsers.push(newUserRecord);
    localStorage.setItem('manageUsersData', JSON.stringify(registeredUsers));
    localStorage.setItem('KILAT_USERS', JSON.stringify(registeredUsers));
    localStorage.setItem('kilat_user_data', JSON.stringify(newUserRecord));

    setTimeout(() => {
        alert("Registrasi Berhasil! Akun Parent Anda telah aktif dan otomatis terhubung.");
        window.location.href = '/profil';
    }, 600);
};

// --- 4. SLIDER WARNA HUE (TUNGGAL) ---
function initColorSlider() {
    const cTrack = document.getElementById('colorTrack');
    const cThumb = document.getElementById('colorThumb');
    if (!cTrack || !cThumb) return;

    let isColorDragging = false;

    function updateColorSlider(percent) {
        percent = Math.max(0, Math.min(percent, 100));
        cThumb.style.left = `${percent}%`;
        const hue = Math.round((percent / 100) * 360);
        document.documentElement.style.setProperty('--bg-color-1', `hsl(${hue}, 55%, 86%)`);
        document.documentElement.style.setProperty('--bg-color-2', `hsl(${(hue + 45) % 360}, 65%, 74%)`);
    }

    updateColorSlider(50);

    function moveColorThumb(e) {
        const rect = cTrack.getBoundingClientRect();
        let x = e.clientX - rect.left;
        updateColorSlider((x / rect.width) * 100);
    }

    cTrack.addEventListener('mousedown', (e) => { isColorDragging = true; moveColorThumb(e); });
    document.addEventListener('mouseup', () => { isColorDragging = false; });
    document.addEventListener('mousemove', (e) => { if (isColorDragging) moveColorThumb(e); });
    cTrack.addEventListener('touchstart', (e) => { isColorDragging = true; moveColorThumb(e.touches[0]); }, {passive: true});
    document.addEventListener('touchend', () => { isColorDragging = false; });
    document.addEventListener('touchmove', (e) => { if (isColorDragging) moveColorThumb(e.touches[0]); }, {passive: true});
}

// --- 5. CUSTOM SCROLLBAR SEPATU RODA (TUNGGAL) ---
function initSkateScrollbar() {
    const skateThumb = document.getElementById('skateThumb');
    if (!skateThumb) return;

    let isSkateDragging = false;
    let startY = 0;
    let startThumbTop = 0;

    window.addEventListener('scroll', () => {
        if (!isSkateDragging) {
            const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
            if (scrollableHeight > 0) {
                const scrollPercent = window.scrollY / scrollableHeight;
                const maxThumbTop = window.innerHeight - skateThumb.offsetHeight;
                skateThumb.style.top = `${scrollPercent * maxThumbTop}px`;
            } else {
                skateThumb.style.top = '0px';
            }
        }
    });

    window.addEventListener('resize', () => window.dispatchEvent(new Event('scroll')));

    function onSkateDragStart(y) {
        isSkateDragging = true;
        startY = y;
        startThumbTop = skateThumb.offsetTop;
        document.body.style.userSelect = 'none';
    }

    function onSkateDragMove(y) {
        if (!isSkateDragging) return;
        const deltaY = y - startY;
        let newThumbTop = startThumbTop + deltaY;
        const maxThumbTop = window.innerHeight - skateThumb.offsetHeight;
        if (maxThumbTop > 0) {
            newThumbTop = Math.max(0, Math.min(newThumbTop, maxThumbTop));
            skateThumb.style.top = `${newThumbTop}px`;
            const scrollPercent = newThumbTop / maxThumbTop;
            const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
            window.scrollTo(0, scrollPercent * scrollableHeight);
        }
    }

    function onSkateDragEnd() {
        isSkateDragging = false;
        document.body.style.userSelect = '';
    }

    skateThumb.addEventListener('mousedown', (e) => onSkateDragStart(e.clientY));
    document.addEventListener('mousemove', (e) => onSkateDragMove(e.clientY));
    skateThumb.addEventListener('touchstart', (e) => { onSkateDragStart(e.touches[0].clientY); e.preventDefault(); }, {passive: false});
    document.addEventListener('touchmove', (e) => { if (isSkateDragging) { onSkateDragMove(e.touches[0].clientY); e.preventDefault(); } }, {passive: false});
    document.addEventListener('mouseup', onSkateDragEnd);
    document.addEventListener('touchend', onSkateDragEnd);

    window.dispatchEvent(new Event('scroll'));
}
// --- AUTH.JS (Hanya untuk fitur UI/Animasi) ---
document.addEventListener("DOMContentLoaded", function () {
    // Animasi Scroll Bar Kustom jika ada
    const skateTrack = document.getElementById('skateTrack');
    const skateThumb = document.getElementById('skateThumb');

    if (skateTrack && skateThumb) {
        window.addEventListener('scroll', function () {
            const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            skateThumb.style.top = scrollPercent + '%';
        });
    }
});
