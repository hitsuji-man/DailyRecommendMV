import { v4 as uuidv4 } from "uuid";

const KEY = "device_id";

export function getDeviceId(): string {
  if (typeof window === "undefined") return "";
  let id = localStorage.getItem(KEY);
  if (!id) {
    id = uuidv4();
    localStorage.setItem(KEY, id);
  }
  return id;
}

/**
 * 新しいゲストとして開始するための device_id 再生成
 * 「ゲストユーザー」→ 「登録ユーザーに昇格」→ 「ログアウトしてゲストでログイン」する際にdevice_id再生成
 */
export function resetDeviceId(): string {
  const id = uuidv4();
  localStorage.setItem(KEY, id);
  return id;
}
