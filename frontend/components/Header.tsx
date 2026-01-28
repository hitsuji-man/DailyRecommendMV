"use client";

import Link from "next/link";
import { useAuth } from "@/hooks/useAuth";

export default function Header() {
  const { user, loading, logout, anonymousLogin } = useAuth();

  if (loading) return null; // or skeleton

  return (
    <header className="flex justify-between p-4 border-b">
      <Link href="/">MyApp</Link>

      <nav className="flex gap-4">
        {!user ? (
          <>
            <button onClick={anonymousLogin} className="cursor-pointer">
              ゲストでログイン
            </button>
            <Link href="/login">ログイン</Link>
            <Link href="/register">登録</Link>
          </>
        ) : (
          <>
            <Link href="/user">ユーザー情報</Link>
            <button onClick={logout} className="cursor-pointer">
              ログアウト
            </button>
          </>
        )}
      </nav>
    </header>
  );
}
