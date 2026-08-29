// ===================================================
// FILE: public/js/appendix.js (Versi 13.08)
// MANAJEMEN DATA APPENDIX, ROLE SINKRONISASI, BIODATA & PENILAIAN
// ===================================================

document.addEventListener("DOMContentLoaded", () => {
    let savedFolder = localStorage.getItem('KILAT_CSS_FOLDER') || 'css';
    const linkTag = document.getElementById('mainStylesheet');
    if (linkTag) {
        let currentHref = linkTag.getAttribute('href');
        let fileName = currentHref.split('/').pop();
        linkTag.setAttribute('href', `${savedFolder}/${fileName}`);
    }
    loadClubLogoToView();
});

const userRole = "{{ $role ?? 'admin' }}";
const currentUserId = "{{ $currentUserId ?? '1' }}";

// --- UTILS & STORAGE HELPER ---
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
        console.error('Gagal mengambil data dari server, beralih ke cache lokal:', error);
    }
    return JSON.parse(localStorage.getItem('manageUsersData')) ||
           JSON.parse(localStorage.getItem('KILAT_USERS')) ||
           [];
}

function saveUsersData(data) {
    localStorage.setItem('manageUsersData', JSON.stringify(data));
    localStorage.setItem('KILAT_USERS', JSON.stringify(data));
}

// --- 1. SINKRONISASI ROLE DARI URL PARAMETER & LOCAL STORAGE ---
function resolveCurrentRole() {
    const currentUserSession = JSON.parse(
        localStorage.getItem('KILAT_CURRENT_USER') ||
        localStorage.getItem('kilat_user_data') ||
        localStorage.getItem('user') ||
        '{}'
    );

    const sessionRole = (currentUserSession.role || '').toLowerCase();

    if (sessionRole === 'admin' || sessionRole === 'administrator' || sessionRole === 'superadmin') {
        localStorage.setItem('userRole', 'admin');
        localStorage.setItem('KILAT_ACTIVE_ROLE', 'admin');
        return 'admin';
    }

    const urlParams = new URLSearchParams(window.location.search);
    const roleFromUrl = urlParams.get('role');

    if (roleFromUrl) {
        const cleanRoleFromUrl = roleFromUrl.toLowerCase().trim();
        localStorage.setItem('userRole', cleanRoleFromUrl);
        localStorage.setItem('KILAT_ACTIVE_ROLE', cleanRoleFromUrl);
    }

    let localRole = localStorage.getItem('userRole') ||
                    localStorage.getItem('KILAT_ACTIVE_ROLE');

    if (!localRole && typeof userRole !== 'undefined') {
        localRole = String(userRole);
    }

    if (!localRole) {
        localRole = sessionRole || 'admin';
    }

    let detected = String(localRole).toLowerCase().trim();

    if (detected === 'orang tua' || detected === 'orangtua' || detected === 'wali') detected = 'parent';
    if (detected === 'pelatih') detected = 'coach';
    if (detected.includes('admin') || detected === 'administrator' || detected === 'superadmin') detected = 'admin';
    if (!detected) detected = 'admin';

    return detected;
}

let detectedRole = resolveCurrentRole();
let currentRole = detectedRole.toUpperCase();

let currentUserSession = JSON.parse(
    localStorage.getItem('KILAT_CURRENT_USER') ||
    localStorage.getItem('kilat_user_data') ||
    localStorage.getItem('user') ||
    'null'
);

const urlParamsGlobal = new URLSearchParams(window.location.search);
let currentParentUsername = urlParamsGlobal.get('parent') ||
    (currentUserSession ? (currentUserSession.username || currentUserSession.email || currentUserSession.name || window.CURRENT_USER_NAME || 'ParentUser') : (window.CURRENT_USER_NAME || 'ParentUser'));

// --- 2. DEKLARASI ELEMEN UI & MODAL ---
const trickModal = document.getElementById('trickModal');
const massModal = document.getElementById('massModal');
const speedModal = document.getElementById('speedModal');
const settingsModal = document.getElementById('settingsModal');
const pendingModal = document.getElementById('pendingModal');

const formTrick = document.getElementById('assessmentForm') || document.getElementById('trickForm');
const formSpeed = document.getElementById('speedForm');

const athleteInput = document.getElementById('athleteName'); // Nama Panggilan
const athleteFullNameInput = document.getElementById('athleteFullName'); // Nama Lengkap
const bioInputs = document.querySelectorAll('.bio-input');
const waBtn = document.getElementById('waBtn');
const analysisTextarea = document.getElementById('analysisTextarea');

const athleteSelect = document.getElementById('athleteSelect');
const athleteSelectFullName = document.getElementById('athleteSelectFullName');
const searchAthlete = document.getElementById('searchAthlete');

const filterKelasMass = document.getElementById('filterKelasMass');
const filterStatusMass = document.getElementById('filterStatusMass');
const filterHasilMass = document.getElementById('filterHasilMass');
const checkAllMass = document.getElementById('checkAllMass');

const btnSettings = document.getElementById('btnSettings');
const btnModeToggle = document.getElementById('btnModeToggle');
const roleLabelDisplay = document.getElementById('roleLabelDisplay');
const btnPendingNotif = document.getElementById('btnPendingNotif');

let currentCellId = null;
let currentTrickName = null;
let currentSpeedType = 'on-skate';
let isMassMode = true;

const CLAY_COLORS = { belum: '#ffffff', ulangi: '#ff6b81', progress: '#ffc977', master: '#50b054' };

// --- PENGATURAN LOGO CLUB & MODAL PENGATURAN ---
function ensureSettingsModalExists() {
    let modalEl = document.getElementById('settingsModal') || document.getElementById('customSettingsModal');
    if (!modalEl) {
        modalEl = document.createElement('div');
        modalEl.id = 'settingsModal';
        modalEl.style.cssText = 'display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:10000; overflow-y:auto;';
        modalEl.innerHTML = `
            <div class="clay-modal-content" style="background:#fff; width:90%; max-width:450px; margin:80px auto; padding:25px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.2);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
                    <h3 style="margin:0; font-size:1.1rem; color:#1e293b;">⚙️ Pengaturan Logo Club</h3>
                    <button type="button" onclick="closeModal('settingsModal')" style="background:none; border:none; font-size:1.2rem; cursor:pointer; font-weight:bold;">&times;</button>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; font-weight:bold; font-size:0.9rem; margin-bottom:8px; color:#334155;">Upload Logo Club (Gambar):</label>
                    <input type="file" id="clubLogoInputFile" accept="image/*" class="form-control" style="width:100%; padding:8px; border-radius:8px; border:1px solid #cbd5e1; font-size:0.9rem;" onchange="previewClubLogo(event)">
                </div>
                <div style="text-align:center; margin-bottom:15px;">
                    <img id="clubLogoPreview" src="" alt="Preview Logo" style="max-height:100px; max-width:100%; display:none; margin:0 auto; border-radius:6px; border:1px solid #ddd; padding:4px;">
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeModal('settingsModal')" style="background:#cbd5e1; color:#334155; border:none; padding:8px 16px; border-radius:6px; font-weight:bold; cursor:pointer;">Batal</button>
                    <button type="button" onclick="saveClubLogoToStorage()" style="background:#22c55e; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:bold; cursor:pointer;"><i class="fa-solid fa-floppy-disk"></i> Simpan Logo</button>
                </div>
            </div>
        `;
        document.body.appendChild(modalEl);
    }
}

let tempBase64Logo = '';

window.previewClubLogo = function(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        tempBase64Logo = e.target.result;
        const previewImg = document.getElementById('clubLogoPreview');
        if (previewImg) {
            previewImg.src = tempBase64Logo;
            previewImg.style.display = 'block';
        }
    };
    reader.readAsDataURL(file);
};

window.saveClubLogoToStorage = function() {
    if (!tempBase64Logo) {
        return alert('Silakan pilih file gambar logo terlebih dahulu!');
    }
    localStorage.setItem('KILAT_CLUB_LOGO', tempBase64Logo);
    loadClubLogoToView();
    closeModal('settingsModal');
    alert('Logo Club berhasil disimpan dan diperbarui!');
};

function loadClubLogoToView() {
    const savedLogo = localStorage.getItem('KILAT_CLUB_LOGO');
    if (!savedLogo) return;

    const logoElements = document.querySelectorAll('.logo-club, [class*="LogoClub"], img[alt*="Logo"], span, div');
    logoElements.forEach(el => {
        let txt = (el.textContent || '').trim();
        if (txt.toLowerCase() === 'logo club' || el.id === 'logoClubContainer') {
            el.innerHTML = `<img src="${savedLogo}" alt="Logo Club" style="max-height:45px; max-width:120px; object-fit:contain; border-radius:4px; vertical-align:middle;">`;
        }
    });
}

if (btnSettings) {
    btnSettings.addEventListener('click', (e) => {
        e.preventDefault();
        ensureSettingsModalExists();
        const savedLogo = localStorage.getItem('KILAT_CLUB_LOGO') || '';
        tempBase64Logo = savedLogo;
        const previewImg = document.getElementById('clubLogoPreview');
        const fileInput = document.getElementById('clubLogoInputFile');
        if (fileInput) fileInput.value = '';
        if (previewImg) {
            if (savedLogo) {
                previewImg.src = savedLogo;
                previewImg.style.display = 'block';
            } else {
                previewImg.style.display = 'none';
            }
        }
        openModalSafely(document.getElementById('settingsModal'));
    });
}

