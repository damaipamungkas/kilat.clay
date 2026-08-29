// ===================================================
// FILE: public/js/admin/users.js
// ===================================================

// --- MANAJEMEN DATA STORAGE (TERINTEGRASI KE DATABASE SERVER LARAVEL) ---
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

    // Perhitungan Total Atlet diselaraskan dengan data server dan sinkronisasi localStorage Appendix
    let serverAthleteCount = users.filter(u => ['atlet', 'athlete'].includes((u.role || '').toLowerCase())).length;

    let athletesList = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
    let athletesDataStore = JSON.parse(localStorage.getItem('athletes_data')) || [];

    let totalUniqueAthletes = new Set();
    athletesList.forEach(n => totalUniqueAthletes.add(n.toLowerCase()));
    athletesDataStore.forEach(item => {
        let nick = item.name || item.nickname || item.id;
        if (nick) totalUniqueAthletes.add(nick.toLowerCase());
    });

    let athleteCount = Math.max(serverAthleteCount, totalUniqueAthletes.size);

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

// --- RENDER TABEL UTAMA & TABEL KEDUA (BIODATA ATLET DARI APPENDIX) ---
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
    } else {
        const tableHeader = document.querySelector('.table-responsive .clay-table-grid');
        let currentGridColumns = tableHeader ? window.getComputedStyle(tableHeader).gridTemplateColumns : '';

        // Ambil cadangan data dari localStorage Appendix untuk pencocokan otomatis
        let athletesList = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
        let athletesDataStore = JSON.parse(localStorage.getItem('athletes_data')) || [];

        filteredUsers.forEach((user) => {
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

            // --- PENGAMBILAN DATA TAUTAN OTOMATIS BERDASARKAN NAMA WALI/PARENT ---
            if (rawRole.toLowerCase() === 'parent' && (!linkedAthletes || linkedAthletes.length === 0)) {
                let matchedLocal = [];
                let parentNameTarget = (user.namaLengkap || user.name || '').trim().toLowerCase();

                athletesList.forEach(nick => {
                    let bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nick)) || {};
                    let ortuName = (bio.ortu || bio.parentName || '').trim().toLowerCase();
                    if (ortuName === parentNameTarget || ortuName.includes(parentNameTarget) || parentNameTarget.includes(ortuName)) {
                        if (!matchedLocal.includes(nick)) matchedLocal.push(nick);
                    }
                });

                athletesDataStore.forEach(item => {
                    let ortuName = (item.ortu || item.parentName || '').trim().toLowerCase();
                    let nick = item.name || item.nickname;
                    if (nick && (ortuName === parentNameTarget || ortuName.includes(parentNameTarget) || parentNameTarget.includes(ortuName))) {
                        if (!matchedLocal.includes(nick)) matchedLocal.push(nick);
                    }
                });

                if (matchedLocal.length > 0) {
                    linkedAthletes = matchedLocal;
                }
            }
            // ------------------------------------------------------------------

            let athletesHtml = '-';

            if (rawRole.toLowerCase() === 'parent') {
                let totalAthletes = Array.isArray(linkedAthletes) ? linkedAthletes.length : 0;
                if (totalAthletes > 0) {
                    let encodedNames = encodeURIComponent(JSON.stringify(linkedAthletes));
                    let parentDisplayName = user.namaLengkap || user.name || 'Parent';
                    athletesHtml = `
                        <button type="button" class="btn-clay" onclick="showLinkedAthletesModal('${parentDisplayName}', '${encodedNames}')" style="background: rgba(59, 130, 246, 0.15); color: #1d4ed8; border: 1px solid #93c5fd; padding: 4px 10px; border-radius: 12px; font-weight: 800; font-size: 0.8rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-person-skating"></i> ${totalAthletes} Atlet Terhubung
                        </button>
                    `;
                } else {
                    athletesHtml = '<span style="color: var(--text-gray); font-size: 0.8rem; font-weight: 700;">0 Atlet</span>';
                }
            } else if (Array.isArray(linkedAthletes) && linkedAthletes.length > 0) {
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
    }

    renderBiodataAppendixTable(users);
    updateStatsCounter();
};

