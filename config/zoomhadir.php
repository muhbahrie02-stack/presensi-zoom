<?php

return [
    // Kata sandi halaman admin. WAJIB diisi di .env
    'admin_password' => env('ADMIN_PASSWORD'),

    // Versi Meeting SDK yang disalin ke public/zoom-sdk (lihat PASANG.md)
    'sdk_versi' => env('ZOOM_SDK_VERSI', '6.10.0'),

    // Selang detak kehadiran dari browser (detik)
    'detak_detik' => 30,
];
