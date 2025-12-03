<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $fillable = [
        'name',
        'channel_id',
        'channel_title',
        'thumbnail_url',
    ];

    public function videos() {
        return $this->hasMany(Video::class);
    }
}
