<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AthleteController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AppendixController;
use App\Http\Controllers\AdminUserController; // <-- Tambahan Controller baru
use App\Http\Controllers\AiChatController;

// ==========================================
// 1. RUTE PUBLIK (Halaman Umum)
// ==========================================
Route::view('/', 'public.index')->name('home');
Route::view('/faq', 'public.faq')->name('faq');
Route::view('/feedback', 'public.feedback')->name('feedback');
Route::view('/knowledge', 'public.knowledge')->name('knowledge');
Route::view('/rate', 'public.rate')->name('rate');
Route::view('/rule', 'public.rule')->name('rule');
Route::view('/schedule', 'public.schedule')->name('schedule');
Route::view('/testimoni', 'public.testimoni')->name('testimoni');

// ==========================================
// 2. RUTE AUTENTIKASI & PROFIL
// ==========================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::view('/register', 'auth.register')->name('register');

Route::middleware(['auth'])->group(function () {
    Route::view('/profil', 'auth.profil')->name('profil');
});

// ==========================================
// 3. RUTE APPENDIX
// ==========================================
Route::middleware(['auth', 'role:admin,Admin,administrator,coach,parent,orang tua,orangtua,pelatih'])->group(function () {
    Route::get('/appendix', [AppendixController::class, 'index'])->name('appendix');
    Route::get('/appendix-index', [AppendixController::class, 'index'])->name('appendix.index');
});

// ==========================================
// 4. RUTE ADMIN (Diperbarui agar mendukung variasi penulisan role admin)
// ==========================================
Route::prefix('admin')->middleware(['auth', 'role:admin,Admin,administrator'])->name('admin.')->group(function () {
    Route::view('/', 'admin.admin')->name('index');
    Route::view('/absence', 'admin.absence')->name('absence');
    Route::view('/billing', 'admin.billing')->name('billing');
    Route::view('/finance', 'admin.finance')->name('finance');
    Route::view('/setting', 'admin.setting')->name('setting');
    Route::view('/users', 'admin.users')->name('users');

    // Endpoint untuk menyimpan akun baru langsung ke Database Server Laravel
    Route::post('/users/store', [AdminUserController::class, 'store'])->name('users.store');

    Route::get('/setting/sql-backup', [AdminSettingController::class, 'downloadSqlBackup'])->name('setting.sql-backup');

    Route::post('/athlete/store', [AthleteController::class, 'store'])->name('athlete.store');
    Route::delete('/athlete/delete/{id}', [AthleteController::class, 'destroy'])->name('athlete.destroy');
    Route::post('/athlete/approve-edit/{id}', [AthleteController::class, 'approveEdit'])->name('athlete.approveEdit');
});

// ==========================================
// 5. FITUR TAMBAHAN (Sesuai Role)
// ==========================================
Route::middleware(['auth', 'role:admin,Admin,administrator,parent,orang tua,orangtua'])->group(function () {
    Route::put('/athlete/update/{id}', [AthleteController::class, 'updateRequest'])->name('athlete.updateRequest');
});

Route::middleware(['auth', 'role:admin,Admin,administrator,coach,pelatih'])->group(function () {
    Route::post('/assessment/trick', [AssessmentController::class, 'storeTrick'])->name('assessment.storeTrick');
    Route::post('/assessment/speed', [AssessmentController::class, 'storeSpeed'])->name('assessment.storeSpeed');
});

Route::post('/ai-chat', [AiChatController::class, 'handleChat']);
