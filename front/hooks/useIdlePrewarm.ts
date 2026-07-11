"use client";

import { useEffect, useState } from "react";

type IdleApi = {
  requestIdleCallback?: (
    cb: () => void,
    options?: { timeout?: number },
  ) => number;
  cancelIdleCallback?: (id: number) => void;
};

type UseIdlePrewarmOptions = {
  disabled?: boolean;
  timeout?: number;
};

export function useIdlePrewarm(options: UseIdlePrewarmOptions = {}) {
  const { disabled = false, timeout = 300 } = options;
  const [isPrewarmed, setIsPrewarmed] = useState(false);

  useEffect(() => {
    if (disabled || isPrewarmed) {
      return;
    }

    let timeoutId: number | undefined;
    let idleId: number | undefined;

    const warm = () => setIsPrewarmed(true);
    const idleApi = window as Window & IdleApi;

    if (typeof idleApi.requestIdleCallback === "function") {
      idleId = idleApi.requestIdleCallback(warm, { timeout });
    } else {
      timeoutId = window.setTimeout(warm, 0);
    }

    return () => {
      if (timeoutId !== undefined) {
        window.clearTimeout(timeoutId);
      }

      if (
        idleId !== undefined &&
        typeof idleApi.cancelIdleCallback === "function"
      ) {
        idleApi.cancelIdleCallback(idleId);
      }
    };
  }, [disabled, timeout, isPrewarmed]);

  return isPrewarmed;
}
