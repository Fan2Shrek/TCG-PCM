"use client";

import React, { useState, useRef, useCallback, useEffect } from "react";
import Card from "./Card";
import { CardModel } from "@/lib/cards/types/card";
import { clamp, DEFAULT_TILT, DEFAULT_GLARE, NORMALIZED_CENTER, HALF_ROTATION, FLIP_DEG, NORMAL_ANIMATION_DURATION_MS, SNAPBACK_ANIMATION_DURATION_MS, SNAPBACK_DELAY_MS, calculateTiltOnHover, calculateGlareOnHover } from "@/lib/cards/cardUtils";
import { CardSize } from "@/constants/card";

export type InteractiveCardProps = {
  card: CardModel;
  size?: CardSize;
  onHover?: (cardId: string) => void;
  onClick?: (cardId: string) => void;
};

export default function InteractiveCard({ card, size = "md", onHover, onClick }: InteractiveCardProps) {
  const [isHovering, setIsHovering] = useState(false);
  const [tilt, setTilt] = useState(DEFAULT_TILT);
  const [glare, setGlare] = useState(DEFAULT_GLARE);
  const [style, setStyle] = useState<React.CSSProperties>({});

  const rootRef = useRef<HTMLDivElement | null>(null);
  const tiltBackTimeoutRef = useRef<number | null>(null);
  const restoreTransitionTimeoutRef = useRef<number | null>(null);

  const clearTimeouts = () => {
    if (tiltBackTimeoutRef.current) {
      clearTimeout(tiltBackTimeoutRef.current);
      tiltBackTimeoutRef.current = null;
    }
    if (restoreTransitionTimeoutRef.current) {
      clearTimeout(restoreTransitionTimeoutRef.current);
      restoreTransitionTimeoutRef.current = null;
    }
  };

  useEffect(() => {
    return () => clearTimeouts();
  }, []);

  const handlePointerMove = useCallback(
    (e: React.PointerEvent) => {
      const rootElement = rootRef.current;
      if (!rootElement) return;

      setIsHovering((prev) => (prev ? prev : true));

      setStyle({
        transition: `transform ${NORMAL_ANIMATION_DURATION_MS}ms cubic-bezier(.2,.9,.2,1)`,
      });

      clearTimeouts();

      const bounds = rootElement.getBoundingClientRect();
      const x = clamp((e.clientX - bounds.left) / bounds.width);
      const y = clamp((e.clientY - bounds.top) / bounds.height);

      const newTilt = calculateTiltOnHover(x, y, tilt.y);
      const newGlare = calculateGlareOnHover(x, y, tilt.y);

      setTilt(newTilt);
      setGlare(newGlare);

      onHover?.(card.id);
    },
    [onHover, card.id, tilt.y],
  );

  const handlePointerLeave = useCallback(() => {
    setIsHovering(false);

    clearTimeouts();

    tiltBackTimeoutRef.current = window.setTimeout(() => {
      setStyle({
        transition: `transform ${SNAPBACK_ANIMATION_DURATION_MS}ms cubic-bezier(.2,.9,.2,1)`,
      });

      const newTilt = calculateTiltOnHover(NORMALIZED_CENTER, NORMALIZED_CENTER, tilt.y);
      const newGlare = calculateGlareOnHover(NORMALIZED_CENTER, NORMALIZED_CENTER, tilt.y);

      setTilt(newTilt);
      setGlare(newGlare);

      restoreTransitionTimeoutRef.current = window.setTimeout(() => {
        setStyle({
          transition: `transform ${NORMAL_ANIMATION_DURATION_MS}ms cubic-bezier(.2,.9,.2,1)`,
        });
      }, SNAPBACK_ANIMATION_DURATION_MS);
    }, SNAPBACK_DELAY_MS);
  }, [tilt.y]);

  const handleClick = useCallback(() => {
    const newRotationY = tilt.y > HALF_ROTATION ? tilt.y - FLIP_DEG : tilt.y + FLIP_DEG;

    setGlare((prev) => ({ x: 100 - prev.x, y: prev.y }));
    setTilt((prev) => ({ ...prev, y: newRotationY }));

    onClick?.(card.id);
  }, [onClick, card.id, tilt.y]);

  return (
    <div ref={rootRef} onClick={handleClick} onPointerMove={handlePointerMove} onPointerLeave={handlePointerLeave} className='cursor-pointer'>
      <Card card={card} size={size} tilt={tilt} glare={glare} isHovering={isHovering} style={style} />
    </div>
  );
}
