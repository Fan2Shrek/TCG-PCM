"use client";
import { emitter } from "@/lib/eventBus";
import { useState, useCallback, useEffect, useRef } from "react";
import { BasicCard } from "../types/card";
import { BasicCard } from "@/lib/cards/types/card";

type UseDragOptions = {
  card: BasicCard;
  onDrag?: (e: MouseEvent) => void;
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

  const handleMouseDown = (e: React.MouseEvent) => {
    e.preventDefault();
    setIsDragging(true);
    setDragStart({ x: e.clientX, y: e.clientY });
    setPointerPos({ x: e.clientX, y: e.clientY });

    emitter.emit("card:drag:start", {
      pos: { x: e.clientX, y: e.clientY },
      card,
    });
  };

  const handleMouseMove = useCallback(
    (e: MouseEvent) => {
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

  const handleMouseUp = useCallback(() => {
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

    window.addEventListener("mousemove", handleMouseMove);
    window.addEventListener("mouseup", handleMouseUp);

    return () => {
      window.removeEventListener("mousemove", handleMouseMove);
      window.removeEventListener("mouseup", handleMouseUp);
    };
  }, [dragStart, handleMouseMove, handleMouseUp]);

  return {
    isDragging,
    pointerPos,
    tilt,
    handleMouseDown,
  };
}