// --- MODAL POP-UP DETAIL DAFTAR ATLET TAUTAN ---
window.showLinkedAthletesModal = function(parentName, athletesJsonEncoded) {
    let athletes = [];
    try {
        athletes = JSON.parse(decodeURIComponent(athletesJsonEncoded));
    } catch(e) {
        athletes = [];
    }

    let modal = document.getElementById('detailLinkedAthletesModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.id = 'detailLinkedAthletesModal';
        modal.innerHTML = `
            <div class="modal-card">
                <h2><i class="fa-solid fa-person-skating" style="color:var(--sidebar-bg, #4f46e5);"></i> Daftar Atlet Terhubung</h2>
                <p style="font-size: 0.85rem; color: var(--text-gray); margin-bottom: 15px; font-weight: 700;">Parent: <strong id="lblParentName" style="color:var(--text-dark);"></strong></p>
                <div id="listLinkedAthletesContent" style="max-height: 250px; overflow-y: auto; margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px;"></div>
                <div class="modal-btns">
                    <button type="button" class="btn-clay btn-cancel" onclick="closeModal('detailLinkedAthletesModal')">Tutup</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    document.getElementById('lblParentName').innerText = parentName;
    const container = document.getElementById('listLinkedAthletesContent');

    if (!athletes || athletes.length === 0) {
        container.innerHTML = `<div style="padding: 10px; text-align: center; color: var(--text-gray); font-weight: 700;">Tidak ada atlet yang terhubung dengan akun ini.</div>`;
    } else {
        let html = '';
        athletes.forEach((athName, index) => {
            html += `
                <div style="background: rgba(79, 70, 229, 0.05); padding: 10px 14px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 0.9rem; color: var(--text-dark);">
                    <span><i class="fa-solid fa-medal" style="color: #f59e0b; margin-right: 8px;"></i> ${athName}</span>
                    <span style="font-size: 0.75rem; background: var(--sidebar-bg, #4f46e5); color: #fff; padding: 2px 8px; border-radius: 4px;">Atlet #${index + 1}</span>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    modal.classList.add('show');
    modal.style.display = 'flex';
};

// --- RENDER TABEL KEDUA (BIODATA ATLET DARI APPENDIX) ---
function renderBiodataAppendixTable(users) {
    const tbody = document.getElementById('biodataAtletTableBody');
    if (!tbody) return;

    let athletesList = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
    let athletesDataStore = JSON.parse(localStorage.getItem('athletes_data')) || [];

    let combinedAthletesMap = new Map();

    athletesList.forEach(nick => {
        let bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nick)) || {};
        combinedAthletesMap.set(nick.toLowerCase(), {
            nik: bio.nik || '-',
            fullName: bio.fullName || nick,
            nickname: nick,
            gender: bio.gender || '-',
            tglLahir: bio.tglLahir || '-',
            alamat: bio.alamat || '-',
            ortu: bio.ortu || bio.connectedParent || '-',
            wa: bio.wa || '-',
            kelas: bio.kelas || 'PEMULA',
            status: bio.status || 'Aktif'
        });
    });

    athletesDataStore.forEach(item => {
        let nick = item.name || item.nickname || item.id;
        if (nick && !combinedAthletesMap.has(nick.toLowerCase())) {
            combinedAthletesMap.set(nick.toLowerCase(), {
                nik: item.nik || '-',
                fullName: item.fullName || item.email || nick,
                nickname: nick,
                gender: item.gender || '-',
                tglLahir: item.tglLahir || '-',
                alamat: item.alamat || '-',
                ortu: item.ortu || '-',
                wa: item.wa || '-',
                kelas: item.kelas || 'PEMULA',
                status: item.status || 'Aktif'
            });
        }
    });

    if (Array.isArray(users)) {
        users.forEach(user => {
            if ((user.role || '').toLowerCase() === 'atlet' || user.biodata_atlet) {
                let bio = user.biodata_atlet || {};
                let nick = user.namaLengkap || user.nama || user.name || 'Atlet';
                if (!combinedAthletesMap.has(nick.toLowerCase())) {
                    combinedAthletesMap.set(nick.toLowerCase(), {
                        nik: bio.nik || '-',
                        fullName: nick,
                        nickname: nick,
                        gender: bio.gender || '-',
                        tglLahir: bio.tglLahir || '-',
                        alamat: bio.alamat || '-',
                        ortu: bio.ortu || '-',
                        wa: bio.wa || '-',
                        kelas: bio.kelas || bio.kategori || 'PEMULA',
                        status: user.status || 'Aktif'
                    });
                }
            }
        });
    }

    let athletesArray = Array.from(combinedAthletesMap.values());

    const filterKelas = document.getElementById('filterKelasAtlet') ? document.getElementById('filterKelasAtlet').value : 'All';
    if (filterKelas !== 'All') {
        athletesArray = athletesArray.filter(a => (a.kelas || '').toLowerCase() === filterKelas.toLowerCase());
    }

    const sortAtletBy = document.getElementById('sortAtletBy') ? document.getElementById('sortAtletBy').value : 'name-asc';
    athletesArray.sort((a, b) => {
        if (sortAtletBy === 'name-asc') return (a.nickname || '').localeCompare(b.nickname || '');
        if (sortAtletBy === 'name-desc') return (b.nickname || '').localeCompare(a.nickname || '');
        if (sortAtletBy === 'date-new') return new Date(b.tglLahir || 0) - new Date(a.tglLahir || 0);
        if (sortAtletBy === 'date-old') return new Date(a.tglLahir || 0) - new Date(b.tglLahir || 0);
        return 0;
    });

    if (athletesArray.length === 0) {
        tbody.innerHTML = `<div style="padding: 20px; text-align: center; color: var(--text-gray); font-weight: 700; grid-column: 1 / -1;">Belum ada data biodata atlet yang tersimpan dari appendix.</div>`;
        return;
    }

    let html = '';
    athletesArray.forEach(ath => {
        let statusColor = (ath.status.toLowerCase() === 'aktif') ? '#2ec4b6' : '#e63946';
        html += `
            <div class="clay-table-grid" style="grid-template-columns: 1.1fr 1.3fr 1.1fr 0.9fr 1fr 1.4fr 1.1fr 1fr 0.9fr 1fr; padding: 10px 12px; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: 0.8rem; background: var(--bg-main); border-radius: 10px; margin-bottom: 6px; box-shadow: var(--clay-shadow-inset);">
                <div style="font-weight: 700;">${ath.nik}</div>
                <div style="font-weight: 800; color: var(--text-dark);">${ath.fullName}</div>
                <div style="font-weight: 800; color: #3b82f6;"><i class="fa-solid fa-person-skating" style="margin-right:4px;"></i>${ath.nickname}</div>
                <div>${ath.gender}</div>
                <div>${ath.tglLahir}</div>
                <div style="font-size: 0.75rem; color: var(--text-gray);">${ath.alamat}</div>
                <div style="font-weight: 700;">${ath.ortu}</div>
                <div>${ath.wa}</div>
                <div><span style="background: rgba(245,158,11,0.15); color: #d97706; padding: 2px 6px; border-radius: 4px; font-weight: 800; font-size: 0.75rem;">${ath.kelas}</span></div>
                <div><strong style="color:${statusColor};">${ath.status}</strong></div>
            </div>
        `;
    });

    tbody.innerHTML = html;
}

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
            alert('✅ Akun berhasil disimpan permanen ke database server!');
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
