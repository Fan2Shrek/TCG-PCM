export enum BoosterOpeningPhase {
  IDLE = "idle",
  PREVIEW = "preview",
  OPENING_DROP_TOP = "opening_drop_top",
  OPENING_SHOOT_BACK_CARDS = "opening_shoot_back_cards",
  OPENING_DROP_EMPTY_BOOSTER = "opening_drop_empty_booster",
  REVEAL_SINGLE = "reveal_single",
  REVEAL_ALL = "reveal_all",
  CONFIRM_EXIT = "confirm_exit",
}

export const isOpeningAnimationPhase = (phase: BoosterOpeningPhase) =>
  phase === BoosterOpeningPhase.OPENING_DROP_TOP ||
  phase === BoosterOpeningPhase.OPENING_SHOOT_BACK_CARDS ||
  phase === BoosterOpeningPhase.OPENING_DROP_EMPTY_BOOSTER;

export const isRevealPhase = (phase: BoosterOpeningPhase) =>
  phase === BoosterOpeningPhase.REVEAL_SINGLE ||
  phase === BoosterOpeningPhase.REVEAL_ALL ||
  phase === BoosterOpeningPhase.CONFIRM_EXIT;
