"use client";

import { useCallback, useEffect, useState } from "react";
import { api } from "@/lib/api";
import axios from "axios";
import { getDeviceId, resetDeviceId } from "@/lib/device";
import { User } from "@/types/User";
import { WAS_REGISTERED_USER } from "@/lib/authFlags";

export function useAuth() {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [authVersion, setAuthVersion] = useState(0);
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  /**
   * ユーザー情報取得
   */
  const refetchUser = useCallback(async () => {
    try {
      const res = await api.get("/user");
      setUser(res.data.data);
    } catch {
      setUser(null);
      localStorage.removeItem("access_token");
    } finally {
      setLoading(false);
    }
  }, []);

  /**
   * ログアウト
   */
  const logout = async () => {
    if (isLoggingOut) return; // 二重防止

    setIsLoggingOut(true);
    try {
      await api.post("/logout");
    } catch (e) {
      // 401 は「すでにログアウト済み」なので正常系
      if (!(axios.isAxiosError(e) && e.response?.status === 401)) {
        throw e;
      }
    } finally {
      localStorage.removeItem("access_token");
      setUser(null);
      setAuthVersion((v) => v + 1);
      setIsLoggingOut(false);
    }
  };

  /**
   * 匿名ログイン(ゲストでログイン)
   */
  const anonymousLogin = async () => {
    // 直前が登録ユーザーだったか？
    const wasRegistered = localStorage.getItem(WAS_REGISTERED_USER) === "1";

    // device_id を決定
    const deviceId = wasRegistered
      ? resetDeviceId() // 直前が登録ユーザーのケースだけ新規発行
      : getDeviceId();

    try {
      // 匿名ログイン
      const res = await api.post("/anonymous-login", {
        device_id: deviceId,
      });

      // token を保存
      localStorage.setItem("access_token", res.data.token);

      // 登録済みフラグは使い切りなので消す
      if (wasRegistered) {
        localStorage.removeItem(WAS_REGISTERED_USER);
      }

      // トークンが変わったら authVersion を+1する
      setAuthVersion((v) => v + 1);
    } catch (e) {
      throw e;
    }
  };

  /**
   * ログイン
   */
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

      // 正規ログインしたら不要
      localStorage.removeItem(WAS_REGISTERED_USER);

      // トークンが変わったら authVersion を+1する
      setAuthVersion((v) => v + 1);
    } catch (e) {
      throw e;
    }
  };

  /**
   * ユーザー登録
   */
  const register = async (name: string, email: string, password: string) => {
    const deviceId = getDeviceId();

    try {
      const res = await api.post("/register", {
        name,
        email,
        password,
        device_id: deviceId,
      });

      // token 保存
      localStorage.setItem("access_token", res.data.token);

      /**
       * ゲスト → 正規ユーザーに昇格した場合のみフラグを立てる
       * 条件文: ゲストユーザーの場合
       */
      if (user !== null && user.email === null) {
        localStorage.setItem(WAS_REGISTERED_USER, "1");
      }

      // 認証状態更新トリガ
      setAuthVersion((v) => v + 1);
    } catch (e) {
      throw e;
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
    logout,
    isLoggingOut,
    anonymousLogin,
    login,
    register,
    refetchUser,
  };
}
