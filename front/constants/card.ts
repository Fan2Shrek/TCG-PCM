export enum CardSize {
  SM = "sm",
  MD = "md",
  LG = "lg",
  XL = "xl",
}

export enum CardRaririty {
  COMMON = "Common",
  UNCOMMON = "Uncommon",
  RARE = "Rare",
  EPIC = "Epic",
  LEGENDARY = "Legendary",
}

export enum CardEffect {
  HACKED = "Hacked",
  TORNED = "Torned",
  POWER_BOOST = "PowerBoost",
}

export enum CardSet {
  ORIGINAL = "Original",
  TBOI = "The Binding of Isaac",
  BTD6 = "Bloons TD 6",
}

export enum CardType {
  CHARACTER = "CHARACTER",
  MONSTER = "MONSTER",
  PASSIVE = "PASSIVE",
  CONSUMABLE = "CONSUMABLE",
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
