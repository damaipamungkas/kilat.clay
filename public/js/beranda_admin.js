document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    if (!sidebar || !toggleBtn) {
        console.error("Elemen #sidebar atau #toggleBtn tidak ditemukan di DOM.");
        return;
    }

    // Fungsi untuk memperbarui posisi tombol atau kelas jika diperlukan
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
    }

    // 1. Toggle via Tombol
    toggleBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSidebar();
    });

    // 2. Deteksi Swipe Kiri/Kanan pada Layar Touch
    let startX = 0;
    let startY = 0;

    window.addEventListener('touchstart', function (e) {
        if (e.touches && e.touches.length > 0) {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }
    }, { passive: true });

    window.addEventListener('touchend', function (e) {
        if (!e.changedTouches || e.changedTouches.length === 0) return;

        let endX = e.changedTouches[0].clientX;
        let endY = e.changedTouches[0].clientY;

        let diffX = startX - endX; // Positif = swipe ke kiri, Negatif = swipe ke kanan
        let diffY = Math.abs(startY - endY);

        if (diffY < 50) { // Hanya proses jika gerakan mendatar
            // Swipe Kiri -> Sembunyikan Sidebar
            if (diffX > 50 && !sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
            }
            // Swipe Kanan -> Tampilkan Sidebar
            else if (diffX < -50 && sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
            }
        }
    }, { passive: true });

    // 3. Tutup sidebar jika klik di luar area sidebar pada layar kecil (opsional)
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && !sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
            }
        }
    });
});
