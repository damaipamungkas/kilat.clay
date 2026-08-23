<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sekolah Sepatu Roda (Claymorphism)</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/admin_dashboard.css') }}">
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/admin/admin.css') }}">

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
</head>
<body>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        loadAccountsFromStorage();
        if (typeof renderTable === 'function') renderTable();
        initAdminCardEditor();
        detectUserRoleAndPermissions();
        initDashboardSync();
        initCashFlowTrendModule();
        initNotificationModule(); // Modul Lonceng Notifikasi Terintegrasi
        initFeedbackWidgetModule(); // Modul Widget Feedbacks Pengunjung
        if (typeof initBillingModule === 'function') initBillingModule();
        if (typeof initFinanceModule === 'function') initFinanceModule();
        if (typeof initAttendanceModule === 'function') initAttendanceModule();
        if (typeof initThemeManager === 'function') initThemeManager();
        if (typeof initModalFormListeners === 'function') initModalFormListeners();
    });

    // --- SINKRONISASI & MANAJEMEN AKUN BASE ---
    let accounts = [];

    function loadAccountsFromStorage() {
        let storedUsers = JSON.parse(localStorage.getItem('manageUsersData')) ||
                          JSON.parse(localStorage.getItem('KILAT_USERS')) ||
                          JSON.parse(localStorage.getItem('KILAT_USERS_DB')) || [];

        let currentUserSession = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') || localStorage.getItem('kilat_user_data') || 'null');
        let accountsMap = new Map();

        if (Array.isArray(storedUsers)) {
            storedUsers.forEach(acc => {
                let key = (acc.username || acc.email || '').toLowerCase().trim();
                if (key) accountsMap.set(key, acc);
            });
        }

        if (currentUserSession) {
            let key = (currentUserSession.email || currentUserSession.username || '').toLowerCase().trim();
            if (key && !accountsMap.has(key)) {
                accountsMap.set(key, {
                    id: currentUserSession.id || Date.now(),
                    name: currentUserSession.namaLengkap || currentUserSession.name || currentUserSession.nama || 'Pengguna',
                    username: currentUserSession.email || currentUserSession.username || 'user@kilat.com',
                    password: currentUserSession.password || '1234',
                    role: currentUserSession.role || 'Admin',
                    status: 'Aktif',
                    linkedAthletes: currentUserSession.atlet || []
                });
            }
        }

        accounts = Array.from(accountsMap.values());
        saveAccountsToStorage();
    }

    function saveAccountsToStorage() {
        localStorage.setItem('manageUsersData', JSON.stringify(accounts));
        localStorage.setItem('KILAT_USERS', JSON.stringify(accounts));
    }

    // --- SINKRONISASI DATA DASHBOARD LENGKAP ---
    function initDashboardSync() {
        const totalAtletEl = document.getElementById('valTotalAtlet');
        const aktifAtletEl = document.getElementById('valAktifAtlet');
        const nonAktifAtletEl = document.getElementById('valNonAktifAtlet');

        const valTotalHadir = document.getElementById('valTotalHadir');
        const valHadirAktif = document.getElementById('valHadirAktif');
        const valHadirNon = document.getElementById('valHadirNon');

        const dashboardSaldo = document.getElementById('dashboardSaldo');
        const sppStatusMain = document.getElementById('sppStatusMain');
        const recentTransactionsList = document.getElementById('recentTransactionsList');

        const storedAthletes = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
        let totalAtlet = storedAthletes.length;
        let aktifAtlet = 0, nonAktifAtlet = 0;

        storedAthletes.forEach(nick => {
            const bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nick)) || {};
            const status = (bio.status || bio.statusKeaktifan || 'aktif').toLowerCase();
            if (status.includes('non') || status.includes('tidak')) {
                nonAktifAtlet++;
            } else {
                aktifAtlet++;
            }
        });

        if (totalAtletEl) totalAtletEl.innerHTML = `${totalAtlet} <span class="value-small">Total</span>`;
        if (aktifAtletEl) aktifAtletEl.innerText = aktifAtlet;
        if (nonAktifAtletEl) nonAktifAtletEl.innerText = nonAktifAtlet;

        const todayStr = new Date().toISOString().split('T')[0];
        let todayAbsensi = JSON.parse(localStorage.getItem('KILAT_ABSENSI_' + todayStr)) || {};
        let hadirAktif = 0, hadirNon = 0;

        Object.keys(todayAbsensi).forEach(nick => {
            let statusAbsen = todayAbsensi[nick];
            if (statusAbsen === 'masuk' || statusAbsen === 'hadir' || statusAbsen === true) {
                hadirAktif++;
            } else {
                hadirNon++;
            }
        });

        let totalHadir = hadirAktif;
        if (valTotalHadir) valTotalHadir.innerHTML = `${totalHadir} <span class="value-small">Hadir</span>`;
        if (valHadirAktif) valHadirAktif.innerText = hadirAktif;
        if (valHadirNon) valHadirNon.innerText = hadirNon;

        let financeDB = JSON.parse(localStorage.getItem('KILAT_FINANCE_DB')) || JSON.parse(localStorage.getItem('KILAT_FINANCE_DATA')) || { bulanan: [], harian: [], daftar: [], lain: [], keluar: [] };

        let totalBulanan = (financeDB.bulanan || []).reduce((acc, curr) => acc + parseInt(curr.amount || curr.nominal || 0), 0);
        let totalHarianLunas = (financeDB.harian || []).reduce((acc, curr) => (curr.statusBayar === 'Terbayar' || curr.status === 'lunas') ? acc + parseInt(curr.amount || curr.nominal || 0) : acc, 0);
        let totalDaftar = (financeDB.daftar || []).reduce((acc, curr) => acc + parseInt(curr.amount || curr.nominal || 0), 0);
        let totalLain = (financeDB.lain || []).reduce((acc, curr) => acc + parseInt(curr.amount || curr.nominal || 0), 0);
        let totalKeluar = (financeDB.keluar || []).reduce((acc, curr) => acc + parseInt(curr.amount || curr.nominal || 0), 0);

        let calculatedSaldo = (totalBulanan + totalHarianLunas + totalDaftar + totalLain) - totalKeluar;
        if (dashboardSaldo) dashboardSaldo.innerText = formatRp(calculatedSaldo);

        const currentMonthPrefix = new Date().toISOString().substring(0, 7);
        let currentMonthBulanan = (financeDB.bulanan || []).filter(item => {
            let itemDate = item.date || item.tanggal || '';
            return itemDate.startsWith(currentMonthPrefix);
        });

        let paidAthletesCount = currentMonthBulanan.length;
        let totalAmountPaidThisMonth = currentMonthBulanan.reduce((acc, curr) => acc + parseInt(curr.amount || curr.nominal || 0), 0);

        if (sppStatusMain) {
            sppStatusMain.innerHTML = `${paidAthletesCount} Atlet <span class="value-small" style="display:block; font-size:0.75rem; margin-top:2px;">(${formatRp(totalAmountPaidThisMonth)})</span>`;
        }

        let billingPaid = JSON.parse(localStorage.getItem('KILAT_BILLING_PAID')) || JSON.parse(localStorage.getItem('KILAT_BILLING_DB')) || [];

        let availableYearsSet = new Set();
        let currentYearStr = new Date().getFullYear().toString();
        availableYearsSet.add(currentYearStr);

        ['bulanan', 'harian', 'daftar', 'lain', 'keluar'].forEach(cat => {
            (financeDB[cat] || []).forEach(trx => {
                let d = trx.date || trx.tanggal || '';
                if (d.length >= 4) availableYearsSet.add(d.substring(0, 4));
            });
        });
        billingPaid.forEach(item => {
            let d = item.date || item.tanggal || item.dueDate || '';
            if (d.length >= 4) availableYearsSet.add(d.substring(0, 4));
        });

        let sortedYears = Array.from(availableYearsSet).sort((a, b) => b - a);

        let percentageCardContainer = document.querySelector('.chart-card:has(#sppPieChart)') || document.querySelector('.pie-legend')?.parentElement;

        let yearSelectEl = document.getElementById('sppYearFilter');
        if (!yearSelectEl && percentageCardContainer) {
            let headerTarget = percentageCardContainer.querySelector('h3, h2') || percentageCardContainer.firstElementChild;
            if (headerTarget) {
                let selectWrapper = document.createElement('div');
                selectWrapper.style.cssText = "display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;";
                selectWrapper.innerHTML = `
                    <span style="font-size:0.85rem; font-weight:900; color:var(--text-dark);">Tahun SPP:</span>
                    <select id="sppYearFilter" class="clay-input" style="padding:4px 8px; font-size:0.75rem; border-radius:10px; cursor:pointer;" onchange="updateSppYearlyData(this.value)">
                        ${sortedYears.map(y => `<option value="${y}" ${y === currentYearStr ? 'selected' : ''}>${y}</option>`).join('')}
                    </select>
                `;
                headerTarget.insertAdjacentElement('afterend', selectWrapper);
                yearSelectEl = document.getElementById('sppYearFilter');
            }
        }

        let selectedYear = yearSelectEl ? yearSelectEl.value : currentYearStr;
        calculateAndRenderYearlySppStats(selectedYear, storedAthletes, financeDB, billingPaid);

        if (recentTransactionsList) {
            recentTransactionsList.innerHTML = '';
            let allTransactions = [];

            ['bulanan', 'harian', 'daftar', 'lain', 'keluar'].forEach(cat => {
                (financeDB[cat] || []).forEach(trx => {
                    if (cat !== 'harian' || trx.statusBayar === 'Terbayar' || trx.status === 'lunas') {
                        let isExpense = cat === 'keluar';
                        allTransactions.push({
                            title: trx.name || trx.nama || (isExpense ? 'Pengeluaran Kas' : 'Pemasukan Kas'),
                            desc: `Kategori: ${cat.toUpperCase()} | Eksekutor: ${trx.account || trx.akun || 'Admin'}`,
                            amount: parseInt(trx.amount || trx.nominal || 0),
                            date: trx.date || trx.tanggal || todayStr,
                            isExpense: isExpense
                        });
                    }
                });
            });

            allTransactions.sort((a, b) => new Date(b.date) - new Date(a.date));

            if (allTransactions.length === 0) {
                recentTransactionsList.innerHTML = '<p style="text-align:center; color:var(--text-gray); font-size:0.85rem; padding:15px; font-weight:800;">Belum ada catatan transaksi kas terbaru.</p>';
            } else {
                let displayLimit = Math.min(allTransactions.length, 4);
                for (let i = 0; i < displayLimit; i++) {
                    let trx = allTransactions[i];
                    let sign = trx.isExpense ? '-' : '+';
                    let colorStyle = trx.isExpense ? '#ff6b81' : '#50b054';
                    recentTransactionsList.innerHTML += `
                        <div class="list-item" style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid rgba(0,0,0,0.05);">
                            <div class="item-info">
                                <h4 style="margin:0; font-size:0.9rem;">${trx.title}</h4>
                                <p style="margin:0; font-size:0.75rem; color:var(--text-gray);">${trx.desc}</p>
                            </div>
                            <div style="font-weight:900; color:${colorStyle}; font-size:0.9rem;">${sign} ${formatRp(trx.amount)}</div>
                        </div>
                    `;
                }
            }
        }
    }

    // --- MODUL NOTIFIKASI LONCENG AKTIVITAS DASBOR ---
    function initNotificationModule() {
        const bellIcon = document.getElementById('bellIcon');
        const notifDropdown = document.getElementById('notifDropdown');
        const notifList = document.getElementById('notifList');
        const notifCount = document.getElementById('notifCount');
        const clearNotifBtn = document.getElementById('clearNotifBtn');

        if (bellIcon && notifDropdown) {
            bellIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                notifDropdown.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!notifDropdown.contains(e.target) && !bellIcon.contains(e.target)) {
                    notifDropdown.classList.remove('show');
                }
            });
        }

        let activities = [];

        // 1. Aktivitas Keuangan
        let financeDB = JSON.parse(localStorage.getItem('KILAT_FINANCE_DB')) || JSON.parse(localStorage.getItem('KILAT_FINANCE_DATA')) || { bulanan: [], harian: [], daftar: [], lain: [], keluar: [] };
        ['bulanan', 'harian', 'daftar', 'lain', 'keluar'].forEach(cat => {
            (financeDB[cat] || []).forEach(trx => {
                let dateStr = trx.date || trx.tanggal || new Date().toISOString().split('T')[0];
                let isOut = cat === 'keluar';
                activities.push({
                    title: isOut ? '💸 Pengeluaran Kas' : '💰 Pemasukan Kas',
                    desc: `${trx.name || trx.nama || 'Transaksi'} (${formatRp(trx.amount || trx.nominal || 0)})`,
                    time: dateStr,
                    timestamp: new Date(dateStr).getTime() || Date.now()
                });
            });
        });

        // 2. Aktivitas Absensi
        for (let i = 0; i < localStorage.length; i++) {
            let key = localStorage.key(i);
            if (key && key.startsWith('KILAT_ABSENSI_')) {
                let dateStr = key.replace('KILAT_ABSENSI_', '');
                activities.push({
                    title: '📋 Catatan Absensi',
                    desc: `Absensi harian diperbarui untuk tanggal ${dateStr}`,
                    time: dateStr,
                    timestamp: new Date(dateStr).getTime() || Date.now()
                });
            }
        }

        // 3. Aktivitas Pusat Akun (Atlet)
        let storedAthletes = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
        storedAthletes.forEach(nick => {
            activities.push({
                title: '⛸️ Pusat Akun Atlet',
                desc: `Data atlet terdaftar: ${nick}`,
                time: new Date().toISOString().split('T')[0],
                timestamp: Date.now() - 5000
            });
        });

        // Urutkan dari yang terbaru
        activities.sort((a, b) => b.timestamp - a.timestamp);

        if (notifList) {
            if (activities.length === 0) {
                notifList.innerHTML = '<div class="notif-item">Belum ada aktivitas terbaru.</div>';
            } else {
                notifList.innerHTML = '';
                activities.slice(0, 15).forEach((act) => {
                    notifList.innerHTML += `
                        <div class="notif-item" style="flex-direction: column; align-items: flex-start; gap: 3px;">
                            <div style="display: flex; justify-content: space-between; width: 100%;">
                                <strong style="color: var(--sidebar-bg); font-size: 0.85rem;">${act.title}</strong>
                                <span style="font-size: 0.65rem; color: var(--text-gray);">${act.time}</span>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-dark);">${act.desc}</span>
                        </div>
                    `;
                });
            }
        }

        let totalCount = Math.min(activities.length, 15);
        if (notifCount) {
            notifCount.innerText = totalCount;
            notifCount.style.display = totalCount > 0 ? 'flex' : 'none';
        }

        if (clearNotifBtn) {
            clearNotifBtn.onclick = () => {
                if (notifCount) {
                    notifCount.innerText = '0';
                    notifCount.style.display = 'none';
                }
                if (notifList) {
                    notifList.innerHTML = '<div class="notif-item">Semua aktivitas telah ditandai dibaca.</div>';
                }
            };
        }
    }

    // --- MODUL WIDGET FEEDBACK PENGUNJUNG TERDAFTAR ---
    function initFeedbackWidgetModule() {
        const feedbackContainer = document.getElementById('recentFeedbackList');
        if (!feedbackContainer) return;

        // Ambil data feedback dari localStorage (kunci umum dari portal feedback / umpan balik)
        let feedbacks = JSON.parse(localStorage.getItem('KILAT_FEEDBACKS') ||
                                   localStorage.getItem('public_feedbacks') ||
                                   localStorage.getItem('feedback_data')) || [];

        if (feedbacks.length === 0) {
            feedbackContainer.innerHTML = '<p style="text-align:center; color:var(--text-gray); font-size:0.85rem; padding:15px; font-weight:800;">Belum ada pesan kritik & saran dari pengunjung terdaftar.</p>';
            return;
        }

        feedbackContainer.innerHTML = '';
        feedbacks.slice(0, 5).forEach((fb) => {
            let author = fb.name || fb.nama || fb.username || 'Pengguna Terdaftar';
            let subject = fb.subject || fb.subjek || 'Umpan Balik';
            let message = fb.message || fb.pesan || '';
            let dateStr = fb.date || fb.tanggal || new Date().toISOString().split('T')[0];

            feedbackContainer.innerHTML += `
                <div class="list-item" style="padding:10px 0; border-bottom:1px solid rgba(0,0,0,0.05);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <strong style="font-size:0.88rem; color:var(--sidebar-bg);"><i class="fa-solid fa-comment-dots" style="color:#7b61ff; margin-right:5px;"></i> ${subject}</strong>
                        <span style="font-size:0.7rem; color:var(--text-gray);">${dateStr}</span>
                    </div>
                    <p style="margin:0 0 4px 0; font-size:0.82rem; color:var(--text-dark); font-style:italic;">"${message}"</p>
                    <div style="font-size:0.72rem; color:var(--text-gray); text-align:right;">Oleh: <strong>${author}</strong></div>
                </div>
            `;
        });
    }

    // --- KALKULASI STATISTIK SPP 1 TAHUN PENUH ---
    function calculateAndRenderYearlySppStats(year, storedAthletes, financeDB, billingPaid) {
        const monthNames = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        let monthlyData = [];
        let totalYearAmount = 0;

        monthNames.forEach((mName, index) => {
            let monthCode = String(index + 1).padStart(2, '0');
            let targetMonthPrefix = `${year}-${monthCode}`;

            let monthBulananTrx = (financeDB.bulanan || []).filter(item => {
                let d = item.date || item.tanggal || '';
                return d.startsWith(targetMonthPrefix);
            });

            let totalAmount = monthBulananTrx.reduce((acc, curr) => acc + parseInt(curr.amount || curr.nominal || 0), 0);
            let countPaid = monthBulananTrx.length;

            billingPaid.forEach(item => {
                let d = item.date || item.tanggal || item.dueDate || '';
                let isLunas = item.status === 'Lunas' || item.status === 'Paid' || item.status === true;
                if (d.startsWith(targetMonthPrefix) && isLunas) {
                    let alreadyCounted = monthBulananTrx.some(b => (b.name || '').toLowerCase() === (item.name || item.nickname || '').toLowerCase());
                    if (!alreadyCounted) {
                        countPaid++;
                        totalAmount += parseInt(item.amount || item.nominal || 0);
                    }
                }
            });

            totalYearAmount += totalAmount;
            monthlyData.push({
                monthName: mName,
                count: countPaid,
                total: totalAmount
            });
        });

        renderSppMonthlyListUI(monthlyData, year, totalYearAmount);
    }

    function updateSppYearlyData(selectedYear) {
        const storedAthletes = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
        let financeDB = JSON.parse(localStorage.getItem('KILAT_FINANCE_DB')) || JSON.parse(localStorage.getItem('KILAT_FINANCE_DATA')) || { bulanan: [], harian: [], daftar: [], lain: [], keluar: [] };
        let billingPaid = JSON.parse(localStorage.getItem('KILAT_BILLING_PAID')) || JSON.parse(localStorage.getItem('KILAT_BILLING_DB')) || [];
        calculateAndRenderYearlySppStats(selectedYear, storedAthletes, financeDB, billingPaid);
    }

    function renderSppMonthlyListUI(monthlyData, year, totalYearAmount) {
        const pieChartEl = document.getElementById('sppPieChart');

        const monthColors = [
            '#7b61ff', '#50b054', '#f59e0b', '#ff6b81', '#38bdf8', '#ec4899',
            '#84cc16', '#14b8a6', '#6366f1', '#f97316', '#a855f7', '#06b6d4'
        ];

        if (pieChartEl && totalYearAmount > 0) {
            let currentDeg = 0;
            let gradientStops = [];

            monthlyData.forEach((m, idx) => {
                if (m.total > 0) {
                    let percentage = (m.total / totalYearAmount) * 100;
                    let nextDeg = currentDeg + (percentage * 3.6);
                    gradientStops.push(`${monthColors[idx]} ${currentDeg}deg ${nextDeg}deg`);
                    currentDeg = nextDeg;
                }
            });

            if (gradientStops.length > 0) {
                pieChartEl.style.background = `conic-gradient(${gradientStops.join(', ')})`;
            } else {
                pieChartEl.style.background = `conic-gradient(#e2e8f0 0deg 360deg)`;
            }
        } else if (pieChartEl) {
            pieChartEl.style.background = `conic-gradient(#e2e8f0 0deg 360deg)`;
        }

        const legendList = document.querySelector('.pie-legend');
        const sppLunasVal = document.getElementById('sppLunasVal');
        const sppBelumVal = document.getElementById('sppBelumVal');
        const sppNunggakVal = document.getElementById('sppNunggakVal');

        if (legendList) {
            legendList.style.cssText = "max-height: 170px; overflow-y: auto; padding-right: 4px; display: flex; flex-direction: column; gap: 6px;";
            legendList.innerHTML = monthlyData.map((m, idx) => `
                <li style="display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; padding: 4px 0; border-bottom: 1px dashed rgba(0,0,0,0.06);">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span class="color-dot" style="background: ${monthColors[idx]}; width: 10px; height: 10px; border-radius: 50%; display: inline-block;"></span>
                        <strong style="color: var(--text-dark);">${m.monthName} ${year}</strong>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-weight: 900; color: #50b054; margin-right: 4px;">${m.count} Atlet</span>
                        <strong style="color: var(--text-dark);">${formatRp(m.total)}</strong>
                    </div>
                </li>
            `).join('');
        }

        if (sppLunasVal) sppLunasVal.innerText = formatRp(totalYearAmount);
        if (sppBelumVal) sppBelumVal.innerText = `${monthlyData.filter(m => m.total > 0).length} Bulan Aktif`;
        if (sppNunggakVal) sppNunggakVal.innerText = `${year}`;
    }

    // --- MODUL TREN ARUS KAS ---
    function initCashFlowTrendModule() {
        let filterSelect = document.getElementById('filterArusKas');
        if (filterSelect) {
            filterSelect.addEventListener('change', () => renderCashFlowTrend(filterSelect.value));
        }

        renderCashFlowTrend('7hari');
    }

    function renderCashFlowTrend(filterType) {
        let financeDB = JSON.parse(localStorage.getItem('KILAT_FINANCE_DB')) || JSON.parse(localStorage.getItem('KILAT_FINANCE_DATA')) || { bulanan: [], harian: [], daftar: [], lain: [], keluar: [] };

        let allTrx = [];
        ['bulanan', 'harian', 'daftar', 'lain', 'keluar'].forEach(cat => {
            (financeDB[cat] || []).forEach(trx => {
                if (cat !== 'harian' || trx.statusBayar === 'Terbayar' || trx.status === 'lunas') {
                    allTrx.push({
                        date: trx.date || trx.tanggal || new Date().toISOString().split('T')[0],
                        amount: parseInt(trx.amount || trx.nominal || 0),
                        isExpense: cat === 'keluar'
                    });
                }
            });
        });

        let periods = [];
        let now = new Date();

        if (filterType === '7bulan') {
            for (let i = 6; i >= 0; i--) {
                let d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                let yearMonthStr = d.toISOString().substring(0, 7);
                let label = d.toLocaleString('id-ID', { month: 'short', year: '2-digit' });

                let totalIn = allTrx.filter(t => t.date.startsWith(yearMonthStr) && !t.isExpense).reduce((sum, t) => sum + t.amount, 0);
                let totalOut = allTrx.filter(t => t.date.startsWith(yearMonthStr) && t.isExpense).reduce((sum, t) => sum + t.amount, 0);

                periods.push({ label, net: totalIn - totalOut });
            }
        } else {
            for (let i = 6; i >= 0; i--) {
                let d = new Date();
                d.setDate(now.getDate() - i);
                let dateStr = d.toISOString().split('T')[0];
                let label = d.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric' });

                let totalIn = allTrx.filter(t => t.date === dateStr && !t.isExpense).reduce((sum, t) => sum + t.amount, 0);
                let totalOut = allTrx.filter(t => t.date === dateStr && t.isExpense).reduce((sum, t) => sum + t.amount, 0);

                periods.push({ label, net: totalIn - totalOut });
            }
        }

        let chartContainer = document.getElementById('barChartContainer');
        let labelsContainer = document.getElementById('barLabelsContainer');

        if (chartContainer) {
            chartContainer.style.cssText = "flex: 1; display: flex; align-items: stretch; justify-content: space-between; width: 100%; min-height: 200px; padding-bottom: 5px; border-bottom: 2px solid rgba(200, 190, 220, 0.3); gap: 12px; margin-top: 15px; margin-bottom: 5px;";
            chartContainer.innerHTML = '';
            if (labelsContainer) labelsContainer.innerHTML = '';

            let maxVal = Math.max(...periods.map(p => Math.abs(p.net)), 1000);

            periods.forEach((p, idx) => {
                let absNet = Math.abs(p.net);
                let percent = absNet > 0 ? (absNet / maxVal) * 100 : 0;
                let finalPercent = Math.max(2, percent);

                let barColor = p.net >= 0 ? 'linear-gradient(180deg, #9882f0 0%, #7b61ff 100%)' : 'linear-gradient(180deg, #ff8da1 0%, #ff6b81 100%)';
                let isTodayClass = idx === periods.length - 1 ? 'today' : '';
                let valTextColor = p.net > 0 ? '#50b054' : (p.net < 0 ? '#ff6b81' : 'var(--text-gray)');

                chartContainer.innerHTML += `
                    <div style="display:flex; flex-direction:column; align-items:center; flex:1; justify-content:flex-end; height: 100%;">
                        <div style="font-size:0.65rem; font-weight:900; margin-bottom:6px; color:${valTextColor}; white-space:nowrap;">${formatRpShort(p.net)}</div>
                        <div class="bar ${isTodayClass}" style="height: calc(${finalPercent}% - 22px); min-height: 8px; background: ${barColor}; width: 100%; max-width: 34px; border-radius: 12px; box-shadow: 2px 2px 5px rgba(0,0,0,0.12), inset 2px 2px 4px rgba(255,255,255,0.4);"></div>
                    </div>
                `;

                if (labelsContainer) {
                    labelsContainer.innerHTML += `<div style="flex:1; text-align:center; font-size:0.75rem; font-weight:800; color:var(--text-gray);">${p.label}</div>`;
                }
            });
        }
    }

    function formatRpShort(val) {
        if (Math.abs(val) >= 1000000) return (val / 1000000).toFixed(1) + 'jt';
        if (Math.abs(val) >= 1000) return (val / 1000).toFixed(0) + 'rb';
        return val;
    }

    function detectUserRoleAndPermissions() {
        let currentUser = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') || localStorage.getItem('kilat_user_data') || 'null');
        let rawRole = currentUser && currentUser.role ? currentUser.role.toLowerCase() : 'umum';

        const roleTextEl = document.getElementById('roleText');
        if (roleTextEl) roleTextEl.innerText = rawRole.toUpperCase();
    }

    function initAdminCardEditor() {}

    function formatRp(angka) {
        return 'Rp ' + Number(angka || 0).toLocaleString('id-ID');
    }
    </script>

    <!-- SIDEBAR -->
    @include('layouts.sidebar')

    <main class="main-content" id="mainContent">
        <!-- Header -->
        <header class="header">
            <h1>Dashboard</h1>
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Cari atlet, kuitansi...">
            </div>
            <div class="header-icons">
                <div class="icon-btn" id="bellIcon">
                    <i class="fa-solid fa-bell"></i>
                    <span class="badge" id="notifCount">0</span>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <h4>Aktivitas Sistem</h4>
                            <span class="notif-clear" id="clearNotifBtn">Tandai Dibaca</span>
                        </div>
                        <div class="notif-list" id="notifList"></div>
                    </div>
                </div>
            </div>
        </header>

        <section class="hero-banner">
            <div class="hero-text">
                <h2 id="heroWelcomeTitle">Selamat Datang, {{ Auth::user()->name ?? 'Admin' }}! ☀️</h2>
                <p>Ringkasan aktivitas dan operasional sekolah sepatu roda hari ini.</p>
                <a href="{{ route('admin.index') }}" class="btn-primary"><i class="fa-solid fa-clipboard-user"></i> Cek Absensi Hari Ini</a>
            </div>
            <i class="fa-solid fa-chart-line" style="font-size: 110px; color: rgba(255, 255, 255, 0.4); text-shadow: 10px 10px 20px rgba(0,0,0,0.1);"></i>
        </section>

        <!-- Kartu Statistik -->
        <section class="stats-grid">
            <div class="stat-card pastel-blue">
                <div class="stat-icon ic-atlet"><i class="fa-solid fa-person-skating"></i></div>
                <h3>Pusat Akun (Atlet)</h3>
                <div class="value" id="valTotalAtlet">0 <span class="value-small">Total</span></div>
                <div class="trend neutral" style="flex-direction: column; align-items: flex-start; padding: 8px;">
                    <div class="detail-akun-grid" style="display: flex; gap: 12px; width: 100%;">
                        <div class="detail-akun-item" style="color:#50b054;"><i class="fa-solid fa-check-circle"></i> Aktif: <span id="valAktifAtlet">0</span></div>
                        <div class="detail-akun-item" style="color:#ff6b81;"><i class="fa-solid fa-circle-xmark"></i> Non-Aktif: <span id="valNonAktifAtlet">0</span></div>
                    </div>
                </div>
            </div>
            <div class="stat-card pastel-yellow">
                <div class="stat-icon ic-hadir"><i class="fa-solid fa-check-double"></i></div>
                <h3>Absensi Hari Ini</h3>
                <div class="value" id="valTotalHadir">0 <span class="value-small">Hadir</span></div>
                <div class="trend up"><i class="fa-solid fa-user-check"></i> Aktif: <span id="valHadirAktif">0</span> | Non-Aktif: <span id="valHadirNon">0</span></div>
            </div>
            <div class="stat-card pastel-green">
                <div class="stat-icon ic-saldo"><i class="fa-solid fa-wallet"></i></div>
                <h3>Saldo Keuangan</h3>
                <div class="value" id="dashboardSaldo" style="font-size:1.4rem;">Rp 0</div>
                <div class="trend up"><i class="fa-solid fa-arrow-trend-up"></i> Terkini dari Kas</div>
            </div>
            <div class="stat-card pastel-pink">
                <div class="stat-icon ic-spp"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <h3>Status SPP Bulan Ini</h3>
                <div class="value" id="sppStatusMain">0% <span class="value-small">Lunas</span></div>
                <div class="trend warn"><i class="fa-solid fa-circle-exclamation"></i> Menunggu Konfirmasi</div>
            </div>
        </section>

        <!-- Kartu Grafik -->
        <section class="charts-container">
            <div class="chart-card pastel-purple">
                <div class="chart-header">
                    <h3>Tren Arus Kas</h3>
                    <select id="filterArusKas" class="badge-timbul">
                        <option value="7hari">7 Hari Terakhir</option>
                        <option value="7bulan">7 Bulan Terakhir</option>
                    </select>
                </div>
                <div class="bar-chart" id="barChartContainer"></div>
                <div class="bar-labels" id="barLabelsContainer"></div>
            </div>
            <div class="chart-card pastel-orange">
                <div class="chart-header"><h3>Persentase SPP</h3></div>
                <div class="pie-container">
                    <div class="pie-chart" id="sppPieChart"></div>
                    <ul class="pie-legend">
                        <li><div><span class="color-dot" style="background: #50b054;"></span> Lunas</div> <strong id="sppLunasVal">0%</strong></li>
                        <li><div><span class="color-dot" style="background: #f59e0b;"></span> Belum Lunas</div><strong id="sppBelumVal">0%</strong></li>
                        <li><div><span class="color-dot" style="background: #ff6b81;"></span> Menunggak</div><strong id="sppNunggakVal">0%</strong></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="bottom-container">
            <div class="chart-card pastel-blue" style="width: 100%;">
                <div class="chart-header">
                    <h3>Pemasukan Kas Terbaru</h3>
                    <a href="{{ route('admin.finance') }}" class="badge-timbul" style="text-decoration:none;">Data Lengkap</a>
                </div>
                <div id="recentTransactionsList"></div>
            </div>
            <div class="chart-card" style="background: transparent; box-shadow: none; padding: 0; width: 100%;">
                <div class="quick-actions-grid">
                    <a href="{{ route('admin.index') }}" class="action-card"><i class="fa-solid fa-clipboard-user"></i><span>Input Absensi</span></a>
                    <a href="{{ route('admin.finance') }}" class="action-card"><i class="fa-solid fa-wallet"></i><span>Catat Kas</span></a>
                    <a href="{{ route('admin.billing') }}" class="action-card"><i class="fa-solid fa-file-invoice-dollar"></i><span>Buat Tagihan</span></a>
                    <a href="{{ route('appendix') }}" class="action-card"><i class="fa-solid fa-book"></i><span>Lihat Appendix</span></a>
                </div>
            </div>
        </section>

        <!-- CARD KHUSUS FEEDBACK / KRITIK & SARAN (Full Melebar Menempel Sisi Kiri-Kanan) -->
        <section class="bottom-banner" style="margin-top: 15px;">
            <div class="chart-card pastel-yellow" style="width: 100%; box-sizing: border-box;">
                <div class="chart-header">
                    <h3>💬 Kritik & Saran (Feedback)</h3>
                    <span class="badge-timbul" style="font-size:0.75rem;">Umpan Balik Member</span>
                </div>
                <div id="recentFeedbackList" style="max-height: 240px; overflow-y: auto; padding-right: 4px;"></div>
            </div>
        </section>

        <section class="bottom-banner" style="margin-top: 15px;">
            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; justify-content: center;">
                <i class="fa-solid fa-bullhorn" style="font-size: 40px; filter: drop-shadow(4px 4px 5px rgba(0,0,0,0.2));"></i>
                <div>
                    <h2>Siaran Pengumuman ke Orang Tua 📢</h2>
                    <p>Kirim pesan broadcast jadwal latihan atau tagihan via WhatsApp.</p>
                </div>
            </div>
            <a href="#" class="btn-secondary">Buat Pesan</a>
        </section>
    </main>

    <!-- JS Terpisah -->
    <script src="{{ asset('js/admin/admin.js') }}"></script>
    <script src="{{ asset('js/beranda_admin.js') }}"></script>
</body>
</html>
