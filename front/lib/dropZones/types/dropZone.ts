import { BasicCard } from "@/lib/cards/types/card";
import { CardSize } from "@/constants/card";

export type DropZone = {
  id: string;
  getRect: () => DOMRect;
  getDropResult: (card: BasicCard) => DropResult;
};

export type DropResult = {
  pos: { x: number; y: number; z: number };
  size: CardSize;
  tilt: { x: number; y: number; z: number };
};
