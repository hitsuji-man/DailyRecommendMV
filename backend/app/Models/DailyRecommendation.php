<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyRecommendation extends Model
{
    // updated_atを無効化したい
    public $timestamps = false;

    protected $fillable = [
        'video_id',
        'recommend_date',
        'created_at',
    ];

    public function video() {
        return $this->belongsTo(Video::class);
    }
}
