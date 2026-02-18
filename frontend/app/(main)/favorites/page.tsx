"use client";

import { useRequireAuth } from "@/hooks/useRequireAuth";
import FavoritesList from "@/components/favorites/FavoritesList";

export default function FavoritesPage() {
  const { loading, isAuthenticated } = useRequireAuth();

  if (loading || !isAuthenticated) return null;

  if (loading || !isAuthenticated) {
    return <p className="p-6 text-center">読み込み中...</p>;
  }

  return (
    <div className="max-w-5xl mx-auto px-4 py-6">
      <h1 className="text-xl font-bold mb-4">お気に入り一覧</h1>
      <FavoritesList />
    </div>
  );
}
