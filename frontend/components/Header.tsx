"use client";

import Link from "next/link";
import { useAuth } from "@/hooks/useAuth";

export default function Header() {
  const { user, loading, logout, anonymousLogin } = useAuth();

  if (loading) return null; // or skeleton

  return (
    <header className="flex justify-between p-4 bg-gray-800">
      <Link href="/" className="text-gray-100">
        MyApp
      </Link>
      <nav className="flex gap-4">
        {!user ? (
          <>
            <button
              onClick={anonymousLogin}
              className="text-gray-100 cursor-pointer"
            >
              ゲストでログイン
            </button>
            <Link href="/login" className="text-gray-100">
              ログイン
            </Link>
            <Link href="/register" className="text-gray-100">
              登録
            </Link>
          </>
        ) : (
          <>
            <Link href="/user" className="text-gray-100">
              ユーザー情報
            </Link>
            <button onClick={logout} className="text-gray-100 cursor-pointer">
              ログアウト
            </button>
          </>
        )}
      </nav>
    </header>
  );
}
