// ===================================================
// FILE: public/js/admin/users.js
// ===================================================

// --- MANAJEMEN DATA STORAGE (TERINTEGRASI KE DATABASE SERVER) ---
async function getUsersData() {
    try {
        let response = await fetch('/admin/users/data', {
            headers: { 'Accept': 'application/json' }
        });
        if (response.ok) {
            let serverUsers = await response.json();
            if (serverUsers && Array.isArray(serverUsers)) {
                return serverUsers;
            }
        }
    } catch (error) {
        console.error('Gagal mengambil data dari server:', error);
    }
    return [];
}

function saveUsersData(data) {
    // Disimpan untuk cadangan lokal jika diperlukan
    localStorage.setItem('manageUsersData', JSON.stringify(data));
    localStorage.setItem('KILAT_USERS', JSON.stringify(data));
}

// --- INITIAL LOAD ---
document.addEventListener('DOMContentLoaded', async function() {
    initColumnResizer();
    await renderTable();
    updateStatsCounter();
    checkAdminAuthorization();
});

// --- VALIDASI OTORISASI ADMIN ---
async function checkAdminAuthorization() {
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
            position: absolute; right: 0; top: 0; bottom: 0; width: 6px; cursor: col-resize; z-index: 10;
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
async function updateStatsCounter() {
    const users = await getUsersData();

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
window.renderTable = async function() {
    const container = document.getElementById('accountTableBody');
    if (!container) return;

    const users = await getUsersData();
    const filterRole = document.getElementById('filterRole') ? document.getElementById('filterRole').value : 'All';
    const searchQuery = document.getElementById('searchInput') ? document.getElementById('searchInput').value.toLowerCase().trim() : '';

    container.innerHTML = '';

    let filteredUsers = users.filter(user => {
        let matchRole = (filterRole === 'All') || ((user.role || '').toLowerCase() === filterRole.toLowerCase());
        let nameStr = (user.namaLengkap || user.nama || user.name || '').toLowerCase();
        let usernameStr = (user.username || user.email || '').toLowerCase();
        return matchRole && (nameStr.includes(searchQuery) || usernameStr.includes(searchQuery));
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
        // Cari indeks asli berdasarkan ID yang unik dari database server
        let originalIndex = users.findIndex(u => u.id === user.id);
        if (originalIndex === -1) originalIndex = users.indexOf(user);

        let rawRole = user.role || 'Admin';
        let userRoleUpper = rawRole.toUpperCase();
        let userEmail = user.username || user.email || '-';
        let userPass = user.password || '******';
        let userStatus = user.status || 'Aktif';
        let roleStyle = getRoleBadgeStyle(rawRole);
        let userName = `<strong>${user.namaLengkap || user.nama || user.name || 'User'}</strong>`;

        let linkedAthletes = user.atletTautan || user.athletes || [];
        let athletesHtml = '-';
        if (Array.isArray(linkedAthletes) && linkedAthletes.length > 0) {
            athletesHtml = linkedAthletes.map((ath, athIdx) => `
                <span class="athlete-tag" style="display: inline-flex; align-items: center; gap: 4px; background: rgba(59, 130, 246, 0.15); color: #1d4ed8; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 700; margin: 2px;">
                    ${ath}
                    <button type="button" onclick="unlinkAthlete(${originalIndex}, ${athIdx})" title="Hapus" style="background: none; border: none; color: #ef4444; font-weight: bold; cursor: pointer;">&times;</button>
                </span>
            `).join('');
        }

        let actionButtons = `
            <div class="action-cell" style="display: flex; gap: 5px; align-items: center; justify-content: center;">
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
            background: var(--bg-main); border-radius: 14px; padding: 10px 12px; margin-bottom: 8px;
            box-shadow: var(--clay-shadow-inset); border-bottom: 1px solid rgba(120, 100, 200, 0.25);
            display: grid; align-items: center;
            ${currentGridColumns ? 'grid-template-columns: ' + currentGridColumns + ';' : ''}
        `;

        rowEl.innerHTML = `
            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${userName}</div>
            <div>${actionButtons}</div>
            <div><span class="badge-role" style="${roleStyle} padding: 4px 10px; border-radius: 12px; font-weight: 800; font-size: 0.75rem;">${userRoleUpper}</span></div>
            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${userEmail}">${userEmail}</div>
            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${userPass}</div>
            <div>${athletesHtml}</div>
            <div><strong style="color:${userStatus.toLowerCase() === 'aktif' ? '#2ec4b6' : '#e63946'};">${userStatus}</strong></div>
        `;
        container.appendChild(rowEl);
    });

    updateStatsCounter();
};

window.openAccountModal = async function(editIndex = null) {
    const modal = document.getElementById('accountModal');
    const titleEl = document.getElementById('accModalTitle');
    const accIdInput = document.getElementById('accId');
    const form = document.getElementById('accountForm');

    if (form) form.reset();

    if (editIndex !== null && editIndex !== undefined && editIndex !== '') {
        let users = await getUsersData();
        let user = users[editIndex];

        if (user) {
            if (accIdInput) accIdInput.value = user.id || editIndex;
            if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-user-pen"></i> Edit Akun';
            if (document.getElementById('accName')) document.getElementById('accName').value = user.namaLengkap || user.nama || user.name || '';
            if (document.getElementById('accUsername')) document.getElementById('accUsername').value = user.username || user.email || '';
            if (document.getElementById('accPassword')) document.getElementById('accPassword').value = user.password || '';
            if (document.getElementById('accRole')) document.getElementById('accRole').value = user.role || 'Admin';
            if (document.getElementById('accStatus')) document.getElementById('accStatus').value = user.status || 'Aktif';
        }
    } else {
        if (accIdInput) accIdInput.value = '';
        if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-user-gear"></i> Form Akun Baru';
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

// --- FUNGSI MENGHAPUS TAUTAN ATLET DARI AKUN ---
window.unlinkAthlete = async function(userIndex, athleteIndex) {
    let users = await getUsersData();
    let user = users[userIndex];
    if (!user) return;

    let targetAthletes = user.atletTautan || user.athletes || [];
    let athleteName = targetAthletes[athleteIndex];

    if (confirm(`Yakin ingin menghapus tautan atlet "${athleteName}" dari akun ini?`)) {
        targetAthletes.splice(athleteIndex, 1);
        user.atletTautan = targetAthletes;

        try {
            let response = await fetch(`/admin/users/update/${user.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    name: user.namaLengkap || user.name,
                    email: user.username || user.email,
                    role: user.role,
                    status: user.status,
                    atletTautan: targetAthletes
                })
            });

            let result = await response.json();
            if (result.success) {
                await renderTable();
            } else {
                alert('⚠️ Gagal memperbarui tautan atlet di server.');
            }
        } catch (error) {
            console.error('Error saat unlinked atlet:', error);
            alert('❌ Terjadi kesalahan koneksi ke server.');
        }
    }
};

// --- SIMPAN / EDIT AKUN KE DATABASE SERVER LARAVEL ---
window.saveAccount = async function(e) {
    if (e) {
        if (typeof e.preventDefault === 'function') e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
    }

    let editIndex = document.getElementById('accId').value;
    let name = document.getElementById('accName').value;
    let username = document.getElementById('accUsername').value;
    let password = document.getElementById('accPassword').value;
    let role = document.getElementById('accRole').value;
    let status = document.getElementById('accStatus').value;

    let payload = {
        name: name,
        email: username,
        password: password,
        role: role.toLowerCase(),
        status: status
    };

    let url = (editIndex !== "" && editIndex !== null && editIndex !== undefined) ? `/admin/users/update/${editIndex}` : `/admin/users/store`;

    try {
        let response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(payload)
        });

        let result = await response.json();
        if (result.success) {
            alert('✅ Akun berhasil disimpan permanen ke database server & Tinker!');
            closeModal('accountModal');
            if (e && e.target && typeof e.target.reset === 'function') {
                e.target.reset();
            }
            await renderTable();
        } else {
            alert('⚠️ Gagal menyimpan ke server: ' + (result.message || 'Kesalahan tidak diketahui.'));
        }
    } catch (error) {
        console.error('Error saat menyimpan akun:', error);
        alert('❌ Terjadi kesalahan koneksi ke server.');
    }

    return false;
};

window.deleteAccount = async function(index) {
    let users = await getUsersData();
    let user = users[index];
    if (!user) return;

    let userName = user.namaLengkap || user.username || 'ini';

    if (confirm(`Yakin ingin menghapus akun "${userName}" dari database server?`)) {
        try {
            let response = await fetch(`/admin/users/delete/${user.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            let result = await response.json();
            if (result.success) {
                alert('✅ Akun berhasil dihapus dari database.');
                await renderTable();
            } else {
                alert('⚠️ Gagal menghapus akun.');
            }
        } catch (error) {
            console.error('Error menghapus akun:', error);
            alert('❌ Terjadi kesalahan koneksi ke server.');
        }
    }
};

window.toggleModalPassword = function() {
    const passwordInput = document.getElementById('accPassword');
    const eyeIcon = document.getElementById('modalEyeIcon');

    if (!passwordInput) return;

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (eyeIcon) eyeIcon.className = 'fa-solid fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        if (eyeIcon) eyeIcon.className = 'fa-solid fa-eye';
    }
};
