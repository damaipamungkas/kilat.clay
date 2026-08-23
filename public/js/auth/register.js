document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registrationForm') || document.getElementById('registerForm');
    const roleInput = document.getElementById('role');
    const genderSelect = document.getElementById('gender');
    const namaInput = document.getElementById('nama');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const agreeCheckbox = document.getElementById('agree');

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
    }

    window.executeSubmit = function (e) {
        if (e) e.preventDefault();

        // Mengambil nilai role secara akurat dan konsisten dalam huruf kapital
        const rolePrefixStr = roleInput ? roleInput.value.toLowerCase() : 'parent';
        const roleVal = rolePrefixStr.toUpperCase() === 'ADMIN' ? 'ADMIN' : 'PARENT';

        const genderVal = genderSelect ? genderSelect.value : 'Mr.';
        const namaLengkapVal = namaInput ? namaInput.value.trim() : '';
        const rawUsername = usernameInput ? usernameInput.value.trim().toLowerCase().replace(/[^a-z0-9._-]/g, '') : '';
        const passwordVal = passwordInput ? passwordInput.value : '';
        const isAgreed = agreeCheckbox ? agreeCheckbox.checked : false;

        if (!namaLengkapVal || !rawUsername || !passwordVal || !isAgreed) {
            alert('Lengkapi seluruh formulir registrasi dan setujui aturan.');
            return false;
        }

        const finalUsername = rawUsername;
        const allUsers = getAllUsers();

        // PERBAIKAN UTAMA: Validasi duplikasi ketat.
        // Memastikan pengecekan username DAN role/kombinasi unik agar akun parent dan admin
        // dengan nama serupa tidak saling menimpa atau tertukar hak aksesnya.
        const isExist = allUsers.some(u =>
            (u.email && u.email.toLowerCase() === finalUsername.toLowerCase()) ||
            (u.username && u.username.toLowerCase() === finalUsername.toLowerCase())
        );

        if (isExist) {
            alert('ID Kredensial / Username ini sudah terdaftar di sistem.');
            return false;
        }

        // Pengamanan tambahan: Jika mendaftar sebagai ADMIN, pastikan tidak ada celah
        // yang mengizinkan pembuatan admin ganda secara tidak sengaja (opsional, sesuaikan kebutuhan).
        if (roleVal === 'ADMIN') {
            const adminExists = allUsers.some(u => (u.role || '').toUpperCase() === 'ADMIN');
            if (adminExists && !confirm('Sudah ada akun Administrator di sistem. Tetap buat akun Admin baru?')) {
                return false;
            }
        }

        // Objek User Baru dengan Role yang terkunci valid
        const newUser = {
            id: Date.now(),
            nama: `${genderVal} ${namaLengkapVal}`,
            namaLengkap: namaLengkapVal,
            username: finalUsername,
            email: finalUsername,
            password: passwordVal,
            role: roleVal, // <-- Role dijamin murni sesuai pilihan form (ADMIN / PARENT)
            gender: genderVal,
            atletTautan: [],
            createdAt: new Date().toISOString()
        };

        // 1. Simpan ke LocalStorage
        allUsers.push(newUser);
        saveAllUsers(allUsers);

        // 2. Set sebagai user login aktif
        saveCurrentUser(newUser);

        alert('Registrasi berhasil! Mengarahkan ke profil...');

        // 3. Navigasi ke Halaman Profil
        window.location.href = '/profil';
        return false;
    };

    if (registerForm) {
        registerForm.addEventListener('submit', window.executeSubmit);
    }
});
