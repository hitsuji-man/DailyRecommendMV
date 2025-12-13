<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    // updated_atを無効化したい
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'video_id',
        'viewed_at',
        'watched_seconds',
        'created_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function video() {
        return $this->belongsTo(Video::class);
    }
}
