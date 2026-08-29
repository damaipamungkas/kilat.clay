let attendanceSelectedIds = new Set();

document.addEventListener('DOMContentLoaded', () => {
    initAttendanceModule();
});

function initAttendanceModule() {
    const filterDateEl = document.getElementById('filterDate');
    if (filterDateEl && !filterDateEl.value) {
        filterDateEl.value = new Date().toISOString().split('T')[0];
    }

    const filterDate = document.getElementById('filterDate');
    const filterGroup = document.getElementById('filterGroup');
    const searchInput = document.getElementById('searchAthleteInput');

    if (filterDate && !filterDate.hasAttribute('data-bound')) {
        filterDate.setAttribute('data-bound', 'true');
        filterDate.addEventListener('change', window.renderAttendance);
    }
    if (filterGroup && !filterGroup.hasAttribute('data-bound')) {
        filterGroup.setAttribute('data-bound', 'true');
        filterGroup.addEventListener('change', window.renderAttendance);
    }
    if (searchInput && !searchInput.hasAttribute('data-bound')) {
        searchInput.setAttribute('data-bound', 'true');
        searchInput.addEventListener('input', window.renderAttendance);
    }

    window.renderAttendance();
}

window.renderAttendance = function() {
    const container = document.getElementById('attendanceContainer');
    const filterDate = document.getElementById('filterDate')?.value || new Date().toISOString().split('T')[0];
    const filterGroup = document.getElementById('filterGroup')?.value || 'All';
    const searchQuery = document.getElementById('searchAthleteInput')?.value.toLowerCase() || '';

    if (!container) return;

    // Prioritaskan data yang di-passing dari database Laravel via window.allAthletes
    let registeredUsers = window.allAthletes ||
                          JSON.parse(localStorage.getItem('KILAT_USERS_LIST')) ||
                          JSON.parse(localStorage.getItem('KILAT_USERS')) ||
                          JSON.parse(localStorage.getItem('manageUsersData')) || [];

    let athletesMap = new Map();

    registeredUsers.forEach(usr => {
        let role = (usr.role || '').toLowerCase();
        let nick = usr.username || usr.name || usr.nama_lengkap || usr.namaLengkap;
        let fullName = usr.name || usr.nama_lengkap || usr.namaLengkap || nick;
        let group = usr.kelas || usr.group || "Pemula";
        let statusAktif = usr.status || usr.statusKeaktifan || "Aktif";
        let identifier = (usr.id || nick).toString();

        if (role.includes('atlet') || !usr.role || window.allAthletes) {
            if (nick) {
                athletesMap.set(identifier, {
                    id: identifier,
                    nickname: nick,
                    fullName: fullName,
                    group: group,
                    statusAktif: statusAktif
                });
            }
        }
    });

    let attendanceDB = JSON.parse(localStorage.getItem('KILAT_ABSENSI_' + filterDate)) || {};

    container.innerHTML = '';
    let totalCount = 0, masukCount = 0, tidakMasukCount = 0;

    athletesMap.forEach((athlete) => {
        const id = athlete.id;
        const nick = athlete.nickname;
        const fullName = athlete.fullName;
        const group = athlete.group;
        const statusAktif = athlete.statusAktif;

        if (filterGroup !== 'All' && group.toLowerCase() !== filterGroup.toLowerCase()) return;
        if (searchQuery && !fullName.toLowerCase().includes(searchQuery) && !nick.toLowerCase().includes(searchQuery)) return;

        totalCount++;
        let statusKehadiran = attendanceDB[id] || attendanceDB[nick] || 'tidak_masuk';
        if (statusKehadiran === 'masuk') masukCount++;
        else tidakMasukCount++;

        let isChecked = attendanceSelectedIds.has(id) || attendanceSelectedIds.has(nick) ? 'checked' : '';
        let btnMasukClass = statusKehadiran === 'masuk' ? 'btn-status-active' : 'btn-status-inactive';
        let btnTidakClass = statusKehadiran === 'tidak_masuk' ? 'btn-status-active' : 'btn-status-inactive';

        let row = document.createElement('div');
        row.className = 'clay-table-grid clay-table-row athlete-row';
        row.setAttribute('data-class', group);
        row.setAttribute('data-name', fullName.toLowerCase());

        row.innerHTML = `
            <div class="name-cell">
                <input type="checkbox" class="row-checkbox" value="${id}" ${isChecked} onchange="toggleAttendanceRow('${id}', this.checked)">
            </div>
            <div>
                <span style="font-weight: 900; font-size: 0.9rem; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${fullName}">${fullName}</span>
            </div>
            <div>
                <div class="status-action-btns" style="display:flex; gap:6px;">
                    <button type="button" class="btn-att ${btnMasukClass}" onclick="setAttendanceStatus('${id}', 'masuk')" style="padding: 6px 12px; font-size:0.75rem; cursor:pointer; border-radius:8px; border:none; font-weight:800; background:${statusKehadiran === 'masuk' ? 'var(--c-masuk)' : 'var(--bg-main)'}; color:${statusKehadiran === 'masuk' ? '#fff' : 'var(--text-dark)'}; box-shadow:var(--clay-shadow-btn);"><i class="fa-solid fa-check"></i> Masuk</button>
                    <button type="button" class="btn-att ${btnTidakClass}" onclick="setAttendanceStatus('${id}', 'tidak_masuk')" style="padding: 6px 12px; font-size:0.75rem; cursor:pointer; border-radius:8px; border:none; font-weight:800; background:${statusKehadiran === 'tidak_masuk' ? 'var(--c-tidak-masuk)' : 'var(--bg-main)'}; color:${statusKehadiran === 'tidak_masuk' ? '#fff' : 'var(--text-dark)'}; box-shadow:var(--clay-shadow-btn);"><i class="fa-solid fa-xmark"></i> Tidak</button>
                </div>
            </div>
            <div><span class="role-badge" style="background:var(--clay-yellow); color:#d48806; font-size:0.75rem; padding: 4px 8px; border-radius:6px; font-weight:800; display:inline-block;">${group}</span></div>
            <div><span class="status-badge sb-aktif" style="font-size:0.75rem; padding: 4px 8px; border-radius:6px; font-weight:800; display:inline-block; background:${statusAktif.toLowerCase() === 'aktif' ? 'var(--c-masuk)' : 'var(--c-tidak-masuk)'}; color:#fff;">${statusAktif}</span></div>
        `;
        container.appendChild(row);
    });

    if (totalCount === 0) {
        container.innerHTML = `<div class="text-center py-4 text-gray-500" style="padding: 20px; text-align: center; grid-column: span 5;">Belum ada data atlet terdaftar dari Appendix.</div>`;
    }

    if (document.getElementById('stat-total')) document.getElementById('stat-total').innerText = totalCount;
    if (document.getElementById('stat-masuk')) document.getElementById('stat-masuk').innerText = masukCount;
    if (document.getElementById('stat-tidak-masuk')) document.getElementById('stat-tidak-masuk').innerText = tidakMasukCount;

    updateSelectAllCheckboxState();
};

