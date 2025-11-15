<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckerController;

Route::get('/', function () {
    return inertia('Auth/Login');
});

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        if ($user->role == 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role == 'checker') {
            return redirect()->route('checker.dashboard');
        }
        
        return redirect()->route('booking.index');
    })->name('dashboard');

    Route::prefix('booking')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('booking.index');
        Route::get('/buat', [BookingController::class, 'buat'])->name('booking.buat');
        Route::post('/', [BookingController::class, 'simpan'])->name('booking.simpan');
        Route::get('/{id}/konfirmasi', [BookingController::class, 'konfirmasi'])->name('booking.konfirmasi');
        Route::post('/{id}/unggah-bukti-pembayaran', [BookingController::class, 'unggahBuktiPembayaran'])->name('booking.unggah-bukti-pembayaran');
        Route::post('/{id}/batal', [BookingController::class, 'batal'])->name('booking.batal');
    });

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/booking/{id}/konfirmasi', [AdminController::class, 'konfirmasiPemesanan'])->name('admin.konfirmasi-pemesanan');
        Route::post('/surat-jalan', [AdminController::class, 'buatSuratJalan'])->name('admin.buat-surat-jalan');
        Route::post('/jadwal', [AdminController::class, 'simpanJadwal'])->name('admin.simpan-jadwal');
    });

    Route::prefix('checker')->group(function () {
        Route::get('/dashboard', [CheckerController::class, 'dashboard'])->name('checker.dashboard');
        Route::post('/booking/{id}/periksa', [CheckerController::class, 'periksaPenumpang'])->name('checker.periksa-penumpang');
    });
});