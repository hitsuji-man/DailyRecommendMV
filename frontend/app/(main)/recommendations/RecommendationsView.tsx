"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import Image from "next/image";
import { formatRelativeDate } from "@/lib/formatRelativeDate";
import HorizontalVideoList from "@/components/HorizontalVideoList";
import LikeButton from "@/components/LikeButton";
import { useAuthContext } from "@/context/AuthContext";

type RecommendationResponse = {
  data: Recommendation;
};

type MixedDailyResponse = {
  data: Recommendation[];
};

type Recommendation = {
  id: number;
  videoId: string;
  title: string;
  description: string;
  channelId: string;
  channelTitle: string;
  thumbnail: {
    url: string;
    width: number;
    height: number;
  };
  publishedAt: string;
  viewCount: number;
  likeCount: number;
  sourceType: "trend" | "playlist";
  recommendDate: string;
  isFavorite: boolean;
  canFavorite: boolean;
  canViewRecommendations: boolean;
  canViewFavorites: boolean;
  canViewHistories: boolean;
};

export default function RecommendationsView() {
  const [recommendation, setRecommendation] = useState<Recommendation | null>(
    null,
  );
  const [mixedDailyVideos, setMixedDailyVideos] = useState<Recommendation[]>(
    [],
  );
  const [loading, setLoading] = useState(true);
  const { loading: authLoading, authVersion } = useAuthContext();

  useEffect(() => {
    if (authLoading) return;

    const fetchData = async () => {
      try {
        const [todayRes, mixedRes] = await Promise.all([
          api.get<RecommendationResponse>("/recommendations/today"),
          api.get<MixedDailyResponse>("/videos/mixed-daily"),
        ]);

        setRecommendation(todayRes.data.data);
        setMixedDailyVideos(mixedRes.data.data);
      } catch (e) {
        console.error("Failed to fetch recommendations", e);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [authVersion, authLoading]);

  if (loading || !recommendation) {
    return <div className="p-6 text-center">読み込み中...</div>;
  }

  return (
    <div className="p-6">
      <h1 className="text-xl font-bold mb-4 text-center">今日のおすすめMV</h1>

      {/* ===上:メイン動画(おすすめMV)=== 画面中央に配置するラッパー */}
      <div className="flex justify-center">
        {/* iframeとpを同じ幅コンテナに入れる */}
        <div className="w-full max-w-3xl">
          {/* 動画 */}
          <div className="aspect-video mb-3">
            <iframe
              className="h-full w-full rounded-lg"
              src={`https://www.youtube.com/embed/${recommendation.videoId}`}
              title={recommendation.title}
              allowFullScreen
            />
          </div>

          {/* タイトル */}
          <p className="text-xl font-semibold leading-snug mb-1">
            {recommendation.title}
          </p>

          {/* 投稿者 + 視聴回数 + 投稿日 + いいねボタン */}
          <div className="flex items-center mt-2">
            {/* 左側：チャンネル情報 */}
            <div className="flex items-center gap-3">
              {/* チャンネルサムネイル（擬似） */}
              <Image
                src={recommendation.thumbnail.url}
                alt={recommendation.channelTitle}
                width={36}
                height={36}
                className="rounded-full object-cover"
              />
              {/* チャンネル名 + 視聴回数 + 投稿日時 */}
              <div className="flex flex-col">
                {/* チャンネル名 */}
                <p className="text-base text-gray-700 hover:text-gray-900 cursor-pointer">
                  投稿者: {recommendation.channelTitle}
                </p>
                {/* 視聴回数 + 投稿日時 */}
                <p className="text-sm text-gray-500">
                  {recommendation.viewCount.toLocaleString()} 回視聴 ・{" "}
                  {formatRelativeDate(recommendation.publishedAt)}
                </p>
              </div>
            </div>

            {/* 右側：いいねボタン(ログイン時のみ) */}
            {recommendation.canFavorite && (
              <div className="ml-4">
                <LikeButton
                  videoId={recommendation.id}
                  initialLiked={recommendation.isFavorite}
                />
              </div>
            )}
          </div>
        </div>
      </div>

      {/* ===下: 関連動画一覧(mixed-daily)=== */}
      <div className="mt-10 max-w-5xl mx-auto">
        <h2 className="text-lg font-semibold mb-4">関連動画一覧</h2>

        <HorizontalVideoList videos={mixedDailyVideos} />
      </div>
    </div>
  );
}
