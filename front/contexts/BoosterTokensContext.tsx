"use client";

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useRef,
  useState,
  type ReactNode,
} from "react";
import api from "@/lib/api/api";

const MAX_BOOSTER_TOKENS = 5;
const BOOSTER_TOKEN_INTERVAL_HOURS = 12;
const BOOSTER_TOKEN_INTERVAL_MINUTES = BOOSTER_TOKEN_INTERVAL_HOURS * 60;
const ONE_MINUTE_MS = 60 * 1000;
const ONE_INTERVAL_MS = BOOSTER_TOKEN_INTERVAL_MINUTES * ONE_MINUTE_MS;

type GenerateBoosterTokensResponse = {
  tokens: number;
  lastBoosterTokensAt: string;
};

type BoosterTokensContextValue = {
  tokens: number;
  minutesTilNextToken: number;
  maxTokens: number;
  progressToNextToken: number;
  refresh: () => Promise<void>;
  consumeOne: () => void;
  isLoading: boolean;
  error: string | null;
};

const toValidDate = (value: string | null): Date | null => {
  if (!value) {
    return null;
  }

  const parsed = new Date(value);
  return Number.isNaN(parsed.getTime()) ? null : parsed;
};

const getMinutesTilNextToken = (
  tokens: number,
  lastClaimedAt: string | null,
) => {
  if (tokens >= MAX_BOOSTER_TOKENS) {
    return 0;
  }

  const parsedDate = toValidDate(lastClaimedAt);
  if (!parsedDate) {
    return BOOSTER_TOKEN_INTERVAL_MINUTES;
  }

  const elapsedMs = Date.now() - parsedDate.getTime();
  if (elapsedMs >= ONE_INTERVAL_MS) {
    return 0;
  }

  return Math.ceil((ONE_INTERVAL_MS - elapsedMs) / ONE_MINUTE_MS);
};

const BoosterTokensContext = createContext<
  BoosterTokensContextValue | undefined
>(undefined);

export function BoosterTokensProvider({ children }: { children: ReactNode }) {
  const [tokens, setTokens] = useState(0);
  const [lastBoosterTokenClaimedAt, setLastBoosterTokenClaimedAt] = useState<
    string | null
  >(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const hasTriggeredAutoRefreshAtZeroRef = useRef(false);
  const [ticker, setTicker] = useState(0);

  const applyGenerateResponse = useCallback(
    ({
      tokens: nextTokens,
      lastBoosterTokensAt,
    }: GenerateBoosterTokensResponse) => {
      setTokens(nextTokens);
      setLastBoosterTokenClaimedAt(lastBoosterTokensAt);
    },
    [],
  );

  const refresh = useCallback(async () => {
    setError(null);

    try {
      const response =
        (await api.user.generateBoosterTokens()) as GenerateBoosterTokensResponse;
      applyGenerateResponse(response);
    } catch (err) {
      const message =
        err instanceof Error ? err.message : "Failed to refresh booster tokens";
      setError(message);
      throw err;
    }
  }, [applyGenerateResponse]);

  const consumeOne = useCallback(() => {
    setTokens((prev) => Math.max(0, prev - 1));
  }, []);

  useEffect(() => {
    refresh().finally(() => {
      setIsLoading(false);
    });
  }, [refresh]);

  useEffect(() => {
    const interval = window.setInterval(() => {
      setTicker((prev) => prev + 1);
    }, 60 * 1000);

    return () => window.clearInterval(interval);
  }, []);

  const minutesTilNextToken = useMemo(() => {
    void ticker;
    return getMinutesTilNextToken(tokens, lastBoosterTokenClaimedAt);
  }, [tokens, lastBoosterTokenClaimedAt, ticker]);

  useEffect(() => {
    if (tokens >= MAX_BOOSTER_TOKENS || minutesTilNextToken > 0) {
      hasTriggeredAutoRefreshAtZeroRef.current = false;
      return;
    }

    if (hasTriggeredAutoRefreshAtZeroRef.current) {
      return;
    }

    hasTriggeredAutoRefreshAtZeroRef.current = true;
    refresh().catch(() => {});
  }, [tokens, minutesTilNextToken, refresh]);

  const progressToNextToken = useMemo(() => {
    if (tokens >= MAX_BOOSTER_TOKENS) {
      return 100;
    }

    const progress =
      ((BOOSTER_TOKEN_INTERVAL_MINUTES - minutesTilNextToken) /
        BOOSTER_TOKEN_INTERVAL_MINUTES) *
      100;

    return Math.max(0, Math.min(100, progress));
  }, [tokens, minutesTilNextToken]);

  const value = useMemo(
    () => ({
      tokens,
      minutesTilNextToken,
      maxTokens: MAX_BOOSTER_TOKENS,
      progressToNextToken,
      refresh,
      consumeOne,
      isLoading,
      error,
    }),
    [
      tokens,
      minutesTilNextToken,
      progressToNextToken,
      refresh,
      consumeOne,
      isLoading,
      error,
    ],
  );

  return (
    <BoosterTokensContext.Provider value={value}>
      {children}
    </BoosterTokensContext.Provider>
  );
}

export function useBoosterTokensContext() {
  const context = useContext(BoosterTokensContext);
  if (context === undefined) {
    throw new Error(
      "useBoosterTokensContext must be used within a BoosterTokensProvider",
    );
  }

  return context;
}
