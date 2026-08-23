<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sekolah Sepatu Roda - KILAT')</title>

    <!-- Google Fonts (Inter & Nunito) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS Utama & Kustom -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">

    <!-- Stack untuk penambahan CSS spesifik per halaman jika diperlukan -->
    @stack('styles')
</head>
<body>

    <script>
        // Inisialisasi tema aktif dari localStorage untuk menghindari kedipan (flash) saat halaman dimuat
        const savedTheme = localStorage.getItem('appTheme') || 'default';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <!-- Memanggil Komponen Sidebar -->
    @include('layouts.sidebar')

    <!-- Konten Utama Halaman -->
    <main class="main-content" id="mainContent">
        @yield('content')
    </main>

    <!-- Skrip Global untuk Scroll & Interaksi Latar Belakang -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mainContent = document.getElementById('mainContent');

            function updateBackgroundOnScroll() {
                if (!mainContent) return;
                const scrollableHeight = mainContent.scrollHeight - mainContent.clientHeight;
                let scrollPercent = scrollableHeight > 0 ? mainContent.scrollTop / scrollableHeight : 0;
                const hue = Math.round(scrollPercent * 360);

                const currentTheme = document.documentElement.getAttribute('data-theme');
                if (currentTheme === 'dark') {
                    document.documentElement.style.setProperty('--bg-color-1', `hsl(${hue}, 30%, 15%)`);
                    document.documentElement.style.setProperty('--bg-color-2', `hsl(${(hue + 45) % 360}, 40%, 12%)`);
                } else {
                    document.documentElement.style.setProperty('--bg-color-1', `hsl(${hue}, 55%, 86%)`);
                    document.documentElement.style.setProperty('--bg-color-2', `hsl(${(hue + 45) % 360}, 65%, 74%)`);
                }
            }

            if (mainContent) {
                mainContent.addEventListener('scroll', updateBackgroundOnScroll);
                window.addEventListener('resize', updateBackgroundOnScroll);
                updateBackgroundOnScroll();
            }
        });
    </script>

    <!-- Stack untuk penambahan JavaScript spesifik per halaman -->
    @stack('scripts')
</body>
</html>
