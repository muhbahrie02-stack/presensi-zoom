<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MasukController;
use App\Http\Middleware\AdminAuth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// ---------- Peserta ----------
Route::prefix('r/{rapat}')->group(function () {
    Route::get('/', [MasukController::class, 'form'])->name('masuk');
    // throttle: mencegah orang menebak-nebak nomor HP yang terdaftar
    Route::post('/', [MasukController::class, 'cek'])->middleware('throttle:15,1')->name('masuk.cek');
    Route::get('/daftar', [MasukController::class, 'daftarForm'])->name('daftar');
    Route::post('/daftar', [MasukController::class, 'daftarSimpan'])->middleware('throttle:10,1')->name('daftar.simpan');
    Route::get('/ruang', [MasukController::class, 'ruang'])->name('ruang');
    Route::post('/ping', [MasukController::class, 'ping'])->name('ping');
    Route::get('/selesai', [MasukController::class, 'selesai'])->name('selesai');
});

// ---------- Admin ----------
Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->middleware('throttle:5,1');

Route::prefix('admin')->middleware(AdminAuth::class)->name('admin.')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    Route::get('/', [AdminController::class, 'rapat'])->name('rapat');
    Route::post('/rapat', [AdminController::class, 'rapatSimpan'])->name('rapat.simpan');
    Route::post('/rapat/{rapat}/aktif', [AdminController::class, 'rapatAktif'])->name('rapat.aktif');
    Route::delete('/rapat/{rapat}', [AdminController::class, 'rapatHapus'])->name('rapat.hapus');
    Route::get('/rapat/{rapat}/laporan', [AdminController::class, 'laporan'])->name('laporan');
    Route::post('/rapat/{rapat}/laporan-zoom', [AdminController::class, 'laporanZoom'])->name('laporan.zoom');

    Route::post('/akun', [AdminController::class, 'akunSimpan'])->name('akun.simpan');
    Route::delete('/akun/{akun}', [AdminController::class, 'akunHapus'])->name('akun.hapus');

    Route::get('/peserta', [AdminController::class, 'peserta'])->name('peserta');
    Route::post('/peserta', [AdminController::class, 'pesertaSimpan'])->name('peserta.simpan');
    Route::post('/peserta/impor', [AdminController::class, 'pesertaImpor'])->name('peserta.impor');
    Route::put('/peserta/{peserta}', [AdminController::class, 'pesertaUbah'])->name('peserta.ubah');
    Route::delete('/peserta/{peserta}', [AdminController::class, 'pesertaHapus'])->name('peserta.hapus');

    Route::get('/referensi', [AdminController::class, 'referensi'])->name('referensi');
    Route::post('/bidang', [AdminController::class, 'bidangSimpan'])->name('bidang.simpan');
    Route::delete('/bidang/{bidang}', [AdminController::class, 'bidangHapus'])->name('bidang.hapus');
    Route::post('/jabatan', [AdminController::class, 'jabatanSimpan'])->name('jabatan.simpan');
    Route::delete('/jabatan/{jabatan}', [AdminController::class, 'jabatanHapus'])->name('jabatan.hapus');
});
