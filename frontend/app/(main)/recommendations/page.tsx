"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { api } from "@/lib/api";
import { Recommendation } from "@/types/Recommendation";
import axios from "axios";
import RecommendationCard from "@/components/RecommendationCard";

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
    return <p className="p-6 text-center">読み込み中...</p>;
  }

  return (
    <div>
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
