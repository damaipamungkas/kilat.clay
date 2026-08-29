document.addEventListener("DOMContentLoaded", () => {
    checkUserAuthentication();
    loadDynamicGallery();
    detectUserRoleAndPermissions();
    loadTestimonials();
    loadSavedCustomContent();
    initFAQ();
    initColorSlider();
    initSkateScrollbar();
    initTestimonialForm();
    initAdminCardEditor();
});

// --- 0. VALIDASI AKUN TERDAFTAR (KILAT_CURRENT_USER / USERS.BLADE) ---
function checkUserAuthentication() {
    let currentUser = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') ||
                                localStorage.getItem('kilat_user_data') ||
                                localStorage.getItem('KILAT_LOGGED_IN_USER') || 'null');

    // Jika berada di halaman testimoni tetapi belum login/terdaftar
    if (!currentUser && window.location.pathname.includes('testimoni')) {
        let registeredUsers = JSON.parse(localStorage.getItem('KILAT_USERS') || localStorage.getItem('users_data') || '[]');
        if (registeredUsers.length > 0) {
            currentUser = registeredUsers[0];
            localStorage.setItem('KILAT_CURRENT_USER', JSON.stringify(currentUser));
        } else {
            alert("⚠️ Anda harus memiliki akun terdaftar untuk mengakses halaman Testimoni!");
        }
    }
}

// --- 1. DETEKSI ROLE, PERMISSIONS & NAVBAR STATUS ---
function detectUserRoleAndPermissions() {
    let currentUser = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') || localStorage.getItem('kilat_user_data') || 'null');
    let rawRole = '';

    if (currentUser && currentUser.role) {
        rawRole = currentUser.role.toLowerCase();
    } else {
        rawRole = (localStorage.getItem('userRole') || localStorage.getItem('KILAT_ROLE') || 'Umum').toLowerCase();
    }

    let formattedRole = 'Umum';

    if (rawRole.includes('admin')) {
        formattedRole = 'ADMIN';
    } else if (rawRole.includes('coach') || rawRole.includes('pelatih')) {
        formattedRole = 'COACH';
    } else if (rawRole.includes('parent') || rawRole.includes('ortu') || rawRole.includes('orang tua')) {
        formattedRole = 'PARENT';
    }

    // Perbarui teks role di Navbar
    const roleTextEl = document.getElementById('roleText');
    if (roleTextEl) roleTextEl.innerText = formattedRole;

    // Perbarui badge tampilan ikon status di Navbar
    const roleBadge = document.getElementById('roleBadgeDisplay');
    if (roleBadge) {
        if (formattedRole === 'ADMIN') {
            roleBadge.innerHTML = `<i class="fa-solid fa-user-shield" style="color: var(--primary-color);"></i> <span>ADMIN</span>`;
        } else if (formattedRole === 'COACH') {
            roleBadge.innerHTML = `<i class="fa-solid fa-chalkboard-user" style="color: #2ecc71;"></i> <span>COACH</span>`;
        } else if (formattedRole === 'PARENT') {
            roleBadge.innerHTML = `<i class="fa-solid fa-user-group" style="color: #3498db;"></i> <span>PARENT</span>`;
        } else {
            roleBadge.innerHTML = `<i class="fa-solid fa-user"></i> <span>UMUM</span>`;
        }
    }
}

// --- 2. HAK SPESIAL ADMIN: TOMBOL EDIT PADA KARTU ---
function initAdminCardEditor() {
    let currentUser = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') || localStorage.getItem('kilat_user_data') || 'null');
    let rawRole = '';

    if (currentUser && currentUser.role) {
        rawRole = currentUser.role.toLowerCase();
    } else {
        rawRole = (localStorage.getItem('userRole') || localStorage.getItem('KILAT_ROLE') || 'Umum').toLowerCase();
    }

    const isAdmin = rawRole.includes('admin');
    const cards = document.querySelectorAll('.tech-card');

    cards.forEach((card) => {
        card.style.position = 'relative';

        if (isAdmin) {
            let editBtn = card.querySelector('.btn-admin-edit-card');
            if (!editBtn) {
                editBtn = document.createElement('button');
                editBtn.className = 'btn-admin-edit-card';
                editBtn.innerHTML = '<i class="fa-solid fa-pen"></i>';
                editBtn.style.cssText = 'position:absolute; top:15px; right:15px; background:var(--sidebar-bg); color:white; border:none; width:32px; height:32px; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; box-shadow:var(--clay-shadow-btn); z-index:10;';
                card.appendChild(editBtn);
            }

            editBtn.style.display = 'flex';

            editBtn.addEventListener('click', function(e) {
                e.stopPropagation();

                const titleEl = card.querySelector('.card-title, h1, h2, h3');
                const descEl = card.querySelector('.card-desc, p');

                let currentTitle = titleEl ? titleEl.innerText : '';
                let currentDesc = descEl ? descEl.innerText : '';

                let newTitle = prompt("✏️ Edit Judul Kartu:", currentTitle);
                if (newTitle !== null && titleEl) {
                    titleEl.innerText = newTitle;
                }

                let newDesc = prompt("✏️ Edit Deskripsi/Isi Kartu:", currentDesc);
                if (newDesc !== null && descEl) {
                    descEl.innerText = newDesc;
                }

                if (newTitle !== null || newDesc !== null) {
                    alert("✅ Perubahan teks berhasil diterapkan secara lokal!");
                }
            });
        }
    });
}

