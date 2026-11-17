<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapat extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['mulai' => 'datetime', 'selesai' => 'datetime', 'aktif' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function akunZoom()
    {
        return $this->belongsTo(AkunZoom::class);
    }

    public function kehadirans()
    {
        return $this->hasMany(Kehadiran::class);
    }

    public function durasiDetik(): int
    {
        return max(0, $this->selesai->getTimestamp() - $this->mulai->getTimestamp());
    }
}
