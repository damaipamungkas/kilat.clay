document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm') || document.querySelector('form');
    const emailInput = document.getElementById('email') || document.querySelector('input[name="email"]');
    const passwordInput = document.getElementById('password') || document.querySelector('input[name="password"]');
    const togglePassword = document.getElementById('togglePassword');

    // Toggle Lihat/Sembunyikan Password
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });
    }

    // Utilitas LocalStorage (Tetap dipertahankan tanpa dihapus)
    function getAllUsers() {
        return JSON.parse(localStorage.getItem('manageUsersData')) ||
               JSON.parse(localStorage.getItem('KILAT_USERS')) || [];
    }

    function saveAllUsers(users) {
        localStorage.setItem('manageUsersData', JSON.stringify(users));
        localStorage.setItem('KILAT_USERS', JSON.stringify(users));
    }

    function saveCurrentUser(user) {
        localStorage.setItem('KILAT_CURRENT_USER', JSON.stringify(user));
        localStorage.setItem('kilat_user_data', JSON.stringify(user));
        localStorage.setItem('userRole', user.role ? user.role.toLowerCase() : 'admin');
    }

    function seedDefaultUsers() {
        let allUsers = getAllUsers();

        const isMasterExist = allUsers.some(u =>
            u.id === 'master-001' ||
            (u.email && u.email.toLowerCase() === 'admin.master@kilat.com')
        );

        if (!isMasterExist) {
            const masterAccount = {
                id: 'master-001',
                nama: 'Mr. Master Admin',
                namaLengkap: 'Master Admin System',
                username: 'admin.master@kilat.com',
                email: 'admin.master@kilat.com',
                password: '1111',
                role: 'ADMIN',
                gender: 'Mr.',
                atletTautan: [],
                createdAt: new Date().toISOString()
            };
            allUsers.push(masterAccount);
            saveAllUsers(allUsers);
        }
    }

    seedDefaultUsers();

    // Penanganan Form Login yang disinkronkan dengan Backend Laravel
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            const emailVal = emailInput ? emailInput.value.trim().toLowerCase() : '';
            const passwordVal = passwordInput ? passwordInput.value : '';

            // Cek lokal opsional untuk membantu menyimpan state tambahan jika diperlukan
            const allUsers = getAllUsers();
            const matchedUser = allUsers.find(u => {
                const isPassMatch = String(u.password) === String(passwordVal);
                if (!isPassMatch) return false;

                const uEmail = (u.email || '').trim().toLowerCase();
                const uUsername = (u.username || '').trim().toLowerCase();
                const uName = (u.nama || u.namaLengkap || u.name || '').trim().toLowerCase();

                return uEmail === emailVal || uUsername === emailVal || uName === emailVal;
            });

            if (matchedUser) {
                saveCurrentUser(matchedUser);
            }

            // TIDAK ADA LAGI e.preventDefault() yang memblokir form!
            // Form sekarang diteruskan secara resmi ke route Laravel (POST /login)
            // agar session server SQLite terbuat dengan benar dan tidak terpental lagi.
        });
    }
});
