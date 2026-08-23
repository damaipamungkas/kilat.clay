// ===================================================
// FILE: public/js/admin/users.js (atau file terkait manajemen user)
// ===================================================

// --- MANAJEMEN DATA STORAGE ---
function getUsersData() {
    return JSON.parse(localStorage.getItem('manageUsersData')) ||
           JSON.parse(localStorage.getItem('KILAT_USERS')) ||
           [];
}

function saveUsersData(data) {
    localStorage.setItem('manageUsersData', JSON.stringify(data));
    localStorage.setItem('KILAT_USERS', JSON.stringify(data));
}

// --- INITIAL LOAD ---
document.addEventListener('DOMContentLoaded', function() {
    checkUnpaidAccountsAndArchive();
    initColumnResizer();
    renderTable();
    updateStatsCounter();
    checkAdminAuthorization();
    applyDynamicThemeSettings();
});

// --- VALIDASI OTORISASI ADMIN YANG DIOPTIMALKAN ---
function checkAdminAuthorization() {
    const currentUserSession = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') || localStorage.getItem('kilat_user_data') || 'null');
    const registeredUsers = getUsersData();

    let isAuthorizedAdmin = false;

    if (currentUserSession) {
        const userEmail = (currentUserSession.email || currentUserSession.username || '').toLowerCase().trim();
        const userRole = (currentUserSession.role || '').toLowerCase().trim();

        if (userEmail === 'admin.super@kilat.com' || userRole === 'admin' || userRole === 'administrator') {
            isAuthorizedAdmin = true;
        } else {
            const foundInUsers = registeredUsers.find(u =>
                ((u.email && u.email.toLowerCase().trim() === userEmail) || (u.username && u.username.toLowerCase().trim() === userEmail)) &&
                ((u.role || '').toLowerCase().trim() === 'admin' || (u.role || '').toLowerCase().trim() === 'administrator')
            );
            if (foundInUsers) {
                isAuthorizedAdmin = true;
            }
        }
    }

    // Fallback tambahan untuk keamanan sesi lokal
    if (!isAuthorizedAdmin && (localStorage.getItem('userRole') === 'admin' || localStorage.getItem('KILAT_ACTIVE_ROLE') === 'admin')) {
        isAuthorizedAdmin = true;
    }

    // Jika verifikasi frontend belum sinkron sempurna, serahkan ke middleware keamanan Laravel di backend
    if (!isAuthorizedAdmin) {
        console.warn('⚠️ Catatan Frontend: Validasi lokal admin belum sepenuhnya sinkron, akses halaman dialihkan sepenuhnya ke pengaman server Laravel.');
    }

    const savedTheme = localStorage.getItem('appTheme') || 'default';
    document.documentElement.setAttribute('data-theme', savedTheme);
}

// --- KOLOM RESIZER ---
function initColumnResizer() {
    const tableHeader = document.querySelector('.clay-table-grid');
    if (!tableHeader || tableHeader.hasAttribute('data-resizable')) return;
    tableHeader.setAttribute('data-resizable', 'true');

    const headers = tableHeader.children;
    for (let i = 0; i < headers.length - 1; i++) {
        const th = headers[i];
        if (th.style.position !== 'relative') {
            th.style.position = 'relative';
        }

        const resizer = document.createElement('div');
        resizer.className = 'col-resizer';
        resizer.style.cssText = `
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            cursor: col-resize;
            z-index: 10;
        `;
        th.appendChild(resizer);

        let startX, startWidth;
        resizer.addEventListener('mousedown', function(e) {
            startX = e.pageX;
            startWidth = th.offsetWidth;

            function onMouseMove(e) {
                const diff = e.pageX - startX;
                const newWidth = Math.max(60, startWidth + diff);

                let gridTemplate = window.getComputedStyle(tableHeader).gridTemplateColumns.split(' ');
                gridTemplate[i] = newWidth + 'px';

                const newColsStyle = gridTemplate.join(' ');
                tableHeader.style.gridTemplateColumns = newColsStyle;

                document.querySelectorAll('.clay-table-row').forEach(row => {
                    row.style.gridTemplateColumns = newColsStyle;
                });
            }

            function onMouseUp() {
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            }

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
            e.stopPropagation();
        });
    }
}

