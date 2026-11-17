<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\Jabatan;
use App\Models\Peserta;
use Illuminate\Database\Seeder;

/**
 * Memuat bidang, jabatan, dan peserta dari database/data/*.csv.
 * Aman dijalankan ulang: data yang sudah ada diperbarui, bukan digandakan.
 *
 *   php artisan db:seed --class=PengurusSeeder
 */
class PengurusSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->baca('bidang.csv') as $b) {
            Bidang::updateOrCreate(['kode' => $b['kode']], ['nama' => $b['nama']]);
        }
        foreach ($this->baca('jabatan.csv') as $j) {
            Jabatan::updateOrCreate(['nama' => $j['nama']], ['urutan' => (int) $j['urutan']]);
        }

        $bidang = Bidang::pluck('id', 'kode');
        $jabatan = Jabatan::pluck('id', 'nama');

        $jumlah = 0;
        foreach ($this->baca('peserta.csv') as $p) {
            Peserta::updateOrCreate(['no_hp' => $p['no_hp']], [
                'nama' => $p['nama'],
                'bidang_id' => $bidang[$p['kode_bidang']],
                'jabatan_id' => $jabatan[$p['jabatan']],
                'terverifikasi' => true,
                'sumber' => 'admin',
            ]);
            $jumlah++;
        }
        $this->command?->info("$jumlah peserta dimuat.");
    }

    /** @return array<int, array<string, string>> */
    private function baca(string $berkas): array
    {
        $f = fopen(database_path("data/$berkas"), 'r');
        $kepala = fgetcsv($f);
        $baris = [];
        while (($r = fgetcsv($f)) !== false) {
            if ($r !== [null]) {
                $baris[] = array_combine($kepala, $r);
            }
        }
        fclose($f);

        return $baris;
    }
}
