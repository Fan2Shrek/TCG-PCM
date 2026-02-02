"use client";

import React, { useRef, useCallback, useEffect } from "react";
import Image from "@/components/atoms/image";
import { CardModel, CardSize, CardSizeClass } from "@/components/types/card";

export type CardViewProps = {
  card: CardModel;
  isFlipped?: boolean;
  size?: CardSize;
  className?: string;
  interactive?: boolean;
  onHover?: (pos: { x: number; y: number }, cardId: string) => void;
  onClick?: (pos: { x: number; y: number }, cardId: string) => void;
};

const clamp = (v: number) => Math.max(0, Math.min(1, v));

const Card = ({
  card,
  size = "md",
  className = "",
  interactive = true,
  onHover,
  onClick,
}: CardViewProps) => {
  const rootRef = useRef<HTMLDivElement | null>(null);
  const animationFrameRef = useRef<number | null>(null);

  const tiltBackTimeoutRef = useRef<number | null>(null);
  const restoreTransitionTimeoutRef = useRef<number | null>(null);

  useEffect(() => {
    return () => {
      if (animationFrameRef.current) cancelAnimationFrame(animationFrameRef.current);
    };
  }, []);

  const layers = card.frontLayers ?? [];

  const sizeClass = CardSizeClass[size];

  // get the position of the cusor over card
  const getNormalizedPosition = (rootElement: HTMLDivElement | null, clientX: number, clientY: number) => {
    if (!rootElement) return { x: 0.5, y: 0.5 };
    const bounds = rootElement.getBoundingClientRect();
    return {
      x: clamp((clientX - bounds.left) / bounds.width),
      y: clamp((clientY - bounds.top) / bounds.height),
    };
  };

  // apply tilt & glare based on cursor position
  const applyTilt = (rootElement: HTMLDivElement, x: number, y: number) => {
    const maxXTilt = 30;
    const maxYTilt = 45;
    const flip = 180;
    const halfRotation = 90;
    const normalizedCenter = 0.5;

    const currentRy = parseFloat(rootElement.style.getPropertyValue("--ry") || "0");

    const rotateXDeg = (y -normalizedCenter) * maxYTilt;
    const rotateYDeg = (normalizedCenter - x) * maxXTilt + (currentRy > halfRotation ? flip : 0);

    const glareX = (currentRy > halfRotation) ? (1 - x) * 100 : x * 100;
    const glareY = y*100;

    if (animationFrameRef.current) cancelAnimationFrame(animationFrameRef.current);
    animationFrameRef.current = requestAnimationFrame(() => {

      rootElement.style.setProperty("--rx", `${rotateXDeg}deg`);
      rootElement.style.setProperty("--ry", `${rotateYDeg}deg`);

      rootElement.style.setProperty("--glare-x", `${glareX}%`);
      rootElement.style.setProperty("--glare-y", `${glareY}%`);
    });
  };

  // logic for flipping card, gotta recalculate glare position to avoid jarring effect
  const applyFlip = (rootElement: HTMLDivElement) => {
    const currentRy = parseFloat(rootElement.style.getPropertyValue("--ry") || "0");
    const newRy = currentRy > 90 ? currentRy - 180 : currentRy + 180;
    rootElement.style.setProperty("--ry", `${newRy}deg`);

    const currentGlareX = parseFloat(rootElement.style.getPropertyValue("--glare-x") || "50") || 50;
    const mirroredGlareX = 100 - currentGlareX;
    rootElement.style.setProperty("--glare-x", `${mirroredGlareX}%`);
  }

  const handlePointerMove = useCallback(
    (e: React.PointerEvent) => {
      if (!interactive) return;

      const rootElement = rootRef.current;
      if (!rootElement) return;

      const normalAnimationDuration = 300;
      rootElement.style.transition = `transform ${normalAnimationDuration}ms cubic-bezier(.2,.9,.2,1)`;

      if (tiltBackTimeoutRef.current) {
        clearTimeout(tiltBackTimeoutRef.current);
        tiltBackTimeoutRef.current = null;
      }

      if (restoreTransitionTimeoutRef.current) {
        clearTimeout(restoreTransitionTimeoutRef.current);
        restoreTransitionTimeoutRef.current = null;
      }

      const { x, y } = getNormalizedPosition(rootElement, e.clientX, e.clientY);
      applyTilt(rootElement, x, y);

      onHover?.({ x, y }, card.id);
    },
    [interactive, onHover, card.id]
  );

  const handlePointerLeave = useCallback(() => {
    const rootElement = rootRef.current;
    if (!rootElement) return;

    const delay = 150;
    const snapbackAnimationDuration = 1000;
    const normalAnimationDuration = 300;

    if (tiltBackTimeoutRef.current) clearTimeout(tiltBackTimeoutRef.current);
      if (restoreTransitionTimeoutRef.current) clearTimeout(restoreTransitionTimeoutRef.current);

      tiltBackTimeoutRef.current = window.setTimeout(() => {
        rootElement.style.transition = `transform ${snapbackAnimationDuration}ms cubic-bezier(.2,.9,.2,1)`;
        applyTilt(rootElement, 0.5, 0.5);

        restoreTransitionTimeoutRef.current = window.setTimeout(() => {
          rootElement.style.transition = `transform ${normalAnimationDuration}ms cubic-bezier(.2,.9,.2,1)`;
        }, snapbackAnimationDuration);

      }, delay);

  }, []);

  const handleClick = useCallback(
    (e: React.MouseEvent) => {
      if (!interactive) return;

      const rootElement = rootRef.current;
      if (!rootElement) return;

      const { x, y } = getNormalizedPosition(rootElement, e.clientX, e.clientY);

      applyFlip(rootElement);

      onClick?.({ x, y }, card.id);
    },
    [interactive, onClick, card.id]
  );

  return (
    <div
      ref={rootRef}
      className={`card-3d relative rounded-xl aspect-5/7 ${sizeClass} ${className} transform-3d will-change-transform transition-transform duration-300 ease-[cubic-bezier(.2,.9,.2,1)] cursor-pointer`}
      aria-label={`Card ${card.id}`}
      onPointerMove={interactive ? handlePointerMove : undefined}
      onPointerLeave={interactive ? handlePointerLeave : undefined}
      onClick={handleClick}
    >
      <div className="absolute inset-0 backface-hidden">
        {layers.map((layer, i) => (
          <Image key={i} src={layer.src} alt={layer.alt ?? `layer-${i}`} fill className={`object-cover z-[${layer.depth}]`}/>
        ))}
      </div>

      <div className="absolute inset-0 rotate-y-180 backface-hidden">
        <Image src={card.backImage ?? "/defaultCardBack.png"} alt={`${card.id} back`} fill className="object-cover" />
      </div>

        {/* glare effect */}
        <div className="absolute inset-0 pointer-events-none glare-effect"></div>


    </div>
  );
};

export default Card;
