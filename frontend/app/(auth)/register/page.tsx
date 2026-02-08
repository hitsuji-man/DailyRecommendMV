"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import axios from "axios";
import { useAuthContext } from "@/context/AuthContext";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faEye, faEyeSlash } from "@fortawesome/free-regular-svg-icons";

type ValidationErrors = {
  [key: string]: string[];
};

export default function RegisterPage() {
  const router = useRouter();

  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [showPassword, setShowPassword] = useState(false);

  const { user, loading: authLoading, register } = useAuthContext();

  // 既に正規ユーザーならトップへ
  useEffect(() => {
    if (!authLoading && user?.email) {
      router.replace("/");
    }
  }, [user, authLoading, router]);

  // 描画ガード
  if (authLoading || user?.email) {
    return null;
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (submitting) return;

    setSubmitting(true);
    setError(null);

    try {
      await register(name, email, password);
      router.push("/");
    } catch (e: unknown) {
      if (axios.isAxiosError(e)) {
        const status = e.response?.status;
        const errors = e.response?.data?.errors as ValidationErrors | undefined;

        if (status === 422 && errors) {
          if (errors.email) {
            setError("このメールアドレスは既に使われています");
          } else if (errors.password) {
            setError("パスワードは8文字以上で入力してください");
          } else if (errors.name) {
            setError("名前を入力してください");
          } else {
            setError("入力内容に誤りがあります");
          }
          return;
        }

        setError("ユーザー登録に失敗しました");
      } else {
        setError("予期しないエラーが発生しました");
      }
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="mx-auto max-w-sm mt-16">
      <h1 className="text-xl font-bold mb-6">ユーザー登録</h1>

      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="block text-sm mb-1">名前</label>
          <input
            type="text"
            className="w-full border rounded px-3 py-2"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
          />
        </div>

        <div>
          <label className="block text-sm mb-1">メールアドレス</label>
          <input
            type="email"
            className="w-full border rounded px-3 py-2"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </div>

        <div>
          <label className="block text-sm mb-1">パスワード</label>
          <div className="relative">
            <input
              type={showPassword ? "text" : "password"}
              className="w-full border rounded px-3 py-2"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              minLength={8}
            />

            <button
              type="button"
              onClick={() => setShowPassword((v) => !v)}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-800"
              aria-label={
                showPassword ? "パスワードを隠す" : "パスワードを表示"
              }
            >
              <FontAwesomeIcon icon={showPassword ? faEyeSlash : faEye} />
            </button>
          </div>
        </div>

        {error && <p className="text-red-600 text-sm">{error}</p>}

        <button
          type="submit"
          disabled={submitting}
          className="w-full bg-gray-900 text-white py-2 rounded hover:bg-gray-800 disabled:opacity-50"
        >
          {submitting ? "登録中…" : "登録する"}
        </button>
      </form>
    </div>
  );
}
