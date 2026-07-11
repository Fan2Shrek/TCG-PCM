import "server-only";

import { cookies } from "next/headers";

import { SESSION_COOKIE } from "@/lib/auth/constants";

const API_INTERNAL_URL = process.env.API_INTERNAL_URL || "http://php/api";

export async function getServerToken(): Promise<string | null> {
  const store = await cookies();
  return store.get(SESSION_COOKIE)?.value ?? null;
}

export async function serverApiFetch<T>(endpoint: string, options?: RequestInit): Promise<T> {
  const token = await getServerToken();
  const headers: HeadersInit = {
    "Content-Type": "application/json",
    ...(token && { Authorization: `Bearer ${token}` }),
    ...options?.headers,
  };

  const response = await fetch(`${API_INTERNAL_URL}${endpoint}`, {
    ...options,
    headers,
  });

  if (!response.ok) {
    if (response.status === 400 || response.status === 401) {
      const errorBody = (await response.json().catch(() => null)) as {
        detail?: string;
      } | null;

      if (errorBody?.detail) {
        throw new Error(errorBody.detail);
      }
    }

    throw new Error(`API request failed with status ${response.status}`);
  }

  if (response.status === 204) {
    return {} as T;
  }

  return response.json();
}

export function serverApiGet<T>(endpoint: string): Promise<T> {
  return serverApiFetch<T>(endpoint, { method: "GET" });
}

export function serverApiPost<T>(endpoint: string, body: unknown = {}): Promise<T> {
  return serverApiFetch<T>(endpoint, {
    method: "POST",
    body: JSON.stringify(body),
  });
}
