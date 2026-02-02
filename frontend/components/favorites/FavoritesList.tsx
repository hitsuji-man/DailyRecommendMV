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

  if (loading) return <div className="p-6 text-center">読み込み中...</div>;

  return (
    <ul className="space-y-4">
      {videos.map((video) => (
        <FavoriteVideoItem
          key={`${video.userId}-${video.videoDbId}`}
          video={video}
        />
      ))}
    </ul>
  );
}