// --- HELPER CEK STATUS / PEMBAYARAN AKTIF ATLET ---
async function isAthleteActiveOrPaidFromUsers(nick) {
    let manageUsers = await getUsersData();
    let athleteUser = manageUsers.find(u => {
        let r = (u.role || '').toLowerCase();
        let uName = (u.name || u.username || u.namaLengkap || '').toLowerCase().trim();
        return r === 'atlet' && uName === nick.toLowerCase().trim();
    });

    if (!athleteUser) {
        const bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nick)) || {};
        let status = (bio.status || 'aktif').toLowerCase();
        if (status !== 'arsip') return true;
    } else {
        let status = (athleteUser.status || 'aktif').toLowerCase();
        if (status !== 'arsip') return true;
    }

    let financeDB = JSON.parse(localStorage.getItem('KILAT_FINANCE_DB')) || { bulanan: [] };
    let savedInvoices = JSON.parse(localStorage.getItem('KILAT_SAVED_INVOICES')) || [];
    let athleteNameLower = nick.toLowerCase().trim();

    let hasPaid = false;
    (financeDB.bulanan || []).forEach(b => {
        if ((b.name || '').toLowerCase().trim() === athleteNameLower) hasPaid = true;
    });

    savedInvoices.forEach(inv => {
        let invName = (inv.athlete?.name || inv.name || '').toLowerCase().trim();
        let isPaid = inv.status && inv.status.toLowerCase() === 'paid';
        if (invName === athleteNameLower && isPaid) hasPaid = true;
    });

    return hasPaid;
}

// --- DROPDOWN & FILTER ATLET ---
async function updateAthleteDropdowns() {
    const athletes = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
    const athletesData = JSON.parse(localStorage.getItem('athletes_data')) || [];
    const manageUsers = await getUsersData();

    let rawList = [...athletes];

    manageUsers.forEach(u => {
        let r = (u.role || '').toLowerCase();
        if (r === 'atlet') {
            let uName = u.name || u.username || u.namaLengkap;
            if (uName) rawList.push(uName);
        }
    });

    athletesData.forEach(a => {
        let name = typeof a === 'object' ? (a.name || a.nickname || a.fullName) : a;
        if (name) rawList.push(name);
    });

    let allAthletes = [...new Set(rawList)].filter(Boolean);

    let nickOptions = '<option value="">-- PANGGILAN --</option>';
    let fullOptions = '<option value="">-- NAMA LENGKAP --</option>';

    const filterText = searchAthlete ? searchAthlete.value.toLowerCase() : '';
    const roleLower = currentRole.toLowerCase();

    let allowedAthletesForParent = [];
    if (roleLower === 'parent' && currentParentUsername) {
        const parentUserObj = manageUsers.find(u =>
            (u.username || '').toLowerCase() === currentParentUsername.toLowerCase() ||
            (u.namaLengkap || u.nama || u.name || '').toLowerCase() === currentParentUsername.toLowerCase()
        );
        if (parentUserObj && (parentUserObj.atletTautan || parentUserObj.athletes)) {
            allowedAthletesForParent = parentUserObj.atletTautan || parentUserObj.athletes;
        }
    }

    for (const nick of allAthletes) {
        if (!await isAthleteActiveOrPaidFromUsers(nick)) continue;

        const bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nick)) || {};
        const extAth = athletesData.find(a => (a.name || a.nickname || a.fullName) === nick) || {};

        if (roleLower === 'parent') {
            let connectedOrtu = bio.connectedParent || bio.ortu || extAth.ortu || '';
            let isLinkedByAdmin = allowedAthletesForParent.some(ath => ath.toLowerCase() === nick.toLowerCase());
            let isConnectedByBio = currentParentUsername && connectedOrtu && connectedOrtu.toLowerCase() === currentParentUsername.toLowerCase();

            if (!isLinkedByAdmin && !isConnectedByBio) continue;
        }

        const fullName = bio.fullName || extAth.fullName || nick;

        if (filterText && !(nick.toLowerCase().includes(filterText) || fullName.toLowerCase().includes(filterText))) continue;

        nickOptions += `<option value="${nick}">${nick}</option>`;
        if (fullName) fullOptions += `<option value="${fullName}" data-nickname="${nick}" data-fullname="${fullName}">${fullName}</option>`;
    }

    if (athleteSelect) athleteSelect.innerHTML = nickOptions;
    if (athleteSelectFullName) athleteSelectFullName.innerHTML = fullOptions;
}

// --- FUNGSI TAB & KOMPONEN DINAMIS ---
function getActiveTabId() {
    const activeBtn = document.querySelector('.curriculum-select-container .curr-btn.active') || document.querySelector('.curr-btn.active');
    return activeBtn ? activeBtn.getAttribute('data-target') : 'classicSlalomView';
}

function updateDynamicComponents() {
    const legendTable = document.getElementById('legendTableDynamic');
    const analysisLabel = document.getElementById('analysisLabelText');
    const activeTab = getActiveTabId();
    const athleteName = athleteInput ? athleteInput.value.trim() : '';

    let legendHTML = '';
    let placeholderText = '';
    let labelText = '';

    if (activeTab === "beginner'sTestView" || activeTab === "beginnerTestView") {
        legendHTML = `
            <thead><tr><th colspan="3">KETERANGAN</th></tr></thead>
            <tbody>
                <tr><td class="bg-white" style="background:${CLAY_COLORS.belum}">BELUM</td></tr>
                <tr><td class="bg-red" style="background:${CLAY_COLORS.ulangi}">ULANGI</td></tr>
                <tr><td class="bg-yellow" style="background:${CLAY_COLORS.progress}">CUKUP</td></tr>
                <tr><td class="bg-green" style="background:${CLAY_COLORS.master}">LANCAR</td></tr>
            </tbody>
        `;
        labelText = "ANALISA / CATATAN BEGINNER:";
        placeholderText = "Tulis catatan perkembangan dasar atlet (balance, posture, fleksibilitas)...";
    } else if (activeTab === "speedSlalomView") {
        legendHTML = `
            <thead><tr><th colspan="2">KETERANGAN</th></tr></thead>
            <tbody>
                <tr><td class="bg-yellow" style="background:${CLAY_COLORS.progress}">+0.2s Setiap 1 Cone Fault</td></tr>
                <tr><td class="bg-red" style="background:${CLAY_COLORS.ulangi}">&gt;4 FAULT = GAGAL</td></tr>
                <tr><td class="bg-green" style="background:${CLAY_COLORS.master}">BERHASIL</td></tr>
            </tbody>
        `;
        labelText = "ANALISA / CATATAN SPEED SLALOM:";
        placeholderText = "Tulis catatan waktu terbaik (PB) dan evaluasi penalti cone...";
    } else if (activeTab === "freestyleSlideView") {
        legendHTML = `
            <thead><tr><th colspan="2">KETERANGAN</th></tr></thead>
            <tbody>
                <tr><td class="bg-yellow" style="background:${CLAY_COLORS.progress}">START</td></tr>
                <tr><td class="bg-red" style="background:${CLAY_COLORS.ulangi}">&lt; 2 METER (GAGAL)</td></tr>
                <tr><td class="bg-green" style="background:${CLAY_COLORS.master}">&gt; 2 METER (BERHASIL)</td></tr>
            </tbody>
        `;
        labelText = "ANALISA / CATATAN FREESTYLE SLIDE:";
        placeholderText = "Tulis catatan jarak slide, keseimbangan, dan variasi trik slide...";
    } else {
        legendHTML = `
            <thead><tr><th colspan="2">KETERANGAN</th></tr></thead>
            <tbody>
                <tr><td class="bg-white" style="background:${CLAY_COLORS.belum}">0 CONE</td></tr>
                <tr><td class="bg-red" style="background:${CLAY_COLORS.ulangi}">1-4 CONES</td></tr>
                <tr><td class="bg-yellow" style="background:${CLAY_COLORS.progress}">5-7 CONES</td></tr>
                <tr><td class="bg-green" style="background:${CLAY_COLORS.master}">8+ CONES</td></tr>
            </tbody>
        `;
        labelText = "ANALISA / CATATAN CLASSIC SLALOM:";
        placeholderText = "Ketik analisa/catatan atlet di sini...";
    }

    if (legendTable) legendTable.innerHTML = legendHTML;
    if (analysisLabel) {
        analysisLabel.innerText = labelText;
        analysisLabel.style.cursor = "pointer";
        analysisLabel.title = "Klik untuk membuka form/modal analisa";
        analysisLabel.onclick = function() {
            openAnalysisModal();
        };
    }
    if (analysisTextarea) {
        analysisTextarea.placeholder = placeholderText;
        analysisTextarea.setAttribute('readonly', 'true');
        loadAnalysisForTab(athleteName, activeTab);
    }
}

function loadAnalysisForTab(athleteName, tabId) {
    if (!analysisTextarea) return;
    if (!athleteName) {
        analysisTextarea.value = '';
        return;
    }
    const bioData = JSON.parse(localStorage.getItem('KILAT_BIO_' + athleteName)) || {};
    const tabAnalysis = bioData.analisaPerTab || {};
    analysisTextarea.value = tabAnalysis[tabId] || '';
}

// --- FUNGSI MODAL POPUP ANALISA ---
function ensureAnalysisModalExists() {
    let modalEl = document.getElementById('analysisModal');
    if (!modalEl) {
        modalEl = document.createElement('div');
        modalEl.id = 'analysisModal';
        modalEl.style.cssText = 'display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:10000; overflow-y:auto;';
        modalEl.innerHTML = `
            <div class="clay-modal-content" style="background:#fff; width:90%; max-width:550px; margin:60px auto; padding:20px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.2);">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
                    <h3 id="analysisModalTitle" style="margin:0; font-size:1.1rem; color:#1e293b;">📝 Form Analisa / Catatan Atlet</h3>
                    <button type="button" onclick="closeModal('analysisModal')" style="background:none; border:none; font-size:1.2rem; cursor:pointer; font-weight:bold;">&times;</button>
                </div>
                <div class="form-group" style="margin-bottom:15px;">
                    <label id="modalAnalysisLabelText" style="display:block; font-weight:bold; font-size:0.9rem; margin-bottom:8px; color:#334155;">Catatan Analisa:</label>
                    <textarea id="modalAnalysisTextarea" class="form-control" rows="6" placeholder="Tulis catatan analisa di sini..." style="width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; font-size:0.95rem; resize:vertical;"></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="deleteAnalysisModal()" style="background:#ef4444; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:bold; cursor:pointer;"><i class="fa-solid fa-trash"></i> Hapus</button>
                    <button type="button" onclick="saveAnalysisModal()" style="background:#22c55e; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:bold; cursor:pointer;"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                </div>
            </div>
        `;
        document.body.appendChild(modalEl);
    }
}

