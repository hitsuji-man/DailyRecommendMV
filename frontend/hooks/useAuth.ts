"use client";

import { useEffect, useState } from "react";
import { api } from "@/lib/api";

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
    } finally {
      setLoading(false);
    }
  };

  const logout = async () => {
    await api.post("/logout");
    setUser(null);
  };

  const anonymousLogin = async () => {
    await api.post("/anonymous-login");
    await fetchUser();
  };

  useEffect(() => {
    fetchUser();
  }, []);

  return {
    user,
    loading,
    logout,
    anonymousLogin,
    refetchUser: fetchUser,
  };
}