// --- 3. MODAL POP-UP EDITOR KUSTOM ---
let currentEditStorageKey = '';
let currentEditElementId = '';

function editSectionContent(sectionKey) {
    if (sectionKey === 'tentang') {
        currentEditElementId = 'dynamicTentangContent';
        currentEditStorageKey = 'KILAT_CUSTOM_TENTANG';
    } else if (sectionKey === 'kursus') {
        currentEditElementId = 'dynamicProgramContent';
        currentEditStorageKey = 'KILAT_CUSTOM_KURSUS';
    } else if (sectionKey === 'prosedur') {
        currentEditElementId = 'dynamicProsedurContent';
        currentEditStorageKey = 'KILAT_CUSTOM_PROSEDUR';
    }

    const targetEl = document.getElementById(currentEditElementId);
    if (targetEl) {
        document.getElementById('modalTextareaInput').value = targetEl.innerHTML;
        document.getElementById('customEditModal').classList.add('active');
    }
}

function closeCustomModal(isSave) {
    const modal = document.getElementById('customEditModal');
    if (modal) modal.classList.remove('active');

    if (isSave) {
        const newContent = document.getElementById('modalTextareaInput').value;
        const targetEl = document.getElementById(currentEditElementId);
        if (targetEl) {
            targetEl.innerHTML = newContent;
            localStorage.setItem(currentEditStorageKey, newContent);
            alert("Konten berhasil diperbarui!");
        }
    }
}

function loadSavedCustomContent() {
    const tentangSaved = localStorage.getItem('KILAT_CUSTOM_TENTANG');
    if (tentangSaved && document.getElementById('dynamicTentangContent')) {
        document.getElementById('dynamicTentangContent').innerHTML = tentangSaved;
    }

    const kursusSaved = localStorage.getItem('KILAT_CUSTOM_KURSUS');
    if (kursusSaved && document.getElementById('dynamicProgramContent')) {
        document.getElementById('dynamicProgramContent').innerHTML = kursusSaved;
    }

    const prosedurSaved = localStorage.getItem('KILAT_CUSTOM_PROSEDUR');
    if (prosedurSaved && document.getElementById('dynamicProsedurContent')) {
        document.getElementById('dynamicProsedurContent').innerHTML = prosedurSaved;
    }
}

// --- 4. GALERI DINAMIS & CAROUSEL (Terintegrasi Server public/images) ---
let galleryItems = [];
let currentSlide = 0;

function loadDynamicGallery() {
    const wrapper = document.getElementById('carousel-wrapper');
    if (!wrapper) return;

    // Ambil data dari variabel global server (jika ada), atau fallback ke localStorage & default
    const serverImages = (window.SERVER_GALLERY_IMAGES && window.SERVER_GALLERY_IMAGES.length > 0) ? window.SERVER_GALLERY_IMAGES : [];
    const customImages = JSON.parse(localStorage.getItem('KILAT_GALLERY_IMAGES')) ||
                         JSON.parse(localStorage.getItem('public_images_gallery')) ||
                         JSON.parse(localStorage.getItem('KILAT_CUSTOM_GALLERY')) || [];

    const defaultImages = [
        '1000887257.png', '1000887258.png', '1000887259.png', '1000887261.png', '1000887274.png'
    ];

    let activeImages = [];
    if (serverImages.length > 0) {
        activeImages = serverImages;
    } else if (customImages.length > 0) {
        activeImages = customImages;
    } else {
        activeImages = defaultImages;
    }

    wrapper.innerHTML = '';
    activeImages.forEach((imgSrc, index) => {
        const item = document.createElement('div');
        item.className = 'carousel-item';
        item.setAttribute('onclick', `jumpToSlide(${index})`);
        item.innerHTML = `<img src="${imgSrc}" alt="Galeri KILAT ${index + 1}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1565992441121-4367c2967103?auto=format&fit=crop&w=600&q=80'">`;
        wrapper.appendChild(item);
    });

    galleryItems = document.querySelectorAll('.carousel-item');
    currentSlide = Math.floor(galleryItems.length / 2);
    updateGallery();

    let startTouchX = 0;
    wrapper.addEventListener('touchstart', e => { startTouchX = e.touches[0].clientX; }, {passive: true});
    wrapper.addEventListener('touchend', e => {
        let endTouchX = e.changedTouches[0].clientX;
        if (startTouchX > endTouchX + 40) moveSlide(1);
        else if (startTouchX < endTouchX - 40) moveSlide(-1);
    });
}

