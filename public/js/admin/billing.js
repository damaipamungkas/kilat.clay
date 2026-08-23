let billingAthletesMaster = [];
        let billingInvoices = [];
        let billingSelectedIds = new Set();
        let billingLastRule = { period: "", tariff: 150000 };

        function formatRp(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }

        function formatPeriodStr(periodVal) {
            if (!periodVal) return '-';
            const parts = periodVal.split('-');
            if (parts.length !== 2) return periodVal;
            const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            const monthIdx = parseInt(parts[1], 10) - 1;
            return `${months[monthIdx] || parts[1]} ${parts[0]}`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            initBillingModule();
        });

        function initBillingModule() {
            const inputPeriodEl = document.getElementById('inputPeriod');
            const inputDueDateEl = document.getElementById('inputDueDate');

            if (inputPeriodEl && !inputPeriodEl.value) {
                const now = new Date();
                const yyyy = now.getFullYear();
                const mm = String(now.getMonth() + 1).padStart(2, '0');
                inputPeriodEl.value = `${yyyy}-${mm}`;
            }
            if (inputDueDateEl && !inputDueDateEl.value) {
                const now = new Date();
                const yyyy = now.getFullYear();
                const mm = String(now.getMonth() + 1).padStart(2, '0');
                inputDueDateEl.value = `${yyyy}-${mm}-10`;
            }

            loadBillingAthletesFromAppendix();
            loadSavedBillingInvoices();

            const generateBtn = document.getElementById('generateBtn');
            if (generateBtn && !generateBtn.hasAttribute('data-bound')) {
                generateBtn.setAttribute('data-bound', 'true');
                generateBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    window.generateInvoices();
                });
            }

            const searchNameInput = document.getElementById('searchName');
            const filterStatusSelect = document.getElementById('filterStatus');
            const filterClassSelect = document.getElementById('filterClass');
            const checkAllBox = document.getElementById('checkAll');

            if (searchNameInput) searchNameInput.addEventListener('input', window.applyBillingFilters);
            if (filterStatusSelect) filterStatusSelect.addEventListener('change', window.applyBillingFilters);
            if (filterClassSelect) filterClassSelect.addEventListener('change', window.applyBillingFilters);
            if (checkAllBox) checkAllBox.addEventListener('change', (e) => window.toggleBillingSelectAll(e.target));
        }

        // Sinkronisasi data dari appendix.blade atau users.blade via localStorage
        function loadBillingAthletesFromAppendix() {
            const clayColors = ["var(--clay-pink)", "var(--clay-yellow)", "var(--clay-purple)", "var(--clay-blue)", "var(--clay-green)"];
            let unifiedAthletes = [];

            // 1. Mengambil data dari daftar Appendix (KILAT_ATHLETES_LIST)
            const storedNames = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
            storedNames.forEach((nick, index) => {
                const bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nick)) || {};
                const name = bio.fullName && bio.fullName.trim() !== '' ? bio.fullName : nick;
                const group = bio.kelas || bio.group || "Pemula";
                const mode = bio.status || bio.statusKeaktifan || "Aktif";
                const lastDiscount = parseInt(bio.diskon || bio.lastDiscount || 0);

                unifiedAthletes.push({
                    id: index + 1,
                    name: name,
                    nickname: nick,
                    group: group,
                    mode: mode.toLowerCase().includes('arsip') ? 'Arsip' : 'Aktif',
                    lastDiscount: lastDiscount,
                    color: clayColors[index % clayColors.length]
                });
            });

            // 2. Mengambil tambahan data dari Pusat Akun / Users (KILAT_USERS_LIST) jika ber-role atlet/parent
            const usersList = JSON.parse(localStorage.getItem('KILAT_USERS_LIST')) || [];
            usersList.forEach((usr) => {
                if (usr.role && (usr.role.toLowerCase().includes('atlet') || usr.role.toLowerCase().includes('parent') || usr.role.toLowerCase().includes('orang tua'))) {
                    const nick = usr.username || usr.name;
                    if (!unifiedAthletes.some(a => a.nickname.toLowerCase() === nick.toLowerCase())) {
                        unifiedAthletes.push({
                            id: unifiedAthletes.length + 1,
                            name: usr.name || nick,
                            nickname: nick,
                            group: usr.kelas || "Pemula",
                            mode: 'Aktif',
                            lastDiscount: 0,
                            color: clayColors[unifiedAthletes.length % clayColors.length]
                        });
                    }
                }
            });

            billingAthletesMaster = unifiedAthletes;

            // Fallback default jika data benar-benar kosong
            if (billingAthletesMaster.length === 0) {
                billingAthletesMaster = [
                    { id: 1, name: "Budi Santoso", nickname: "Budi", group: "Pemula", mode: "Aktif", lastDiscount: 0, color: "var(--clay-blue)" },
                    { id: 2, name: "Ayu Lestari", nickname: "Ayu", group: "Madya", mode: "Aktif", lastDiscount: 0, color: "var(--clay-pink)" }
                ];
            }
        }

        function loadSavedBillingInvoices() {
            const savedInvoices = JSON.parse(localStorage.getItem('KILAT_SAVED_INVOICES')) || [];
            const savedRule = JSON.parse(localStorage.getItem('KILAT_LAST_GENERATE_RULE')) || { period: "", tariff: 150000 };

            if (savedInvoices.length > 0) {
                billingInvoices = savedInvoices;
                billingLastRule = savedRule;
                const inputPeriodEl = document.getElementById('inputPeriod');
                const inputTariffEl = document.getElementById('inputTariff');
                if (savedRule.period && inputPeriodEl) {
                    inputPeriodEl.value = savedRule.period;
                }
                if (savedRule.tariff && inputTariffEl) {
                    inputTariffEl.value = savedRule.tariff;
                }
                checkBillingAutoArchive();
                window.applyBillingFilters();
                updateBillingSummary();
                updateBillingStats();
            }
        }

        function saveBillingInvoicesToStorage() {
            localStorage.setItem('KILAT_SAVED_INVOICES', JSON.stringify(billingInvoices));
            localStorage.setItem('KILAT_LAST_GENERATE_RULE', JSON.stringify(billingLastRule));
        }

        function checkBillingAutoArchive() {
            const today = new Date().toISOString().split('T')[0];
            let changedCount = 0;
            billingInvoices.forEach(inv => {
                if (today > inv.dueDate && inv.status === 'Unpaid' && inv.mode === 'Aktif') {
                    inv.mode = 'Arsip';
                    changedCount++;
                }
            });
            if (changedCount > 0) {
                saveBillingInvoicesToStorage();
            }
        }

        window.generateInvoices = function() {
            loadBillingAthletesFromAppendix();
            if (billingAthletesMaster.length === 0) {
                alert("⚠️ Data atlet masih kosong. Daftarkan atlet terlebih dahulu melalui menu Appendix atau Pusat Akun.");
                return;
            }

            const inputPeriodEl = document.getElementById('inputPeriod');
            const inputDueDateEl = document.getElementById('inputDueDate');
            const inputTariffEl = document.getElementById('inputTariff');
            const applyDiscountEl = document.getElementById('apply-discount');

            const periodInput = inputPeriodEl ? inputPeriodEl.value : '';
            const dueDateInput = inputDueDateEl ? inputDueDateEl.value : '';
            const tariffInput = parseInt(inputTariffEl?.value) || 150000;
            const applyDiscount = applyDiscountEl ? applyDiscountEl.checked : false;

            if (!periodInput || !dueDateInput) {
                alert("⚠️ Pilih periode dan tanggal jatuh tempo tagihan terlebih dahulu!");
                return;
            }
            if (tariffInput <= 0) {
                alert("⚠️ Masukkan tarif dasar SPP yang valid!");
                return;
            }

            // Simpan peta diskon dari invoice periode sebelumnya jika toggle "Gunakan histori" (applyDiscount) AKTIF
            let previousDiscountsMap = {};
            if (applyDiscount) {
                billingInvoices.forEach(inv => {
                    const key = inv.athlete.nickname ? inv.athlete.nickname.toLowerCase() : inv.athlete.name.toLowerCase();
                    previousDiscountsMap[key] = inv.discount;
                });
            }

            // Hapus data tagihan lama pada periode yang sama untuk mencegah duplikasi ganda
            billingInvoices = billingInvoices.filter(inv => inv.period !== periodInput);

            // Masukkan data generate baru untuk periode tersebut
            billingAthletesMaster.forEach(a => {
                let diskon = 0;
                const key = a.nickname ? a.nickname.toLowerCase() : a.name.toLowerCase();

                if (applyDiscount) {
                    // Jika ada histori diskon pada periode sebelumnya, gunakan; jika tidak, gunakan lastDiscount dari biodata atlet
                    if (previousDiscountsMap[key] !== undefined) {
                        diskon = previousDiscountsMap[key];
                    } else {
                        diskon = a.lastDiscount || 0;
                    }
                }

                let tagihan = Math.max(0, tariffInput - diskon);

                billingInvoices.push({
                    id: a.id,
                    athlete: a,
                    period: periodInput,
                    tariff: tariffInput,
                    discount: diskon,
                    total: tagihan,
                    dueDate: dueDateInput,
                    status: "Unpaid",
                    mode: a.mode
                });
            });

            billingLastRule = { period: periodInput, tariff: tariffInput };
            saveBillingInvoicesToStorage();
            billingSelectedIds.clear();
            checkBillingAutoArchive();
            window.applyBillingFilters();
            updateBillingSummary();
            updateBillingStats();
            alert(`✅ Berhasil men-generate tagihan SPP standar (Rp ${tariffInput.toLocaleString('id-ID')}) periode ${formatPeriodStr(periodInput)}! Tagihan periode lain tetap aman.`);
        };

        window.applyBillingFilters = function() {
            const searchVal = document.getElementById('searchName')?.value.toLowerCase() || '';
            const filterStatusVal = document.getElementById('filterStatus')?.value || 'Semua';
            const filterClassVal = document.getElementById('filterClass')?.value || 'Semua';

            let filtered = billingInvoices.filter(inv => {
                const matchName = inv.athlete.name.toLowerCase().includes(searchVal) || inv.athlete.nickname.toLowerCase().includes(searchVal);

                let matchStatus = true;
                if (filterStatusVal === 'Paid') matchStatus = (inv.status === 'Paid');
                else if (filterStatusVal === 'Unpaid') matchStatus = (inv.status === 'Unpaid');
                else if (filterStatusVal === 'Aktif') matchStatus = (inv.mode === 'Aktif');
                else if (filterStatusVal === 'Arsip') matchStatus = (inv.mode === 'Arsip');

                let matchClass = true;
                if (filterClassVal !== 'Semua') {
                    matchClass = (inv.athlete.group && inv.athlete.group.toLowerCase() === filterClassVal.toLowerCase());
                }

                return matchName && matchStatus && matchClass;
            });

            renderBillingTable(filtered);
        };

        function renderBillingTable(filteredList) {
            const tbody = document.getElementById('tableBody');
            const checkAll = document.getElementById('checkAll');
            if (!tbody) return;

            // URUTAN KOLOM: Kotak Centang (40px) - Atlet (1.5fr) - Tagihan (1fr) - Diskon (0.8fr) - Status (1fr) - Periode (1fr) - Jatuh Tempo (1fr) - Tarif Awal (1fr)
            const standardGridTemplate = "40px minmax(140px, 1.5fr) minmax(100px, 1fr) minmax(80px, 0.8fr) minmax(100px, 1fr) minmax(90px, 1fr) minmax(100px, 1fr) minmax(90px, 1fr)";

            const tableHeaderContainer = tbody.parentElement?.querySelector('.clay-table-header') || document.querySelector('.clay-table-header');
            if (tableHeaderContainer) {
                tableHeaderContainer.style.display = 'grid';
                tableHeaderContainer.style.gridTemplateColumns = standardGridTemplate;
                tableHeaderContainer.style.alignItems = 'center';
                tableHeaderContainer.style.gap = '12px';
                tableHeaderContainer.style.width = '100%';
                tableHeaderContainer.style.boxSizing = 'border-box';
            }

            tbody.innerHTML = '';
            if (filteredList.length === 0) {
                tbody.innerHTML = '<div style="text-align:center; padding: 25px; font-weight:800; color:var(--text-gray);">Belum ada data tagihan SPP. Silakan generate tagihan terlebih dahulu.</div>';
                if (checkAll) checkAll.checked = false;
                return;
            }

            filteredList.forEach(inv => {
                const checkedStr = billingSelectedIds.has(inv.id) ? 'checked' : '';
                const statClass = inv.status === 'Paid' ? 'status-paid' : 'status-unpaid';
                const statIcon = inv.status === 'Paid' ? 'fa-check' : 'fa-xmark';

                const statusLabel = inv.mode === 'Arsip' ? 'Arsip' : 'Aktif';
                const statusLabelBg = inv.mode === 'Arsip' ? 'rgba(255, 165, 0, 0.2)' : 'rgba(80, 176, 84, 0.2)';
                const statusLabelColor = inv.mode === 'Arsip' ? '#d48806' : '#2d4a36';

                const row = document.createElement('div');
                row.className = 'clay-table-grid clay-row';
                row.style.cssText = `
                    display: grid;
                    grid-template-columns: ${standardGridTemplate};
                    align-items: center;
                    gap: 12px;
                    width: 100%;
                    box-sizing: border-box;
                `;

                row.innerHTML = `
                    <div class="check-cell"><input type="checkbox" class="custom-checkbox row-check" value="${inv.id}" ${checkedStr} onchange="toggleBillingRowSelect(this)"></div>
                    <div class="col-atlet" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: flex; align-items: center; gap: 8px;">
                        <div class="atlet-info" title="${inv.athlete.name}" style="overflow: hidden; width: 100%;">
                            <div style="display: flex; align-items: center; gap: 6px; width: 100%;">
                                <h4 style="margin: 0; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${inv.athlete.name}</h4>
                                <span style="font-size: 0.65rem; padding: 1px 5px; border-radius: 4px; background: ${statusLabelBg}; color: ${statusLabelColor}; font-weight: 700; flex-shrink: 0;">${statusLabel}</span>
                            </div>
                            <p style="margin: 0; font-size: 0.75rem; color: var(--text-gray);">${inv.athlete.group || 'Pemula'}</p>
                        </div>
                    </div>
                    <div style="font-weight:900;">${formatRp(inv.total)}</div>
                    <div><input type="text" inputmode="numeric" class="diskon-input" value="${inv.discount}" onchange="updateBillingDiscount(${inv.id}, this.value)" style="width: 100%; max-width: 65px;"></div>
                    <div><div class="status-badge ${statClass}" onclick="toggleSingleStatus(${inv.id})" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; font-size: 0.75rem; cursor: pointer;" title="Klik untuk ubah status Paid/Unpaid"><i class="fa-solid ${statIcon}"></i> ${inv.status.toUpperCase()}</div></div>
                    <div>${formatPeriodStr(inv.period)}</div>
                    <div>${inv.dueDate}</div>
                    <div>${formatRp(inv.tariff)}</div>
                `;
                tbody.appendChild(row);
            });

            const allFilteredSelected = filteredList.every(inv => billingSelectedIds.has(inv.id)) && filteredList.length > 0;
            if (checkAll) checkAll.checked = allFilteredSelected;
            checkBillingSelections();
        }

        window.toggleSingleStatus = function(id) {
            const inv = billingInvoices.find(i => i.id === id);
            if (!inv) return;

            const activeRole = localStorage.getItem('KILAT_ACTIVE_ROLE') || 'Admin Sistem';
            let billingPaidDB = JSON.parse(localStorage.getItem('KILAT_BILLING_PAID')) || [];
            const today = new Date().toISOString().split('T')[0];

            if (inv.status === 'Paid') {
                inv.status = 'Unpaid';
            } else {
                inv.status = 'Paid';
                if (inv.mode === 'Arsip') inv.mode = 'Aktif';

                // Catat ke riwayat pembayaran jika diubah menjadi Paid
                billingPaidDB.unshift({
                    date: today,
                    period: inv.period,
                    nickname: inv.athlete.nickname,
                    name: inv.athlete.name,
                    amount: inv.total,
                    account: activeRole
                });
            }

            localStorage.setItem('KILAT_BILLING_PAID', JSON.stringify(billingPaidDB));
            saveBillingInvoicesToStorage();
            window.applyBillingFilters();
            updateBillingSummary();
            updateBillingStats();
        };

        window.updateBillingDiscount = function(id, newVal) {
            const val = parseInt(newVal) || 0;
            const inv = billingInvoices.find(i => i.id === id);
            if (inv) {
                inv.discount = val;
                inv.total = Math.max(0, inv.tariff - val);
                saveBillingInvoicesToStorage();
                window.applyBillingFilters();
                updateBillingSummary();
            }
        };

        function updateBillingStats() {
            const countActive = document.getElementById('countActive');
            const countArchive = document.getElementById('countArchive');
            const countTotal = document.getElementById('countTotal');

            if (countActive) countActive.innerText = billingInvoices.filter(i => i.mode === 'Aktif').length;
            if (countArchive) countArchive.innerText = billingInvoices.filter(i => i.mode === 'Arsip').length;
            if (countTotal) countTotal.innerText = billingInvoices.length;
        }

        function updateBillingSummary() {
            if (billingInvoices.length === 0) return;
            const sumPeriod = document.getElementById('sumPeriod');
            if (sumPeriod) sumPeriod.innerHTML = `<i class="fa-solid fa-calendar-days"></i> ${formatPeriodStr(billingLastRule.period)}`;

            let totalTagihan = 0, totalDiskon = 0, totalUnpaid = 0;
            billingInvoices.forEach(inv => {
                if (inv.mode === 'Aktif') {
                    totalTagihan += inv.tariff;
                    totalDiskon += inv.discount;
                    if (inv.status === 'Unpaid') totalUnpaid += inv.total;
                }
            });

            const sumNetBalance = document.getElementById('sumNetBalance');
            const sumTagihan = document.getElementById('sumTagihan');
            const sumDiskon = document.getElementById('sumDiskon');
            const sumUnpaid = document.getElementById('sumUnpaid');

            if (sumNetBalance) sumNetBalance.innerHTML = `<i class="fa-solid fa-wallet" style="color:var(--sidebar-bg);"></i> ${formatRp(totalTagihan - totalDiskon)}`;
            if (sumTagihan) sumTagihan.innerHTML = `<i class="fa-solid fa-money-bill-wave" style="color:var(--status-paid);"></i> ${formatRp(totalTagihan)}`;
            if (sumDiskon) sumDiskon.innerHTML = `<i class="fa-solid fa-tags" style="color:#ffcc66;"></i> ${formatRp(totalDiskon)}`;
            if (sumUnpaid) sumUnpaid.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="color:var(--status-unpaid);"></i> ${formatRp(totalUnpaid)}`;
        }

        window.toggleBillingSelectAll = function(cb) {
            const searchVal = document.getElementById('searchName')?.value.toLowerCase() || '';
            const filterStatusVal = document.getElementById('filterStatus')?.value || 'Semua';
            const filterClassVal = document.getElementById('filterClass')?.value || 'Semua';

            let filtered = billingInvoices.filter(inv => {
                const matchName = inv.athlete.name.toLowerCase().includes(searchVal) || inv.athlete.nickname.toLowerCase().includes(searchVal);
                let matchStatus = true;
                if (filterStatusVal === 'Paid') matchStatus = (inv.status === 'Paid');
                else if (filterStatusVal === 'Unpaid') matchStatus = (inv.status === 'Unpaid');
                else if (filterStatusVal === 'Aktif') matchStatus = (inv.mode === 'Aktif');
                else if (filterStatusVal === 'Arsip') matchStatus = (inv.mode === 'Arsip');

                let matchClass = true;
                if (filterClassVal !== 'Semua') {
                    matchClass = (inv.athlete.group && inv.athlete.group.toLowerCase() === filterClassVal.toLowerCase());
                }
                return matchName && matchStatus && matchClass;
            });

            if (cb.checked) {
                filtered.forEach(i => billingSelectedIds.add(i.id));
            } else {
                filtered.forEach(i => billingSelectedIds.delete(i.id));
            }
            renderBillingTable(filtered);
        };

        window.toggleBillingRowSelect = function(cb) {
            const id = parseInt(cb.value);
            if (cb.checked) billingSelectedIds.add(id);
            else billingSelectedIds.delete(id);
            checkBillingSelections();
        };

        function checkBillingSelections() {
            const bar = document.getElementById('bulkActionBar');
            const selectedCount = document.getElementById('selectedCount');
            if (selectedCount) selectedCount.innerText = billingSelectedIds.size;
            if (bar) {
                if (billingSelectedIds.size > 0) bar.classList.add('show');
                else bar.classList.remove('show');
            }
        }

        window.executeBulk = function(actionType) {
            const activeRole = localStorage.getItem('KILAT_ACTIVE_ROLE') || 'Admin Sistem';
            let billingPaidDB = JSON.parse(localStorage.getItem('KILAT_BILLING_PAID')) || [];
            const today = new Date().toISOString().split('T')[0];

            billingInvoices.forEach(inv => {
                if (billingSelectedIds.has(inv.id)) {
                    if (actionType === 'Paid' || actionType === 'Unpaid') {
                        if (actionType === 'Paid' && inv.status !== 'Paid') {
                            billingPaidDB.unshift({
                                date: today,
                                period: inv.period,
                                nickname: inv.athlete.nickname,
                                name: inv.athlete.name,
                                amount: inv.total,
                                account: activeRole
                            });
                        }
                        inv.status = actionType;
                        if (actionType === 'Paid' && inv.mode === 'Arsip') inv.mode = 'Aktif';
                    }
                    if (actionType === 'Arsip' || actionType === 'Aktif') {
                        inv.mode = actionType;
                    }
                }
            });

            if (actionType === 'Paid') {
                localStorage.setItem('KILAT_BILLING_PAID', JSON.stringify(billingPaidDB));
            }

            saveBillingInvoicesToStorage();
            billingSelectedIds.clear();
            window.applyBillingFilters();
            updateBillingSummary();
            updateBillingStats();
            alert(`✅ Berhasil memproses aksi massal (${actionType}) pada data terpilih!`);
        };

        // --- VALIDASI HAK AKSES ADMIN & PENGAMBILAN NAMA ROLE ---
        const currentUserSession = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') || localStorage.getItem('kilat_user_data') || 'null');
        const registeredUsers = JSON.parse(localStorage.getItem('manageUsersData')) || JSON.parse(localStorage.getItem('KILAT_USERS')) || [];

        let isAuthorizedAdmin = false;
        let activeAdminName = "Admin";

        if (currentUserSession) {
            const userEmail = (currentUserSession.email || '').toLowerCase().trim();
            const userRole = (currentUserSession.role || '').toUpperCase().trim();
            activeAdminName = currentUserSession.namaLengkap || currentUserSession.nama || currentUserSession.username || currentUserSession.name || 'Admin';

            if (userEmail === 'admin.super@kilat.com') {
                isAuthorizedAdmin = true;
            }
            else if (userRole === 'ADMIN') {
                isAuthorizedAdmin = true;
            } else {
                const foundInUsers = registeredUsers.find(u =>
                    (u.email && u.email.toLowerCase().trim() === userEmail) &&
                    (u.role && u.role.toUpperCase().trim() === 'ADMIN')
                );
                if (foundInUsers) {
                    isAuthorizedAdmin = true;
                    activeAdminName = foundInUsers.namaLengkap || foundInUsers.nama || foundInUsers.username || activeAdminName;
                }
            }
        }

        if (!isAuthorizedAdmin) {
            alert('⚠️ Akses Ditolak: Halaman ini khusus untuk Administrator.');
            window.location.href = "{{ route('login') }}";
        }

        const savedTheme = localStorage.getItem('appTheme') || 'default';
        document.documentElement.setAttribute('data-theme', savedTheme);
