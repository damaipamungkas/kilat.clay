<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Feedback - KILAT</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Terpisah & Dinamis dengan ID mainStylesheet -->
    <link rel="stylesheet" id="mainStylesheet" href="{{ asset('css/public.css') }}">
</head>
<body>

<!-- Script Validasi Akun Terdaftar & Sinkronisasi Tema Global -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // 1. Validasi Akun Terdaftar
        let currentUser = JSON.parse(
            localStorage.getItem('KILAT_CURRENT_USER') ||
            localStorage.getItem('kilat_user_data') ||
            localStorage.getItem('KILAT_LOGGED_IN_USER') || 'null'
        );

        if (!currentUser) {
            let registeredUsers = JSON.parse(
                localStorage.getItem('KILAT_USERS') ||
                localStorage.getItem('manageUsersData') ||
                localStorage.getItem('users_data') || '[]'
            );

            if (registeredUsers.length > 0) {
                // Ambil akun pertama yang terdaftar jika belum ada session aktif
                currentUser = registeredUsers[0];
                localStorage.setItem('KILAT_CURRENT_USER', JSON.stringify(currentUser));
            } else {
                alert("⚠️ Anda harus memiliki akun terdaftar untuk mengakses Portal Feedback!");
                window.location.href = "{{ route('register') }}"; // Redirect ke halaman register
                return;
            }
        }

        // 2. Sinkronisasi Tema Global
        let savedFolder = localStorage.getItem('KILAT_CSS_FOLDER') || 'css';
        const linkTag = document.getElementById('mainStylesheet');
        if (linkTag) {
            let currentHref = linkTag.getAttribute('href');
            let fileName = currentHref.split('/').pop();
            linkTag.setAttribute('href', `{{ asset('') }}${savedFolder}/${fileName}`);
        }

        // 3. Inisialisasi Penanganan Form Feedback
        initFeedbackSubmitHandler(currentUser);
    });

    function initFeedbackSubmitHandler(user) {
        const feedbackForm = document.getElementById('feedbackForm');
        if (!feedbackForm) return;

        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const subjectInput = feedbackForm.querySelector('input[type="text"]');
            const messageInput = feedbackForm.querySelector('textarea');

            if (!subjectInput || !messageInput) return;

            const subject = subjectInput.value.trim();
            const message = messageInput.value.trim();
            const authorName = user.namaLengkap || user.name || user.nama || user.username || 'Pengguna Terdaftar';

            // Ambil data feedback yang sudah ada di localStorage
            let existingFeedbacks = JSON.parse(
                localStorage.getItem('KILAT_FEEDBACKS') ||
                localStorage.getItem('public_feedbacks') ||
                localStorage.getItem('feedback_data') || '[]'
            );

            // Buat objek feedback baru
            const newFeedback = {
                accountKey: user.username || user.email || authorName,
                name: authorName,
                subject: subject,
                message: message,
                date: new Date().toISOString().split('T')[0]
            };

            // Simpan ke array feedback
            existingFeedbacks.unshift(newFeedback);

            localStorage.setItem('KILAT_FEEDBACKS', JSON.stringify(existingFeedbacks));
            localStorage.setItem('public_feedbacks', JSON.stringify(existingFeedbacks));

            alert("✅ Kritik & Saran berhasil dikirimkan! Terima kasih atas umpan balik Anda.");
            feedbackForm.reset();
        });
    }
</script>

<div class="container">

    <header class="hero">
        <h1>PORTAL FEEDBACK<br>KILAT</h1>
        @include('layouts.divider')
    </header>

    <!-- Slider Warna (Hue) -->
    @include('layouts.slider')
    @include('layouts.icon-menu')

    <div class="tech-card">
        <div class="card-bg" style="gap: 25px;">
            <h2 class="card-title"><i class="fa-solid fa-satellite-dish"></i> Kanal Kritik & Saran</h2>
            <form id="feedbackForm" style="display: flex; flex-direction: column; gap: 20px; width: 100%;">
                <div class="form-group">
                    <label>SUBJEK</label>
                    <input type="text" class="sci-fi-input" placeholder="Contoh: Fasilitas Latihan..." required>
                </div>
                <div class="form-group">
                    <label>PESAN MASUKAN</label>
                    <textarea class="sci-fi-input" placeholder="Tuliskan masukan atau saran Anda untuk KILAT..." required></textarea>
                </div>
                <button type="submit" class="btn-neon btn-full">KIRIM PESAN <i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <div class="bottom-actions">
        <a href="{{ route('home') }}">
            <button class="btn-neon btn-full"><i class="fa-solid fa-arrow-left"></i> KEMBALI KE BERANDA</button>
        </a>
    </div>

    <footer class="footer">
        <div>Kanal Aspirasi Aktif</div>
            @include('layouts.footer')
        <div>Community Engagement</div>
    </footer>
</div>

<div class="skate-scroll-track" id="skateTrack"></div>
<div class="skate-scroll-thumb" id="skateThumb" title="Tarik untuk menggulir halaman"></div>

<!-- JS Terpisah -->
<script src="{{ asset('js/public.js') }}"></script>

</body>
</html>