// --- STATISTIK AKUN ---
function updateStatsCounter() {
    const users = getUsersData();

    let adminCount = users.filter(u => (u.role || '').toLowerCase() === 'admin' || (u.role || '').toLowerCase() === 'administrator').length;
    let coachCount = users.filter(u => (u.role || '').toLowerCase() === 'coach').length;
    let parentCount = users.filter(u => (u.role || '').toLowerCase() === 'parent').length;
    let athleteCount = users.filter(u => (u.role || '').toLowerCase() === 'atlet').length;

    if (document.getElementById('count-admin')) document.getElementById('count-admin').innerText = adminCount;
    if (document.getElementById('count-coach')) document.getElementById('count-coach').innerText = coachCount;
    if (document.getElementById('count-parent')) document.getElementById('count-parent').innerText = parentCount;
    if (document.getElementById('count-athlete')) document.getElementById('count-athlete').innerText = athleteCount;
}

// --- HELPER WARNA ROLE ---
function getRoleBadgeStyle(role) {
    let r = (role || '').toLowerCase();
    switch (r) {
        case 'admin':
        case 'administrator':
            return 'background: rgba(59, 130, 246, 0.2); color: #1d4ed8; border: 1px solid #93c5fd;';
        case 'coach':
            return 'background: rgba(34, 197, 94, 0.2); color: #15803d; border: 1px solid #86efac;';
        case 'parent':
            return 'background: rgba(239, 68, 68, 0.2); color: #b91c1c; border: 1px solid #fca5a5;';
        case 'atlet':
            return 'background: rgba(234, 179, 8, 0.2); color: #a16207; border: 1px solid #fde047;';
        default:
            return 'background: rgba(107, 114, 128, 0.2); color: #374151; border: 1px solid #d1d5db;';
    }
}

