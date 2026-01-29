"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import axios from "axios";
import { getDeviceId } from "@/lib/device";

export type User = {
  id: number;
  name: string;
};

export function useAuth() {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  const fetchUser = async () => {
    try {
      const res = await api.get("/user");
      setUser(res.data);
    } catch {
      setUser(null);
      localStorage.removeItem("access_token");
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    try {
      await api.post("/logout");
    } finally {
      localStorage.removeItem("access_token");
      setUser(null);
    }
  };

  const anonymousLogin = async () => {
    const deviceId = getDeviceId();

    try {
      // 匿名ログイン
      const res = await api.post("/anonymous-login", {
        device_id: deviceId,
      });

      // token を保存
      localStorage.setItem("access_token", res.data.token);

      await fetchUser();
    } catch (e) {
      if (axios.isAxiosError(e)) {
        console.log(e.response?.data);
      }
    }
  };

  // ログイン状態確認はtokenがある時だけ
  useEffect(() => {
    const token = localStorage.getItem("access_token");
    if (token) {
      fetchUser(); // ← tokenがある時だけ
    } else {
      setLoading(false);
    }
  }, []);

  return {
    user,
    loading,
    logout,
    anonymousLogin,
    refetchUser: fetchUser,
  };
}
