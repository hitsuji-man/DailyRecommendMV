"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuthContext } from "@/context/AuthContext";

/**
 * 認証必須ページ用 Hook
 * - 未ログインなら即 /login にリダイレクト
 * - ログアウトをリアルタイムで検知
 */
export function useRequireAuth() {
  const router = useRouter();
  const { user, loading } = useAuthContext();

  useEffect(() => {
    // 認証状態が確定していて、未ログインなら弾く
    if (!loading && !user) {
      router.replace("/login");
    }
  }, [user, loading, router]);

  return {
    user,
    loading,
    isAuthenticated: !!user,
  };
}