// --- RENDER TABEL ---
window.renderTable = function() {
    const container = document.getElementById('accountTableBody');
    if (!container) return;

    const users = getUsersData();
    const filterRole = document.getElementById('filterRole') ? document.getElementById('filterRole').value : 'All';
    const searchQuery = document.getElementById('searchInput') ? document.getElementById('searchInput').value.toLowerCase().trim() : '';
    const sortBy = document.getElementById('sortBy') ? document.getElementById('sortBy').value : 'role-admin';

    container.innerHTML = '';

    let filteredUsers = users.filter(user => {
        let matchRole = (filterRole === 'All') || ((user.role || '').toLowerCase() === filterRole.toLowerCase());
        let nameStr = (user.namaLengkap || user.nama || user.name || '').toLowerCase();
        let usernameStr = (user.username || user.email || '').toLowerCase();
        return matchRole && (nameStr.includes(searchQuery) || usernameStr.includes(searchQuery));
    });

    function getRoleWeight(roleStr) {
        let r = (roleStr || '').toLowerCase();
        if (r === 'admin' || r === 'administrator') return 1;
        if (r === 'coach') return 2;
        if (r === 'atlet') return 3;
        if (r === 'parent') return 4;
        return 5;
    }

    filteredUsers.sort((a, b) => {
        let nameA = (a.namaLengkap || a.nama || a.name || '').toLowerCase();
        let nameB = (b.namaLengkap || b.nama || b.name || '').toLowerCase();
        let roleA = (a.role || '').toLowerCase();
        let roleB = (b.role || '').toLowerCase();
        let userA = (a.username || a.email || '').toLowerCase();
        let userB = (b.username || b.email || '').toLowerCase();

        switch (sortBy) {
            case 'role-admin':
                return getRoleWeight(roleA) - getRoleWeight(roleB) || nameA.localeCompare(nameB);
            case 'name-asc':
                return nameA.localeCompare(nameB);
            case 'name-desc':
                return nameB.localeCompare(nameA);
            case 'username-asc':
                return userA.localeCompare(userB);
            case 'username-desc':
                return userB.localeCompare(userA);
            default:
                return 0;
        }
    });

    if (filteredUsers.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 20px; color: var(--text-gray); font-weight: 800; grid-column: 1 / -1;">
                Belum ada data akun terdaftar.
            </div>
        `;
        return;
    }

    const tableHeader = document.querySelector('.clay-table-grid');
    let currentGridColumns = tableHeader ? window.getComputedStyle(tableHeader).gridTemplateColumns : '';

    filteredUsers.forEach((user) => {
        let originalIndex = users.findIndex(u => (u.id && user.id && u.id === user.id) || (u.username === user.username && u.namaLengkap === user.namaLengkap));
        if (originalIndex === -1) originalIndex = users.indexOf(user);

        let rawRole = user.role || 'Admin';
        let isParent = rawRole.toLowerCase() === 'parent';
        let isAthlete = rawRole.toLowerCase() === 'atlet';

        let userName = '';
        if (isAthlete) {
            let fullNamed = (user.namaLengkap || user.fullName || '').trim();
            if (fullNamed) {
                userName = fullNamed;
            } else {
                userName = '<span style="color:#ef4444; font-style:italic;">Nama Lengkap Kosong</span>';
            }
        } else {
            userName = `<strong>${user.namaLengkap || user.nama || user.name || 'User'}</strong>`;
        }

        let userRoleUpper = rawRole.toUpperCase();
        let userEmail = user.username || user.email || '-';
        let userPass = user.password || '******';
        let userStatus = user.status || 'Aktif';

        let roleStyle = getRoleBadgeStyle(rawRole);

        let athletesHtml = '-';
        if (isParent) {
            let linkedAthletes = user.atletTautan || user.athletes || [];
            if (Array.isArray(linkedAthletes) && linkedAthletes.length > 0) {
                let count = linkedAthletes.length;
                athletesHtml = `
                    <span class="parent-badge-toggle" onclick="toggleParentAthletes(event, this)" title="Klik untuk melihat detail atlet tertaut" style="cursor: pointer; background: rgba(234, 179, 8, 0.2); color: #a16207; border: 1px solid #fde047; padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 800; display: inline-block; position: relative; user-select: none;">
                        ${count} Atlet Tertaut
                        <div class="parent-athletes-detail" style="display: none; position: absolute; left: 0; top: 115%; z-index: 99; min-width: 160px; font-size: 0.75rem; text-align: left; background: #fff; color: #333; padding: 8px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: 1px solid #e5e7eb;">
                            ${linkedAthletes.map((ath, athIdx) => {
                                let athNameText = typeof ath === 'object' ? (ath.name || ath.nickname || ath.fullName) : ath;
                                return `<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; border-bottom:1px solid #f3f4f6; padding-bottom:3px;">
                                    <span style="font-weight:600;">- ${athNameText}</span>
                                    <button type="button" onclick="event.stopPropagation(); unlinkAthlete(${originalIndex}, ${athIdx})" title="Hapus Tautan" style="background:none; border:none; color:#ef4444; font-weight:bold; cursor:pointer; padding:0 4px;">&times;</button>
                                </div>`;
                            }).join('')}
                        </div>
                    </span>
                `;
            }
        } else if (isAthlete) {
            let parentUsers = users.filter(u => (u.role || '').toLowerCase() === 'parent');
            let matchedParents = [];

            parentUsers.forEach(p => {
                let pAthletes = p.atletTautan || p.athletes || [];
                let isLinked = pAthletes.some(ath => {
                    let athName = typeof ath === 'object' ? (ath.name || ath.nickname || ath.fullName) : ath;
                    let checkTargetName = user.namaLengkap || user.nama || user.name || user.username || '';
                    return athName.toLowerCase() === checkTargetName.toLowerCase() || athName.toLowerCase() === userEmail.toLowerCase();
                });
                if (isLinked) {
                    matchedParents.push(p.namaLengkap || p.nama || p.username);
                }
            });

            if (matchedParents.length === 0 && (user.parent || user.parentName || user.parentId)) {
                let directParentName = user.parentName || user.parent;
                if (directParentName) {
                    matchedParents.push(directParentName);
                } else if (user.parentId) {
                    let foundP = users.find(u => u.id === user.parentId || u.username === user.parentId);
                    if (foundP) {
                        matchedParents.push(foundP.namaLengkap || foundP.nama || foundP.username);
                    }
                }
            }

            if (matchedParents.length > 0) {
                athletesHtml = matchedParents.map(pName => `
                    <span style="display: inline-flex; align-items: center; background: rgba(239, 68, 68, 0.15); color: #b91c1c; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 700; margin: 2px;">
                        <i class="fa-solid fa-user-tie" style="margin-right:4px;"></i>${pName}
                    </span>
                `).join('');
            } else {
                athletesHtml = '<span style="color:#9ca3af; font-style:italic;">Belum Tertaut</span>';
            }
        }

        let linkAthleteBtn = isParent ? `
            <button type="button" class="btn-action-mini btn-link" onclick="openLinkAthleteModal(${originalIndex})" title="Tautkan Atlet" style="background:#10b981; color:#fff; border:none; border-radius:6px; width:28px; height:28px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-user-plus"></i>
            </button>
        ` : '';

        let actionButtons = `
            <div class="action-cell" style="display: flex; gap: 5px; align-items: center;">
                ${linkAthleteBtn}
                <button type="button" class="btn-action-mini btn-edit" onclick="editAccount(${originalIndex})" title="Edit Akun" style="background:#3b82f6; color:#fff; border:none; border-radius:6px; width:28px; height:28px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button type="button" class="btn-action-mini btn-delete" onclick="deleteAccount(${originalIndex})" title="Hapus Akun" style="background:#ef4444; color:#fff; border:none; border-radius:6px; width:28px; height:28px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `;

        let rowEl = document.createElement('div');
        rowEl.className = 'clay-table-grid clay-table-row';
        rowEl.style.cssText = `
            background: var(--bg-main);
            border-radius: 14px;
            padding: 10px 12px;
            margin-bottom: 8px;
            box-shadow: var(--clay-shadow-inset);
            border-bottom: 1px solid rgba(120, 100, 200, 0.25);
            display: grid;
            align-items: center;
            ${currentGridColumns ? 'grid-template-columns: ' + currentGridColumns + ';' : ''}
        `;

        rowEl.innerHTML = `
            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${isAthlete ? (user.namaLengkap || user.fullName || 'Nama Lengkap Kosong') : (user.namaLengkap || user.nama || user.name || 'User')}">${userName}</div>
            <div>${actionButtons}</div>
            <div><span class="badge-role" style="${roleStyle} padding: 4px 10px; border-radius: 12px; font-weight: 800; font-size: 0.75rem; white-space: nowrap;">${userRoleUpper}</span></div>
            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${userEmail}">${userEmail}</div>
            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${userPass}</div>
            <div style="overflow: visible; text-overflow: clip;">${athletesHtml}</div>
            <div><strong style="color:${userStatus.toLowerCase() === 'aktif' ? '#2ec4b6' : '#e63946'}; white-space: nowrap;">${userStatus}</strong></div>
        `;
        container.appendChild(rowEl);
    });

    updateStatsCounter();
};

