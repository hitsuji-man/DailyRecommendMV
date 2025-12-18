<?php

use App\Http\Controllers\DailyRecommendationController;
use App\Http\Controllers\UserHistoryController;
use App\Http\Controllers\VideoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function() {
    Route::get('/videos/trending', [VideoController::class, 'getTrendingMusicVideos']);
    Route::get('/videos/mixed-daily', [VideoController::class, 'getMixedDailyList']);
    // 管理者のみCRONでPOSTしたい
    Route::post('/videos/mixed-daily', [VideoController::class, 'saveMixedDailyList']);

    Route::middleware(['dev.login'])->group(function() {
        Route::get('/videos/{id}', [VideoController::class, 'showVideo']);
        Route::get('/histories', [UserHistoryController::class, 'getUserHistories']);
    });

    Route::get('/recommendations', [DailyRecommendationController::class, 'getDailyRecommendVideoHistory']);
    Route::get('/recommendations/today', [DailyRecommendationController::class, 'getDailyRecommendVideo']);
    Route::post('/recommendations/today', [DailyRecommendationController::class, 'saveDailyRecommendVideo']);
});