function openAnalysisModal() {
    const athleteName = athleteInput ? athleteInput.value.trim() : '';
    if (!athleteName) return alert('Pilih atau tentukan atlet terlebih dahulu!');

    ensureAnalysisModalExists();
    const modal = document.getElementById('analysisModal');
    const modalTextarea = document.getElementById('modalAnalysisTextarea');
    const modalTitle = document.getElementById('analysisModalTitle');
    const activeTab = getActiveTabId();

    if (modalTitle) modalTitle.innerHTML = `📝 Analisa Atlet: <strong>${athleteName}</strong> (${activeTab})`;

    const bioData = JSON.parse(localStorage.getItem('KILAT_BIO_' + athleteName)) || {};
    const tabAnalysis = bioData.analisaPerTab || {};
    if (modalTextarea) {
        modalTextarea.value = tabAnalysis[activeTab] || '';
        if (currentRole.toLowerCase() === 'parent') {
            modalTextarea.setAttribute('readonly', 'true');
        } else {
            modalTextarea.removeAttribute('readonly');
        }
    }

    openModalSafely(modal);
}

window.saveAnalysisModal = function() {
    if (currentRole.toLowerCase() === 'parent') return alert('Akses terbatas untuk Parent.');
    const athleteName = athleteInput ? athleteInput.value.trim() : '';
    if (!athleteName) return alert('Pilih atlet terlebih dahulu!');

    const modalTextarea = document.getElementById('modalAnalysisTextarea');
    const newText = modalTextarea ? modalTextarea.value : '';
    const activeTab = getActiveTabId();

    let bioData = JSON.parse(localStorage.getItem('KILAT_BIO_' + athleteName)) || {};
    if (!bioData.analisaPerTab) bioData.analisaPerTab = {};

    bioData.analisaPerTab[activeTab] = newText;
    bioData.analisa = newText;

    localStorage.setItem('KILAT_BIO_' + athleteName, JSON.stringify(bioData));

    if (analysisTextarea) analysisTextarea.value = newText;
    closeModal('analysisModal');
    alert('Catatan / Analisa berhasil disimpan!');
};

window.deleteAnalysisModal = function() {
    if (currentRole.toLowerCase() === 'parent') return alert('Akses terbatas untuk Parent.');
    const athleteName = athleteInput ? athleteInput.value.trim() : '';
    if (!athleteName) return alert('Pilih atlet terlebih dahulu!');

    if (confirm('Yakin ingin menghapus catatan / analisa untuk kelas ini?')) {
        const activeTab = getActiveTabId();
        let bioData = JSON.parse(localStorage.getItem('KILAT_BIO_' + athleteName)) || {};

        if (bioData.analisaPerTab) {
            delete bioData.analisaPerTab[activeTab];
        }
        bioData.analisa = '';

        localStorage.setItem('KILAT_BIO_' + athleteName, JSON.stringify(bioData));

        const modalTextarea = document.getElementById('modalAnalysisTextarea');
        if (modalTextarea) modalTextarea.value = '';
        if (analysisTextarea) analysisTextarea.value = '';

        closeModal('analysisModal');
        alert('Catatan analisa berhasil dihapus.');
    }
};

// --- 3. STATE HISTORY & MODAL CONTROL ---
function openModalSafely(modalEl) {
    if (!modalEl) return;
    modalEl.style.display = 'block';
    modalEl.style.position = 'fixed';
    modalEl.style.top = '0';
    modalEl.style.left = '0';
    modalEl.style.width = '100vw';
    modalEl.style.height = '100vh';
    modalEl.style.backgroundColor = 'rgba(0,0,0,0.5)';
    modalEl.style.overflowY = 'auto';
    modalEl.style.zIndex = '9999';

    const modalContent = modalEl.querySelector('.modal-content, .clay-modal-content');
    if (modalContent) {
        modalContent.style.maxHeight = '85vh';
        modalContent.style.overflowY = 'auto';
        modalContent.style.margin = '40px auto';
    }

    modalEl.classList.add('show');
    if (history.state?.modalId !== modalEl.id) {
        history.pushState({ modalId: modalEl.id }, '');
    }
}

function closeModalSafely(modalEl) {
    if (!modalEl) return;
    modalEl.style.display = 'none';
    modalEl.classList.remove('show');
    if (history.state?.modalId === modalEl.id) {
        history.back();
    }
}

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) closeModalSafely(modal);
};

window.addEventListener('popstate', (event) => {
    let anyOpen = false;
    [trickModal, massModal, speedModal, settingsModal, pendingModal, document.getElementById('analysisModal')].forEach(m => {
        if (m && (m.style.display === 'block' || m.classList.contains('show'))) {
            m.style.display = 'none';
            m.classList.remove('show');
            anyOpen = true;
        }
    });
    if (anyOpen) event.preventDefault();
});

// --- 4. PENERAPAN HAK AKSES ROLE & PENGUNCIAN INPUT UNTUK PARENT ---
function applyRolePermissions() {
    detectedRole = resolveCurrentRole();
    currentRole = detectedRole.toUpperCase();

    if (roleLabelDisplay) roleLabelDisplay.innerText = currentRole;

    const badgeAksesAkun = document.getElementById('badgeAksesAkun') || document.querySelector('.badge-role-top');
    if (badgeAksesAkun) badgeAksesAkun.innerText = `AKSES AKUN: ${currentRole}`;

    const btnPanelAdmin = document.getElementById('btnPanelAdmin') || document.querySelector('[id*="PanelAdmin"], [href*="admin"]');
    const clickableCells = document.querySelectorAll('.clickable-cell, .clickable-speed, td.clickable-cell, td[data-id], td[data-speed], td[data-type], table.matrix-table td:not(:first-child)');
    const roleLower = currentRole.toLowerCase();

    const allPanelAdminEls = document.querySelectorAll('#btnPanelAdmin, button[onclick*="PanelAdmin"], a[onclick*="PanelAdmin"]');
    if (roleLower !== 'admin') {
        allPanelAdminEls.forEach(el => el.style.display = 'none');
    }

    const adminActionElements = Array.from(document.querySelectorAll('button, a')).filter(el => {
        const txt = (el.textContent || '').trim().toUpperCase();
        return txt.includes('CETAK') || txt.includes('HAPUS ATLET') || txt.includes('EDIT DATA ATLET');
    });

    if (roleLower === 'admin') {
        if (btnPanelAdmin) btnPanelAdmin.style.display = 'inline-flex';
        allPanelAdminEls.forEach(el => el.style.display = 'inline-flex');
        if (btnSettings) btnSettings.style.display = 'inline-block';
        if (btnModeToggle) btnModeToggle.style.display = 'inline-block';
        if (btnPendingNotif) btnPendingNotif.style.display = 'inline-flex';

        adminActionElements.forEach(el => el.style.display = 'inline-block');

        bioInputs.forEach(input => {
            input.setAttribute('readonly', 'true');
            if (input.tagName === 'SELECT') input.setAttribute('disabled', 'true');
        });

        clickableCells.forEach(cell => {
            cell.style.pointerEvents = 'auto';
            cell.style.cursor = 'pointer';
        });

        updatePendingNotificationBadge();

    } else if (roleLower === 'coach') {
        if (btnPanelAdmin) btnPanelAdmin.style.display = 'none';
        if (btnSettings) btnSettings.style.display = 'none';
        if (btnPendingNotif) btnPendingNotif.style.display = 'none';
        if (btnModeToggle) btnModeToggle.style.display = 'inline-block';

        adminActionElements.forEach(el => el.style.display = 'none');

        bioInputs.forEach(input => {
            input.setAttribute('disabled', 'true');
            input.setAttribute('readonly', 'true');
        });

        clickableCells.forEach(cell => {
            cell.style.pointerEvents = 'auto';
            cell.style.cursor = 'pointer';
        });

    } else if (roleLower === 'parent') {
        if (btnPanelAdmin) btnPanelAdmin.style.display = 'none';
        if (btnSettings) btnSettings.style.display = 'none';
        if (btnModeToggle) btnModeToggle.style.display = 'none';
        if (btnPendingNotif) btnPendingNotif.style.display = 'none';

        adminActionElements.forEach(el => {
            const txt = (el.textContent || '').trim().toUpperCase();
            if (txt.includes('CETAK')) {
                el.style.display = 'inline-flex';
            } else {
                el.style.display = 'none';
            }
        });

        if (analysisTextarea) {
            analysisTextarea.setAttribute('readonly', 'true');
        }

        bioInputs.forEach(input => {
            input.setAttribute('readonly', 'true');
            if (input.tagName === 'SELECT') input.setAttribute('disabled', 'true');
        });

        clickableCells.forEach(cell => {
            cell.style.pointerEvents = 'none';
            cell.style.cursor = 'default';
        });
    }

    if (btnModeToggle) {
        if (isMassMode) {
            btnModeToggle.classList.add('active');
            btnModeToggle.innerHTML = '📋 MODE: MASSAL AKTIF';
            btnModeToggle.style.backgroundColor = '#ff4757';
            btnModeToggle.style.color = '#ffffff';
        } else {
            btnModeToggle.classList.remove('active');
            btnModeToggle.innerHTML = '📋 MODE: INDIVIDU';
            btnModeToggle.style.backgroundColor = '';
            btnModeToggle.style.color = '';
        }
    }
}

// --- 5. VERIFIKASI PENDING ATLET ---
function getPendingAthletes() {
    const athletes = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
    let pendingList = [];

    athletes.forEach(nick => {
        const bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nick)) || {};
        let st = String(bio.status || '').toLowerCase();
        if (st === 'pending' || st === 'menunggu verifikasi') {
            pendingList.push({ nick, bio });
        }
    });
    return pendingList;
}

function updatePendingNotificationBadge() {
    if (currentRole.toLowerCase() !== 'admin' || !btnPendingNotif) return;
    const pendingList = getPendingAthletes();
    const badge = btnPendingNotif.querySelector('.badge-count');
    if (badge) {
        badge.innerText = pendingList.length;
        badge.style.display = pendingList.length > 0 ? 'inline-block' : 'none';
    }
}