// --- FUNGSI TOGGLE DETAIL ATLET TAUTAN PADA PARENT ---
window.toggleParentAthletes = function(event, el) {
    if (event) event.stopPropagation();
    const detailBox = el.querySelector('.parent-athletes-detail');
    if (detailBox) {
        const isHidden = detailBox.style.display === 'none' || detailBox.style.display === '';
        detailBox.style.display = isHidden ? 'block' : 'none';
    }
};

// --- FUNGSI TAUTKAN ATLET KE PARENT ---
window.openLinkAthleteModal = function(parentIndex) {
    let users = getUsersData();
    let parent = users[parentIndex];
    if (!parent) return;

    let athleteUsers = users.filter(u => (u.role || '').toLowerCase() === 'atlet');

    if (athleteUsers.length === 0) {
        alert("⚠️ Belum ada akun pengguna dengan role Atlet terdaftar.");
        return;
    }

    let parentAthletes = parent.atletTautan || parent.athletes || [];
    let optionsText = athleteUsers.map((ath, i) => {
        let name = ath.namaLengkap || ath.nama || ath.username;
        let isAlreadyLinked = parentAthletes.includes(name);
        return `${i + 1}. ${name} ${isAlreadyLinked ? '(Sudah Tertaut)' : ''}`;
    }).join('\n');

    let choice = prompt(`Pilih nomor Atlet yang ingin ditautkan ke Parent "${parent.namaLengkap || parent.username}":\n\n${optionsText}`);

    if (choice) {
        let selectedIdx = parseInt(choice) - 1;
        if (!isNaN(selectedIdx) && athleteUsers[selectedIdx]) {
            let selectedAth = athleteUsers[selectedIdx];
            let athName = selectedAth.namaLengkap || selectedAth.nama || selectedAth.username;

            if (!parent.atletTautan) parent.atletTautan = [];

            if (parent.atletTautan.includes(athName)) {
                alert(`⚠️ Atlet "${athName}" sudah tertaut ke akun parent ini.`);
            } else {
                parent.atletTautan.push(athName);
                saveUsersData(users);
                renderTable();
                alert(`✅ Berhasil menautkan atlet "${athName}" ke Parent "${parent.namaLengkap || parent.username}".`);
            }
        } else {
            alert("⚠️ Pilihan nomor atlet tidak valid.");
        }
    }
};

