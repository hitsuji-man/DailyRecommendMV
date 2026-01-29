<?php
namespace App\Services;

use App\Models\DailyRecommendation;
use App\Models\User;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyRecommendationService
{
    /**
     * おすすめMV履歴一覧を取得
     * @return Collection
     */
    public function getDailyRecommendVideoHistory(): Collection {
       return DailyRecommendation::with('video')->orderBy('id', 'desc')->get();
    }

    /**
     * 今日のおすすめMV動画の取得(保存を含む):
     *
     * 既に今日の動画が保存されている場合は、daily_recommendationsテーブルから動画を取得し、まだ保存されていない場合はvideosテーブルからランダム抽選してdaily_recommendationsテーブルに「保存」し取得する
     * @return Video
     */
    public function pickDailyRecommendVideo(?User $user = null): Video
    {
        $today = now()->toDateString();

        // 既に今日の動画が保存されているか確認
        $existing = DailyRecommendation::where('recommend_date', $today)
            ->with([
                'video' => fn ($q) => $q->withIsFavorite($user)
            ])
            ->first();

        // リレーションからVideoを返す
        if($existing) {
            $video = $existing->video;
            $video->recommend_date = $existing->recommend_date;

            // 未ログイン時の保険
            $video->is_favorite ??= 0;

            return $video;
        }

        // まだ保存されていなければ、ランダム抽選して取得
        $recommendVideo = Video::inRandomOrder()
            ->withIsFavorite($user)
            ->first();

        // 直近1週間に同じMVを取得していた場合、それを除いて再度ランダム抽選
        if ($this->checkAlreadySavedVideoFor1Week($recommendVideo)) {
            // 直近1週間で保存したMVのvideo_idリストを取得
            $oneWeekAgo = now()->subDays(7)->toDateString();
            $excludeVideoIds = DailyRecommendation::where('recommend_date', '>=', $oneWeekAgo)
                ->pluck('video_id');

            // 上記video_idリストを除いて再度ランダム取得
            $recommendVideo = Video::whereNotIn('id',$excludeVideoIds)
                ->inRandomOrder()
                ->withIsFavorite($user)
                ->first();
        }

        $recommendVideo->recommend_date = $today;

        // ランダム取得したMVを固定化させるためにここで保存
        DailyRecommendation::create([
            'video_id'       => $recommendVideo->id,
            'recommend_date' => $today,
            'created_at'     => now(),
        ]);

        return $recommendVideo;
    }

    /**
     * 直近1週間で同じMVが保存されていないかチェック
     * @return boolean
     */
    public function checkAlreadySavedVideoFor1Week(Video $recommendVideo): bool
    {
        $oneWeekAgo = now()->subDays(7)->toDateString();

        // 同じvideo_idが含まれているか
        // daily_recommendationsテーブルを1週間(7レコード)検索
        return DailyRecommendation::where('video_id', $recommendVideo->id)
            ->where('recommend_date', '>=', $oneWeekAgo)
            ->exists();
    }
}
