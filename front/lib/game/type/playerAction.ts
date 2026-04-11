export const PlayerActionType = {
  PLAY_CARD: "play_card",
  ATTACK: "attack",
  END_TURN: "end_turn",
} as const;

export type PlayerActionType =
  (typeof PlayerActionType)[keyof typeof PlayerActionType];