function openPendingModal() {
    if (currentRole.toLowerCase() !== 'admin') return;
    const container = document.getElementById('pendingListContainer');
    if (!container) return;

    const pendingList = getPendingAthletes();
    if (pendingList.length === 0) {
        container.innerHTML = '<div style="padding:15px; text-align:center; color:#777;">Tidak ada pendaftaran atlet baru yang menunggu verifikasi.</div>';
    } else {
        let html = '';
        pendingList.forEach(item => {
            html += `
                <div class="pending-item-card" style="border:1px solid #ddd; padding:12px; border-radius:8px; margin-bottom:10px; background:#fff;">
                    <div><strong>Nama Panggilan:</strong> ${item.nick}</div>
                    <div><strong>Nama Lengkap (Email):</strong> ${item.bio.fullName || '-'}</div>
                    <div><strong>NIK:</strong> ${item.bio.nik || '-'}</div>
                    <div><strong>Orang Tua / Parent:</strong> ${item.bio.connectedParent || '-'}</div>
                    <div><strong>WhatsApp:</strong> ${item.bio.wa || '-'}</div>
                    <div style="margin-top:8px; display:flex; gap:8px;">
                        <button type="button" style="background:#50b054; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;" onclick="approveAthlete('${item.nick}')">✅ ACC / Verifikasi</button>
                        <button type="button" style="background:#ff6b81; color:#fff; border:none; padding:6px 12px; border-radius:4px; cursor:pointer;" onclick="rejectAthlete('${item.nick}')">❌ Tolak</button>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }
    openModalSafely(pendingModal);
}

if (btnPendingNotif) btnPendingNotif.addEventListener('click', openPendingModal);

window.approveAthlete = async function(nick) {
    if (currentRole.toLowerCase() !== 'admin') return;
    let bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + nick)) || {};
    bio.status = 'Aktif';
    localStorage.setItem('KILAT_BIO_' + nick, JSON.stringify(bio));
    alert(`Atlet "${nick}" berhasil diverifikasi dan diset Aktif!`);
    updatePendingNotificationBadge();
    openPendingModal();
    await updateAthleteDropdowns();
};

window.rejectAthlete = async function(nick) {
    if (currentRole.toLowerCase() !== 'admin') return;
    if (!confirm(`Tolak pendaftaran atlet "${nick}"? Data akan dihapus.`)) return;

    let athletes = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
    localStorage.setItem('KILAT_ATHLETES_LIST', JSON.stringify(athletes.filter(name => name !== nick)));
    localStorage.removeItem('KILAT_BIO_' + nick);
    localStorage.removeItem('KILAT_PROFIL_' + nick);

    let manageUsers = await getUsersData();
    manageUsers = manageUsers.filter(u => u.name !== nick && u.username !== nick);
    saveUsersData(manageUsers);

    alert(`Pendaftaran atlet "${nick}" ditolak.`);
    updatePendingNotificationBadge();
    openPendingModal();
    await updateAthleteDropdowns();
};

if (searchAthlete) searchAthlete.addEventListener('input', updateAthleteDropdowns);

// --- 6. SELEKSI ATLET & BIODATA LOGIC ---
async function handleSelectChange(targetNick) {
    if (targetNick) {
        const bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + targetNick));

        if (currentRole.toLowerCase() === 'parent') {
            const manageUsers = await getUsersData();
            const parentUserObj = manageUsers.find(u =>
                (u.username || '').toLowerCase() === currentParentUsername.toLowerCase() ||
                (u.namaLengkap || u.nama || u.name || '').toLowerCase() === currentParentUsername.toLowerCase()
            );
            const allowedAthletes = parentUserObj ? (parentUserObj.atletTautan || parentUserObj.athletes || []) : [];
            let connectedOrtu = bio ? (bio.connectedParent || bio.ortu || '') : '';

            let isLinkedByAdmin = allowedAthletes.some(ath => ath.toLowerCase() === targetNick.toLowerCase());
            let isConnectedByBio = currentParentUsername && connectedOrtu && connectedOrtu.toLowerCase() === currentParentUsername.toLowerCase();

            if (!isLinkedByAdmin && !isConnectedByBio) {
                alert('⚠️ Akses Ditolak: Anda tidak memiliki izin untuk melihat data atlet ini.');
                return handleSelectChange("");
            }
        }

        if (athleteInput) athleteInput.value = targetNick;
        if (athleteSelect) athleteSelect.value = targetNick;
        if (bio && bio.fullName && athleteSelectFullName) athleteSelectFullName.value = bio.fullName;

        loadBiodata(targetNick);
        loadBoard();
        localStorage.setItem('lastActiveAthlete', targetNick);
    } else {
        loadBiodata("");
        loadBoard();
        localStorage.removeItem('lastActiveAthlete');
    }
    updateDynamicComponents();
}

if (athleteSelect) athleteSelect.addEventListener('change', () => handleSelectChange(athleteSelect.value));
if (athleteSelectFullName) {
    athleteSelectFullName.addEventListener('change', () => {
        const selectedOpt = athleteSelectFullName.options[athleteSelectFullName.selectedIndex];
        const val = athleteSelectFullName.value;
        if (!val) return handleSelectChange("");

        let nick = selectedOpt ? selectedOpt.getAttribute('data-nickname') : '';
        if (!nick) {
            const athletesData = JSON.parse(localStorage.getItem('athletes_data')) || [];
            const athletesList = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
            let found = athletesData.find(a => a.id == val || a.email == val || a.fullName == val || a.name == val);
            nick = found ? (found.name || found.nickname || found.id) : val;

            if (!athletesList.includes(nick)) {
                const matchInList = athletesList.find(a => a.toLowerCase() === String(nick).toLowerCase());
                if (matchInList) nick = matchInList;
            }
        }

        handleSelectChange(nick);
    });
}

function loadBiodata(athleteName) {
    const nikInput = document.getElementById('bioNIK');

    if (!athleteName) {
        bioInputs.forEach(input => input.value = '');
        if (athleteFullNameInput) athleteFullNameInput.value = '';
        if (analysisTextarea) analysisTextarea.value = '';
        if (waBtn) waBtn.style.display = 'none';
        updateDynamicComponents();
        return;
    }

    const bioData = JSON.parse(localStorage.getItem('KILAT_BIO_' + athleteName));
    if (bioData) {
        if (nikInput) nikInput.value = (currentRole.toLowerCase() === 'coach') ? '*** RAHASIA ***' : (bioData.nik || '');
        if (athleteFullNameInput) athleteFullNameInput.value = bioData.fullName || '';
        if (document.getElementById('bioGender')) document.getElementById('bioGender').value = bioData.gender || '';
        if (document.getElementById('bioTglLahir')) document.getElementById('bioTglLahir').value = bioData.tglLahir || '';
        if (document.getElementById('bioAlamat')) document.getElementById('bioAlamat').value = bioData.alamat || '';

        if (document.getElementById('bioOrtu')) {
            document.getElementById('bioOrtu').value = bioData.ortu || bioData.connectedParent || currentParentUsername || window.CURRENT_USER_NAME || '';
        }

        if (document.getElementById('bioKelas')) document.getElementById('bioKelas').value = bioData.kelas || 'PEMULA';
        if (document.getElementById('bioStatus')) document.getElementById('bioStatus').value = bioData.status || 'Aktif';
        if (document.getElementById('bioWA')) document.getElementById('bioWA').value = bioData.wa || '';

        updateWaButton(bioData.wa);
        updateDynamicComponents();
    } else {
        bioInputs.forEach(input => input.value = '');
        if (athleteFullNameInput) athleteFullNameInput.value = '';
        if (analysisTextarea) analysisTextarea.value = '';
        if (waBtn) waBtn.style.display = 'none';
        updateDynamicComponents();
    }
}

function updateWaButton(waNumber) {
    if (!waBtn) return;
    if (waNumber && waNumber.trim() !== '') {
        let cleanNumber = waNumber.replace(/\D/g, '');
        if (cleanNumber.startsWith('0')) cleanNumber = '62' + cleanNumber.substring(1);
        waBtn.href = `https://wa.me/${cleanNumber}`;
        waBtn.style.display = 'inline-block';
    } else waBtn.style.display = 'none';
}

function getTodayFormatted() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

// --- 7. PENILAIAN MODAL, HISTORI, & WARNA LEGENDA ---
function getScoreColor(val) {
    if (val === null || val === undefined) return CLAY_COLORS.belum;
    const strVal = String(val).trim().toUpperCase();

    if (strVal === '< 2 M' || strVal === '< 2 METER' || strVal === 'GAGAL' || strVal === 'ULANGI' || strVal === 'MERAH') {
        return CLAY_COLORS.ulangi;
    }
    if (strVal === '> 2 M' || strVal === '> 2 METER' || strVal === 'BERHASIL' || strVal === 'LANCAR' || strVal === 'HIJAU') {
        return CLAY_COLORS.master;
    }
    if (strVal === 'START' || strVal === 'CUKUP' || strVal === 'KUNING') {
        return CLAY_COLORS.progress;
    }

    const numVal = parseInt(strVal, 10);
    if (isNaN(numVal) || numVal <= 0) return CLAY_COLORS.belum;
    if (numVal >= 8) return CLAY_COLORS.master;
    if (numVal >= 5) return CLAY_COLORS.progress;
    return CLAY_COLORS.ulangi;
}

function getSpeedScoreColor(coneValue, timeValue) {
    if (!timeValue || parseFloat(timeValue) <= 0) return CLAY_COLORS.belum;
    const cones = parseInt(coneValue, 10) || 0;
    if (cones > 4) return CLAY_COLORS.ulangi;
    return CLAY_COLORS.master;
}

function updateScoreCell(athleteName, cellId, scoreValue, dateValue = null) {
    if (!athleteName || !cellId) return;

    let db = JSON.parse(localStorage.getItem('KILAT_DB_' + athleteName)) || {};
    let historyDb = JSON.parse(localStorage.getItem('KILAT_HISTORI_' + athleteName)) || {};

    const formattedDate = dateValue || getTodayFormatted();
    db[cellId] = scoreValue;
    if (!historyDb[cellId]) historyDb[cellId] = [];

    historyDb[cellId].unshift({
        score: scoreValue,
        date: formattedDate,
        evaluator: currentRole
    });

    localStorage.setItem('KILAT_DB_' + athleteName, JSON.stringify(db));
    localStorage.setItem('KILAT_HISTORI_' + athleteName, JSON.stringify(historyDb));

    const targetCell = document.querySelector(`.clickable-cell[data-id="${cellId}"], td[data-id="${cellId}"]`);
    if (targetCell) {
        targetCell.style.setProperty('background-color', getScoreColor(scoreValue), 'important');
    }
}

window.saveTrickYoutubeLink = function() {
    const athleteName = athleteInput ? athleteInput.value.trim() : '';
    if (!athleteName || !currentCellId) return alert('Pilih atlet dan trik terlebih dahulu!');

    const ytInput = trickModal ? trickModal.querySelector('#trickYoutubeUrl') : null;
    if (!ytInput) return;

    const ytUrl = ytInput.value.trim();
    let youtubeDb = JSON.parse(localStorage.getItem('KILAT_YOUTUBE_DB_' + athleteName)) || {};
    youtubeDb[currentCellId] = ytUrl;
    localStorage.setItem('KILAT_YOUTUBE_DB_' + athleteName, JSON.stringify(youtubeDb));

    alert('Link YouTube berhasil disimpan untuk trik ini!');
};

function renderScoreHistory(athleteName, cellId) {
    const historyContainer = document.getElementById('trickHistoryList') || document.getElementById('scoreHistoryContainer') || document.getElementById('historyList');
    if (!historyContainer) return;

    const historyDb = JSON.parse(localStorage.getItem('KILAT_HISTORI_' + athleteName)) || {};
    const itemHistory = historyDb[cellId] || [];

    if (itemHistory.length === 0) {
        historyContainer.innerHTML = '<div style="font-size:0.85rem; color:#888; font-style:italic;">Belum ada riwayat penilaian.</div>';
        return;
    }

    let html = '<ul style="list-style:none; padding:0; margin:5px 0; max-height:120px; overflow-y:auto;">';
    itemHistory.forEach(h => {
        let displayScore = h.score;
        if (!isNaN(parseInt(h.score, 10))) displayScore += ' Cone';

        html += `
            <li style="font-size:0.85rem; padding:4px 0; border-bottom:1px dashed #eee; display:flex; justify-content:space-between;">
                <span><strong>Hasil: ${displayScore}</strong> (${h.evaluator || 'Coach'})</span>
                <span style="color:#666;">${h.date}</span>
            </li>
        `;
    });
    html += '</ul>';
    historyContainer.innerHTML = html;
}

function ensureScoreSelectOptions() {
    let targetInput = document.getElementById('assessmentScore') || document.getElementById('trickScore');
    const wrapper = document.getElementById('modalScoreInputWrapper');
    const activeTab = getActiveTabId();

    let optionsHTML = '';
    let labelText = 'Hasil Sesuai Keterangan:';

    if (activeTab === 'freestyleSlideView') {
        labelText = 'Hasil Sesuai Keterangan (Freestyle Slide):';
        optionsHTML = `
            <option value="0">-- PILIH HASIL SLIDE --</option>
            <option value="< 2 M">&lt; 2 METER (GAGAL)</option>
            <option value="> 2 M">&gt; 2 METER (BERHASIL)</option>
        `;
    } else if (activeTab === "beginner'sTestView" || activeTab === "beginnerTestView") {
        labelText = 'Hasil Sesuai Keterangan (Beginner):';
        optionsHTML = `
            <option value="BELUM">BELUM</option>
            <option value="ULANGI">ULANGI</option>
            <option value="CUKUP">CUKUP</option>
            <option value="LANCAR">LANCAR</option>
        `;
    } else {
        labelText = 'Jumlah Cone / Tingkat Kelancaran:';
        optionsHTML = `
            <option value="0">0 Cone (Belum / Gagal)</option>
            <option value="1">1 Cone</option>
            <option value="2">2 Cones</option>
            <option value="3">3 Cones</option>
            <option value="4">4 Cones</option>
            <option value="5">5 Cones</option>
            <option value="6">6 Cones</option>
            <option value="7">7 Cones</option>
            <option value="8">8 Cones</option>
            <option value="9">9 Cones</option>
            <option value="10">10+ Cones (Lancar)</option>
        `;
    }

    if (wrapper) {
        wrapper.innerHTML = `
            <label id="modalScoreLabel" for="assessmentScore">${labelText}</label>
            <select id="assessmentScore" class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc; font-weight: bold; margin-top: 5px;">
                ${optionsHTML}
            </select>
        `;
        return document.getElementById('assessmentScore');
    } else if (targetInput) {
        targetInput.innerHTML = optionsHTML;
        return targetInput;
    }
    return null;
}

function ensureYoutubeInputInTrickModal() {
    if (!trickModal) return;
    let ytGroup = trickModal.querySelector('#modalYoutubeGroup');
    if (!ytGroup) {
        const submitBtn = trickModal.querySelector('.submit-btn, button[type="submit"]');
        const container = submitBtn ? submitBtn.parentElement : trickModal.querySelector('.modal-content');
        if (container) {
            ytGroup = document.createElement('div');
            ytGroup.id = 'modalYoutubeGroup';
            ytGroup.className = 'form-group';
            ytGroup.style.marginTop = '10px';
            ytGroup.innerHTML = `
                <label for="trickYoutubeUrl">Link YouTube (Tautan Video Trik):</label>
                <div class="yt-input-group" style="display:flex; gap:8px;">
                    <input type="url" id="trickYoutubeUrl" class="form-control" placeholder="https://youtube.com/..." style="flex-grow:1; padding:8px; border-radius:8px; border:none; background:var(--bg-main); box-shadow:var(--clay-shadow-inset); font-weight:800; font-size:11px;">
                    <button type="button" id="saveYtBtn" onclick="saveTrickYoutubeLink()" style="background:var(--clay-green, #0400ff); color:dark; border:none; padding:0 10px; border-radius:8px; font-weight:900; font-size:9px; cursor:pointer;" title="Simpan Link YouTube">Simpan Link</button>
                    <a id="trickYoutubeBtn" href="#" target="_blank" class="yt-view-btn" style="display:none; align-items:center; justify-content:center; padding:0 10px; background:var(--c-alpa); color:white; border-radius:8px; text-decoration:none; font-weight:900; font-size:9px;">Buka</a>
                </div>
            `;
            if (submitBtn) container.insertBefore(ytGroup, submitBtn);
            else container.appendChild(ytGroup);
        }
    }

    const ytInput = trickModal.querySelector('#trickYoutubeUrl');
    const ytBtn = trickModal.querySelector('#trickYoutubeBtn');
    if (ytInput && ytBtn) {
        ytInput.oninput = function() {
            let val = ytInput.value.trim();
            if (val) {
                ytBtn.href = val.startsWith('http') ? val : 'https://' + val;
                ytBtn.style.display = 'flex';
            } else {
                ytBtn.style.display = 'none';
            }
        };
    }
}

function hideTrickNameField() {
    const trickNameInputs = document.querySelectorAll('#modalTrickNameGroup, #modalTrickName, #assessmentTrickName, #trickName, input[name="trickName"], input[placeholder*="Nama Trik"]');
    trickNameInputs.forEach(input => {
        const parentLabelGroup = input.closest('.form-group, .input-group, .mb-3, div');
        if (parentLabelGroup) parentLabelGroup.style.display = 'none';
        else input.style.display = 'none';
    });
}

function updateSpeedCell(athleteName, speedType, timeValue, coneValue, dateValue = null) {
    if (!athleteName) return;

    let rawType = speedType || currentSpeedType || 'on-skate';
    const typeKey = rawType.replace('_', '-').toLowerCase();

    const formattedDate = dateValue || getTodayFormatted();
    const rawTime = parseFloat(timeValue) || 0;
    const cones = parseInt(coneValue, 10) || 0;
    const penaltyTime = (rawTime + (cones * 0.2)).toFixed(3);

    let speedDb = JSON.parse(localStorage.getItem('KILAT_SPEED_DB_' + athleteName)) || {};
    let speedHistoryDb = JSON.parse(localStorage.getItem('KILAT_SPEED_HISTORI_' + athleteName)) || {};

    const speedData = {
        time: rawTime > 0 ? rawTime.toFixed(3) : '0.000',
        cone: cones,
        totalTime: rawTime > 0 ? penaltyTime : '0.000',
        date: formattedDate,
        evaluator: currentRole
    };

    if (!speedDb[typeKey] || parseFloat(speedDb[typeKey].totalTime || 0) === 0 || parseFloat(penaltyTime) < parseFloat(speedDb[typeKey].totalTime)) {
        speedDb[typeKey] = speedData;
    }

    if (!speedHistoryDb[typeKey]) speedHistoryDb[typeKey] = [];
    speedHistoryDb[typeKey].unshift(speedData);
    if (speedHistoryDb[typeKey].length > 10) speedHistoryDb[typeKey] = speedHistoryDb[typeKey].slice(0, 10);

    localStorage.setItem('KILAT_SPEED_DB_' + athleteName, JSON.stringify(speedDb));
    localStorage.setItem('KILAT_SPEED_HISTORI_' + athleteName, JSON.stringify(speedHistoryDb));

    const targetSpeedCell = document.querySelector(`td[data-type="${typeKey}"], .clickable-speed[data-speed="${typeKey}"]`);
    if (targetSpeedCell) {
        if (rawTime > 0) targetSpeedCell.innerHTML = `<strong>${penaltyTime} Detik</strong>`;
        else targetSpeedCell.textContent = '0.000 Detik';
        targetSpeedCell.style.setProperty('background-color', getSpeedScoreColor(cones, rawTime), 'important');
    }

    renderSpeedHistory(athleteName, typeKey);
}

function renderSpeedHistory(athleteName, speedType) {
    let cleanType = (speedType || 'on-skate').replace('_', '-').toLowerCase();
    const isOff = (cleanType === 'off-skate' || cleanType === 'b');

    let containerId = isOff ? 'globalHistoryOffSkate' : 'globalHistoryOnSkate';
    let historyContainer = document.getElementById(containerId) || document.getElementById(isOff ? 'speedHistoryOffSkate' : 'speedHistoryOnSkate');

    if (!historyContainer) return;

    const speedHistoryDb = JSON.parse(localStorage.getItem('KILAT_SPEED_HISTORI_' + athleteName)) || {};
    const itemHistory = speedHistoryDb[cleanType] || speedHistoryDb[cleanType.replace('-', '_')] || [];

    if (itemHistory.length === 0) {
        historyContainer.innerHTML = '<div class="history-item flex-center-gray"><em>Belum ada rekor.</em></div>';
        return;
    }

    let html = '<div class="history-item-list" style="max-height:160px; overflow-y:auto; padding-right:4px;">';
    itemHistory.forEach(h => {
        let isFailed = (parseInt(h.cone, 10) > 4);
        let statusBadge = isFailed ? '<span style="color:#ff6b81; font-weight:bold;">[GAGAL >4 CONE]</span>' : '<span style="color:#50b054; font-weight:bold;">[BERHASIL]</span>';

        html += `
            <div style="font-size:0.85rem; padding:6px 4px; border-bottom:1px dashed #ddd; display:flex; justify-content:space-between; align-items:center;">
                <span><strong>${h.totalTime || h.time}s</strong> (${h.time}s + ${h.cone} cone) ${statusBadge}</span>
                <span style="color:#666; font-size:0.8rem;">${h.date || '-'}</span>
            </div>
        `;
    });
    html += '</div>';
    historyContainer.innerHTML = html;
}

function toggleMassMode() {
    isMassMode = !isMassMode;
    if (btnModeToggle) {
        if (isMassMode) {
            btnModeToggle.classList.add('active');
            btnModeToggle.innerHTML = '📋 MODE: MASSAL AKTIF';
            btnModeToggle.style.backgroundColor = '#ff4757';
            btnModeToggle.style.color = '#ffffff';
        } else {
            btnModeToggle.classList.remove('active');
            btnModeToggle.innerHTML = '📋 MODE: INDIVIDU';
            btnModeToggle.style.backgroundColor = '';
            btnModeToggle.style.color = '';
        }
    }
}

if (btnModeToggle) {
    btnModeToggle.addEventListener('click', (e) => {
        e.preventDefault();
        toggleMassMode();
    });
}

function loadBoard() {
    const athleteName = athleteInput ? athleteInput.value.trim() : '';
    const db = athleteName ? (JSON.parse(localStorage.getItem('KILAT_DB_' + athleteName)) || {}) : {};

    document.querySelectorAll('table.matrix-table tbody tr').forEach((tr, rIdx) => {
        const cells = tr.querySelectorAll('td');
        cells.forEach((cell, cIdx) => {
            if (cIdx === 0) return;
            let cellId = cell.getAttribute('data-id');
            if (!cellId) {
                cellId = cell.innerText.trim() || `R${rIdx}_C${cIdx}`;
                cell.setAttribute('data-id', cellId);
                cell.classList.add('clickable-cell');
            }
        });
    });

    document.querySelectorAll('.clickable-cell, td[data-id]').forEach(cell => {
        const cellId = cell.getAttribute('data-id');
        if (!cellId) return;
        const val = db[cellId] || '0';
        cell.style.setProperty('background-color', getScoreColor(val), 'important');
    });

    loadSpeedBoard(athleteName);
    applyRolePermissions();
}

function loadSpeedBoard(athleteName) {
    if (!athleteName) return;
    const speedDb = JSON.parse(localStorage.getItem('KILAT_SPEED_DB_' + athleteName)) || {};

    document.querySelectorAll('.clickable-speed, td[data-speed], td[data-type]').forEach(cell => {
        const key = (cell.getAttribute('data-type') || cell.getAttribute('data-speed') || '').replace('_', '-').toLowerCase();
        if (!key) return;

        const data = speedDb[key] || speedDb[key.replace('-', '_')] || {};
        const rawTime = parseFloat(data.time) || 0;
        const coneVal = parseInt(data.cone, 10) || 0;
        const displayTime = data.totalTime || (rawTime > 0 ? (rawTime + (coneVal * 0.2)).toFixed(3) : null);

        if (displayTime && rawTime > 0) {
            cell.innerHTML = `<strong>${displayTime} Detik</strong>`;
            cell.style.setProperty('background-color', getSpeedScoreColor(coneVal, rawTime), 'important');
        } else {
            cell.textContent = '0.000 Detik';
            cell.style.setProperty('background-color', CLAY_COLORS.belum, 'important');
        }
    });

    renderSpeedHistory(athleteName, 'on-skate');
    renderSpeedHistory(athleteName, 'off-skate');
}

// ===================================================
// SISTEM PENILAIAN MASSAL MODAL
// ===================================================

function renderMassQuickActionButtons() {
    const massActionContainer = document.getElementById('massActionBtns');
    if (!massActionContainer) return;

    const activeTab = getActiveTabId();
    let buttonsHTML = '';

    if (activeTab === 'freestyleSlideView') {
        buttonsHTML = `
            <button type="button" style="background:${CLAY_COLORS.ulangi}; color:#fff; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; font-weight:bold;" onclick="applyQuickScoreToChecked('< 2 M')">&lt; 2 METER</button>
            <button type="button" style="background:${CLAY_COLORS.master}; color:#fff; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; font-weight:bold;" onclick="applyQuickScoreToChecked('> 2 M')">&gt; 2 METER</button>
        `;
    } else if (activeTab === "beginner'sTestView" || activeTab === "beginnerTestView") {
        buttonsHTML = `
            <button type="button" style="background:${CLAY_COLORS.ulangi}; color:#fff; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; font-weight:bold;" onclick="applyQuickScoreToChecked('ULANGI')">ULANGI</button>
            <button type="button" style="background:${CLAY_COLORS.progress}; color:#333; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; font-weight:bold;" onclick="applyQuickScoreToChecked('CUKUP')">CUKUP</button>
            <button type="button" style="background:${CLAY_COLORS.master}; color:#fff; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; font-weight:bold;" onclick="applyQuickScoreToChecked('LANCAR')">LANCAR</button>
        `;
    } else {
        for (let i = 0; i <= 10; i++) {
            let label = (i === 10) ? '10+' : String(i);
            let bg = (i >= 8) ? CLAY_COLORS.master : (i >= 5 ? CLAY_COLORS.progress : CLAY_COLORS.ulangi);
            buttonsHTML += `<button type="button" style="background:${bg}; color:#fff; border:none; padding:4px 8px; border-radius:6px; cursor:pointer; font-size:0.8rem; font-weight:bold;" onclick="applyQuickScoreToChecked('${i}')">${label}</button>`;
        }
    }
    massActionContainer.innerHTML = buttonsHTML;
}

function updateMassAthleteList() {
    const container = document.getElementById('massAthleteContainer');
    if (!container) return;

    const valKelas = filterKelasMass ? filterKelasMass.value : '';
    const valStatus = filterStatusMass ? filterStatusMass.value : '';
    const valHasil = filterHasilMass ? filterHasilMass.value : '';

    const athletes = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];
    if (athletes.length === 0) {
        container.innerHTML = '<div style="padding:15px; text-align:center; color:#777;">Belum ada data atlet terdaftar.</div>';
        return;
    }

    let html = '';
    let filteredCount = 0;
    const activeTab = getActiveTabId();
    const isClassic = (activeTab !== 'freestyleSlideView' && activeTab !== "beginner'sTestView" && activeTab !== "beginnerTestView");
    const isSlide = (activeTab === 'freestyleSlideView');

    athletes.forEach((athNick) => {
        const bio = JSON.parse(localStorage.getItem('KILAT_BIO_' + athNick)) || {};
        const athKelas = bio.kelas || 'PEMULA';
        const athStatus = bio.status || 'Aktif';

        const db = JSON.parse(localStorage.getItem('KILAT_DB_' + athNick)) || {};
        const scoreVal = String(db[currentCellId] !== undefined ? db[currentCellId] : '0');
        const colorHex = getScoreColor(scoreVal);

        if (valKelas && athKelas.toUpperCase() !== valKelas.toUpperCase()) return;
        if (valStatus && athStatus.toLowerCase() !== valStatus.toLowerCase()) return;
        if (valHasil) {
            const h = valHasil.toLowerCase();
            if (h === 'kosong' && (scoreVal !== '0' && scoreVal !== '' && scoreVal !== null)) return;
            if (h === 'merah' && colorHex !== CLAY_COLORS.ulangi) return;
            if (h === 'kuning' && colorHex !== CLAY_COLORS.progress) return;
            if (h === 'hijau' && colorHex !== CLAY_COLORS.master) return;
        }

        filteredCount++;

        let radiosHTML = `<div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:6px;" id="radioGroup_${athNick}">`;
        if (isClassic) {
            for (let i = 0; i <= 10; i++) {
                let valStr = String(i);
                let labelDisplay = (i === 10) ? '10+' : String(i);
                let isChecked = (scoreVal === valStr || (i === 10 && parseInt(scoreVal) >= 10));
                let bgBtn = getScoreColor(valStr);
                let fontStyle = isChecked ? 'color: #2563eb; font-weight: 900; border: 2px solid #2563eb;' : 'color: #1e293b; font-weight: bold; border: 1px solid #cbd5e1;';

                radiosHTML += `
                    <label style="display:inline-flex; align-items:center; justify-content:center; background:${bgBtn}; ${fontStyle} padding:3px 8px; border-radius:4px; font-size:0.75rem; cursor:pointer;" onclick="handleCustomRadioClick('${athNick}', '${valStr}')">
                        <input type="radio" name="massScore_${athNick}" value="${valStr}" ${isChecked ? 'checked' : ''} style="display:none;">
                        ${labelDisplay}
                    </label>
                `;
            }
        } else if (isSlide) {
            let options = [
                { val: "0", label: "0 (Belum)", color: CLAY_COLORS.belum },
                { val: "< 2 M", label: "< 2 M (Gagal)", color: CLAY_COLORS.ulangi },
                { val: "> 2 M", label: "> 2 M (Berhasil)", color: CLAY_COLORS.master }
            ];
            options.forEach(opt => {
                let isChecked = (scoreVal === opt.val);
                let fontStyle = isChecked ? 'color: #2563eb; font-weight: 900; border: 2px solid #2563eb;' : 'color: #1e293b; font-weight: bold; border: 1px solid #cbd5e1;';
                radiosHTML += `
                    <label style="display:inline-flex; align-items:center; justify-content:center; background:${opt.color}; ${fontStyle} padding:4px 10px; border-radius:4px; font-size:0.75rem; cursor:pointer;" onclick="handleCustomRadioClick('${athNick}', '${opt.val}')">
                        <input type="radio" name="massScore_${athNick}" value="${opt.val}" ${isChecked ? 'checked' : ''} style="display:none;">
                        ${opt.label}
                    </label>
                `;
            });
        } else {
            let options = [
                { val: "BELUM", label: "BELUM", color: CLAY_COLORS.belum },
                { val: "ULANGI", label: "ULANGI", color: CLAY_COLORS.ulangi },
                { val: "CUKUP", label: "CUKUP", color: CLAY_COLORS.progress },
                { val: "LANCAR", label: "LANCAR", color: CLAY_COLORS.master }
            ];
            options.forEach(opt => {
                let isChecked = (scoreVal === opt.val);
                let fontStyle = isChecked ? 'color: #2563eb; font-weight: 900; border: 2px solid #2563eb;' : 'color: #1e293b; font-weight: bold; border: 1px solid #cbd5e1;';
                radiosHTML += `
                    <label style="display:inline-flex; align-items:center; justify-content:center; background:${opt.color}; ${fontStyle} padding:4px 10px; border-radius:4px; font-size:0.75rem; cursor:pointer;" onclick="handleCustomRadioClick('${athNick}', '${opt.val}')">
                        <input type="radio" name="massScore_${athNick}" value="${opt.val}" ${isChecked ? 'checked' : ''} style="display:none;">
                        ${opt.label}
                    </label>
                `;
            });
        }
        radiosHTML += `</div>`;

        html += `
            <div class="mass-athlete-row" style="padding:10px; border-bottom:1px dashed #ddd; background:#fff; border-radius:8px; margin-bottom:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:bold; cursor:pointer;">
                        <input type="checkbox" class="mass-check mass-check-item" value="${athNick}">
                        <span>${athNick} <small style="color:#666;">(${bio.fullName || athNick})</small></span>
                    </label>
                    <span style="background:#f1f5f9; padding:2px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold; color:#334155;">Kelas: ${athKelas}</span>
                </div>
                ${radiosHTML}
            </div>
        `;
    });

    container.innerHTML = (filteredCount === 0) ? '<div style="padding:15px; text-align:center; color:#777;">Tidak ada atlet yang cocok dengan filter.</div>' : html;
    if (checkAllMass) checkAllMass.checked = false;
}

window.handleCustomRadioClick = function(athNick, valStr) {
    const group = document.getElementById('radioGroup_' + athNick);
    if (!group) return;

    group.querySelectorAll('label').forEach(lbl => {
        const inp = lbl.querySelector('input[type="radio"]');
        if (inp && inp.value === valStr) {
            inp.checked = true;
            lbl.style.color = '#2563eb';
            lbl.style.fontWeight = '900';
            lbl.style.border = '2px solid #2563eb';
        } else {
            if (inp) inp.checked = false;
            lbl.style.color = '#1e293b';
            lbl.style.fontWeight = 'bold';
            lbl.style.border = '1px solid #cbd5e1';
        }
    });
};

window.applyQuickScoreToChecked = function(scoreValue) {
    const checkedBoxes = document.querySelectorAll('.mass-check-item:checked');
    if (checkedBoxes.length === 0) return alert('Centang atlet terlebih dahulu pada daftar di bawah!');
    checkedBoxes.forEach(cb => window.handleCustomRadioClick(cb.value, scoreValue));
};

function openMassModalForCell(trickName) {
    if (!massModal) return;
    const massTrickInput = document.getElementById('massTrickName');
    if (massTrickInput) massTrickInput.value = trickName || 'Penilaian Massal';

    const massDateInput = document.getElementById('massDate');
    if (massDateInput) massDateInput.value = getTodayFormatted();

    const athleteName = athleteInput ? athleteInput.value.trim() : '';
    let youtubeDb = athleteName ? (JSON.parse(localStorage.getItem('KILAT_YOUTUBE_DB_' + athleteName)) || {}) : {};
    let savedYtUrl = currentCellId ? (youtubeDb[currentCellId] || '') : '';

    let massTitleEl = massModal.querySelector('.modal-title-purple') || massModal.querySelector('h3');
    if (massTitleEl) {
        let ytBadgeHTML = '';
        if (savedYtUrl) {
            let finalYtUrl = savedYtUrl.startsWith('http') ? savedYtUrl : 'https://' + savedYtUrl;
            ytBadgeHTML = ` <a href="${finalYtUrl}" target="_blank" class="yt-view-btn" style="display:inline-flex; align-items:center; gap:3px; padding:2px 8px; background:var(--c-alpa, #ff6b81); color:white; border-radius:6px; text-decoration:none; font-size:10px; font-weight:900; vertical-align:middle;" title="Lihat Video YouTube"><i class="fa-brands fa-youtube"></i> Lihat YouTube</a>`;
        }
        massTitleEl.innerHTML = `📋 Penilaian Massal Trik${ytBadgeHTML}`;
    }

    renderMassQuickActionButtons();
    updateMassAthleteList();
    openModalSafely(massModal);
}

if (filterKelasMass) filterKelasMass.addEventListener('change', updateMassAthleteList);
if (filterStatusMass) filterStatusMass.addEventListener('change', updateMassAthleteList);
if (filterHasilMass) filterHasilMass.addEventListener('change', updateMassAthleteList);

if (checkAllMass) {
    checkAllMass.addEventListener('change', function() {
        const isChecked = this.checked;
        document.querySelectorAll('.mass-check-item').forEach(cb => cb.checked = isChecked);
    });
}

window.closeMassModal = function() {
    const massDateInput = document.getElementById('massDate');
    const selectedDate = massDateInput ? massDateInput.value : getTodayFormatted();

    let savedCount = 0;
    const athletes = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];

    athletes.forEach(athNick => {
        const cb = document.querySelector(`.mass-check-item[value="${athNick}"]`);
        if (!cb || !cb.checked) return;

        const checkedRadio = document.querySelector(`input[name="massScore_${athNick}"]:checked`);
        if (checkedRadio) {
            updateScoreCell(athNick, currentCellId, checkedRadio.value, selectedDate);
            savedCount++;
        }
    });

    loadBoard();
    closeModalSafely(massModal);
    if (savedCount > 0) alert(`Penilaian massal untuk ${savedCount} atlet berhasil disimpan!`);
};

