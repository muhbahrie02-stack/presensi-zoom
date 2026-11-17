<?php

namespace App\Models;

use App\Support\DurasiHadir;
use Illuminate\Database\Eloquent\Model;

class Kehadiran extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'klik_at' => 'datetime', 'masuk_at' => 'datetime',
            'terakhir_at' => 'datetime', 'keluar_at' => 'datetime',
        ];
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public function rapat()
    {
        return $this->belongsTo(Rapat::class);
    }

    public function hadir(Rapat $rapat): bool
    {
        return DurasiHadir::hadir($this->masuk_at !== null, $this->durasi_detik, $rapat->durasiDetik(), $rapat->min_persen);
    }
}
