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
import { toast } from "sonner";
import api from "@/lib/api/api";
import type { UserBadge } from "@/app/types/badge";
import BadgeIcon from "@/components/atoms/badges/BadgeIcon";

const BADGE_LABELS: Record<UserBadge["badgeName"], string> = {
  opened_booster: "Ouvreur de boosters",
  game_played: "Joueur assidu",
  game_win: "Vainqueur",
};

type BadgesContextValue = {
  badges: UserBadge[];
  refresh: () => Promise<void>;
  isLoading: boolean;
};

const BadgesContext = createContext<BadgesContextValue | undefined>(
  undefined,
);

const notifyLevelUps = (previous: UserBadge[], next: UserBadge[]) => {
  for (const badge of next) {
    const previousLevel =
      previous.find((b) => b.badgeName === badge.badgeName)?.level ?? 0;

    if (badge.level <= previousLevel) {
      continue;
    }

    toast.custom(() => (
      <div className="flex items-center gap-3 rounded-lg border bg-card p-3 shadow-lg">
        <BadgeIcon badgeName={badge.badgeName} level={badge.level} size={40} />
        <div>
          <p className="font-semibold">Succès débloqué !</p>
          <p className="text-sm text-muted-foreground">
            {BADGE_LABELS[badge.badgeName]} — niveau {badge.level}
          </p>
        </div>
      </div>
    ));
  }
};

export function BadgesProvider({
  children,
  enabled = true,
}: {
  children: ReactNode;
  enabled?: boolean;
}) {
  const [badges, setBadges] = useState<UserBadge[]>([]);
  const [isLoading, setIsLoading] = useState(enabled);
  const badgesRef = useRef<UserBadge[]>([]);
  const hasLoadedOnceRef = useRef(false);

  const refresh = useCallback(async () => {
    const next = await api.badge.getMine();
    if (hasLoadedOnceRef.current) {
      notifyLevelUps(badgesRef.current, next);
    }
    hasLoadedOnceRef.current = true;
    badgesRef.current = next;
    setBadges(next);
  }, []);

  const [prevEnabled, setPrevEnabled] = useState(enabled);

  // Resets badges state when disabled, computed during render
  // (see "Adjusting state in render" in the React docs).
  if (enabled !== prevEnabled) {
    setPrevEnabled(enabled);
    if (!enabled) {
      setBadges([]);
      setIsLoading(false);
    }
  }

  useEffect(() => {
    if (!enabled) {
      badgesRef.current = [];
      hasLoadedOnceRef.current = false;
      return;
    }

    // eslint-disable-next-line react-hooks/set-state-in-effect -- refresh() awaits a network call before setIsLoading resolves, it isn't synchronous
    refresh()
      .catch(() => {})
      .finally(() => setIsLoading(false));
  }, [enabled, refresh]);

  return (
    <BadgesContext.Provider value={{ badges, refresh, isLoading }}>
      {children}
    </BadgesContext.Provider>
  );
}

export function useBadgesContext() {
  const context = useContext(BadgesContext);
  if (context === undefined) {
    throw new Error("useBadgesContext must be used within a BadgesProvider");
  }

  return context;
}