// GLOBAL CLICK HANDLER
document.addEventListener('click', (e) => {
    if (currentRole.toLowerCase() === 'parent') return;

    const cell = e.target.closest('td[data-id], .clickable-cell, table.matrix-table td');
    if (cell && !cell.hasAttribute('data-speed') && !cell.hasAttribute('data-type')) {
        let cellId = cell.getAttribute('data-id');
        if (!cellId) {
            cellId = cell.innerText.trim();
            cell.setAttribute('data-id', cellId);
        }
        currentCellId = cellId;
        currentTrickName = cell.innerText.trim();

        if (isMassMode) {
            openMassModalForCell(currentTrickName);
            return;
        }

        const athleteName = athleteInput ? athleteInput.value.trim() : '';
        if (!athleteName) return alert('Pilih atlet terlebih dahulu untuk melakukan penilaian!');

        hideTrickNameField();
        ensureYoutubeInputInTrickModal();

        const modalTitle = document.getElementById('trickModalTitle') || document.querySelector('#trickModal h3');
        if (modalTitle) modalTitle.innerText = `Penilaian Trik: ${currentTrickName}`;

        if (trickModal) {
            if (formTrick) formTrick.reset();
            const db = JSON.parse(localStorage.getItem('KILAT_DB_' + athleteName)) || {};
            const val = db[currentCellId] || 0;

            const scoreSelect = ensureScoreSelectOptions();
            if (scoreSelect) scoreSelect.value = val;

            const dateInput = document.getElementById('modalDate') || document.getElementById('assessmentDate');
            if (dateInput) dateInput.value = getTodayFormatted();

            const youtubeDb = JSON.parse(localStorage.getItem('KILAT_YOUTUBE_DB_' + athleteName)) || {};
            const savedYtUrl = youtubeDb[currentCellId] || '';
            const ytInput = trickModal.querySelector('#trickYoutubeUrl');
            const ytBtn = trickModal.querySelector('#trickYoutubeBtn');
            if (ytInput) {
                ytInput.value = savedYtUrl;
                if (savedYtUrl && ytBtn) {
                    ytBtn.href = savedYtUrl.startsWith('http') ? savedYtUrl : 'https://' + savedYtUrl;
                    ytBtn.style.display = 'flex';
                } else if (ytBtn) ytBtn.style.display = 'none';
            }

            renderScoreHistory(athleteName, currentCellId);
            openModalSafely(trickModal);
        }
    }

    const speedCell = e.target.closest('.clickable-speed, td[data-speed], td[data-type]');
    if (speedCell) {
        currentSpeedType = (speedCell.getAttribute('data-type') || speedCell.getAttribute('data-speed') || 'on-skate').replace('_', '-').toLowerCase();
        const athleteName = athleteInput ? athleteInput.value.trim() : '';
        if (!athleteName) return alert('Pilih atlet terlebih dahulu untuk penilaian Speed Slalom!');

        if (speedModal && formSpeed) {
            formSpeed.reset();
            const speedDb = JSON.parse(localStorage.getItem('KILAT_SPEED_DB_' + athleteName)) || {};
            const item = speedDb[currentSpeedType] || speedDb[currentSpeedType.replace('-', '_')] || {};

            if (document.getElementById('speedTime')) document.getElementById('speedTime').value = item.time || '';
            const coneInput = document.getElementById('speedFault') || document.getElementById('speedCone');
            if (coneInput) coneInput.value = item.cone || 0;

            const speedDateInput = document.getElementById('speedDate') || document.querySelector('#speedModal input[type="date"]');
            if (speedDateInput) speedDateInput.value = item.date || getTodayFormatted();

            renderSpeedHistory(athleteName, currentSpeedType);
            openModalSafely(speedModal);
        }
    }
});

