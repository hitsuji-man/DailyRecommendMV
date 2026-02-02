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
    public function getUserFavorites(User $user): Collection
    {
        // お気に入り判定:ローカルスコープ呼び出し
        return UserFavorite::with([
            'video' => fn ($q) => $q->withIsFavorite($user)
        ])
        ->where('user_id', $user->id)
        ->orderBy('id', 'desc')
        ->get();
    }

    /**
     * お気に入りを1件保存する
     * @return void
     */
    public function saveUserFavorite(int $videoId, User $user): void
    {
        $video = Video::findOrFail($videoId);
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
    public function deleteUserFavorite(int $videoId, User $user): void
    {
        $deleted = UserFavorite::where('video_id', $videoId)
            ->where('user_id', $user->id)
            ->delete();
        if($deleted === 0) {
            throw new \DomainException('user favorite not found');
        }
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
