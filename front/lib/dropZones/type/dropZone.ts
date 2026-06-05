import { BasicCard, CardSize } from "../../../components/types/card";

export type DropZone = {
  id: string;
  getRect: () => DOMRect;
  getDropResult: (card: BasicCard) => DropResult;
};

export type DropResult = {
  pos: { x: number; y: number };
  size: CardSize;
  tilt: number;
};
