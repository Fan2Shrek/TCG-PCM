import "server-only";

import { redirect } from "next/navigation";

import { ApiError, serverApiFetch } from "@/lib/api/server";

// For authenticated page-level reads: redirects to /login on an expired/invalid
// JWT. Token refresh can't happen here — Next.js only allows cookie mutation from
// a Server Action or Route Handler, not during a Server Component's render (see
// /api/proxy/[...path]/route.ts, which does the refresh-and-retry for client reads).
export async function authApiFetch<T>(endpoint: string, options?: RequestInit): Promise<T> {
  try {
    return await serverApiFetch<T>(endpoint, options);
  } catch (err) {
    if (err instanceof ApiError && err.status === 401) {
      redirect("/login");
    }
    throw err;
  }
}

export function authApiGet<T>(endpoint: string): Promise<T> {
  return authApiFetch<T>(endpoint, { method: "GET" });
}
