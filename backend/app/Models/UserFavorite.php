<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavorite extends Model
{
    // updated_atを無効化したい
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'video_id',
        'created_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function video() {
        return $this->belongsTo(Video::class);
    }

}
