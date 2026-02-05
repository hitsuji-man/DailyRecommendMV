"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuthContext } from "@/context/AuthContext";

export default function UserPage() {
  const router = useRouter();
  const { user, loading: authLoading } = useAuthContext();

  // 認証ガード（副作用だけを書く）
  useEffect(() => {
    if (!authLoading && !user) {
      router.replace("/login");
    }
  }, [authLoading, user, router]);

  // TODO:表示ローディング（派生状態）
  if (authLoading) {
    return <p className="p-6 text-center">読み込み中...</p>;
  }

  // リダイレクト中
  if (!user) {
    return null;
  }

  return (
    <div className="max-w-md mx-auto p-4 space-y-4">
      <h1 className="text-xl font-bold">ユーザー情報</h1>

      <div className="border rounded-md p-4 space-y-2">
        <p>
          <span className="font-semibold">ID:</span> {user.id}
        </p>
        <p>
          <span className="font-semibold">名前:</span> {user.name}
        </p>
        <p>
          <span className="font-semibold">Email:</span>{" "}
          {user.email ?? "（未設定）"}
        </p>
        <p>
          <span className="font-semibold">種別:</span>{" "}
          {user.is_guest ? "ゲストユーザー" : "登録ユーザー"}
        </p>
        <p>
          <span className="font-semibold">作成日:</span>{" "}
          {user.created_at ?? "-"}
        </p>
      </div>
    </div>
  );
}
