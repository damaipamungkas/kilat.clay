<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Bantuan & FAQ - KILAT</title>

    <!-- Font & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/public.css') }}">
    <style>
        .faq-admin-actions { display: flex; gap: 8px; margin-top: 10px; justify-content: flex-end; }
        .btn-faq-edit { background: #3b82f6; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 0.8rem; }
        .btn-faq-save { background: #22c55e; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 0.8rem; display: none; }

        /* Perbaikan: Ukuran kotak editor pertanyaan memenuhi lebar teks/kontainer */
        .faq-q-text { width: 100%; display: block; }
        .faq-edit-input { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; font-family: 'Nunito', sans-serif; box-sizing: border-box; margin-bottom: 5px; background: #fff; color: #333; display: block; }
        textarea.faq-edit-input { resize: vertical; min-height: 70px; }

        /* Styling Floating Chat AI */
        .ai-chat-float-btn { position: fixed; bottom: 25px; right: 25px; background: linear-gradient(135deg, #6366f1, #a855f7); color: #fff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; cursor: pointer; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4); z-index: 1000; transition: transform 0.3s ease; }
        .ai-chat-float-btn:hover { transform: scale(1.1); }
        .ai-chat-modal { position: fixed; bottom: 95px; right: 25px; width: 350px; max-width: 90vw; height: 480px; background: #ffffff; border-radius: 16px; box-shadow: 0 12px 35px rgba(0,0,0,0.2); display: flex; flex-direction: column; overflow: hidden; z-index: 1000; transform: translateY(20px); opacity: 0; pointer-events: none; transition: all 0.3s ease; }
        .ai-chat-modal.active { transform: translateY(0); opacity: 1; pointer-events: auto; }
        .ai-chat-header { background: linear-gradient(135deg, #6366f1, #a855f7); color: #fff; padding: 15px; display: flex; align-items: center; justify-content: space-between; font-weight: bold; }
        .ai-chat-header button { background: none; border: none; color: #fff; font-size: 1.1rem; cursor: pointer; }
        .ai-chat-body { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background: #f8fafc; font-family: 'Nunito', sans-serif; }
        .ai-message { padding: 10px 14px; border-radius: 12px; font-size: 0.9rem; max-width: 80%; line-height: 1.4; word-break: break-word; }
        .ai-message.bot { background: #e2e8f0; color: #1e293b; align-self: flex-start; border-bottom-left-radius: 2px; }
        .ai-message.user { background: #6366f1; color: #ffffff; align-self: flex-end; border-bottom-right-radius: 2px; }
        .ai-chat-footer { padding: 12px; background: #fff; border-top: 1px solid #e2e8f0; display: flex; gap: 8px; }
        .ai-chat-input { flex: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; outline: none; }
        .ai-chat-send { background: #6366f1; color: #fff; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .ai-chat-send:hover { background: #4f46e5; }
    </style>
</head>
<body>

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

        // Terapkan pesan sambutan awal dari pengaturan setting jika ada
        const savedWelcomeMsg = localStorage.getItem('KILAT_AI_WELCOME_MSG');
        const botWelcomeEl = document.getElementById('aiBotWelcomeText');
        if (savedWelcomeMsg && botWelcomeEl) {
            botWelcomeEl.innerText = savedWelcomeMsg;
        }
    });
</script>

<div class="container">

    @include('layouts.icon-menu')

    <header class="hero">
        <h1>PUSAT BANTUAN<br>& FAQ</h1>
        @include('layouts.divider')
        <p class="text-bold-muted mb-4">Temukan jawaban dari pertanyaan yang paling sering diajukan mengenai program latihan, pendaftaran, dan fasilitas KILAT.</p>
    </header>

    @include('layouts.slider')

    <div class="tech-card">
        <div class="card-bg pastel-blue gap-4">
            <div class="search-container w-100">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="faq-search" class="search-input" placeholder="Ketik kata kunci pertanyaan... (contoh: jadwal, biaya, umur)">
            </div>

            <div class="no-result" id="no-result">
                Sistem tidak menemukan kecocokan data. Silakan gunakan kata kunci lain.
            </div>

            <div class="faq-wrapper" id="faq-wrapper">

                <!-- FAQ ITEM 1 -->
                <div class="faq-item" data-faq-id="1">
                    <div class="faq-question">
                        <span class="faq-q-text">Berapa usia minimal untuk bergabung dengan KILAT?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="answer-content">
                            <span class="faq-a-text">Usia minimal yang disarankan untuk bergabung adalah <span class="highlight">4 tahun</span>. Pada usia ini, motorik dan keseimbangan anak umumnya sudah siap untuk menerima materi dasar pengenalan <em>inline skate</em>.</span>
                        </div>
                    </div>
                    <div class="faq-admin-actions admin-container" style="display: none;">
                        <button type="button" class="btn-faq-edit" onclick="toggleEditFaq(this)">Edit FAQ</button>
                        <button type="button" class="btn-faq-save" onclick="saveFaqData(this)">Simpan</button>
                    </div>
                </div>

                <!-- FAQ ITEM 2 -->
                <div class="faq-item" data-faq-id="2">
                    <div class="faq-question">
                        <span class="faq-q-text">Apakah peralatan (sepatu roda & helm) harus beli sendiri?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="answer-content">
                            <span class="faq-a-text">Untuk sesi <span class="highlight">Trial (Percobaan)</span>, kami menyediakan penyewaan alat lengkap. Namun, jika sudah resmi mendaftar sebagai <em>Member</em>, diwajibkan untuk memiliki peralatan sendiri demi kenyamanan dan higienitas atlet. Kami juga menjual peralatan berstandar di lokasi latihan.</span>
                        </div>
                    </div>
                    <div class="faq-admin-actions admin-container" style="display: none;">
                        <button type="button" class="btn-faq-edit" onclick="toggleEditFaq(this)">Edit FAQ</button>
                        <button type="button" class="btn-faq-save" onclick="saveFaqData(this)">Simpan</button>
                    </div>
                </div>

                <!-- FAQ ITEM 3 -->
                <div class="faq-item" data-faq-id="3">
                    <div class="faq-question">
                        <span class="faq-q-text">Di mana lokasi dan jadwal latihan rutin diadakan?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="answer-content">
                            <span class="faq-a-text">Latihan rutin kami laksanakan di area <span class="highlight">GOR Jayabaya Kota Kediri</span> dan <span class="highlight">Simpang Lima Gumul (SLG)</span>. Jadwal spesifik bervariasi bergantung pada kelas (Pemula/Menengah/Ahli), umumnya dilaksanakan pada sore hari (Rabu & Jumat) serta pagi hari di akhir pekan.</span>
                        </div>
                    </div>
                    <div class="faq-admin-actions admin-container" style="display: none;">
                        <button type="button" class="btn-faq-edit" onclick="toggleEditFaq(this)">Edit FAQ</button>
                        <button type="button" class="btn-faq-save" onclick="saveFaqData(this)">Simpan</button>
                    </div>
                </div>

                <!-- FAQ ITEM 4 -->
                <div class="faq-item" data-faq-id="4">
                    <div class="faq-question">
                        <span class="faq-q-text">Bagaimana prosedur pembayaran iuran bulanan?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="answer-content">
                            <span class="faq-a-text">Iuran dibayarkan setiap tanggal 1 hingga 5 pada awal bulan. Pembayaran dapat dilakukan secara <span class="highlight">Cash (Tunai)</span> kepada admin di lokasi, atau melalui transfer via Bank BCA, Mandiri, dan QRIS (OVO/Dana/Gopay).</span>
                        </div>
                    </div>
                    <div class="faq-admin-actions admin-container" style="display: none;">
                        <button type="button" class="btn-faq-edit" onclick="toggleEditFaq(this)">Edit FAQ</button>
                        <button type="button" class="btn-faq-save" onclick="saveFaqData(this)">Simpan</button>
                    </div>
                </div>

                <!-- FAQ ITEM 5 -->
                <div class="faq-item" data-faq-id="5">
                    <div class="faq-question">
                        <span class="faq-q-text">Apakah ada batas maksimal usia untuk ikut kursus?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="answer-content">
                            <span class="faq-a-text"><span class="highlight">Tidak ada batas maksimal usia.</span> Kami juga menyediakan kelas reguler untuk orang dewasa (kategori <em>Urban</em> & <em>Fitness</em>) yang ingin sekadar menjadikan <em>inline skate</em> sebagai hobi pembakar kalori yang menyenangkan.</span>
                        </div>
                    </div>
                    <div class="faq-admin-actions admin-container" style="display: none;">
                        <button type="button" class="btn-faq-edit" onclick="toggleEditFaq(this)">Edit FAQ</button>
                        <button type="button" class="btn-faq-save" onclick="saveFaqData(this)">Simpan</button>
                    </div>
                </div>

                <!-- FAQ ITEM 6 -->
                <div class="faq-item" data-faq-id="6">
                    <div class="faq-question">
                        <span class="faq-q-text">Berapa lama rata-rata waktu yang dibutuhkan untuk bisa meluncur?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <div class="answer-content">
                            <span class="faq-a-text">Perkembangan setiap individu berbeda-beda. Namun rata-rata anak-anak maupun dewasa sudah mampu berdiri seimbang dan meluncur dasar setelah <span class="highlight">4 hingga 8 kali sesi pertemuan</span> (kurang lebih 1 bulan latihan rutin).</span>
                        </div>
                    </div>
                    <div class="faq-admin-actions admin-container" style="display: none;">
                        <button type="button" class="btn-faq-edit" onclick="toggleEditFaq(this)">Edit FAQ</button>
                        <button type="button" class="btn-faq-save" onclick="saveFaqData(this)">Simpan</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="bottom-actions">
        <a href="{{ route('home') }}" class="btn-neon btn-full text-decoration-none text-center">
            <i class="fa-solid fa-arrow-left"></i> KEMBALI KE BERANDA
        </a>
    </div>
    <footer class="footer">
        <div>Layanan Informasi Resmi</div>
            @include('layouts.footer')
        <div>Pusat Bantuan & Dukungan</div>
    </footer>

</div>

<!-- Komponen Floating Chat AI Gemini -->
<div class="ai-chat-float-btn" onclick="toggleAiChat()" title="Tanya Asisten AI KILAT">
    <i class="fa-solid fa-robot"></i>
</div>

<div class="ai-chat-modal" id="aiChatModal">
    <div class="ai-chat-header">
        <span>⚡ Asisten AI KILAT</span>
        <button onclick="toggleAiChat()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="ai-chat-body" id="aiChatBody">
        <div class="ai-message bot" id="aiBotWelcomeText">Halo! Saya Asisten AI KILAT. Ada yang bisa saya bantu terkait jadwal latihan, biaya, atau informasi seputar sekolah sepatu roda KILAT?</div>
    </div>
    <div class="ai-chat-footer">
        <input type="text" id="aiChatInput" class="ai-chat-input" placeholder="Tulis pertanyaan..." onkeypress="handleAiKeyPress(event)">
        <button type="button" class="ai-chat-send" onclick="sendAiMessage()"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk menggulir halaman"></div>

<!-- JS Bawaan & Fitur Interaktif FAQ -->
<script src="{{ asset('js/public.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        checkAndApplyAdminPermissions();
        loadSavedFaqFromStorage();
        initFaqAccordion();
        initFaqSearch();
    });

    // --- Cek Hak Akses Admin ---
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
            document.querySelectorAll('.admin-container').forEach(el => {
                el.style.display = 'flex';
            });
        }
    }

    // --- Fitur Edit & Simpan FAQ ---
    function toggleEditFaq(btn) {
        const item = btn.closest('.faq-item');
        const saveBtn = item.querySelector('.btn-faq-save');
        const qTextEl = item.querySelector('.faq-q-text');
        const aTextEl = item.querySelector('.faq-a-text');

        const currentQ = qTextEl.innerText.trim();
        const currentA = aTextEl.innerHTML.trim();

        qTextEl.innerHTML = `<input type="text" class="faq-edit-input" value="${currentQ}">`;
        aTextEl.innerHTML = `<textarea class="faq-edit-input">${currentA}</textarea>`;

        btn.style.display = 'none';
        saveBtn.style.display = 'inline-block';
    }

    function saveFaqData(btn) {
        const item = btn.closest('.faq-item');
        const editBtn = item.querySelector('.btn-faq-edit');
        const qTextEl = item.querySelector('.faq-q-text');
        const aTextEl = item.querySelector('.faq-a-text');

        const qInput = qTextEl.querySelector('input');
        const aInput = aTextEl.querySelector('textarea');

        if (qInput && aInput) {
            qTextEl.innerText = qInput.value.trim();
            aTextEl.innerHTML = aInput.value.trim();
        }

        btn.style.display = 'none';
        editBtn.style.display = 'inline-block';

        persistFaqDataToStorage();
        alert('✅ Perubahan FAQ berhasil disimpan!');
    }

    function persistFaqDataToStorage() {
        const faqData = {};
        document.querySelectorAll('.faq-item').forEach((item, idx) => {
            const faqId = item.getAttribute('data-faq-id') || idx;
            const question = item.querySelector('.faq-q-text').innerText.trim();
            const answer = item.querySelector('.faq-a-text').innerHTML.trim();
            faqData[faqId] = { question, answer };
        });
        localStorage.setItem('KILAT_FAQ_CUSTOM_DATA', JSON.stringify(faqData));
    }

    function loadSavedFaqFromStorage() {
        const saved = JSON.parse(localStorage.getItem('KILAT_FAQ_CUSTOM_DATA') || '{}');
        if (Object.keys(saved).length === 0) return;

        document.querySelectorAll('.faq-item').forEach((item, idx) => {
            const faqId = item.getAttribute('data-faq-id') || idx;
            const data = saved[faqId];
            if (data) {
                const qTextEl = item.querySelector('.faq-q-text');
                const aTextEl = item.querySelector('.faq-a-text');
                if (qTextEl) qTextEl.innerText = data.question;
                if (aTextEl) aTextEl.innerHTML = data.answer;
            }
        });
    }

    // --- Accordion & Search Functionality ---
    function initFaqAccordion() {
        document.querySelectorAll('.faq-question').forEach(q => {
            q.addEventListener('click', function(e) {
                // Jangan toggle accordion jika klik sedang dalam mode edit input
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

                const item = this.parentElement;
                const isActive = item.classList.contains('active');

                document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });
    }

    function initFaqSearch() {
        const searchInput = document.getElementById('faq-search');
        const noResult = document.getElementById('no-result');

        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('.faq-item');
            let matchCount = 0;

            items.forEach(item => {
                const qText = item.querySelector('.faq-q-text').innerText.toLowerCase();
                const aText = item.querySelector('.faq-a-text').innerText.toLowerCase();

                if (qText.includes(keyword) || aText.includes(keyword)) {
                    item.style.display = 'block';
                    matchCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            if (noResult) {
                noResult.style.display = (matchCount === 0 && keyword !== '') ? 'block' : 'none';
            }
        });
    }

    // --- Integrasi Chat AI Gemini ---
    function toggleAiChat() {
        const modal = document.getElementById('aiChatModal');
        modal.classList.toggle('active');
    }

    function handleAiKeyPress(e) {
        if (e.key === 'Enter') {
            sendAiMessage();
        }
    }

    async function sendAiMessage() {
        const inputEl = document.getElementById('aiChatInput');
        const chatBody = document.getElementById('aiChatBody');
        const userMsg = inputEl.value.trim();

        if (!userMsg) return;

        chatBody.innerHTML += `<div class="ai-message user">${escapeHtml(userMsg)}</div>`;
        inputEl.value = '';
        chatBody.scrollTop = chatBody.scrollHeight;

        const loadingId = 'loading_' + Date.now();
        chatBody.innerHTML += `<div class="ai-message bot" id="${loadingId}">Sedang mengetik...</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;

        try {
            // 1. Ambil FAQ Context yang aktif di halaman
            let faqContext = "";
            document.querySelectorAll('.faq-item').forEach(item => {
                const qTextEl = item.querySelector('.faq-q-text');
                const aTextEl = item.querySelector('.faq-a-text');

                const q = qTextEl ? qTextEl.textContent.trim() : '';
                const a = aTextEl ? aTextEl.textContent.trim() : '';
                if(q && a) faqContext += `- Q: ${q}\n  A: ${a}\n`;
            });

            // 2. Ambil Aturan Khusus (Persona/Rules) dan Basis Pengetahuan (Knowledge Base) yang diset di halaman Setting
            const customRules = localStorage.getItem('KILAT_AI_SYSTEM_RULES') || "Selalu jawab dengan ramah dan sopan.";
            const knowledgeBase = localStorage.getItem('KILAT_AI_KNOWLEDGE_BASE') || "";
            const customInstruction = localStorage.getItem('KILAT_AI_CUSTOM_INSTRUCTION') || "Semangat meluncur bersama KILAT!";

            // 3. Masukkan API Key Gemini dengan aman dari environment Laravel
            const apiKey = "{{ config('services.google.api_key') ?? env('GOOGLE_API_KEY') }}";

            // 4. Susun prompt lengkap dengan menyertakan aturan setting dan knowledge base secara dinamis
            const prompt = `Anda adalah asisten AI resmi untuk "KILAT" (Kediri Inline Skate School).

ATURAN KHUSUS / PERSONA AI:
${customRules}

BASIS PENGETAHUAN TAMBAHAN (KNOWLEDGE BASE / DOKUMEN / RIWAYAT CHAT):
${knowledgeBase ? knowledgeBase : "(Tidak ada tambahan pengetahuan khusus)"}

INFORMASI FAQ UTAMA:
${faqContext}

TANDA TANGAN / FOOTER / INSTRUKSI TAMBAHAN DI AKHIR PESAN:
${customInstruction}

Pertanyaan Pengguna: ${userMsg}`;

            // Menggunakan gemini-3.6-flash
            const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=${apiKey}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    contents: [{ parts: [{ text: prompt }] }]
                })
            });

            const data = await response.json();

            let botReply = "Maaf, terjadi kendala saat memproses jawaban.";
            if (data.candidates && data.candidates[0]?.content?.parts?.[0]?.text) {
                botReply = data.candidates[0].content.parts[0].text;
            } else if (data.error) {
                botReply = "Error dari API: " + (data.error.message || "Periksa kembali API Key Anda.");
            }

            document.getElementById(loadingId).remove();
            chatBody.innerHTML += `<div class="ai-message bot">${escapeHtml(botReply)}</div>`;
        } catch (error) {
            document.getElementById(loadingId).remove();
            chatBody.innerHTML += `<div class="ai-message bot">Maaf, terjadi kesalahan koneksi jaringan ke sistem AI.</div>`;
        }

        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>
</body>
</html>
