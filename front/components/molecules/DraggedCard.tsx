"use client";

import React from "react";
import { createPortal } from "react-dom";
import Card from "./Card";
import { BasicCard, CardSize } from "../types/card";

type DraggedCardProps = {
  card: BasicCard;
  cardSize: CardSize;
  pointerPos: { x: number; y: number } | null;
  tilt: { x: number; y: number };
  targetPos: { x: number; y: number } | null;
  targetSize: CardSize;
  targetTilt: number;
  isReturning: boolean;
};

export default function DraggedCard({
  card,
  cardSize,
  targetPos,
  targetSize,
  targetTilt,
  pointerPos,
  tilt,
  isReturning,
}: DraggedCardProps) {
  if (typeof document === "undefined") return null;

  let x = 0;
  let y = 0;
  let currentSize = cardSize;
  let currentTilt = { x: 0, y: 0, z: 0 };
  let shouldTransition = false;

  if (pointerPos && !isReturning) {
    x = pointerPos.x - window.innerWidth / 2;
    y = pointerPos.y - window.innerHeight / 2;
    currentSize = cardSize;
    currentTilt = { ...tilt, z: 0 };
  } else if (isReturning && targetPos) {
    x = targetPos.x - window.innerWidth / 2;
    y = targetPos.y - window.innerHeight / 2;
    currentSize = targetSize;
    currentTilt = { x: 0, y: 0, z: targetTilt };
    shouldTransition = true;
  }

  const style: React.CSSProperties = {
    position: "fixed",
    top: "50%",
    left: "50%",
    transform: `translate(calc(-50% + ${x}px), calc(-50% + ${y}px))`,
    zIndex: 50,
    cursor: "grabbing",
    transition: shouldTransition
      ? "transform 220ms cubic-bezier(.2,.8,.2,1)"
      : undefined,
  };

  const portal = (
    <div style={style}>
      <Card card={card} size={currentSize} tilt={currentTilt} />
    </div>
  );

  return createPortal(portal, document.body);
}