if (formTrick) {
    formTrick.addEventListener('submit', (e) => {
        e.preventDefault();
        if (currentRole.toLowerCase() === 'parent') return;

        const athleteName = athleteInput ? athleteInput.value.trim() : '';
        if (!athleteName || !currentCellId) return;

        const scoreSelect = document.getElementById('assessmentScore') || document.getElementById('trickScore');
        const dateInput = document.getElementById('modalDate') || document.getElementById('assessmentDate');

        updateScoreCell(athleteName, currentCellId, scoreSelect ? scoreSelect.value : 0, (dateInput && dateInput.value) ? dateInput.value : getTodayFormatted());
        closeModalSafely(trickModal);
        loadBoard();
    });
}

if (formSpeed) {
    formSpeed.addEventListener('submit', (e) => {
        e.preventDefault();
        if (currentRole.toLowerCase() === 'parent') return;

        const athleteName = athleteInput ? athleteInput.value.trim() : '';
        if (!athleteName) return alert('Silakan pilih atlet terlebih dahulu!');

        const timeInput = document.getElementById('speedTime') || document.querySelector('#speedModal input[type="number"]');
        const coneInput = document.getElementById('speedFault') || document.getElementById('speedCone');
        const dateInput = document.getElementById('speedDate') || document.querySelector('#speedModal input[type="date"]');

        updateSpeedCell(athleteName, currentSpeedType, timeInput ? timeInput.value : '0', coneInput ? coneInput.value : '0', (dateInput && dateInput.value) ? dateInput.value : getTodayFormatted());
        closeModalSafely(speedModal);
        loadBoard();
    });
}

