"use client";

import { useEffect, useState, useRef } from "react";
import { useRouter } from "next/navigation";
import { useAuthContext } from "@/context/AuthContext";
import { api } from "@/lib/api";
import { User } from "@/types/User";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faEye, faEyeSlash } from "@fortawesome/free-regular-svg-icons";

export default function UserPage() {
  const router = useRouter();
  const { user, loading: authLoading, refetchUser } = useAuthContext();

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

  const isGuest = user.is_guest;

  return (
    <div className="max-w-md mx-auto p-4 space-y-4">
      <h1 className="text-xl font-bold">ユーザー情報</h1>

      {/* 共通：ユーザー概要 */}
      <UserSummary user={user} />

      {/* ゲストユーザー */}
      {isGuest && <GuestNotice />}

      {/* 正規ユーザー */}
      {!isGuest && (
        <>
          <ProfileForm user={user} onUpdated={refetchUser} />
          <PasswordForm />
        </>
      )}
    </div>
  );
}

function UserSummary({ user }: { user: User }) {
  return (
    <div className="border rounded-md p-4 space-y-2">
      {/* <p>
        <span className="font-semibold">ID:</span> {user.id}
      </p> */}
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
        <span className="font-semibold">作成日:</span> {user.created_at ?? "-"}
      </p>
    </div>
  );
}

function GuestNotice() {
  const router = useRouter();

  return (
    <div className="border rounded-md p-4 bg-gray-50 space-y-3">
      <p className="text-sm text-gray-700">
        現在はゲストユーザーです。
        <br />
        会員登録すると、お気に入りと視聴履歴の引き継ぎや他端末ログインが可能になります。
      </p>
      <button
        onClick={() => router.push("/register")}
        className="w-full px-4 py-2 rounded-md bg-gray-900 text-white hover:bg-gray-800"
      >
        会員登録する
      </button>
    </div>
  );
}

function ProfileForm({
  user,
  onUpdated,
}: {
  user: User;
  onUpdated: () => void;
}) {
  const [name, setName] = useState(user.name);
  const [email, setEmail] = useState(user.email ?? "");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async () => {
    if (loading) return;
    setLoading(true);

    try {
      await api.post("/user", { name, email });
      await onUpdated();
      alert("ユーザー情報を更新しました");
    } catch (e) {
      alert("更新に失敗しました");
      throw e;
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="border rounded-md p-4 space-y-3">
      <h2 className="font-semibold">プロフィール編集</h2>

      <input
        className="w-full border rounded px-3 py-2"
        value={name}
        onChange={(e) => setName(e.target.value)}
        placeholder="名前"
      />

      <input
        className="w-full border rounded px-3 py-2"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        placeholder="メールアドレス"
      />

      <button
        onClick={handleSubmit}
        disabled={loading}
        className="w-full px-4 py-2 rounded-md bg-gray-900 text-white hover:bg-gray-800"
      >
        保存
      </button>
    </div>
  );
}

function PasswordForm() {
  const [current, setCurrent] = useState("");
  const [password, setPassword] = useState("");
  const [confirm, setConfirm] = useState("");
  const [loading, setLoading] = useState(false);
  const currentRef = useRef<HTMLInputElement>(null);
  const [showCurrent, setShowCurrent] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);

  const handleSubmit = async () => {
    if (loading) return;
    if (password !== confirm) {
      alert("パスワードが一致しません");
      return;
    }

    setLoading(true);

    try {
      await api.post("/user/password", {
        current_password: current,
        new_password: password,
        new_password_confirmation: confirm,
      });
      alert("パスワードを変更しました");
      setCurrent("");
      setPassword("");
      setConfirm("");
      // フォーカスを外す
      currentRef.current?.blur();
    } catch {
      alert("変更に失敗しました");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="border rounded-md p-4 space-y-3">
      <h2 className="font-semibold">パスワード変更</h2>

      {/* 現在のパスワード */}
      <div className="relative">
        <input
          type={showCurrent ? "text" : "password"}
          className="w-full border rounded px-3 py-2 pr-10"
          placeholder="現在のパスワード"
          value={current}
          onChange={(e) => setCurrent(e.target.value)}
        />

        <button
          type="button"
          onClick={() => setShowCurrent((v) => !v)}
          className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-800"
          aria-label={showCurrent ? "パスワードを隠す" : "パスワードを表示"}
        >
          <FontAwesomeIcon icon={showCurrent ? faEyeSlash : faEye} />
        </button>
      </div>

      {/* 新しいパスワード */}
      <div className="relative">
        <input
          type={showPassword ? "text" : "password"}
          className="w-full border rounded px-3 py-2 pr-10"
          placeholder="新しいパスワード"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
        />

        <button
          type="button"
          onClick={() => setShowPassword((v) => !v)}
          className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-800"
          aria-label={showPassword ? "パスワードを隠す" : "パスワードを表示"}
        >
          <FontAwesomeIcon icon={showPassword ? faEyeSlash : faEye} />
        </button>
      </div>

      {/* 新しいパスワード(確認) */}
      <div className="relative">
        <input
          type={showConfirm ? "text" : "password"}
          className="w-full border rounded px-3 py-2"
          placeholder="新しいパスワード（確認）"
          value={confirm}
          onChange={(e) => setConfirm(e.target.value)}
        />

        <button
          type="button"
          onClick={() => setShowConfirm((v) => !v)}
          className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-800"
          aria-label={showConfirm ? "パスワードを隠す" : "パスワードを表示"}
        >
          <FontAwesomeIcon icon={showConfirm ? faEyeSlash : faEye} />
        </button>
      </div>

      <button
        onClick={handleSubmit}
        disabled={loading}
        className="w-full px-4 py-2 rounded-md bg-gray-900 text-white hover:bg-gray-800"
      >
        パスワード変更
      </button>
    </div>
  );
}
