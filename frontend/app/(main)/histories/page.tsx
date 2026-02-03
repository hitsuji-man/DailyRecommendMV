"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import axios from "axios";
import { useRouter } from "next/navigation";
import { History } from "@/types/History";
import HistoryItem from "@/components/HistoryItem";

export default function HistoriesPage() {
  const [histories, setHistories] = useState<History[]>([]);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    const fetchHistories = async () => {
      try {
        const res = await api.get("/histories");
        setHistories(res.data.data);
      } catch (e) {
        if (axios.isAxiosError(e) && e.response?.status === 401) {
          router.push("/login");
          return; // ここで処理終了
        }
        console.error("Failed to fetch histories", e);
      } finally {
        setLoading(false);
      }
    };

    fetchHistories();
  }, [router]);

  /** videoDbId で削除 */
  const handleDelete = async (id: number) => {
    try {
      await api.delete(`/histories/${id}`);
      setHistories((prev) => prev.filter((h) => h.id !== id));
    } catch {
      alert("視聴履歴の削除に失敗しました");
    }
  };

  if (loading) return <p className="p-6 text-center">読み込み中...</p>;

  if (histories.length === 0) {
    return (
      <p className="text-gray-500 p-6 text-center">視聴履歴はありません</p>
    );
  }

  return (
    <div className="max-w-5xl mx-auto px-4 py-6">
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
