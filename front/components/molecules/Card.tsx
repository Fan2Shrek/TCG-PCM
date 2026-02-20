"use client";

import React, { useState, useRef, useCallback, useEffect } from "react";
import CardFront from "../atoms/CardFront";
import CardBack from "../atoms/CardBack";
import CardGlare from "../atoms/CardGlare";
import { CardModel, CardSizeClass, CardSize, CardLayer } from "../types/card";
import { clamp, DEFAULT_TILT, DEFAULT_GLARE, NORMALIZED_CENTER, HALF_ROTATION, FLIP_DEG, NORMAL_ANIMATION_DURATION_MS, SNAPBACK_ANIMATION_DURATION_MS, SNAPBACK_DELAY_MS, calculateTilt, calculateGlare } from "../utils/cardUtils";

export type CardViewProps = {
  card: CardModel;
  size?: CardSize;
  interactive?: boolean;
  onHover?: (cardId: string) => void;
  onClick?: (cardId: string) => void;
};

const Card = ({ card, size = "md", interactive = true, onHover, onClick }: CardViewProps) => {
  const [isHovering, setIsHovering] = useState(false);
  const [tilt, setTilt] = useState(DEFAULT_TILT);
  const [glare, setGlare] = useState(DEFAULT_GLARE);

  const rootRef = useRef<HTMLDivElement | null>(null);
  const tiltBackTimeoutRef = useRef<number | null>(null);
  const restoreTransitionTimeoutRef = useRef<number | null>(null);

  const sizeClass = CardSizeClass[size];
  const cartFrontLayers = card.frontLayers ?? [];

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
      console.log(1);
      if (!interactive) return;

      const rootElement = rootRef.current;
      if (!rootElement) return;

      setIsHovering((prev) => (prev ? prev : true));

      rootElement.style.transition = `transform ${NORMAL_ANIMATION_DURATION_MS}ms cubic-bezier(.2,.9,.2,1)`;

      clearTimeouts();

      const bounds = rootElement.getBoundingClientRect();
      const x = clamp((e.clientX - bounds.left) / bounds.width);
      const y = clamp((e.clientY - bounds.top) / bounds.height);

      const newTilt = calculateTilt(x, y, tilt.y);
      const newGlare = calculateGlare(x, y, tilt.y);

      setTilt(newTilt);
      setGlare(newGlare);

      onHover?.(card.id);
    },
    [interactive, onHover, card.id, tilt.y],
  );

  const handlePointerLeave = useCallback(() => {
    if (!interactive) return;

    const rootElement = rootRef.current;
    if (!rootElement) return;

    setIsHovering(false);

    clearTimeouts();

    tiltBackTimeoutRef.current = window.setTimeout(() => {
      rootElement.style.transition = `transform ${SNAPBACK_ANIMATION_DURATION_MS}ms cubic-bezier(.2,.9,.2,1)`;

      const newTilt = calculateTilt(NORMALIZED_CENTER, NORMALIZED_CENTER, tilt.y);
      const newGlare = calculateGlare(NORMALIZED_CENTER, NORMALIZED_CENTER, tilt.y);

      setTilt(newTilt);
      setGlare(newGlare);

      restoreTransitionTimeoutRef.current = window.setTimeout(() => {
        rootElement.style.transition = `transform ${NORMAL_ANIMATION_DURATION_MS}ms cubic-bezier(.2,.9,.2,1)`;
      }, SNAPBACK_ANIMATION_DURATION_MS);
    }, SNAPBACK_DELAY_MS);
  }, [interactive, tilt.y]);

  const handleClick = useCallback(() => {
    const newRotationY = tilt.y > HALF_ROTATION ? tilt.y - FLIP_DEG : tilt.y + FLIP_DEG;

    setGlare((prev) => ({ x: 100 - prev.x, y: prev.y }));
    setTilt((prev) => ({ ...prev, y: newRotationY }));

    onClick?.(card.id);
  }, [onClick, card.id, tilt.y]);

  return (
    <div
      ref={rootRef}
      className={`relative rounded-xl aspect-5/7 ${sizeClass} transform-3d will-change-transform transition-transform duration-300 ease-[cubic-bezier(.2,.9,.2,1)] cursor-pointer`}
      style={
        {
          transform: `perspective(1000px) rotateX(${tilt.x}deg) rotateY(${tilt.y}deg)`,
        } as React.CSSProperties
      }
      onPointerMove={interactive ? handlePointerMove : undefined}
      onPointerLeave={interactive ? handlePointerLeave : undefined}
      onClick={handleClick}
    >
      <CardFront layers={cartFrontLayers as CardLayer[]} tilt={tilt} glare={glare} isHovering={isHovering} />
      <CardBack backImage={card.backImage} id={card.id} />
      <CardGlare glare={glare} isHovering={isHovering} />
    </div>
  );
};

export default Card;
