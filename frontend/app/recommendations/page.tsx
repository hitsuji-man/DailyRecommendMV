import { API_BASE_URL } from "@/lib/api";
import Image from "next/image";
import { formatRelativeDate } from "@/lib/formatRelativeDate";

type RecommendationResponse = {
  data: Recommendation;
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
};

async function getTodayRecommendations(): Promise<Recommendation> {
  const res = await fetch(`${API_BASE_URL}/api/v1/recommendations/today`, {
    // App Router ではこれが重要
    cache: "no-store", // 常に最新を取得（おすすめは日替わり想定）
  });

  if (!res.ok) {
    throw new Error("Failed to fetch recommendations");
  }

  const json: RecommendationResponse = await res.json();
  return json.data;
}

export default async function RecommendationsPage() {
  const recommendation = await getTodayRecommendations();

  return (
    <div className="p-6">
      <h1 className="text-xl font-bold mb-4 text-center">今日のおすすめMV</h1>

      {/* 画面中央に配置するラッパー */}
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

          <div className="flex items-center gap-3">
            {/* チャンネルサムネイル（擬似） */}
            <Image
              src={recommendation.thumbnail.url}
              alt={recommendation.channelTitle}
              width={36}
              height={36}
              className="rounded-full object-cover"
            />

            {/* チャンネル名 + 視聴回数 */}
            <div className="flex flex-col">
              {/* チャンネル名 */}
              <p className="text-base text-gray-700 hover:text-gray-900 cursor-pointer">
                投稿者: {recommendation.channelTitle}
              </p>

              {/* 視聴回数+投稿日時 */}
              <p className="text-sm text-gray-500">
                {recommendation.viewCount.toLocaleString()} 回視聴 ・{" "}
                {formatRelativeDate(recommendation.publishedAt)}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
