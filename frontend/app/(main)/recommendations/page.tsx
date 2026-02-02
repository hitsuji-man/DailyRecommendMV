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
            <li key={recommendation.id} className="flex gap-4">
              {/* サムネイル */}
              <Image
                src={recommendation.thumbnail.url}
                alt={recommendation.title}
                width={320}
                height={180}
                className="rounded-md mb-2"
              />
              <div>
                <p className="font-medium">{recommendation.title}</p>
                <p className="text-sm text-gray-600">
                  投稿者: {recommendation.channelTitle}
                </p>
                {/* 視聴回数 + 投稿日時 */}
                <p className="text-sm text-gray-500">
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
