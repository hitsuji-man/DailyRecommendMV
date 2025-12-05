<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = [
        'channel_id',
        'channel_title',
        'thumbnail',
    ];

    public function videos() {
        return $this->hasMany(Video::class);
    }
}
