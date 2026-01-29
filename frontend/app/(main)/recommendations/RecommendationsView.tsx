import { API_BASE_URL } from "@/lib/api";
import Image from "next/image";
import { formatRelativeDate } from "@/lib/formatRelativeDate";
import HorizontalVideoList from "@/components/HorizontalVideoList";

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

type MixedDailyResponse = {
  data: Recommendation[];
};

/**
 * 本日のおすすめ動画を取得
 */
async function getTodayRecommendations(): Promise<Recommendation> {
  const res = await fetch(`${API_BASE_URL}/recommendations/today`, {
    // App Router ではこれが重要
    cache: "no-store", // 常に最新を取得（おすすめは日替わり想定）
  });

  if (!res.ok) {
    throw new Error("Failed to fetch recommendations");
  }

  const json: RecommendationResponse = await res.json();
  return json.data;
}

/**
 * ミックス動画リスト(トレンド+「Catch Up Japan」プレイリスト)を取得
 */
async function getMixedDailyVideos(): Promise<Recommendation[]> {
  const res = await fetch(`${API_BASE_URL}/videos/mixed-daily`, {
    cache: "no-store",
  });

  if (!res.ok) {
    throw new Error("Failed to fetch mixed daily videos");
  }

  const json: MixedDailyResponse = await res.json();
  return json.data;
}

export default async function RecommendationsView() {
  const [recommendation, mixedDailyVideos] = await Promise.all([
    getTodayRecommendations(),
    getMixedDailyVideos(),
  ]);

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

              {/* 視聴回数+投稿日時 */}
              <p className="text-sm text-gray-500">
                {recommendation.viewCount.toLocaleString()} 回視聴 ・{" "}
                {formatRelativeDate(recommendation.publishedAt)}
              </p>
            </div>
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
