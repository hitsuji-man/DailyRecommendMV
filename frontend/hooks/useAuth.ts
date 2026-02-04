"use client";

import { useCallback, useEffect, useState } from "react";
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
  const [authVersion, setAuthVersion] = useState(0);
  // isLoggingOutは未使用
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  const refetchUser = useCallback(async () => {
    try {
      const res = await api.get("/user");
      setUser(res.data);
    } catch {
      setUser(null);
      localStorage.removeItem("access_token");
    } finally {
      setLoading(false);
    }
  }, []);

  const logout = async () => {
    setIsLoggingOut(true);
    try {
      await api.post("/logout");
    } finally {
      localStorage.removeItem("access_token");
      setUser(null);
      setAuthVersion((v) => v + 1);
      setIsLoggingOut(false);
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

      // トークンが変わったら authVersion を+1する
      setAuthVersion((v) => v + 1);
    } catch (e) {
      if (axios.isAxiosError(e)) {
        console.log(e.response?.data);
      }
    }
  };

  const login = async (email: string, password: string) => {
    const deviceId = getDeviceId();

    try {
      const res = await api.post("/login", {
        email,
        password,
        device_id: deviceId,
      });

      // token 保存
      localStorage.setItem("access_token", res.data.token);
      // 必要なら user も保存
      // localStorage.setItem("user", JSON.stringify(res.data.user));

      // トークンが変わったら authVersion を+1する
      setAuthVersion((v) => v + 1);
    } catch (e) {
      if (axios.isAxiosError(e)) {
        console.log(e.response?.data);
      }
    }
  };

  // ログイン状態確認はtokenがある時だけ
  useEffect(() => {
    const token = localStorage.getItem("access_token");
    if (!token) {
      setLoading(false);
      return;
    }
    refetchUser();
  }, [refetchUser]);

  return {
    user,
    loading,
    authVersion,
    login,
    logout,
    anonymousLogin,
    refetchUser,
  };
}
