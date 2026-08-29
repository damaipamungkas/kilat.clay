    document.addEventListener("DOMContentLoaded", function() {
        checkAndApplyAdminPermissions();
        loadSavedRulesFromStorage();
    });

    function checkIsAdmin() {
        try {
            const session = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') || localStorage.getItem('kilat_user_data') || 'null');
            const users = JSON.parse(localStorage.getItem('manageUsersData') || localStorage.getItem('KILAT_USERS') || '[]');
            if (!session) return false;

            const email = (session.email || session.username || '').toLowerCase().trim();
            const role = (session.role || '').toUpperCase().trim();

            if (email === 'admin.super@kilat.com' || role === 'ADMIN') return true;

            const found = users.find(u =>
                ((u.email && u.email.toLowerCase().trim() === email) || (u.username && u.username.toLowerCase().trim() === email)) &&
                ((u.role || '').toUpperCase().trim() === 'ADMIN')
            );
            return !!found;
        } catch(e) {
            return false;
        }
    }

    function checkAndApplyAdminPermissions() {
        if (checkIsAdmin()) {
            document.querySelectorAll('.admin-container, .admin-col, .btn-rule-add').forEach(el => {
                el.style.display = (el.tagName === 'TH' || el.tagName === 'TD') ? 'table-cell' : 'inline-block';
            });
        }
    }

    function toggleEditCard(btn) {
        const card = btn.closest('.tech-card');
        const saveBtn = card.querySelector('.btn-rule-save');
        const addBtn = card.querySelector('.btn-rule-add');
        const cells = card.querySelectorAll('.editable-cell');

        cells.forEach(cell => {
            const currentText = cell.innerText.trim();
            cell.innerHTML = `<input type="text" value="${currentText}">`;
        });

        btn.style.display = 'none';
        saveBtn.style.display = 'inline-block';
        if(addBtn) addBtn.style.display = 'inline-block';
    }

    function saveCardData(btn) {
        const card = btn.closest('.tech-card');
        const editBtn = card.querySelector('.btn-rule-edit');
        const addBtn = card.querySelector('.btn-rule-add');
        const cells = card.querySelectorAll('.editable-cell');

        cells.forEach(cell => {
            const input = cell.querySelector('input');
            if (input) {
                cell.innerText = input.value.trim();
            }
        });

        btn.style.display = 'none';
        editBtn.style.display = 'inline-block';
        if(addBtn) addBtn.style.display = 'none';

        persistCardDataToStorage();
        alert('✅ Perubahan peraturan berhasil disimpan!');
    }

    function addTableRow(btn) {
        const card = btn.closest('.tech-card');
        const tbody = card.querySelector('tbody');
        const rowCount = tbody.rows.length + 1;

        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>${rowCount}</td>
            <td class="editable-cell"><input type="text" placeholder="Tulis peraturan baru..."></td>
            <td class="admin-col"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
        `;
        tbody.appendChild(newRow);
    }

    function deleteTableRow(btn) {
        const row = btn.closest('tr');
        const tbody = row.closest('tbody');
        row.remove();

        // Perbarui nomor urut
        Array.from(tbody.rows).forEach((r, idx) => {
            r.cells[0].innerText = idx + 1;
        });
    }

    function persistCardDataToStorage() {
        const cardsData = {};
        document.querySelectorAll('.tech-card').forEach((card, idx) => {
            const cardId = card.getAttribute('data-card-id') || idx;
            const rows = [];
            card.querySelectorAll('tbody tr').forEach(tr => {
                const text = tr.querySelector('.editable-cell').innerText.trim();
                if(text) rows.push(text);
            });
            cardsData[cardId] = rows;
        });
        localStorage.setItem('KILAT_RULES_TABLES_DATA', JSON.stringify(cardsData));
    }

    function loadSavedRulesFromStorage() {
        const saved = JSON.parse(localStorage.getItem('KILAT_RULES_TABLES_DATA') || '{}');
        if (Object.keys(saved).length === 0) return;

        document.querySelectorAll('.tech-card').forEach((card, idx) => {
            const cardId = card.getAttribute('data-card-id') || idx;
            const rowsData = saved[cardId];
            if (rowsData && Array.isArray(rowsData)) {
                const tbody = card.querySelector('tbody');
                tbody.innerHTML = '';
                rowsData.forEach((text, rIdx) => {
                    const tr = document.createElement('tr');
                    const isAdmin = checkIsAdmin();
                    tr.innerHTML = `
                        <td>${rIdx + 1}</td>
                        <td class="editable-cell">${text}</td>
                        <td class="admin-col" style="display: ${isAdmin ? 'table-cell' : 'none'};"><button type="button" class="btn-rule-del" onclick="deleteTableRow(this)"><i class="fa-solid fa-trash"></i></button></td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        });
    }