function updateGallery() {
    if (!galleryItems || galleryItems.length === 0) return;
    galleryItems.forEach((item, index) => {
        item.className = 'carousel-item';
        let offset = index - currentSlide;

        if (offset === 0) item.classList.add('pos-center');
        else if (offset === -1) item.classList.add('pos-prev1');
        else if (offset === 1) item.classList.add('pos-next1');
        else if (offset === -2) item.classList.add('pos-prev2');
        else if (offset === 2) item.classList.add('pos-next2');
        else if (offset < -2) item.classList.add('pos-hidden-left');
        else if (offset > 2) item.classList.add('pos-hidden-right');
    });
}

function moveSlide(direction) {
    if (!galleryItems || galleryItems.length === 0) return;
    currentSlide += direction;
    if (currentSlide < 0) currentSlide = 0;
    if (currentSlide >= galleryItems.length) currentSlide = galleryItems.length - 1;
    updateGallery();
}

function jumpToSlide(index) {
    currentSlide = index;
    updateGallery();
}

// --- 5. TESTIMONIALS (Disinkronkan dengan inputan testimoni.blade) ---
function loadTestimonials() {
    const storedTesti = JSON.parse(localStorage.getItem('KILAT_TESTIMONIALS') ||
                                   localStorage.getItem('public_testimonials') ||
                                   localStorage.getItem('testimonials_data')) || [];

    const container = document.getElementById('dynamicTestimonialsContainer');
    if (!container) return;

    container.innerHTML = '';

    if (!storedTesti || storedTesti.length === 0) {
        container.innerHTML = '<div style="color:var(--text-muted); font-size:0.85rem; text-align:center; padding:15px; font-weight:700;">Belum ada ulasan atau testimoni yang dikirimkan.</div>';
        if(document.getElementById('testiAverageScore')) document.getElementById('testiAverageScore').innerText = '0.0';
        if(document.getElementById('testiCountText')) document.getElementById('testiCountText').innerText = 'Belum ada ulasan terverifikasi';
        if(document.getElementById('testiStarsContainer')) document.getElementById('testiStarsContainer').innerHTML = '<i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i><i class="fa-regular fa-star"></i>';
        return;
    }

    let totalScore = 0;
    storedTesti.forEach(t => {
        let ratingVal = Number(t.rating || t.stars || t.score || 5);
        totalScore += ratingVal;

        let starsHtml = '';
        for(let i=1; i<=5; i++) {
            if(i <= ratingVal) starsHtml += '<i class="fa-solid fa-star"></i>';
            else starsHtml += '<i class="fa-regular fa-star"></i>';
        }

        const item = document.createElement('div');
        item.className = 'testi-item';
        item.innerHTML = `
            <div class="testi-header">
                <span class="testi-name">${t.name || t.nama || 'Anonim'}</span>
                <span class="testi-stars">${starsHtml}</span>
            </div>
            <div class="testi-msg">"${t.message || t.msg || t.pesan || ''}"</div>
        `;
        container.appendChild(item);
    });

    let avg = (totalScore / storedTesti.length).toFixed(1);
    if(document.getElementById('testiAverageScore')) document.getElementById('testiAverageScore').innerText = avg;
    if(document.getElementById('testiCountText')) document.getElementById('testiCountText').innerHTML = `Berdasarkan akumulasi <strong>${storedTesti.length} ulasan member terverifikasi</strong>`;

    let summaryStarsHtml = '';
    let fullStars = Math.floor(avg);
    let hasHalf = (avg - fullStars) >= 0.5;
    for(let i=1; i<=5; i++) {
        if(i <= fullStars) summaryStarsHtml += '<i class="fa-solid fa-star"></i>';
        else if(i === fullStars + 1 && hasHalf) summaryStarsHtml += '<i class="fa-solid fa-star-half-stroke"></i>';
        else summaryStarsHtml += '<i class="fa-regular fa-star"></i>';
    }
    if(document.getElementById('testiStarsContainer')) document.getElementById('testiStarsContainer').innerHTML = summaryStarsHtml;
}

