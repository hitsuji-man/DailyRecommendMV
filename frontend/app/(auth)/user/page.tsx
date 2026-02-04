"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { api } from "@/lib/api";
import { User } from "@/types/User";
import axios from "axios";

export default function UserPage() {
  const router = useRouter();
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const res = await api.get("/user");
        setUser(res.data.data); // UserResource
      } catch (e) {
        if (axios.isAxiosError(e) && e.response?.status === 401) {
          router.push("/login");
          return;
        }
        console.error("Failed to fetch user", e);
      } finally {
        setLoading(false);
      }
    };

    fetchUser();
  }, [router]);

  if (loading) {
    return <p className="p-6 text-center">読み込み中...</p>;
  }

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
