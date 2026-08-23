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
        /* CSS Khusus Halaman Pembaruan Sistem */
        .development-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 120px);
            text-align: center;
            padding: 40px 20px;
        }
        .development-card {
            background: var(--clay-purple, #e2d9fc);
            border-radius: 35px;
            padding: 50px 40px;
            box-shadow: var(--clay-shadow-card, 12px 12px 24px rgba(163, 145, 219, 0.4), -12px -12px 24px #ffffff);
            max-width: 550px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        .development-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: var(--bg-main, #f3f0ff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: var(--sidebar-bg, #6366f1);
            box-shadow: var(--clay-shadow-inset, inset 6px 6px 12px rgba(163, 145, 219, 0.3), inset -6px -6px 12px #ffffff);
            margin-bottom: 10px;
        }
        .development-card h2 {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--text-dark, #1e1b4b);
            margin: 0;
        }
        .development-card p {
            font-size: 1rem;
            font-weight: 800;
            color: var(--text-gray, #64748b);
            line-height: 1.6;
            margin: 0 0 15px 0;
        }
        .btn-back-home {
            background: var(--sidebar-bg, #6366f1);
            color: white;
            padding: 14px 28px;
            border-radius: 20px;
            font-weight: 900;
            font-size: 1rem;
            text-decoration: none;
            box-shadow: var(--clay-shadow-btn, 6px 6px 12px rgba(99, 102, 241, 0.4), -6px -6px 12px rgba(255, 255, 255, 0.8));
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-back-home:hover {
            transform: scale(1.03);
            filter: brightness(0.95);
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

        <!-- KONTEN PEMBERITAHUAN PEMBARUAN SISTEM -->
        <div class="development-container">
            <div class="development-card">
                <div class="development-icon">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <h2>Optimalisasi Sistem</h2>
                <p>Modul Billing SPP sedang dalam tahap integrasi dan pembaruan arsitektur guna penyelarasan data keuangan yang lebih terpusat. Silakan kembali ke beranda admin untuk melanjutkan aktivitas Anda.</p>
                <a href="{{ route('admin.index') }}" class="btn-back-home">
                    <i class="fa-solid fa-house"></i> Kembali ke Beranda Admin
                </a>
            </div>
        </div>
    </main>

    <!-- JS Terpisah -->
    <script src="{{ asset('js/beranda_admin.js') }}"></script>
</body>
</html>