// --- 6. FAQ ACCORDION & SEARCH ---
function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        if(question) {
            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                faqItems.forEach(otherItem => {
                    otherItem.classList.remove('active');
                    const ans = otherItem.querySelector('.faq-answer');
                    if(ans) ans.style.maxHeight = null;
                });
                if (!isActive) {
                    item.classList.add('active');
                    const answer = item.querySelector('.faq-answer');
                    if(answer) answer.style.maxHeight = answer.scrollHeight + "px";
                }
            });
        }
    });

    const searchInput = document.getElementById('faq-search');
    const noResultMsg = document.getElementById('no-result');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase().trim();
            let visibleCount = 0;
            faqItems.forEach(item => {
                const textContent = item.textContent.toLowerCase();
                if (textContent.includes(keyword)) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                    item.classList.remove('active');
                    const ans = item.querySelector('.faq-answer');
                    if(ans) ans.style.maxHeight = null;
                }
            });
            if (noResultMsg) {
                noResultMsg.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    }
}

// --- 7. SLIDER WARNA HUE ---
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

// --- 8. CUSTOM SCROLLBAR SEPATU RODA ---
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

        newThumbTop = Math.max(0, Math.min(newThumbTop, maxThumbTop));
        skateThumb.style.top = `${newThumbTop}px`;

        const scrollPercent = newThumbTop / maxThumbTop;
        const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
        window.scrollTo(0, scrollPercent * scrollableHeight);
    }

    function onSkateDragEnd() {
        isSkateDragging = false;
        document.body.style.userSelect = '';
    }

    skateThumb.addEventListener('mousedown', (e) => onSkateDragStart(e.clientY));
    document.addEventListener('mousemove', (e) => onSkateDragMove(e.clientY));

    skateThumb.addEventListener('touchstart', (e) => {
        onSkateDragStart(e.touches[0].clientY);
        e.preventDefault();
    }, {passive: false});

    document.addEventListener('touchmove', (e) => {
        if (isSkateDragging) {
            onSkateDragMove(e.touches[0].clientY);
            e.preventDefault();
        }
    }, {passive: false});

    document.addEventListener('mouseup', onSkateDragEnd);
    document.addEventListener('touchend', onSkateDragEnd);

    window.dispatchEvent(new Event('scroll'));
}

