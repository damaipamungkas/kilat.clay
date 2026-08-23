<nav class="navbar" style="flex-direction: column; gap: 12px; padding: 14px 20px;">
    <!-- 1. Menu Navigasi Utama dengan Target ID yang Sesuai -->
    <div class="nav-links" style="display: flex; flex-direction: row; flex-wrap: wrap; justify-content: center; align-items: center; gap: 10px; width: 100%;">
        <a href="#target-tentang" style="color: var(--text-muted); background: var(--bg-main); padding: 8px 14px; border-radius: 12px; box-shadow: var(--clay-shadow-inset); font-size: 0.85rem; font-weight: 800; text-decoration: none;">TENTANG</a>
        <a href="#target-galeri" style="color: var(--text-muted); background: var(--bg-main); padding: 8px 14px; border-radius: 12px; box-shadow: var(--clay-shadow-inset); font-size: 0.85rem; font-weight: 800; text-decoration: none;">GALERI</a>
        <a href="#target-kursus" style="color: var(--text-muted); background: var(--bg-main); padding: 8px 14px; border-radius: 12px; box-shadow: var(--clay-shadow-inset); font-size: 0.85rem; font-weight: 800; text-decoration: none;">KURSUS</a>
        <a href="#target-testimoni" style="color: var(--text-muted); background: var(--bg-main); padding: 8px 14px; border-radius: 12px; box-shadow: var(--clay-shadow-inset); font-size: 0.85rem; font-weight: 800; text-decoration: none;">TESTIMONI</a>
        <a href="#target-prosedur" style="color: var(--text-muted); background: var(--bg-main); padding: 8px 14px; border-radius: 12px; box-shadow: var(--clay-shadow-inset); font-size: 0.85rem; font-weight: 800; text-decoration: none;">PROSEDUR</a>
        <a href="#target-kontak" style="color: var(--text-muted); background: var(--bg-main); padding: 8px 14px; border-radius: 12px; box-shadow: var(--clay-shadow-inset); font-size: 0.85rem; font-weight: 800; text-decoration: none;">KONTAK</a>
    </div>

    <!-- 2. Tombol Status / Akun -->
    <div class="nav-links" style="display: flex; flex-direction: row; flex-wrap: wrap; align-items: center; justify-content: center; gap: 10px; width: 100%;">
        <a href="{{ route('login') }}" style="color: var(--text-muted); background: var(--bg-main); padding: 8px 14px; border-radius: 12px; box-shadow: var(--clay-shadow-inset); font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; text-shadow: var(--text-timbul-light); text-decoration: none; transition: 0.2s;">
            <i class="fa-solid fa-user"></i> Daftar / Masuk
        </a>

        <a href="{{ route('profil') }}" style="color: var(--text-muted); background: var(--bg-main); padding: 8px 14px; border-radius: 12px; box-shadow: var(--clay-shadow-inset); font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; text-shadow: var(--text-timbul-light); text-decoration: none; transition: 0.2s;">
            <i class="fa-solid fa-address-card"></i> Akun
        </a>
    </div>
</nav>
