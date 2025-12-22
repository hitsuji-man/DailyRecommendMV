<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserFavorite;
use Illuminate\Database\Eloquent\Collection;

class UserFavoriteService
{

    /**
     * お気に入り一覧を取得する
     * @return Collection
     */
    public function getUserFavorites(?User $user = null): Collection
    {
        return UserFavorite::with([
            'video' => function ($q) use ($user) {
                $q->when($user, function ($q) use ($user) {
                    $q->withCount([
                        'userFavorites as is_favorite' => function ($q2) use ($user) {
                            $q2->where('user_id', $user->id);
                        }
                    ]);
                });
            }
        ])
        ->orderBy('id', 'desc')
        ->get();
    }

}
