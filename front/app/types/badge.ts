export type BadgeName = "opened_booster" | "game_played" | "game_win";

export type UserBadge = {
  badgeName: BadgeName;
  level: number;
  score: number;
  currentThreshold: number;
  nextThreshold: number | null;
};

export type BadgesResponse = {
  badges: UserBadge[];
};
