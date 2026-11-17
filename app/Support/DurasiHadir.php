<?php

namespace App\Support;

class DurasiHadir
{
    /** Jeda antar-detak yang masih dihitung sebagai hadir (detik). */
    public const JEDA_MAKS = 90;

    /**
     * Menambah durasi berdasarkan selisih detak terakhir. Jeda yang terlalu
     * panjang (tab ditutup, koneksi putus) tidak dihitung.
     */
    public static function tambah(int $durasi, ?int $terakhir, int $sekarang): int
    {
        if ($terakhir === null) {
            return $durasi;
        }
        $jeda = $sekarang - $terakhir;

        return ($jeda > 0 && $jeda <= self::JEDA_MAKS) ? $durasi + $jeda : $durasi;
    }

    /** Hadir bila sudah masuk meeting dan memenuhi persentase minimal durasi rapat. */
    public static function hadir(bool $sudahMasuk, int $durasi, int $durasiRapat, int $minPersen): bool
    {
        if (! $sudahMasuk) {
            return false;
        }
        if ($minPersen <= 0 || $durasiRapat <= 0) {
            return true;
        }

        return $durasi >= (int) ceil($durasiRapat * $minPersen / 100);
    }
}
