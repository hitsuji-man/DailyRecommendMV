<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'youtube_id',
        'artist_id',
        'title',
        'description',
        'channel_id',
        'channel_title',
        'thumbnail_url',
        'published_at',
        'view_count',
        'like_count',
        'source_type',
    ];

    public function artist() {
        return $this->belongsTo(Artist::class);
    }
}
