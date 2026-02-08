<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyRecommendationController;
use App\Http\Controllers\UserFavoriteController;
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
    // 動画取得(公開+任意認証)
    Route::middleware('optional.auth')
        ->get('/videos/{id}', [VideoController::class, 'showVideo']);

    Route::middleware(['auth:sanctum'])->group(function() {
        Route::get('/recommendations', [DailyRecommendationController::class, 'getDailyRecommendVideoHistories']);

        Route::get('/histories', [UserHistoryController::class, 'getUserHistories']);
        Route::delete('/histories/{id}', [UserHistoryController::class, 'deleteUserHistory']);
        Route::delete('/histories', [UserHistoryController::class, 'deleteAllUsersHistories']);

        Route::get('/favorites', [UserFavoriteController::class, 'getUserFavorites']);
        Route::post('/favorites/{id}', [UserFavoriteController::class, 'saveUserFavorite']);
        Route::delete('/favorites/{id}', [UserFavoriteController::class, 'deleteUserFavorite']);

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
         Route::post('/user', [AuthController::class, 'update']);
        Route::post('/user/password', [AuthController::class, 'changePassword']);
    });

    // 今日のおすすめMV(公開+任意認証)
    Route::middleware('optional.auth')
        ->get('/recommendations/today', [DailyRecommendationController::class, 'getDailyRecommendVideo']);
    // 管理者のみCRONでPOSTしたい
    Route::post('/recommendations/today', [DailyRecommendationController::class, 'saveDailyRecommendVideo']);

    Route::post('/anonymous-login', [AuthController::class, 'anonymousLogin']);
    Route::post('/login', [AuthController::class, 'login']);
    // 任意認証(未ログインユーザー + ゲストユーザー)
    Route::middleware('optional.auth')
        ->post('/register', [AuthController::class, 'register']);
});
