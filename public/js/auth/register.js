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

        const rolePrefixStr = roleInput ? roleInput.value.toLowerCase() : 'parent';
        const roleVal = rolePrefixStr.toUpperCase();
        const genderVal = genderSelect ? genderSelect.value : 'Mr.';
        const namaLengkapVal = namaInput ? namaInput.value.trim() : '';
        const rawUsername = usernameInput ? usernameInput.value.trim().toLowerCase().replace(/[^a-z0-9._-]/g, '') : '';
        const passwordVal = passwordInput ? passwordInput.value : '';
        const isAgreed = agreeCheckbox ? agreeCheckbox.checked : false;

        if (!namaLengkapVal || !rawUsername || !passwordVal || !isAgreed) {
            alert('Lengkapi seluruh formulir registrasi dan setujui aturan.');
            return false;
        }

        // PERBAIKAN: Menggunakan nilai rawUsername murni tanpa menyertakan prefix role maupun domain
        const finalUsername = rawUsername;
        const allUsers = getAllUsers();

        // Cek duplikasi akun berdasarkan username bersih yang diketik
        const isExist = allUsers.some(u =>
            (u.email && u.email.toLowerCase() === finalUsername.toLowerCase()) ||
            (u.username && u.username.toLowerCase() === finalUsername.toLowerCase())
        );

        if (isExist) {
            alert('ID Kredensial / Username ini sudah terdaftar di sistem.');
            return false;
        }

        // Objek User Baru: Properti `username` menyimpan string murni sesuai yang diketik pengguna
        const newUser = {
            id: Date.now(),
            nama: `${genderVal} ${namaLengkapVal}`,
            namaLengkap: namaLengkapVal,
            username: finalUsername, // <-- MURNI SESUAI YANG DIKETIK TANPA PREFIX/DOMAIN
            email: finalUsername,
            password: passwordVal,
            role: roleVal,
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
