<?php

namespace App\Models;

use App\Support\NamaBersih;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['terverifikasi' => 'boolean'];
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    /** 000.Biro KKK | Ketua. Fulan */
    public function namaTampilan(): string
    {
        return NamaBersih::tampilan($this->bidang->kode, $this->bidang->nama, $this->jabatan->nama, $this->nama);
    }
}
