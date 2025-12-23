<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserFavorite;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;

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

    /**
     * お気に入りを1件保存する
     * @return void
     */
    public function saveUserFavorite(int $id, User $user): void
    {
        $video = Video::findOrFail($id);
        try {
            $this->storeUserFavorite($video, $user);
        } catch (QueryException $error) {
            throw new \RuntimeException("fail to save user favorite");
        }
    }

    /**
     * お気に入りを1件削除する
     * @return void
     */
    public function deleteUserFavorite(int $id): void
    {
        $favorite = UserFavorite::find($id);
        if(!$favorite) {
            throw new \DomainException('user favorite not found');
        }
        $favorite->delete();
    }

    /**
     * DBにお気に入りレコードを保存
     * @return void
     */
    private function storeUserFavorite(Video $video, User $user): void
    {
        UserFavorite::firstOrCreate([
            'user_id'    => $user->id,
            'video_id'   => $video->id,
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

}
