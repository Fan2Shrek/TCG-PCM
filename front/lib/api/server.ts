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
  const isFormData = options?.body instanceof FormData;
  const headers: HeadersInit = {
    ...(!isFormData && { "Content-Type": "application/json" }),
    ...(token && { Authorization: `Bearer ${token}` }),
    ...options?.headers,
  };

  const response = await fetch(`${API_INTERNAL_URL}${endpoint}`, {
    ...options,
    headers,
  });

  if (!response.ok) {
    const errorBody = (await response.json().catch(() => null)) as {
      detail?: string;
      message?: string;
    } | null;

    if (errorBody?.detail) {
      throw new Error(errorBody.detail);
    }

    // Lexik's login failure handler (invalid credentials, throttling) returns
    // { code, message } instead of API Platform's { detail }.
    if (errorBody?.message) {
      throw new Error(errorBody.message);
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

export function serverApiPostFormData<T>(endpoint: string, body: FormData): Promise<T> {
  return serverApiFetch<T>(endpoint, {
    method: "POST",
    body,
  });
}
