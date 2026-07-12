import { BasicCard } from "@/lib/cards/types/card";

export type CardCollectionEntry = {
  card: BasicCard;
  quantity: number;
};

export type CardCollectionResponse = {
  entries: CardCollectionEntry[];
};
