"use client";
import { emitter } from "@/lib/eventBus";
import { useState, useCallback, useEffect, useRef } from "react";
import { BasicCard } from "@/lib/cards/types/card";

type UseDragOptions = {
  card: BasicCard;
  onDrag?: (e: PointerEvent) => void;
  onDragEnd?: () => void;
};

export function useDrag({ onDrag, onDragEnd, card }: UseDragOptions) {
  const [isDragging, setIsDragging] = useState(false);
  const [dragStart, setDragStart] = useState<{ x: number; y: number } | null>(
    null,
  );
  const [pointerPos, setPointerPos] = useState<{ x: number; y: number } | null>(
    null,
  );
  const [tilt, setTilt] = useState({ x: 0, y: 0 });

  const prevPos = useRef<{ x: number; y: number } | null>(null);
  const resetTiltTimer = useRef<number | null>(null);

  const handlePointerDown = (e: React.PointerEvent) => {
    e.preventDefault();
    setIsDragging(true);
    setDragStart({ x: e.clientX, y: e.clientY });
    setPointerPos({ x: e.clientX, y: e.clientY });

    emitter.emit("card:drag:start", {
      pos: { x: e.clientX, y: e.clientY },
      card,
    });
  };

  const handlePointerMove = useCallback(
    (e: PointerEvent) => {
      if (!dragStart) return;

      const x = e.clientX - dragStart.x;
      const y = e.clientY - dragStart.y;

      setPointerPos({ x: e.clientX, y: e.clientY });

      if (prevPos.current) {
        const dx = x - prevPos.current.x;
        const dy = y - prevPos.current.y;

        const tiltX = Math.max(-50, Math.min(50, -dy));
        const tiltY = Math.max(-50, Math.min(50, dx));

        setTilt({ x: tiltX, y: tiltY });
      }

      prevPos.current = { x, y };

      if (resetTiltTimer.current) clearTimeout(resetTiltTimer.current);

      resetTiltTimer.current = window.setTimeout(() => {
        setTilt({ x: 0, y: 0 });
      }, 200);

      emitter.emit("card:drag:move", {
        pos: { x: e.clientX, y: e.clientY },
        card,
      });

      onDrag?.(e);
    },
    [dragStart, onDrag, card],
  );

  const handlePointerUp = useCallback(() => {
    if (dragStart) {
      setIsDragging(false);
      setDragStart(null);
      setTilt({ x: 0, y: 0 });
      prevPos.current = null;
      emitter.emit("card:drag:end", {
        pos: { x: pointerPos?.x, y: pointerPos?.y },
        card,
      });

      onDragEnd?.();
    }
  }, [dragStart, onDragEnd, card, pointerPos]);

  useEffect(() => {
    if (!dragStart) return;

    window.addEventListener("pointermove", handlePointerMove);
    window.addEventListener("pointerup", handlePointerUp);
    window.addEventListener("pointercancel", handlePointerUp);

    return () => {
      window.removeEventListener("pointermove", handlePointerMove);
      window.removeEventListener("pointerup", handlePointerUp);
      window.removeEventListener("pointercancel", handlePointerUp);
    };
  }, [dragStart, handlePointerMove, handlePointerUp]);

  return {
    isDragging,
    pointerPos,
    tilt,
    handlePointerDown,
  };
}
