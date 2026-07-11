import { BoosterType } from "@/constants/booster";
import { BasicCard } from "@/lib/cards/types/card";

export type Booster = {
  id: string;
  boosterType: BoosterType;
};

export type InventorySetStat = {
  set: string;
  ownedCards: number;
  totalCards: number;
};

export type CollectionCardsResponse = {
  type: BoosterType;
  offset: number;
  step: number | null;
  total: number;
  cards: BasicCard[];
};
