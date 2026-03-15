"use client";
import { useState, useCallback, useEffect, useRef } from "react";

type UseDragOptions = {
  onDrag?: (e: MouseEvent) => void;
  onDragEnd?: () => void;
};

export function useDrag({ onDrag, onDragEnd }: UseDragOptions = {}) {
  const [isDragging, setIsDragging] = useState(false);
  const [dragStart, setDragStart] = useState<{ x: number; y: number } | null>(null);
  const [dragOffset, setDragOffset] = useState<{ x: number; y: number } | null>(null);
  const [tilt, setTilt] = useState({ x: 0, y: 0 });

  const prevPos = useRef<{ x: number; y: number } | null>(null);
  const resetTiltTimer = useRef<number | null>(null);

  const handleMouseDown = (e: React.MouseEvent) => {
    e.preventDefault();
    setIsDragging(true);
    setDragStart({ x: e.clientX, y: e.clientY });
    setDragOffset({ x: 0, y: 0 });
  };

  const handleMouseMove = useCallback(
    (e: MouseEvent) => {
      if (!dragStart) return;

      const x = e.clientX - dragStart.x;
      const y = e.clientY - dragStart.y;

      setDragOffset({ x, y });

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

      onDrag?.(e);
    },
    [dragStart, onDrag]
  );

  const handleMouseUp = useCallback(() => {
    if (dragStart) {
      setIsDragging(false);
      setDragOffset(null);
      setDragStart(null);
      setTilt({ x: 0, y: 0 });
      prevPos.current = null;
      onDragEnd?.();
    }
  }, [dragStart, onDragEnd]);

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
    dragOffset,
    tilt,
    handleMouseDown,
  };
}
