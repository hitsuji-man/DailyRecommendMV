<?php

namespace App\Http\Controllers;

use App\Http\Resources\DailyRecommendationResource;
use App\Services\DailyRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DailyRecommendationController extends Controller
{
    private DailyRecommendationService $dailyRecommendationService;

    public function __construct(DailyRecommendationService $dailyRecommendationService) {
        $this->dailyRecommendationService = $dailyRecommendationService;
    }

    /**
     * おすすめMV履歴一覧を取得
     * @return AnonymousResourceCollection
     */
    public function getDailyRecommendVideoHistory(): AnonymousResourceCollection
    {
        $dailyRecommendationsHistory = $this->dailyRecommendationService->getDailyRecommendVideoHistory();
        return DailyRecommendationResource::collection($dailyRecommendationsHistory);
    }

    /**
     * 今日のおすすめMVを取得(なければ保存)
     * @return DailyRecommendationResource
     */
    public function getDailyRecommendVideo(DailyRecommendationService $dailyRecommendationService): DailyRecommendationResource
    {
        return new DailyRecommendationResource($dailyRecommendationService->pickDailyRecommendVideo());
    }

    /**
     * 今日のおすすめMVをDBに保存
     * @return JsonResponse
     */
    public function saveDailyRecommendVideo(DailyRecommendationService $dailyRecommendationService): JsonResponse
    {
        // GET API(getDailyRecommendVideo())を先に呼んでも、POST API(saveDailyRecommendVideo())を先に呼んでも常に結果は同じ(冪等)で壊れない
        $dailyRecommendationService->pickDailyRecommendVideo();

        return response()->json([
            'status'  => 'success',
            'message' => 'save recommendVideo'
        ]);
    }
}
