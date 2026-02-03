"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { Recommendation } from "@/types/Recommendation";
import axios from "axios";
import RecommendationCard from "@/components/RecommendationCard";
import { useRequireAuth } from "@/hooks/useRequireAuth";

export default function RecommendationsPage() {
  const { user, loading: authLoading } = useRequireAuth();

  const [recommendations, setRecommendations] = useState<Recommendation[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return; // 未ログイン時は叩かない

    const fetchRecommendations = async () => {
      try {
        const res = await api.get("/recommendations");
        setRecommendations(res.data.data);
      } catch (e) {
        if (axios.isAxiosError(e) && e.response?.status === 401) {
          // 念のため（基本は useRequireAuth が拾う）
          return;
        }
        console.error(e);
      } finally {
        setLoading(false);
      }
    };

    fetchRecommendations();
  }, [user]);

  if (authLoading || loading) {
    return <p className="p-6 text-center">読み込み中...</p>;
  }

  return (
    <div className="max-w-5xl mx-auto px-4 py-6">
      <h1 className="text-xl font-bold mb-4">おすすめMV履歴</h1>

      {recommendations.length === 0 ? (
        <p className="text-sm text-gray-500">履歴はまだありません</p>
      ) : (
        <ul className="space-y-4">
          {recommendations.map((recommendation) => (
            <RecommendationCard
              key={recommendation.id}
              recommendation={recommendation}
            />
          ))}
        </ul>
      )}
    </div>
  );
}
