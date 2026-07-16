"use client";

import type { UserBadge } from "@/app/types/badge";
import { useBadgesContext } from "@/contexts/BadgesContext";
import BadgeIcon from "@/components/atoms/badges/BadgeIcon";

const BADGE_LABELS: Record<UserBadge["badgeName"], string> = {
  opened_booster: "Ouvreur de boosters",
  game_played: "Joueur assidu",
  game_win: "Vainqueur",
};

const BADGE_DESCRIPTIONS: Record<UserBadge["badgeName"], string> = {
  opened_booster: "Ouvrir des boosters",
  game_played: "Jouer des parties",
  game_win: "Gagner des parties",
};

const MAX_LEVEL = 5;

type BadgesPageClientProps = {
  initialBadges: UserBadge[];
};

export default function BadgesPageClient({
  initialBadges,
}: BadgesPageClientProps) {
  const { badges } = useBadgesContext();
  const displayedBadges = badges.length > 0 ? badges : initialBadges;

  return (
    <div className="mx-2 my-4 sm:mx-4">
      <h1 className="mb-4 text-2xl font-bold">Succès</h1>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {displayedBadges.map((badge) => {
          const progress =
            badge.nextThreshold !== null
              ? ((badge.score - badge.currentThreshold) /
                  (badge.nextThreshold - badge.currentThreshold)) *
                100
              : 100;

          return (
            <div
              key={badge.badgeName}
              className="flex items-center gap-4 rounded-2xl border-2 border-ink-outline bg-card p-4 shadow-[var(--sticker-shadow-sm)]"
            >
              <BadgeIcon badgeName={badge.badgeName} level={badge.level} />

              <div className="flex-1">
                <p className="font-display font-extrabold">
                  {BADGE_LABELS[badge.badgeName]}
                </p>
                <p className="text-sm text-muted-foreground">
                  {badge.level > 0
                    ? `Niveau ${badge.level} / ${MAX_LEVEL}`
                    : BADGE_DESCRIPTIONS[badge.badgeName]}
                </p>

                {badge.nextThreshold !== null && (
                  <div className="mt-2">
                    <div className="h-2.5 w-full overflow-hidden rounded-full border-2 border-ink-outline bg-muted">
                      <div
                        className="h-full rounded-full bg-primary transition-[width]"
                        style={{ width: `${Math.max(0, Math.min(100, progress))}%` }}
                      />
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">
                      {badge.score} / {badge.nextThreshold}
                    </p>
                  </div>
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
