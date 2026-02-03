"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { History } from "@/types/History";
import HistoryItem from "@/components/HistoryItem";

export default function HistoriesPage() {
  const [histories, setHistories] = useState<History[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchHistories = async () => {
      try {
        const res = await api.get("/histories");
        setHistories(res.data.data);
      } catch (e) {
        console.error("Failed to fetch histories", e);
      } finally {
        setLoading(false);
      }
    };

    fetchHistories();
  }, []);

  /** videoDbId で削除 */
  const handleDelete = async (videoDbId: number) => {
    try {
      await api.delete(`/histories/${videoDbId}`);
      setHistories((prev) => prev.filter((h) => h.videoDbId !== videoDbId));
    } catch {
      alert("視聴履歴の削除に失敗しました");
    }
  };

  if (loading) return <p className="p-6 text-center">読み込み中...</p>;

  if (histories.length === 0) {
    return <p className="text-gray-500">視聴履歴はありません</p>;
  }

  return (
    <div>
      <h1 className="text-xl font-bold mb-4">視聴履歴</h1>

      <div className="space-y-4">
        {histories.map((history) => (
          <HistoryItem
            key={`${history.videoDbId}-${history.viewedAt}`}
            history={history}
            onDelete={handleDelete}
          />
        ))}
      </div>
    </div>
  );
}
