import axios from "axios";

const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;

if (!baseUrl) {
  throw new Error("NEXT_PUBLIC_API_BASE_URL is not defined");
}

export const API_BASE_URL = baseUrl;

export const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

// Authorization 自動付与
api.interceptors.request.use((config) => {
  if (typeof window !== "undefined") {
    const token = localStorage.getItem("access_token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  }
  return config;
});