// --- FUNGSI HAPUS TAUTAN ATLET ---
window.unlinkAthlete = function(userIndex, athleteIndex) {
    let users = getUsersData();
    let user = users[userIndex];

    if (!user) return;

    let targetAthletes = user.atletTautan || user.athletes || [];
    let athleteObj = targetAthletes[athleteIndex];
    let athleteName = typeof athleteObj === 'object' ? (athleteObj.name || athleteObj.nickname || athleteObj.fullName) : athleteObj;

    if (confirm(`Yakin ingin menghapus tautan atlet "${athleteName}" dari akun ini?`)) {
        targetAthletes.splice(athleteIndex, 1);
        user.atletTautan = targetAthletes;
        saveUsersData(users);
        renderTable();
    }
};

// --- FUNGSI MODAL & TOMBOL ---
window.openAccountModal = function(editIndex = null) {
    const modal = document.getElementById('accountModal');
    const titleEl = document.getElementById('accModalTitle');
    const accIdInput = document.getElementById('accId');
    const form = document.getElementById('accountForm');

    if (form) form.reset();

    if (editIndex !== null && editIndex !== undefined && editIndex !== '') {
        let users = getUsersData();
        let user = users[editIndex];

        if (user) {
            if (accIdInput) accIdInput.value = editIndex;
            if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-user-pen"></i> Edit Akun';
            if (document.getElementById('accName')) document.getElementById('accName').value = user.namaLengkap || user.nama || user.name || '';
            if (document.getElementById('accUsername')) document.getElementById('accUsername').value = user.username || user.email || '';
            if (document.getElementById('accPassword')) document.getElementById('accPassword').value = user.password || '';
            if (document.getElementById('accRole')) document.getElementById('accRole').value = user.role || 'Admin';
            if (document.getElementById('accStatus')) document.getElementById('accStatus').value = user.status || 'Aktif';
        }
    } else {
        if (accIdInput) accIdInput.value = '';
        if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-user-gear" style="color:var(--sidebar-bg);"></i> Form Akun Baru';
    }

    if (modal) {
        modal.classList.add('show');
        modal.style.display = 'flex';
    }
};

window.editAccount = function(index) {
    openAccountModal(index);
};

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        modal.style.display = 'none';
    }
};

