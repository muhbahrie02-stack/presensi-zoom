<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkunZoom extends Model
{
    protected $guarded = [];
    protected $hidden = ['sdk_secret'];

    protected function casts(): array
    {
        return ['sdk_secret' => 'encrypted'];
    }
}
