<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing SPP - Sekolah Sepatu Roda (Claymorphism)</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/admin_dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/billing.css') }}">

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

    <style>
        /* CSS Tambahan untuk Toggle & Filter Periode */
        .clay-checkbox-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-main);
            padding: 10px 15px;
            border-radius: 14px;
            box-shadow: var(--clay-shadow-inset);
        }
        .toggle-switch-checkbox {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
            background-color: #cbd5e1;
            border-radius: 12px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: var(--clay-shadow-btn);
        }
        .toggle-switch-checkbox::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background-color: #ffffff;
            border-radius: 50%;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .toggle-real:checked + .toggle-switch-checkbox {
            background-color: var(--sidebar-bg, #6366f1);
        }
        .toggle-real:checked + .toggle-switch-checkbox::after {
            transform: translateX(24px);
        }
    </style>
</head>
<body>

    <!-- SIDEBAR (TERSTANDARISASI) -->
    @include('layouts.sidebar')

    <main class="main-content" id="mainContent">
        <header class="header">
            <h1>Billing SPP</h1>
            <div class="header-icons">
                <div class="icon-btn"><i class="fa-solid fa-bell"></i></div>
                <a href="{{ route('profil') }}" class="icon-btn" id="profileIconBtn" title="Profil Admin">
                    <i class="fa-solid fa-user"></i>
                </a>
            </div>
        </header>

        <!-- KONTESINER PEMBUNGKUS AGAR KARTU KIRI & KANAN SELALU BERDAMPINGAN -->
        <div class="billing-top-container">

            <!-- Kartu Kiri: Generate Tarif Bulanan -->
            <section class="control-panel">
                <h2><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--sidebar-bg);"></i> Generate Tarif Bulanan</h2>
                <div class="generate-grid">
                    <div class="form-group">
                        <label>Periode</label>
                        <input type="month" id="inputPeriod" class="clay-input" onchange="checkAndApplyDiscountHistory()">
                    </div>
                    <div class="form-group">
                        <label>Jatuh Tempo</label>
                        <input type="date" id="inputDueDate" class="clay-input">
                    </div>
                    <div class="form-group">
                        <label>Tarif Dasar (Rp)</label>
                        <input type="text" id="inputTariff" inputmode="numeric" pattern="[0-9]*" class="clay-input" value="150000" placeholder="Ketik angka...">
                    </div>
                    <div class="form-group">
                        <label>Pengaturan Diskon</label>
                        <div class="clay-checkbox-wrapper">
                            <label for="apply-discount" style="cursor:pointer; margin:0; font-weight:700; font-size:0.85rem; color:var(--text-dark);">Gunakan histori</label>
                            <input type="checkbox" id="apply-discount" class="toggle-real" checked style="display:none;" onchange="checkAndApplyDiscountHistory()">
                            <label for="apply-discount" class="toggle-switch-checkbox"></label>
                        </div>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <button onclick="generateInvoices()" class="btn-clay btn-primary" style="width: 100%;">
                            <i class="fa-solid fa-paper-plane"></i> Generate
                        </button>
                    </div>
                </div>
            </section>

            <!-- Kartu Kanan: Periode Aktif & Saldo Netto -->
            <section class="summary-frame">
                <h2><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--sidebar-bg);"></i>Periode Aktif</h2>
                <div class="summary-grid-mini">
                    <div class="summary-item">
                        <span class="summary-label">Saldo Netto</span>
                        <span class="summary-value highlight-net" id="sumNetBalance"><i class="fa-solid fa-wallet" style="color:var(--sidebar-bg);"></i> Rp 0</span>
                        <span class="summary-value" id="sumPeriod" style="font-size: 0.8rem; color: var(--text-gray);"><i class="fa-solid fa-calendar-days"></i> - </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Total Tagihan</span>
                        <span class="summary-value" id="sumTagihan"><i class="fa-solid fa-money-bill-wave" style="color:var(--status-paid);"></i> Rp 0</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Total Diskon</span>
                        <span class="summary-value" id="sumDiskon"><i class="fa-solid fa-tags" style="color:#ffcc66;"></i> Rp 0</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Belum Dibayar</span>
                        <span class="summary-value" id="sumUnpaid"><i class="fa-solid fa-circle-exclamation" style="color:var(--status-unpaid);"></i> Rp 0</span>
                    </div>
                </div>
            </section>

        </div>

        <section class="toolbar">
            <div class="total-badge">
                <div>Aktif: <span class="active-val" id="countActive">0</span></div>
                <div>Arsip: <span class="archive-val" id="countArchive">0</span></div>
                <div>Total: <span id="countTotal">0</span></div>
            </div>
            <div class="filter-group">
                <!-- Filter Periode Bulan -->
                <input type="month" id="filterPeriodeBulan" class="clay-input" onchange="customApplyFilters()" style="width: 160px; height: 42px; padding: 0 10px;" title="Filter berdasarkan Bulan Periode">

                <select class="clay-input" id="filterStatus" onchange="customApplyFilters()" style="width: 150px; height: 42px; padding: 0 10px;">
                    <option value="Semua">Semua Status</option>
                    <option value="Paid">Lunas (Paid)</option>
                    <option value="Unpaid">Belum Lunas (Unpaid)</option>
                    <option value="Aktif">Mode Aktif</option>
                    <option value="Arsip">Mode Arsip</option>
                </select>
                <div class="search-bar-clay">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchName" placeholder="Cari nama atlet..." onkeyup="customApplyFilters()">
                </div>
            </div>
        </section>

        <div class="table-responsive">
            <div class="clay-table-grid clay-table-header">
                <div class="check-cell"><input type="checkbox" class="custom-checkbox" id="checkAll" onchange="toggleSelectAll(this)"></div>
                <div class="header-atlet">ATLET<div class="resizer" id="atletResizer" title="Geser untuk memperlebar kolom"></div></div>
                <div>TAGIHAN</div><div>DISKON</div><div>STATUS</div><div>PERIODE</div><div>JATUH TEMPO</div><div>TARIF DEFAULT</div>
            </div>
            <div id="tableBody"></div>
        </div>
    </main>

    <div class="bulk-action-bar" id="bulkActionBar">
        <span class="bulk-text"><span id="selectedCount">0</span> Terpilih:</span>
        <button class="btn-bulk aktif" onclick="executeBulk('Paid')"><i class="fa-solid fa-check"></i> Paid</button>
        <button class="btn-bulk arsip" onclick="executeBulk('Unpaid')"><i class="fa-solid fa-xmark"></i> Unpaid</button>
        <button class="btn-bulk arsip" onclick="executeBulk('Arsip')"><i class="fa-solid fa-box-archive"></i> Arsipkan</button>
        <button class="btn-bulk aktif" onclick="executeBulk('Aktif')"><i class="fa-solid fa-rotate-left"></i> Aktifkan</button>
    </div>

    <!-- JS Terpisah -->
    <script src="{{ asset('js/admin/billing.js') }}"></script>
    <script src="{{ asset('js/beranda_admin.js') }}"></script>
    <script>
        // --- SCRIPT TAMBAHAN SINKRONISASI DISKON & FILTER PENCARIAN/PERIODE ---
        function checkAndApplyDiscountHistory() {
            const useHistory = document.getElementById('apply-discount');
            const periodInput = document.getElementById('inputPeriod');
            if (!useHistory || !periodInput) return;

            if (useHistory.checked && periodInput.value) {
                let savedInvoices = JSON.parse(localStorage.getItem('KILAT_SAVED_INVOICES')) || [];
                let discountMap = {};
                savedInvoices.forEach(inv => {
                    let athleteName = inv.athlete?.name || inv.name;
                    let discVal = parseInt(inv.discount || inv.diskon || 0);
                    if (athleteName && discVal > 0) {
                        discountMap[athleteName.toLowerCase().trim()] = discVal;
                    }
                });

                document.querySelectorAll('.table-row-item, .clay-table-grid').forEach(row => {
                    let nameEl = row.querySelector('.atlet-name, strong');
                    let discInput = row.querySelector('.discount-input, input[type="number"]');
                    if (nameEl && discInput) {
                        let nameKey = nameEl.innerText.toLowerCase().trim();
                        if (discountMap[nameKey] !== undefined) {
                            discInput.value = discountMap[nameKey];
                        }
                    }
                });
            }
        }

        // Fungsi Filter Tambahan untuk Menangani Filter Periode Bulan & Pencarian Nama
        function customApplyFilters() {
            if (typeof applyFilters === 'function') {
                applyFilters();
            }

            const periodeVal = document.getElementById('filterPeriodeBulan').value; // Format: YYYY-MM
            const searchVal = document.getElementById('searchName').value.toLowerCase().trim();

            const rows = document.querySelectorAll('#tableBody > .clay-table-grid, #tableBody > div');
            rows.forEach(row => {
                let textContent = row.innerText.toLowerCase();
                let matchSearch = searchVal === '' || textContent.includes(searchVal);

                let matchPeriode = true;
                if (periodeVal) {
                    const [year, monthNum] = periodeVal.split('-');
                    const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                    let monthName = months[parseInt(monthNum) - 1]?.toLowerCase() || '';

                    let hasPeriodText = textContent.includes(periodeVal) || (textContent.includes(monthName) && textContent.includes(year));
                    matchPeriode = hasPeriodText;
                }

                if (matchSearch && matchPeriode) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            const searchInput = document.getElementById('searchName');
            if (searchInput) {
                searchInput.addEventListener('input', customApplyFilters);
            }
        });
    </script>
</body>
</html>
