"use client";

import { useEffect, useState } from "react";

import type { SessionUser } from "@/lib/auth/session";

export function useCurrentUser() {
  const [user, setUser] = useState<SessionUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    fetch("/api/session")
      .then((res) => res.json())
      .then((data) => {
        if (!cancelled) setUser(data.user ?? null);
      })
      .finally(() => {
        if (!cancelled) setIsLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, []);

  return { user, isLoading };
}
