"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { FavoriteVideo } from "@/types/FavoriteVideo";
import FavoriteVideoItem from "./FavoriteVideoItem";

export default function FavoritesList() {
  const [videos, setVideos] = useState<FavoriteVideo[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api
      .get("/favorites")
      .then((res) => setVideos(res.data.data))
      .finally(() => setLoading(false));
  }, []);

  // お気に入り解除したら即一覧から消す(解除されたその1件以外を残す)
  const handleUnfavorite = (userId: number, videoDbId: number) => {
    setVideos((prev) =>
      prev.filter((v) => !(v.userId === userId && v.videoDbId === videoDbId)),
    );
  };

  if (loading) return <div className="p-6 text-center">読み込み中...</div>;

  // お気に入りが空(0件)の時の表示
  if (videos.length === 0) {
    return (
      <div className="py-16 text-center text-gray-500">
        <p className="text-lg">お気に入りはまだありません</p>
        <p className="mt-2 text-sm">
          気になる動画を見つけたら ❤️ を押してみましょう
        </p>
      </div>
    );
  }
  return (
    <ul className="space-y-4">
      {videos.map((video) => (
        <FavoriteVideoItem
          key={`${video.userId}-${video.videoDbId}`}
          video={video}
          onUnfavorite={handleUnfavorite}
        />
      ))}
    </ul>
  );
}