// --- 9. INITIALIZATION & TABS ---
document.addEventListener("DOMContentLoaded", async function() {
    applyRolePermissions();
    await updateAthleteDropdowns();
    hideTrickNameField();
    ensureScoreSelectOptions();
    ensureYoutubeInputInTrickModal();
    ensureAnalysisModalExists();
    loadClubLogoToView();

    const lastActive = localStorage.getItem('lastActiveAthlete');
    if (lastActive) await handleSelectChange(lastActive);
    else updateDynamicComponents();
});

document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.curriculum-select-container .curr-btn[data-target], .curr-btn[data-target]');
    const views = document.querySelectorAll('.curriculum-view');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            views.forEach(view => view.style.display = 'none');
            this.classList.add('active');

            const targetView = document.getElementById(this.getAttribute('data-target'));
            if (targetView) targetView.style.display = 'block';

            updateDynamicComponents();
            ensureScoreSelectOptions();
        });
    });
});

// --- 10. MANAJEMEN AKUN & ADMIN PANEL ---
document.addEventListener('DOMContentLoaded', async function() {
    if (typeof renderTable === 'function') await renderTable();
    await updateStatsCounter();
});

async function updateStatsCounter() {
    const users = await getUsersData();
    let adminCount = users.filter(u => (u.role || '').toLowerCase() === 'admin' || (u.role || '').toLowerCase() === 'administrator').length;
    let coachCount = users.filter(u => (u.role || '').toLowerCase() === 'coach').length;
    let parentCount = users.filter(u => (u.role || '').toLowerCase() === 'parent').length;
    let athletes = JSON.parse(localStorage.getItem('KILAT_ATHLETES_LIST')) || [];

    if (document.getElementById('count-admin')) document.getElementById('count-admin').innerText = adminCount;
    if (document.getElementById('count-coach')) document.getElementById('count-coach').innerText = coachCount;
    if (document.getElementById('count-parent')) document.getElementById('count-parent').innerText = parentCount;
    if (document.getElementById('count-athlete')) document.getElementById('count-athlete').innerText = athletes.length;
}

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
        container.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--text-gray); font-weight: 800; grid-column: 1 / -1;">Belum ada data akun terdaftar.</div>';
        return;
    }

    filteredUsers.forEach((user) => {
        let originalIndex = users.findIndex(u => u.id === user.id);
        if (originalIndex === -1) originalIndex = users.indexOf(user);

        let userName = user.namaLengkap || user.nama || user.name || 'Admin';
        let userRole = (user.role || 'Admin').toUpperCase();
        let userEmail = user.email || user.username || '-';
        let userPass = user.password || '******';
        let userStatus = user.status || 'Aktif';
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

        let rowEl = document.createElement('div');
        rowEl.className = 'clay-table-grid clay-table-row';
        rowEl.innerHTML = `
            <div><strong>${userName}</strong></div>
            <div style="text-align: center; display: flex; gap: 5px; justify-content: center;">
                <button type="button" style="background:#3b82f6; color:#fff; border:none; border-radius:4px; padding:3px 7px; cursor:pointer;" onclick="editAccount(${originalIndex})"><i class="fa-solid fa-pen-to-square"></i></button>
                <button type="button" style="background:#ef4444; color:#fff; border:none; border-radius:4px; padding:3px 7px; cursor:pointer;" onclick="deleteAccount(${originalIndex})"><i class="fa-solid fa-trash"></i></button>
            </div>
            <div><span class="badge-role">${userRole}</span></div>
            <div>${userEmail}</div>
            <div>${userPass}</div>
            <div>${athletesHtml}</div>
            <div><strong style="color:${userStatus.toLowerCase() === 'aktif' ? '#2ec4b6' : '#e63946'};">${userStatus}</strong></div>
        `;
        container.appendChild(rowEl);
    });

    await updateStatsCounter();
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
        saveUsersData(users);

        try {
            await fetch(`/admin/users/update/${user.id}`, {
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
        } catch (e) {
            console.warn('Gagal sinkronisasi unlinked ke server:', e);
        }

        await renderTable();
    }
};

