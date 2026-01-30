"use client";

import Link from "next/link";
import { useAuthContext } from "@/context/AuthContext";

export default function Header() {
  // authVersionは未使用。購読のみ
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { user, authVersion, loading, logout, anonymousLogin } =
    useAuthContext();

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
