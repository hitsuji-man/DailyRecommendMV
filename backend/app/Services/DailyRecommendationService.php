<?php
namespace App\Services;

use App\Models\DailyRecommendation;
use App\Models\Video;
use Carbon\Carbon;

class DailyRecommendationService
{
    /**
     * 今日のおすすめMV動画の取得(保存を含む):
     *
     * 既に今日の動画が保存されている場合は、daily_recommendationsテーブルから動画を取得し、まだ保存されていない場合はvideosテーブルからランダム抽選して「保存」し取得する
     * @return Video
     */
    public function pickDailyRecommendVideo(): Video
    {
        $today = now()->toDateString();

        // 既に今日の動画が保存されているか確認
        $existing = DailyRecommendation::where('recommend_date', $today)->first();

        // リレーションからVideoを返す
        if($existing) {
            return $existing->video;
        }

        // まだ保存されていなければ、ランダム抽選して取得
        $recommendVideo = Video::inRandomOrder()->first();

        // 直近7日間に同じMVを取得していた場合、それを除いて再度ランダム抽選
        if ($this->checkAlreadySavedVideoFor1Week($recommendVideo)) {
            // Videoテーブルから該当MVのレコードを除く
            $videos = Video::whereNotIn('id',[$recommendVideo->id])->get();
            // 再度ランダム取得
            $recommendVideo = $videos->random();
        }

        // ランダム取得したMVを固定化させるためにここで保存
        DailyRecommendation::create([
            'video_id'       => $recommendVideo->id,
            'recommend_date' => $today,
            'created_at'     => now(),
        ]);

        return $recommendVideo;
    }

    /**
     * 直近7日間で同じMVが保存されていないかチェック
     * @return boolean
     */
    public function checkAlreadySavedVideoFor1Week(Video $recommendVideo): bool
    {
        $alreadySavedFlag = false;
        // daily_recommendationsテーブルを7日間(7レコード)検索
        $thisWeeksRecord = DailyRecommendation::whereBetween('created_at',[
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->get();

        // 同じvideo_idが含まれているか
        foreach ($thisWeeksRecord as $record) {
            $record['video_id'] == $recommendVideo->id
                            ? $alreadySavedFlag = true : $alreadySavedFlag = false;
        }
        return $alreadySavedFlag;
    }
}
