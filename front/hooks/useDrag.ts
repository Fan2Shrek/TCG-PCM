"use client";
import { emitter } from "@/lib/eventBus";
import { useState, useCallback, useEffect, useRef } from "react";
import { BasicCard } from "@/lib/cards/types/card";

type UseDragOptions = {
  card: BasicCard;
  onDrag?: (e: PointerEvent) => void;
  onDragEnd?: () => void;
  onClick?: () => void;
};

const DRAG_THRESHOLD_PX = 6;

export function useDrag({ onDrag, onDragEnd, onClick, card }: UseDragOptions) {
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
  // Whether the current pointer interaction has crossed the drag threshold
  // (i.e. it's a real drag, not just a click).
  const hasStartedDragRef = useRef(false);

  const handlePointerDown = (e: React.PointerEvent) => {
    e.preventDefault();
    hasStartedDragRef.current = false;
    setDragStart({ x: e.clientX, y: e.clientY });
    setPointerPos({ x: e.clientX, y: e.clientY });
  };

  const handlePointerMove = useCallback(
    (e: PointerEvent) => {
      if (!dragStart) return;

      const x = e.clientX - dragStart.x;
      const y = e.clientY - dragStart.y;

      setPointerPos({ x: e.clientX, y: e.clientY });

      if (!hasStartedDragRef.current) {
        if (
          Math.abs(x) < DRAG_THRESHOLD_PX &&
          Math.abs(y) < DRAG_THRESHOLD_PX
        ) {
          return;
        }

        hasStartedDragRef.current = true;
        setIsDragging(true);
        emitter.emit("card:drag:start", {
          pos: { x: e.clientX, y: e.clientY },
          card,
        });
      }

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
      const wasDragging = hasStartedDragRef.current;

      setIsDragging(false);
      setDragStart(null);
      setTilt({ x: 0, y: 0 });
      prevPos.current = null;
      hasStartedDragRef.current = false;

      if (wasDragging) {
        emitter.emit("card:drag:end", {
          pos: { x: pointerPos?.x, y: pointerPos?.y },
          card,
        });

        onDragEnd?.();
      } else {
        // The browser still dispatches a native "click" right after this
        // pointerup, for the same physical gesture we just handled. If our
        // onClick callback repositions the element (e.g. a "selected" lift
        // effect), that click's hit-test can land on whatever is now under
        // the cursor instead of this card — swallow it so it can't trigger
        // an unrelated element underneath.
        const swallowNextClick = (ev: MouseEvent) => {
          ev.stopPropagation();
          ev.preventDefault();
        };
        window.addEventListener("click", swallowNextClick, {
          capture: true,
          once: true,
        });

        onClick?.();
      }
    }
  }, [dragStart, onDragEnd, onClick, card, pointerPos]);

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
