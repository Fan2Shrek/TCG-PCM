import { BoosterType } from "@/constants/booster";

export type Booster = {
  id: string;
  boosterType: BoosterType;
};

export type InventorySetStat = {
  set: string;
  ownedCards: number;
  totalCards: number;
};
