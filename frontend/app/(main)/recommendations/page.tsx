"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { api } from "@/lib/api";
import { Recommendation } from "@/types/Recommendation";
import Image from "next/image";
import axios from "axios";
import { formatRelativeDate } from "@/lib/formatRelativeDate";

export default function RecommendationsPage() {
  const router = useRouter();

  const [recommendations, setRecommendations] = useState<Recommendation[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchRecommendations = async () => {
      try {
        const res = await api.get("/recommendations");
        setRecommendations(res.data.data);
      } catch (e) {
        if (axios.isAxiosError(e) && e.response?.status === 401) {
          router.replace("/login");
          return;
        }
        console.error(e);
      } finally {
        setLoading(false);
      }
    };

    fetchRecommendations();
  }, [router]);

  if (loading) {
    return <p>読み込み中...</p>;
  }

  return (
    <div>
      <h1 className="text-xl font-bold mb-4">おすすめMV履歴</h1>

      {recommendations.length === 0 ? (
        <p className="text-sm text-gray-500">履歴はまだありません</p>
      ) : (
        <ul className="space-y-4">
          {recommendations.map((recommendation) => (
            <li key={recommendation.id} className="flex gap-3 items-start">
              {/* サムネイル */}
              <div className="flex-shrink-0">
                <Image
                  src={recommendation.thumbnail.url}
                  alt={recommendation.title}
                  width={160}
                  height={90}
                  className="rounded-md mb-2"
                />
              </div>
              {/* タイトル + チャンネル名 + 視聴回数 + 投稿日時 */}
              <div className="flex-1 min-w-0">
                {/* タイトル */}
                <p className="font-large text-sm sm:text-base line-clamp-3">
                  {recommendation.title}
                </p>

                {/* チャンネル名(投稿者) */}
                <p className="text-xs sm:text-sm text-gray-600 mt-1">
                  投稿者: {recommendation.channelTitle}
                </p>

                {/* 視聴回数 + 投稿日時 */}
                <p className="text-xs sm:text-sm text-gray-500 mt-1">
                  {recommendation.viewCount.toLocaleString()} 回視聴 ・{" "}
                  {formatRelativeDate(recommendation.publishedAt)}
                </p>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