// --- 9. FORM TESTIMONIAL, TOGGLE, AUTO-FILL NAMA & STAR RATING (1 AKUN 1 ULASAN) ---
function initTestimonialForm() {
    const anonimToggle = document.getElementById('anonimToggle');
    const inputNama = document.getElementById('nama');
    const submitBtn = document.querySelector('#testimoniForm button[type="submit"]') || document.querySelector('#testimoniForm .submit-btn');

    let currentUser = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') ||
                                localStorage.getItem('kilat_user_data') || 'null');

    if (!currentUser) {
        let registeredUsers = JSON.parse(localStorage.getItem('KILAT_USERS') || localStorage.getItem('users_data') || '[]');
        if (registeredUsers.length > 0) {
            currentUser = registeredUsers[0];
        }
    }

    const accountIdentifier = currentUser ? (currentUser.username || currentUser.email || currentUser.name || currentUser.nama || 'default_user') : 'default_user';

    if (inputNama && currentUser) {
        inputNama.value = currentUser.name || currentUser.nama || '';
    }

    let existingTesti = JSON.parse(localStorage.getItem('KILAT_TESTIMONIALS') ||
                                  localStorage.getItem('public_testimonials') ||
                                  localStorage.getItem('testimonials_data')) || [];

    let userExistingIndex = existingTesti.findIndex(t => (t.accountKey && t.accountKey === accountIdentifier) || (t.name === inputNama.value && inputNama.value !== ''));

    const stars = document.querySelectorAll('.stars i');
    const ratingDisplay = document.getElementById('ratingDisplay');
    let currentRating = 0;

    const ratingLabels = {
        1: "1 / 5 (Sangat Kurang)",
        2: "2 / 5 (Kurang)",
        3: "3 / 5 (Cukup)",
        4: "4 / 5 (Sangat Baik)",
        5: "5 / 5 (Sempurna)"
    };

    if (userExistingIndex !== -1) {
        const prevReview = existingTesti[userExistingIndex];
        currentRating = Number(prevReview.rating || 5);

        if (document.getElementById('pesan')) document.getElementById('pesan').value = prevReview.message || '';
        if (document.getElementById('message')) document.getElementById('message').value = prevReview.message || '';

        stars.forEach(s => {
            const starVal = parseInt(s.getAttribute('data-val'));
            if (starVal <= currentRating) s.classList.add('active');
        });

        if (ratingDisplay && ratingLabels[currentRating]) {
            ratingDisplay.innerText = ratingLabels[currentRating];
            ratingDisplay.style.color = "var(--gold)";
        }

        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> UPDATE / PERBARUI ULASAN';
        }
    }

    if (anonimToggle && inputNama) {
        anonimToggle.addEventListener('change', function() {
            if (this.checked) {
                inputNama.disabled = true;
                inputNama.value = "Anonim";
                inputNama.style.opacity = "0.6";
                inputNama.style.cursor = "not-allowed";
                inputNama.style.boxShadow = "var(--clay-shadow-inset)";
            } else {
                inputNama.disabled = false;
                inputNama.value = currentUser ? (currentUser.name || currentUser.nama || "") : "";
                inputNama.style.opacity = "1";
                inputNama.style.cursor = "text";
                inputNama.focus();
            }
        });
    }

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const val = parseInt(this.getAttribute('data-val'));
            currentRating = val;

            stars.forEach(s => {
                const starVal = parseInt(s.getAttribute('data-val'));
                if (starVal <= val) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });

            if (ratingDisplay) {
                ratingDisplay.innerText = ratingLabels[val];
                ratingDisplay.style.color = "var(--gold)";
                ratingDisplay.style.textShadow = "1px 1px 0px #ffffff, 2px 2px 4px rgba(245, 158, 11, 0.4)";
            }
        });
    });

    const form = document.getElementById('testimoniForm');
    const overlay = document.getElementById('successOverlay');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (currentRating === 0) {
                alert("Peringatan: Harap berikan rating bintang terlebih dahulu!");
                return;
            }

            const nameVal = inputNama ? inputNama.value : "Anonim";
            const messageVal = document.getElementById('pesan') ? document.getElementById('pesan').value : (document.getElementById('message') ? document.getElementById('message').value : '');

            let updatedTesti = JSON.parse(localStorage.getItem('KILAT_TESTIMONIALS') ||
                                          localStorage.getItem('public_testimonials') ||
                                          localStorage.getItem('testimonials_data')) || [];

            let targetIndex = updatedTesti.findIndex(t => (t.accountKey && t.accountKey === accountIdentifier) || (t.name === nameVal && nameVal !== 'Anonim'));

            const reviewPayload = {
                accountKey: accountIdentifier,
                name: nameVal,
                rating: currentRating,
                message: messageVal,
                date: new Date().toISOString()
            };

            if (targetIndex !== -1) {
                updatedTesti[targetIndex] = reviewPayload;
            } else {
                updatedTesti.unshift(reviewPayload);
            }

            localStorage.setItem('KILAT_TESTIMONIALS', JSON.stringify(updatedTesti));
            localStorage.setItem('public_testimonials', JSON.stringify(updatedTesti));

            if (overlay) overlay.classList.add('show');
            loadTestimonials();
        });
    }
}

function closeSuccess() {
    const overlay = document.getElementById('successOverlay');
    const form = document.getElementById('testimoniForm');
    const stars = document.querySelectorAll('.stars i');
    const ratingDisplay = document.getElementById('ratingDisplay');
    const inputNama = document.getElementById('nama');

    if (overlay) overlay.classList.remove('show');
    if (form) form.reset();
    if (stars) stars.forEach(s => s.classList.remove('active'));

    if (ratingDisplay) {
        ratingDisplay.innerText = "0 / 5";
        ratingDisplay.style.color = "var(--text-muted)";
        ratingDisplay.style.textShadow = "none";
    }

    if (inputNama) {
        inputNama.disabled = false;
        inputNama.style.opacity = "1";
        inputNama.style.cursor = "text";
        let currentUser = JSON.parse(localStorage.getItem('KILAT_CURRENT_USER') || localStorage.getItem('kilat_user_data') || 'null');
        if (currentUser && (currentUser.name || currentUser.nama)) {
            inputNama.value = currentUser.name || currentUser.nama;
        }
    }
}
