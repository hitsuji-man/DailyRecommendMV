import axios from "axios";

const baseUrl = process.env.NEXT_PUBLIC_API_BASE_URL;

if (!baseUrl) {
  throw new Error("NEXT_PUBLIC_API_BASE_URL is not defined");
}

export const API_BASE_URL = baseUrl;

export const api = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true, // ★ Sanctum / Cookie 前提
  headers: {
    "Content-Type": "application/json",
  },
});
