"use client";

import React from "react";
import { createPortal } from "react-dom";
import Card from "./Card";
import { BasicCard } from "@/lib/cards/types/card";
import { CardSize } from "@/constants/card";
import { resolveDropZone } from "@/lib/dropZones/dropzoneResolver";
import { emitter } from "@/lib/eventBus";

type DraggedCardProps = {
  card: BasicCard;
  pointerPos: { x: number; y: number } | null;
  tilt: { x: number; y: number; z: number };
  originPos: { x: number; y: number } | null;
  originSize: CardSize;
  originTilt: { x: number; y: number; z: number };
  isDropped: boolean;
};

export default function DraggedCard({
  card,
  originPos,
  originSize,
  originTilt,
  pointerPos,
  tilt,
  isDropped,
}: DraggedCardProps) {
  if (typeof document === "undefined") return null;

  let x = 0;
  let y = 0;
  let currentSize: CardSize = CardSize.MD;
  let currentTilt = { ...tilt, z: 0 };
  let shouldTransition = false;

  // storing "current" target here, initialized by handcard but updated if dropped over a playable zone
  let targetPos = originPos;
  let targetSize = originSize;
  let targetTilt = originTilt;

  const onDragEnd = () => {
    if (!pointerPos) return;

    const dropResult = resolveDropZone(pointerPos, card);
    if (dropResult) {
      targetPos = dropResult.pos;
      targetSize = dropResult.size;
      targetTilt = dropResult.tilt;
      emitter.emit("card:played", { pos: dropResult.pos, card });
    } else {
      emitter.emit("card:return-hand", { pos: pointerPos, card });
    }
  };

  if (pointerPos && !isDropped) {
    x = pointerPos.x - window.innerWidth / 2;
    y = pointerPos.y - window.innerHeight / 2;
  } else {
    onDragEnd();
    if (!targetPos) return;

    x = targetPos.x - window.innerWidth / 2;
    y = targetPos.y - window.innerHeight / 2;
    currentSize = targetSize;
    currentTilt = targetTilt;
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
      ? "transform 300ms cubic-bezier(.2,.8,.2,1)"
      : undefined,
  };

  const portal = (
    <div style={style}>
      <Card card={card} size={currentSize} tilt={currentTilt} />
    </div>
  );

  return createPortal(portal, document.body);
}
