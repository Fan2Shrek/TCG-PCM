export enum CardSize {
  XS = "xs",
  SM = "sm",
  MD = "md",
  LG = "lg",
  XL = "xl",
  XLL = "2xl",
}

export enum CardRaririty {
  COMMON = "COMMON",
  UNCOMMON = "UNCOMMON",
  RARE = "RARE",
  EPIC = "EPIC",
  LEGENDARY = "LEGENDARY",
}

export enum CardEffect {
  HACKED = "Hacked",
  TORNED = "Torned",
  POWER_BOOST = "PowerBoost",
}

export enum CardSet {
  ORIGINAL = "ORIGINAL",
  TBOI = "TBOI",
  BTD6 = "BTD6",
}

export enum CardType {
  CHARACTER = "CHARACTER",
  MONSTER = "MONSTER",
  PASSIVE = "PASSIVE",
  CONSUMABLE = "CONSUMABLE",
}

export const CardSizeMap: Record<CardSize, string> = {
  [CardSize.XS]: "w-card-xs",
  [CardSize.SM]: "w-card-sm",
  [CardSize.MD]: "w-card-md",
  [CardSize.LG]: "w-card-lg",
  [CardSize.XL]: "w-card-xl",
  [CardSize.XLL]: "w-card-2xl",
} as const;

export enum FoilEffects {
  HOLO = "Holographic",
  RAINBOW = "Rainbow",
  GOLDEN = "Golden",
}
