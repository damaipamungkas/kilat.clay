// --- INISIALISASI SLIDER WARNA HUE ---
function initColorSlider() {
    const cTrack = document.getElementById('colorTrack');
    const cThumb = document.getElementById('colorThumb');
    if (!cTrack || !cThumb) return;

    let isColorDragging = false;

    function updateColorSlider(percent, save = false) {
        percent = Math.max(0, Math.min(percent, 100));
        cThumb.style.left = `${percent}%`;
        const hue = Math.round((percent / 100) * 360);

        // Terapkan warna gradasi secara dinamis ke variabel CSS root
        document.documentElement.style.setProperty('--bg-color-1', `hsl(${hue}, 55%, 86%)`);
        document.documentElement.style.setProperty('--bg-color-2', `hsl(${(hue + 45) % 360}, 65%, 74%)`);

        // Simpan preferensi secara permanen
        if (save) {
            localStorage.setItem('themeHuePercent', percent);
        }
    }

    // Muat nilai yang tersimpan sebelumnya (default 50 jika belum ada)
    const savedPercent = localStorage.getItem('themeHuePercent') !== null ? parseFloat(localStorage.getItem('themeHuePercent')) : 50;
    updateColorSlider(savedPercent, false);

    function moveColorThumb(e) {
        const rect = cTrack.getBoundingClientRect();
        const clientX = e.clientX !== undefined ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
        let x = clientX - rect.left;
        updateColorSlider((x / rect.width) * 100, true);
    }

    // Event Listener untuk Mouse
    cTrack.addEventListener('mousedown', (e) => {
        isColorDragging = true;
        moveColorThumb(e);
    });
    document.addEventListener('mouseup', () => {
        isColorDragging = false;
    });
    document.addEventListener('mousemove', (e) => {
        if (isColorDragging) moveColorThumb(e);
    });

    // Event Listener untuk Perangkat Sentuh (Mobile/HP)
    cTrack.addEventListener('touchstart', (e) => {
        isColorDragging = true;
        moveColorThumb(e);
    }, { passive: true });
    document.addEventListener('touchend', () => {
        isColorDragging = false;
    });
    document.addEventListener('touchmove', (e) => {
        if (isColorDragging) moveColorThumb(e);
    }, { passive: true });
}

// Pastikan fungsi dipanggil setelah dokumen selesai dimuat
document.addEventListener('DOMContentLoaded', () => {
    initColorSlider();
});