window.setAttendanceStatus = function(identifier, status) {
    const filterDate = document.getElementById('filterDate')?.value || new Date().toISOString().split('T')[0];
    let attendanceDB = JSON.parse(localStorage.getItem('KILAT_ABSENSI_' + filterDate)) || {};

    attendanceDB[identifier] = status;
    localStorage.setItem('KILAT_ABSENSI_' + filterDate, JSON.stringify(attendanceDB));

    let financeDB = JSON.parse(localStorage.getItem('KILAT_FINANCE_DB')) || { harian: [] };
    let harianList = financeDB.harian || [];

    if (status === 'masuk') {
        let exists = harianList.some(h => h.date === filterDate && h.name.toLowerCase() === identifier.toLowerCase());
        if (!exists) {
            harianList.unshift({
                date: filterDate,
                name: identifier,
                amount: 10000,
                statusBayar: 'Belum Bayar',
                account: 'Admin Sistem',
                keterangan: 'Otomatis dari Absensi Latihan'
            });
        }
    } else {
        financeDB.harian = harianList.filter(h => !(h.date === filterDate && h.name.toLowerCase() === identifier.toLowerCase() && h.statusBayar === 'Belum Bayar'));
    }

    localStorage.setItem('KILAT_FINANCE_DB', JSON.stringify(financeDB));
    renderAttendance();
};

