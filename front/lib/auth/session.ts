import "server-only";

import { getServerToken } from "@/lib/api/server";

export type SessionUser = {
  username: string;
};

export async function getCurrentUser(): Promise<SessionUser | null> {
  const token = await getServerToken();
  if (!token) return null;

  try {
    const payload = JSON.parse(Buffer.from(token.split(".")[1], "base64").toString("utf-8"));
    return { username: payload.username };
  } catch {
    return null;
  }
}
