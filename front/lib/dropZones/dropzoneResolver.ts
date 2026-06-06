import { BasicCard } from "../cards/types/card";
import { getDropZones } from "./dropzoneRegistry";
import { DropResult } from "./types/dropZone";

export function resolveDropZone(
  pointer: {
    x: number;
    y: number;
  },
  card: BasicCard,
): DropResult | null {
  const zones = getDropZones();

  for (const zone of zones) {
    const rect = zone.getRect();

    if (
      pointer.x >= rect.left &&
      pointer.x <= rect.right &&
      pointer.y >= rect.top &&
      pointer.y <= rect.bottom
    ) {
      return zone.getDropResult(card);
    }
  }

  return null;
}