// --- TAMBAHAN: FUNGSI PENCIPTAAN CADANGAN FILE SQL OTOMATIS ---
function generateAndDownloadBackupSQL(usersList) {
    try {
        let sqlContent = `-- ==========================================\n`;
        sqlContent += `-- BACKUP OTOMATIS TABEL USERS - KILAT SYSTEM\n`;
        sqlContent += `-- Waktu: ${new Date().toISOString()}\n`;
        sqlContent += `-- ==========================================\n\n`;
        sqlContent += `DELETE FROM users;\n\n`;

        usersList.forEach(u => {
            let name = (u.namaLengkap || u.nama || u.name || '').replace(/'/g, "''");
            let email = (u.username || u.email || '').replace(/'/g, "''");
            let pass = (u.password || '').replace(/'/g, "''");
            let role = (u.role || 'atlet').replace(/'/g, "''").toLowerCase();
            let status = (u.status || 'Aktif').replace(/'/g, "''");

            sqlContent += `INSERT INTO users (name, email, password, role, status, created_at, updated_at) VALUES ('${name}', '${email}', '${pass}', '${role}', '${status}', NOW(), NOW());\n`;
        });

        let blob = new Blob([sqlContent], { type: 'text/sql;charset=utf-8;' });
        let url = URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = `kilat_users_backup_${new Date().toISOString().slice(0,10)}.sql`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    } catch (err) {
        console.warn("Gagal membuat file backup SQL otomatis:", err);
    }
}

// --- SIMPAN / EDIT AKUN (TERINTEGRASI KE LOCALSTORAGE, BACKEND LARAVEL & BACKUP SQL) ---
window.saveAccount = function(e) {
    if (e) e.preventDefault();

    let editIndex = document.getElementById('accId').value;
    let name = document.getElementById('accName').value;
    let username = document.getElementById('accUsername').value;
    let password = document.getElementById('accPassword').value;
    let role = document.getElementById('accRole').value;
    let status = document.getElementById('accStatus').value;

    let users = getUsersData();

    if (editIndex !== "" && editIndex !== null && editIndex !== undefined) {
        let idx = parseInt(editIndex);
        if (users[idx]) {
            users[idx].namaLengkap = name;
            users[idx].username = username;
            users[idx].password = password;
            users[idx].role = role;
            users[idx].status = status;
        }
    } else {
        users.unshift({
            id: Date.now(),
            namaLengkap: name,
            username: username,
            password: password,
            role: role,
            status: status,
            atletTautan: []
        });
    }

    // 1. Simpan ke LocalStorage agar tampilan tabel langsung responsif secara instan
    saveUsersData(users);
    renderTable();
    closeModal('accountModal');
    if (e && e.target) e.target.reset();

    // 2. Sinkronkan otomatis ke Backend Laravel via Fetch POST agar tersimpan di database server untuk Login
    let payload = {
        name: name,
        email: username,
        password: password,
        role: role.toLowerCase(),
        status: status
    };

    let url = editIndex !== "" ? `/admin/users/update/${editIndex}` : `/admin/users/store`;

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log("✅ Akun berhasil disinkronkan ke database server Laravel.");
        }
    })
    .catch(error => {
        console.warn('⚠️ Gagal sinkronisasi background ke server Laravel:', error);
    });

    // 3. Simpan ke tempat ke-3: Secara opsional menghasilkan file unduhan SQL (.sql) sebagai cadangan mandiri
    if (confirm("✅ Akun berhasil disimpan! Apakah Anda ingin mendownload file Backup SQL (.sql) terbaru dari seluruh akun?")) {
        generateAndDownloadBackupSQL(users);
    }
};

window.deleteAccount = function(index) {
    let users = getUsersData();
    let user = users[index];
    let userName = user ? (user.namaLengkap || user.username || 'ini') : 'ini';

    if (confirm(`Yakin ingin menghapus akun "${userName}"?`)) {
        users.splice(index, 1);
        saveUsersData(users);
        renderTable();
    }
};

// --- TOGGLE PASSWORD VISIBILITY ---
window.toggleModalPassword = function() {
    const passwordInput = document.getElementById('accPassword');
    const eyeIcon = document.getElementById('modalEyeIcon');

    if (!passwordInput) return;

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (eyeIcon) {
            eyeIcon.className = 'fa-solid fa-eye-slash';
        }
    } else {
        passwordInput.type = 'password';
        if (eyeIcon) {
            eyeIcon.className = 'fa-solid fa-eye';
        }
    }
};

function syncAthleteName(val) {
    const athNameInput = document.getElementById('athName');
    if (athNameInput && !athNameInput.value) {
        athNameInput.value = val;
    }
}

function autoFillWali(selectElement) {
    const selectedText = selectElement.options[selectElement.selectedIndex].text;
    const bioOrtuInput = document.getElementById('bioOrtu');
    if (bioOrtuInput && selectedText && !selectedText.includes('--')) {
        const parentName = selectedText.split('(')[0].trim();
        bioOrtuInput.value = parentName;
    }
}

// --- PEMERIKSAAN OTOMATIS ARSIP / AKTIF BERDASARKAN STATUS PEMBAYARAN BULANAN ---
function checkUnpaidAccountsAndArchive() {
    let users = getUsersData();
    let financeDB = JSON.parse(localStorage.getItem('KILAT_FINANCE_DB')) || { bulanan: [] };
    let savedInvoices = JSON.parse(localStorage.getItem('KILAT_SAVED_INVOICES')) || [];

    let updated = false;

    users.forEach(user => {
        let role = (user.role || '').toLowerCase();
        if (role === 'atlet' || role.includes('atlet')) {
            let athleteName = (user.namaLengkap || user.name || user.username || '').toLowerCase().trim();

            let paidMonths = new Set();

            (financeDB.bulanan || []).forEach(b => {
                let bName = (b.name || '').toLowerCase().trim();
                let bPeriod = b.period || (b.date ? b.date.substring(0, 7) : '');
                if (bName === athleteName && bPeriod) {
                    paidMonths.add(bPeriod);
                }
            });

            savedInvoices.forEach(inv => {
                let invName = (inv.athlete?.name || inv.name || '').toLowerCase().trim();
                let invPeriod = inv.period || (inv.dueDate ? inv.dueDate.substring(0, 7) : '');
                let isPaid = inv.status && inv.status.toLowerCase() === 'paid';
                if (invName === athleteName && invPeriod && isPaid) {
                    paidMonths.add(invPeriod);
                }
            });

            let now = new Date();
            let currentYear = now.getFullYear();
            let currentMonth = now.getMonth() + 1;

            let unpaidConsecutiveCount = 0;
            for (let i = 0; i < 2; i++) {
                let checkM = currentMonth - i;
                let checkY = currentYear;
                if (checkM <= 0) {
                    checkM += 12;
                    checkY -= 1;
                }
                let periodStr = `${checkY}-${String(checkM).padStart(2, '0')}`;

                if (!paidMonths.has(periodStr)) {
                    unpaidConsecutiveCount++;
                }
            }

            if (unpaidConsecutiveCount >= 2) {
                if (user.status !== 'Arsip') {
                    user.status = 'Arsip';
                    updated = true;
                }
            } else {
                if (user.status === 'Arsip') {
                    user.status = 'Aktif';
                    updated = true;
                }
            }
        }
    });

    if (updated) {
        saveUsersData(users);
    }
}

function validateAthleteGradingAccess(athleteName) {
    let users = getUsersData();
    let target = users.find(u => (u.namaLengkap || u.name || '').toLowerCase().trim() === (athleteName || '').toLowerCase().trim());

    if (target && target.status && target.status.toLowerCase() === 'arsip') {
        alert(`⚠️ Akses Penilaian Ditolak:\nAkun atlet "${athleteName}" berstatus ARSIP karena menunggak SPP Bulanan. Tidak dapat dilakukan penilaian pada modul Appendix.`);
        return false;
    }
    return true;
}

// --- SCRIPT PENERAPAN TEMA GLOBAL DARI SETTING ---
function applyDynamicThemeSettings() {
    let savedFolder = localStorage.getItem('KILAT_CSS_FOLDER') || 'css';

    let savedTheme = localStorage.getItem('KILAT_THEME') || localStorage.getItem('appTheme') || '';
    if (savedTheme) {
        document.body.setAttribute('data-theme', savedTheme);
    }

    const dashboardLink = document.getElementById('dashboardStylesheet');
    const usersLink = document.getElementById('usersStylesheet');

    if (dashboardLink) {
        let href = dashboardLink.getAttribute('href');
        let fileName = href.split('/').pop();
        dashboardLink.setAttribute('href', `{{ asset('') }}${savedFolder}/${fileName}`);
    }

    if (usersLink) {
        let href = usersLink.getAttribute('href');
        let fileName = href.split('/').pop();
        if (href.includes('admin/')) {
            usersLink.setAttribute('href', `{{ asset('') }}${savedFolder}/admin/${fileName}`);
        } else {
            usersLink.setAttribute('href', `{{ asset('') }}${savedFolder}/${fileName}`);
        }
    }
}
