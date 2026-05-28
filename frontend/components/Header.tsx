"use client";

import Link from "next/link";
import { useState } from "react";
import { Bars3Icon } from "@heroicons/react/24/outline";
import { useAuthContext } from "@/context/AuthContext";

export default function Header() {
  // authVersionは未使用。購読のみ
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { user, authVersion, loading, logout, isLoggingOut, anonymousLogin } =
    useAuthContext();
  const [isMenuOpen, setIsMenuOpen] = useState(false);

  if (loading) return null; // or skeleton

  const closeMenu = () => setIsMenuOpen(false);

  const handleAnonymousLogin = () => {
    closeMenu();
    anonymousLogin();
  };

  const handleLogout = () => {
    closeMenu();
    logout();
  };

  const navItems = !user ? (
    <>
      <button
        onClick={handleAnonymousLogin}
        className="p-0 text-left text-gray-100 cursor-pointer"
      >
        ゲストでログイン
      </button>
      <Link href="/login" onClick={closeMenu} className="text-gray-100">
        ログイン
      </Link>
      <Link href="/register" onClick={closeMenu} className="text-gray-100">
        登録
      </Link>
    </>
  ) : (
    <>
      <Link href="/user" onClick={closeMenu} className="text-gray-100">
        ユーザー情報
      </Link>
      <button
        onClick={handleLogout}
        disabled={isLoggingOut}
        className="p-0 text-left text-gray-100 cursor-pointer disabled:cursor-not-allowed disabled:opacity-60"
      >
        ログアウト
      </button>
    </>
  );

  return (
    <header className="relative flex items-center justify-between p-4 bg-gray-800">
      <Link href="/" className="text-gray-100">
        TOP
      </Link>
      <nav className="hidden gap-4 md:flex">{navItems}</nav>
      <div className="md:hidden">
        <button
          type="button"
          onClick={() => setIsMenuOpen((current) => !current)}
          aria-label="メニューを開閉"
          aria-expanded={isMenuOpen}
          className="flex flex-col items-center gap-1 text-gray-100 cursor-pointer"
        >
          <Bars3Icon className="h-6 w-6" aria-hidden="true" />
          <span className="text-xs leading-none">メニュー</span>
        </button>
      </div>
      <nav
        className={`fixed right-0 top-[72px] z-10 w-[45vw] min-w-44 max-w-64 flex-col gap-5 bg-gray-800/90 p-5 shadow-lg backdrop-blur-sm md:hidden ${
          isMenuOpen ? "flex" : "hidden"
        }`}
      >
        {navItems}
      </nav>
    </header>
  );
}
