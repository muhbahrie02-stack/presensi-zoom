<?php

namespace App\Support;

/**
 * Menyamakan penulisan nomor HP Indonesia: 0812..., 62812..., +62 812-..., 812...
 * semuanya menjadi 0812...
 */
class NomorHp
{
    public static function normal(?string $input): ?string
    {
        $angka = preg_replace('/\D+/', '', (string) $input);

        if (str_starts_with($angka, '0062')) {
            $angka = '0' . substr($angka, 4);
        } elseif (str_starts_with($angka, '62')) {
            $angka = '0' . substr($angka, 2);
        } elseif (str_starts_with($angka, '8')) {
            $angka = '0' . $angka;
        }

        // Nomor seluler Indonesia: 08 + 8 s.d. 11 digit
        return preg_match('/^08\d{8,11}$/', $angka) ? $angka : null;
    }
}
