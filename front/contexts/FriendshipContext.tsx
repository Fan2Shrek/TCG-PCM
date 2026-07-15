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
import { Friendship } from "@/types/friendship";

// Friend requests use polling rather than Mercure: the hub's subscriber cookie only
// authorizes the topic list it was minted for (e.g. a specific `trade/{id}` or `game/{id}`
// session), so a permanent per-user topic like `friendships/{username}` would need its own
// login-time token and would clobber whichever session topic was last authorized.
const POLL_INTERVAL_MS = 20_000;

type FriendshipContextValue = {
  friends: Friendship[];
  pendingRequests: Friendship[];
  isLoading: boolean;
  refresh: () => Promise<void>;
};

const FriendshipContext = createContext<FriendshipContextValue | undefined>(undefined);

export function FriendshipProvider({
  children,
  enabled = true,
}: {
  children: ReactNode;
  enabled?: boolean;
}) {
  const [friends, setFriends] = useState<Friendship[]>([]);
  const [pendingRequests, setPendingRequests] = useState<Friendship[]>([]);
  const [isLoading, setIsLoading] = useState(enabled);
  const pendingRef = useRef<Friendship[]>([]);

  const refresh = useCallback(async () => {
    if (!enabled) return;

    try {
      const [friendsList, pending] = await Promise.all([
        client.friend.list(),
        client.friend.pending(),
      ]);
      setFriends(friendsList);
      if (pending.length > pendingRef.current.length) {
        const newest = pending.find(
          (request) => !pendingRef.current.some((existing) => existing.id === request.id),
        );
        if (newest) {
          import("sonner").then(({ toast }) =>
            toast.info(`${newest.requester.username} vous a envoyé une demande d'ami.`),
          );
        }
      }
      pendingRef.current = pending;
      setPendingRequests(pending);
    } catch (error) {
      console.error("Failed to fetch friendships:", error);
    }
  }, [enabled]);

  const [prevEnabled, setPrevEnabled] = useState(enabled);

  // Resets friendship state when disabled, computed during render
  // (see "Adjusting state in render" in the React docs).
  if (enabled !== prevEnabled) {
    setPrevEnabled(enabled);
    if (!enabled) {
      setFriends([]);
      setPendingRequests([]);
      setIsLoading(false);
    }
  }

  useEffect(() => {
    if (!enabled) {
      pendingRef.current = [];
      return;
    }

    // eslint-disable-next-line react-hooks/set-state-in-effect -- refresh() awaits a network call before setIsLoading resolves, it isn't synchronous
    refresh().then(() => setIsLoading(false));
    const interval = setInterval(refresh, POLL_INTERVAL_MS);
    return () => clearInterval(interval);
  }, [enabled, refresh]);

  return (
    <FriendshipContext.Provider value={{ friends, pendingRequests, isLoading, refresh }}>
      {children}
    </FriendshipContext.Provider>
  );
}

export function useFriendship() {
  const context = useContext(FriendshipContext);
  if (context === undefined) {
    throw new Error("useFriendship must be used within a FriendshipProvider");
  }
  return context;
}