window.openAccountModal = async function(editIndex = null) {
    const modal = document.getElementById('accountModal');
    const titleEl = document.getElementById('accModalTitle');
    const accIdInput = document.getElementById('accId');
    const form = document.getElementById('accountForm');
    if (form) form.reset();

    if (editIndex !== null && editIndex !== '') {
        let users = await getUsersData();
        let user = users[editIndex];
        if (user) {
            if (accIdInput) accIdInput.value = user.id || editIndex;
            if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-user-pen"></i> Edit Akun';
            if (document.getElementById('accName')) document.getElementById('accName').value = user.namaLengkap || user.nama || user.name || '';
            if (document.getElementById('accUsername')) document.getElementById('accUsername').value = user.email || user.username || '';
            if (document.getElementById('accPassword')) document.getElementById('accPassword').value = user.password || '';
            if (document.getElementById('accRole')) document.getElementById('accRole').value = user.role || 'Admin';
            if (document.getElementById('accStatus')) document.getElementById('accStatus').value = user.status || 'Aktif';
        }
    } else {
        if (accIdInput) accIdInput.value = '';
        if (titleEl) titleEl.innerHTML = '<i class="fa-solid fa-user-gear"></i> Form Akun';
    }

    if (modal) openModalSafely(modal);
};

window.editAccount = function(index) { openAccountModal(index); };

// --- SIMPAN / EDIT AKUN TERINTEGRASI KE DATABASE SERVER LARAVEL ---
window.saveAccount = function(e) {
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

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(payload)
    })
    .then(async response => {
        let contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return response.json();
        } else {
            throw new Error("Server mengembalikan respons non-JSON.");
        }
    })
    .then(async data => {
        if (data && (data.success || data.status === 'success')) {
            alert('✅ Akun berhasil disimpan ke database server!');
            closeModal('accountModal');
            if (e && e.target && typeof e.target.reset === 'function') {
                e.target.reset();
            }
            if (typeof renderTable === 'function') await renderTable();
        } else {
            alert('⚠️ Gagal menyimpan akun ke server: ' + (data.message || 'Kesalahan tidak diketahui.'));
        }
    })
    .catch(error => {
        console.error('Error saat mengirim data ke backend:', error);
        alert('❌ Terjadi kesalahan koneksi ke server backend.');
    });

    return false;
};

window.deleteAccount = async function(index) {
    let users = await getUsersData();
    let user = users[index];
    if (!user) return;

    if (confirm('Yakin ingin menghapus akun ini?')) {
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
    const input = document.getElementById('accPassword');
    const icon = document.getElementById('modalEyeIcon');
    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
        if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
    } else {
        input.type = 'password';
        if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    }
};

// Header Lock Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const btnToggleLock = document.getElementById('btnToggleLock');
    if (btnToggleLock) {
        btnToggleLock.addEventListener('click', function() {
            const wrappers = document.querySelectorAll('.table-responsive');
            const tables = document.querySelectorAll('.matrix-table');

            const isCurrentlyLocked = wrappers.length > 0 && wrappers[0].classList.contains('is-locked');

            wrappers.forEach(el => {
                if (isCurrentlyLocked) {
                    el.classList.remove('is-locked');
                    el.style.maxHeight = 'none';
                    el.style.overflowY = 'visible';
                } else {
                    el.classList.add('is-locked');
                    el.style.maxHeight = '55vh';
                    el.style.overflowY = 'auto';
                }
            });

            tables.forEach(el => {
                if (isCurrentlyLocked) {
                    el.classList.remove('is-locked');
                } else {
                    el.classList.add('is-locked');
                }
            });

            if (isCurrentlyLocked) {
                btnToggleLock.innerHTML = '🔓 Header Terbuka';
                btnToggleLock.style.background = 'var(--c-alpa)';
            } else {
                btnToggleLock.innerHTML = '🔒 Header Terkunci';
                btnToggleLock.style.background = 'var(--c-hadir)';
            }
        });
    }
});
