"use client";

import React from "react";
import CardFront from "../atoms/CardFront";
import CardBack from "../atoms/CardBack";
import CardGlare from "../atoms/CardGlare";
import { CardModel, CardSizeMap, CardSize, CardLayer, cardAspectRatio } from "../types/card";
import { DEFAULT_TILT, DEFAULT_GLARE } from "../utils/cardUtils";

export type CardViewProps = {
  card: CardModel;
  size?: CardSize;
  tilt?: { x: number; y: number, z: number };
  glare?: { x: number; y: number };
  isHovering?: boolean;
  style?: React.CSSProperties;
  className?: string;
};

const Card = ({ card, size = "md", tilt, glare, isHovering, style, className }: CardViewProps) => {
  const cartFrontLayers = card.frontLayers ?? [];
  const cardSizeInfo = CardSizeMap[size];

  const appliedTilt = tilt ?? DEFAULT_TILT;
  const appliedGlare = glare ?? DEFAULT_GLARE;

  return (
    <div
      className={`relative rounded-xl ${cardAspectRatio} ${cardSizeInfo} transform-3d will-change-transform cursor-pointer ${className ?? ''}`}
      style={
        {
          transform: `perspective(1000px) rotateX(${appliedTilt.x}deg) rotateY(${appliedTilt.y}deg) rotateZ(${appliedTilt.z}deg)`,
          ...(style ?? {}),
        } as React.CSSProperties
      }
    >
      <CardFront layers={cartFrontLayers as CardLayer[]} tilt={appliedTilt} glare={appliedGlare} isHovering={!!isHovering} />
      <CardBack backImage={card.backImage} id={card.id} />
      <CardGlare glare={appliedGlare} isHovering={!!isHovering} />
    </div>
  );
};

export default Card;
