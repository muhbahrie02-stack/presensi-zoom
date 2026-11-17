<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun_zooms', function (Blueprint $t) {
            $t->id();
            $t->string('nama');            // mis. "dppwi.adum"
            $t->string('sdk_key');         // Client ID app Meeting SDK
            $t->text('sdk_secret');        // Client Secret, disimpan terenkripsi
            $t->timestamps();
        });

        Schema::create('bidangs', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 10)->unique();   // "000"
            $t->string('nama');                 // "Biro KKK"
            $t->timestamps();
        });

        Schema::create('jabatans', function (Blueprint $t) {
            $t->id();
            $t->string('nama')->unique();       // "Ketua"
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->timestamps();
        });

        Schema::create('pesertas', function (Blueprint $t) {
            $t->id();
            $t->string('no_hp', 15)->unique();
            $t->string('nama');
            $t->foreignId('bidang_id')->constrained()->restrictOnDelete();
            $t->foreignId('jabatan_id')->constrained()->restrictOnDelete();
            $t->boolean('terverifikasi')->default(false);
            $t->string('sumber', 10)->default('admin');   // admin | mandiri
            $t->timestamps();
        });

        Schema::create('rapats', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 40)->unique();
            $t->string('judul');
            $t->string('mode', 10)->default('link');          // link = tanpa app Zoom; sdk = Zoom tertanam
            $t->string('zoom_host', 60)->default('zoom.us');  // mis. telkomsel.zoom.us
            $t->foreignId('akun_zoom_id')->nullable()->constrained()->restrictOnDelete();   // hanya mode sdk
            $t->string('meeting_number', 20);
            $t->string('passcode')->nullable();
            $t->dateTime('mulai');
            $t->dateTime('selesai');
            $t->unsignedTinyInteger('min_persen')->default(0);   // syarat hadir: % durasi rapat
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        Schema::create('kehadirans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rapat_id')->constrained()->cascadeOnDelete();
            $t->foreignId('peserta_id')->constrained()->cascadeOnDelete();
            $t->string('nama_tampilan');
            $t->string('token', 64);                 // sesi aktif; berganti tiap login ulang
            $t->dateTime('klik_at');                 // pertama kali memasukkan nomor HP
            $t->dateTime('masuk_at')->nullable();    // pertama kali tersambung ke meeting
            $t->dateTime('terakhir_at')->nullable(); // detak terakhir
            $t->dateTime('keluar_at')->nullable();
            $t->unsignedInteger('durasi_detik')->default(0);
            $t->timestamps();
            $t->unique(['rapat_id', 'peserta_id']);
        });
    }

    public function down(): void
    {
        foreach (['kehadirans', 'rapats', 'pesertas', 'jabatans', 'bidangs', 'akun_zooms'] as $tabel) {
            Schema::dropIfExists($tabel);
        }
    }
};
