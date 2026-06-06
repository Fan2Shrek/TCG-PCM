export enum CardSize {
  SM = "sm",
  MD = "md",
  LG = "lg",
  XL = "xl",
}

export const CardSizeMap: Record<CardSize, string> = {
  [CardSize.SM]: "w-card-sm",
  [CardSize.MD]: "w-card-md",
  [CardSize.LG]: "w-card-lg",
  [CardSize.XL]: "w-card-xl",
} as const;

export enum FoilEffects {
  HOLO = "Holographic",
  RAINBOW = "Rainbow",
  GOLDEN = "Golden",
}
