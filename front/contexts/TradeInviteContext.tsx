"use client";

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useRef,
  useState,
  type ReactNode,
} from "react";
import client from "@/lib/api/api";
import { useCurrentUser } from "@/hooks/useCurrentUser";
import { Trade } from "@/types/trade";

// Mirrors FriendshipContext's polling approach: a Trade's Mercure topic cookie is only
// authorized once the session page mints it (see TradeContext.subscribe), so a global
// "do I have an active trade" check has to be a plain poll rather than a push notification.
const POLL_INTERVAL_MS = 15_000;

type TradeInviteContextValue = {
  activeTrade: Trade | null;
  refresh: () => Promise<void>;
};

const TradeInviteContext = createContext<TradeInviteContextValue | undefined>(undefined);

export function TradeInviteProvider({
  children,
  enabled = true,
}: {
  children: ReactNode;
  enabled?: boolean;
}) {
  const { user: currentUser } = useCurrentUser();
  const [activeTrade, setActiveTrade] = useState<Trade | null>(null);
  const previousIdRef = useRef<string | null>(null);

  const refresh = useCallback(async () => {
    if (!enabled) return;

    try {
      const trade = await client.trade.getActive();
      const isNewInvite =
        trade && trade.id !== previousIdRef.current && trade.recipient.username === currentUser?.username;
      if (isNewInvite) {
        import("sonner").then(({ toast }) =>
          toast.info(`${trade.initiator.username} propose un échange.`),
        );
      }
      previousIdRef.current = trade?.id ?? null;
      setActiveTrade(trade);
    } catch (error) {
      console.error("Failed to fetch active trade:", error);
    }
  }, [enabled, currentUser]);

  const [prevEnabled, setPrevEnabled] = useState(enabled);

  // Resets trade-invite state when disabled, computed during render
  // (see "Adjusting state in render" in the React docs).
  if (enabled !== prevEnabled) {
    setPrevEnabled(enabled);
    if (!enabled) {
      setActiveTrade(null);
    }
  }

  useEffect(() => {
    if (!enabled) {
      previousIdRef.current = null;
      return;
    }

    // eslint-disable-next-line react-hooks/set-state-in-effect -- refresh() awaits a network call before setActiveTrade resolves, it isn't synchronous
    refresh();
    const interval = setInterval(refresh, POLL_INTERVAL_MS);
    return () => clearInterval(interval);
  }, [enabled, refresh]);

  return (
    <TradeInviteContext.Provider value={{ activeTrade, refresh }}>
      {children}
    </TradeInviteContext.Provider>
  );
}

export function useTradeInvite() {
  const context = useContext(TradeInviteContext);
  if (context === undefined) {
    throw new Error("useTradeInvite must be used within a TradeInviteProvider");
  }
  return context;
}