window.toggleAttendanceRow = function(identifier, isChecked) {
    if (isChecked) {
        attendanceSelectedIds.add(identifier);
    } else {
        attendanceSelectedIds.delete(identifier);
    }
    updateSelectAllCheckboxState();
    checkModalVisibility();
};

window.toggleSelectAll = function(isChecked) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = isChecked;
        const identifier = cb.value;
        if (isChecked) {
            attendanceSelectedIds.add(identifier);
        } else {
            attendanceSelectedIds.delete(identifier);
        }
    });
    checkModalVisibility();
};

function updateSelectAllCheckboxState() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    if (!selectAllCheckbox || checkboxes.length === 0) return;

    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    const someChecked = Array.from(checkboxes).some(cb => cb.checked);

    selectAllCheckbox.checked = allChecked;
    selectAllCheckbox.indeterminate = someChecked && !allChecked;
}

function checkModalVisibility() {
    let modal = document.getElementById('statusModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'statusModal';
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-card">
                <h3>Ubah Status Kehadiran?</h3>
                <p style="font-size: 0.8rem; color: var(--text-gray); margin-bottom: 10px; font-weight: 700;">Terapkan perubahan untuk atlet terpilih.</p>
                <div class="modal-btns">
                    <button class="btn-modal-masuk" onclick="applyMassStatus('masuk')">Masuk</button>
                    <button class="btn-modal-tidak" onclick="applyMassStatus('tidak_masuk')">Tidak</button>
                    <button class="btn-modal-batal" onclick="closeStatusModal()">Batal</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    if (attendanceSelectedIds.size > 0) {
        modal.classList.add('show');
    } else {
        modal.classList.remove('show');
    }
}

window.closeStatusModal = function() {
    attendanceSelectedIds.clear();
    const checkboxes = document.querySelectorAll('.row-checkbox, #selectAllCheckbox');
    checkboxes.forEach(cb => cb.checked = false);
    const modal = document.getElementById('statusModal');
    if (modal) modal.classList.remove('show');
    renderAttendance();
};

window.applyMassStatus = function(status) {
    const filterDate = document.getElementById('filterDate')?.value || new Date().toISOString().split('T')[0];
    let attendanceDB = JSON.parse(localStorage.getItem('KILAT_ABSENSI_' + filterDate)) || {};

    attendanceSelectedIds.forEach(identifier => {
        attendanceDB[identifier] = status;
    });

    localStorage.setItem('KILAT_ABSENSI_' + filterDate, JSON.stringify(attendanceDB));

    let financeDB = JSON.parse(localStorage.getItem('KILAT_FINANCE_DB')) || { harian: [] };
    let harianList = financeDB.harian || [];

    attendanceSelectedIds.forEach(identifier => {
        if (status === 'masuk') {
            let exists = harianList.some(h => h.date === filterDate && h.name.toLowerCase() === identifier.toLowerCase());
            if (!exists) {
                harianList.unshift({
                    date: filterDate,
                    name: identifier,
                    amount: 10000,
                    statusBayar: 'Belum Bayar',
                    account: 'Admin Sistem',
                    keterangan: 'Otomatis dari Absensi Latihan'
                });
            }
        } else {
            harianList = harianList.filter(h => !(h.date === filterDate && h.name.toLowerCase() === identifier.toLowerCase() && h.statusBayar === 'Belum Bayar'));
        }
    });
    financeDB.harian = harianList;
    localStorage.setItem('KILAT_FINANCE_DB', JSON.stringify(financeDB));

    attendanceSelectedIds.clear();
    const modal = document.getElementById('statusModal');
    if (modal) modal.classList.remove('show');

    const checkboxes = document.querySelectorAll('.row-checkbox, #selectAllCheckbox');
    checkboxes.forEach(cb => cb.checked = false);

    renderAttendance();
};
