<?php

namespace App\Support;

/**
 * Menghapus gelar dan sapaan dari nama. Tidak sempurna: admin tetap bisa
 * mengedit nama di halaman Peserta.
 */
class NamaBersih
{
    /** Gelar/sapaan di depan nama (tanpa titik, huruf kecil). */
    private const DEPAN = [
        'dr', 'drs', 'dra', 'ir', 'prof', 'h', 'hj', 'kh', 'km', 'ust', 'ustadz',
        'ustaz', 'ustadzah', 'ustazah', 'syaikh', 'syekh', 'tgk', 'apt', 'ns',
        'bpk', 'bapak', 'ibu', 'sdr', 'sdri', 'akh', 'ukh', 'al-ustadz', 'al-ustaz',
        'eng', 'drg', 'drh', 'ar', 'gr', 'hc',
        'eng', 'drg', 'drh', 'ar', 'gr', 'hc',
    ];

    /** Gelar di belakang nama yang ditulis tanpa koma (tanpa titik, huruf kecil). */
    private const BELAKANG = [
        'sh', 'mh', 'sag', 'mag', 'spd', 'mpd', 'spdi', 'mpdi', 'se', 'me', 'mm',
        'st', 'mt', 'sip', 'msi', 'ssi', 'skom', 'mkom', 'ssos', 'shi', 'mhi',
        'sei', 'mei', 'skm', 'mkes', 'sked', 'lc', 'ma', 'ba', 'phd', 'mba',
        'msc', 'mm', 'sth', 'mth', 'sthi', 'mthi', 'ssy', 'msy', 'sfarm', 'skep',
        'amd', 'dipl', 'mpi', 'spi', 'shum', 'mhum', 'spsi', 'mpsi', 'sik', 'mikom',
    ];

    public static function bersihkan(string $nama): string
    {
        // Karakter tak terlihat (hasil salin-tempel dari WhatsApp/Word)
        $nama = preg_replace('/[\x{200B}-\x{200D}\x{2060}\x{FEFF}]/u', '', $nama);
        $nama = trim(preg_replace('/\s+/u', ' ', $nama));

        // "Kurniawan.S.H." -> "Kurniawan, S.H."
        $nama = preg_replace('/(?<=\p{Ll})\.(?=\p{Lu}\.)/u', ', ', $nama);

        // Semua yang ditulis setelah koma dianggap gelar: "Fulan, S.H., M.H."
        $nama = trim(explode(',', $nama)[0]);

        $kata = explode(' ', $nama);

        while (count($kata) > 1 && in_array(self::kunci($kata[0]), self::DEPAN, true)) {
            array_shift($kata);
        }
        while (count($kata) > 1 && in_array(self::kunci(end($kata)), self::BELAKANG, true)) {
            array_pop($kata);
        }

        return trim(implode(' ', $kata));
    }

    /** "S.H." -> "sh", "Dr." -> "dr", "(H.C.)" -> "hc" */
    private static function kunci(string $kata): string
    {
        return mb_strtolower(str_replace(['.', '(', ')'], '', $kata));
    }

    /** Pola nama tampilan Zoom: 000.Biro KKK | Ketua. Fulan */
    public static function tampilan(string $kode, string $bidang, string $jabatan, string $nama): string
    {
        return sprintf('%s.%s | %s. %s', $kode, $bidang, $jabatan, $nama);
    }
}
