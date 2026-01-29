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
        'thumbnail',
        'published_at',
        'view_count',
        'like_count',
        'source_type',
    ];

    protected $casts = [
        'thumbnail' => 'array',
        'published_at' => 'datetime',
    ];

    public function artist() {
        return $this->belongsTo(Artist::class);
    }

    public function dailyRecommendations()
    {
        return $this->hasMany(DailyRecommendation::class);
    }

    public function userHistories() {
        return $this->hasMany(UserHistory::class);
    }

    public function userFavorites() {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * ローカルスコープ呼び出し
     * scope + スコープ名(withIsFavorite($user)で呼び出す)
     * 第1引数 $query は 自動で渡される
     * 呼び出し側では $query を書かない
     */
    public function scopeWithIsFavorite($query, ?User $user)
    {
        if (!$user) {
            return $query;
        }

        // userFavoritesリレーションより、ログインユーザーでレコードが存在(お気に入り済 count=1)、存在しない(未お気に入り count=0)
        return $query->withCount([
            'userFavorites as is_favorite' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }
        ]);
    }
}
