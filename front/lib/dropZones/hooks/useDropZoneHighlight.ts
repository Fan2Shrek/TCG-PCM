import { useEffect, useState } from "react";
import { emitter } from "@/lib/eventBus";
import { BasicCard } from "@/lib/cards/types/card";

export function useDropZoneHighlight(getRect: () => DOMRect) {
  const [isDragging, setIsDragging] = useState(false);
  const [isHovered, setIsHovered] = useState(false);

  useEffect(() => {
    const onStart = () => setIsDragging(true);
    const onEnd = () => setIsDragging(false);

    const onMove = (pos: { x: number; y: number }, card: BasicCard) => {
      const rect = getRect();

      const inside =
        pos.x >= rect.left &&
        pos.x <= rect.right &&
        pos.y >= rect.top &&
        pos.y <= rect.bottom;

      setIsHovered(inside);
    };

    emitter.on("card:drag:start", onStart);
    emitter.on("card:drag:end", onEnd);
    emitter.on("card:drag:move", onMove);

    return () => {
      emitter.off("card:drag:start", onStart);
      emitter.off("card:drag:end", onEnd);
      emitter.off("card:drag:move", onMove);
    };
  }, []);

  return {
    isDragging,
    isHovered: isHovered,
    setIsHovered,
  };
}
