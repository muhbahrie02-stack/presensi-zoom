<?php

namespace App\Support;

/**
 * Membaca laporan peserta Zoom (CSV dari Reports > Usage > Participants).
 * Baris informasi rapat di bagian atas dilewati; satu orang yang keluar-masuk
 * beberapa kali dijumlahkan durasinya.
 */
class LaporanZoom
{
    /** @return array<string, array{nama:string, asli:?string, durasi_detik:int, masuk:?int, keluar:?int}> dikunci nama ternormalisasi */
    public static function baca(string $csv): array
    {
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);
        $baris = preg_split('/\r\n|\r|\n/', $csv);
        $pemisah = ',';
        $kol = null;
        $hasil = [];

        foreach ($baris as $b) {
            if (trim($b) === '') {
                continue;
            }
            if ($kol === null) {
                foreach ([',', ';', "\t"] as $p) {
                    $sel = array_map(fn ($x) => mb_strtolower(trim($x)), str_getcsv($b, $p));
                    $iNama = self::cari($sel, ['name (original name)', 'name', 'nama (nama asli)', 'nama']);
                    $iDur = self::cari($sel, ['duration (minutes)', 'total duration (minutes)', 'durasi (menit)', 'total durasi (menit)']);
                    if ($iNama !== null && $iDur !== null) {
                        $pemisah = $p;
                        $kol = [
                            'nama' => $iNama, 'durasi' => $iDur,
                            'masuk' => self::cari($sel, ['join time', 'waktu bergabung']),
                            'keluar' => self::cari($sel, ['leave time', 'waktu keluar']),
                        ];
                        break;
                    }
                }
                continue;
            }

            $sel = str_getcsv($b, $pemisah);
            $nama = trim($sel[$kol['nama']] ?? '');
            if ($nama === '') {
                continue;
            }
            $detik = (int) round(((float) str_replace(',', '.', $sel[$kol['durasi']] ?? '0')) * 60);
            $masuk = $kol['masuk'] !== null ? (strtotime($sel[$kol['masuk']] ?? '') ?: null) : null;
            $keluar = $kol['keluar'] !== null ? (strtotime($sel[$kol['keluar']] ?? '') ?: null) : null;

            $k = self::kunci($nama);
            if (! isset($hasil[$k])) {
                $hasil[$k] = ['nama' => $nama, 'asli' => self::asli($nama), 'durasi_detik' => 0, 'masuk' => $masuk, 'keluar' => $keluar];
            }
            $hasil[$k]['durasi_detik'] += $detik;
            if ($masuk && (! $hasil[$k]['masuk'] || $masuk < $hasil[$k]['masuk'])) {
                $hasil[$k]['masuk'] = $masuk;
            }
            if ($keluar && (! $hasil[$k]['keluar'] || $keluar > $hasil[$k]['keluar'])) {
                $hasil[$k]['keluar'] = $keluar;
            }
        }

        return $hasil;
    }

    /**
     * Kunci pencocokan. Zoom menulis "Nama Baru (Nama Asli)" bila peserta
     * mengganti nama; yang dipakai adalah nama sebelum tanda kurung.
     */
    public static function kunci(string $nama): string
    {
        $nama = preg_replace('/\s*\([^()]*\)\s*$/u', '', $nama);

        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $nama)));
    }

    /** Nama asli di dalam kurung penutup, bila ada: "Budi (000.Biro KKK | Ketua. Budi)". */
    public static function asli(string $nama): ?string
    {
        return preg_match('/\(([^()]+)\)\s*$/u', $nama, $m)
            ? mb_strtolower(trim(preg_replace('/\s+/u', ' ', $m[1])))
            : null;
    }

    private static function cari(array $sel, array $nama): ?int
    {
        foreach ($nama as $n) {
            $i = array_search($n, $sel, true);
            if ($i !== false) {
                return $i;
            }
        }

        return null;
    }
}
