<!-- SIDEBAR KOMPONEN -->
<aside class="sidebar" id="sidebar">
    <div class="toggle-btn" id="toggleBtn"><i class="fa-solid fa-chevron-left"></i></div>
    <div class="profile-section">
        <div class="profile-pic"><i class="fa-solid fa-user-astronaut"></i></div>
        <h2 id="sidebarRoleName">
            {{ ucfirst(auth()->user()->role ?? 'Admin') }} - {{ auth()->user()->name ?? 'User' }}
        </h2>
    </div>

    <ul class="nav-menu">
        <li>
            <a href="{{ route('admin.index') }}" class="{{ request()->routeIs('admin.index') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('admin.billing') }}" class="{{ request()->routeIs('admin.billing') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar"></i> Billing SPP
            </a>
        </li>
        <li>
            <a href="{{ route('admin.finance') }}" class="{{ request()->routeIs('admin.finance') ? 'active' : '' }}">
                <i class="fa-solid fa-wallet"></i> Keuangan
            </a>
        </li>
        <li>
            <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <i class="fa-solid fa-id-badge"></i> Pusat Akun
            </a>
        </li>
        <li>
            <a href="{{ route('admin.absence') }}" class="{{ request()->routeIs('admin.absence') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-user"></i> Absensi
            </a>
        </li>
        <li>
            <a href="{{ route('admin.setting') }}" class="{{ request()->routeIs('admin.setting') ? 'active' : '' }}">
                <i class="fa-solid fa-gear"></i> Pengaturan
            </a>
        </li>
        <li>
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="fa-solid fa-globe"></i> Beranda
            </a>
        </li>
    </ul>

    <!-- Perbaikan: Mengubah route('appendix') menjadi route('appendix.index') -->
    <a href="{{ route('appendix') }}" class="sidebar-appendix-box {{ request()->routeIs('appendix') ? 'active' : '' }}">
        <i class="fa-solid fa-book"></i>
        <span>Appendix</span>
    </a>
</aside>
