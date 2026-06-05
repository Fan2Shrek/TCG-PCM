"use client";

import React from "react";
import { createPortal } from "react-dom";
import Card from "./Card";
import { BasicCard, CardSize } from "../types/card";
import { resolveDropZone } from "@/lib/dropZones/dropzoneResolver";
import { emitter } from "@/lib/eventBus";

type DraggedCardProps = {
  card: BasicCard;
  pointerPos: { x: number; y: number } | null;
  tilt: { x: number; y: number };
  originPos: { x: number; y: number } | null;
  originSize: CardSize;
  originTilt: number;
  isReturning: boolean;
};

export default function DraggedCard({
  card,
  originPos,
  originSize,
  originTilt,
  pointerPos,
  tilt,
  isReturning,
}: DraggedCardProps) {
  if (typeof document === "undefined") return null;

  let x = 0;
  let y = 0;
  let currentSize = originSize;
  let currentTilt = { x: 0, y: 0, z: 0 };
  let shouldTransition = false;

  // storing "current" target here, initialized by handcard but updated if dropped over a playable zone
  let targetPos = originPos;
  let targetSize = originSize;
  let targetTilt = originTilt;

  const onDragEnd = () => {
    const dropResult = resolveDropZone(pointerPos!, card);
    if (dropResult) {
      targetPos = dropResult.pos;
      targetSize = dropResult.size;
      targetTilt = dropResult.tilt;
    } else {
      emitter.emit("card:return-hand", { card, pointerPos });
    }
  };

  if (pointerPos && !isReturning) {
    x = pointerPos.x - window.innerWidth / 2;
    y = pointerPos.y - window.innerHeight / 2;
    currentSize = originSize;
    currentTilt = { ...tilt, z: 0 };
  } else if (isReturning && targetPos) {
    onDragEnd();
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
