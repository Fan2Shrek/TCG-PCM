import { BasicCard } from "@/lib/cards/types/card";

export type DropZone = {
  id: string;
  getRect: () => DOMRect;
  getDropResult: (card: BasicCard) => string | null;
};
