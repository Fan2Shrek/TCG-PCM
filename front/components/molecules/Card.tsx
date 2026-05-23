"use client";

import React from "react";
import CardFront from "../atoms/CardFront";
import CardBack from "../atoms/CardBack";
import CardGlare from "../atoms/CardGlare";
import { CardModel, CardSizeMap, CardSize, CardLayer } from "../types/card";
import { DEFAULT_TILT, DEFAULT_GLARE } from "../utils/cardUtils";
import { getImage } from "@/lib/api/api";

import { convertDescriptions } from "@/lib/game/cardUtils";

export type CardViewProps = {
  card: BasicCard;
  size?: CardSize;
  tilt?: { x: number; y: number, z: number };
  glare?: { x: number; y: number };
  isHovering?: boolean;
  style?: React.CSSProperties;
  className?: string;
};

const Card = ({ card, size = "md", tilt, glare, isHovering, style, className }: CardViewProps) => {
  const cardSizeInfo = CardSizeMap[size];

  const appliedTilt = tilt ?? DEFAULT_TILT;
  const appliedGlare = glare ?? DEFAULT_GLARE;
  console.log(getImage(card.image))

  const tempCardLayers = [
      {
		src: card?.image && getImage(card.image) || '/isaac_card_layer_1.png',
		isHovering: false,
		depth: -20,
	  },
      { src: '/isaac_card_layer_2.webp', depth: -10 },
      {
        src: '/isaac_card_layer_3.png',
        depth: 0,
        foil: '/foil.webp',
        mask: '/mask.webp',
      },
      { src: '/isaac_card_layer_4.gif', depth: 20 },
  ];

  if (false === card?.isActive) {
	tempCardLayers.push({ src: '/cross.webp', depth: 20 });
  }

  return (
    <div
	  id={card?.instanceId}
      className={`relative rounded-xl aspect-card ${cardSizeInfo} transform-3d will-change-transform user-select-none${className ?? ''}`}
      style={
        {
          transform: `perspective(1000px) rotateX(${appliedTilt.x}deg) rotateY(${appliedTilt.y}deg) rotateZ(${appliedTilt.z}deg)`,
          ...(style ?? {}),
        } as React.CSSProperties
      }
    >
      <CardFront layers={tempCardLayers} tilt={appliedTilt} glare={appliedGlare} isHovering={!!isHovering} />
      <CardBack id={card?.instanceId} />
      <CardGlare glare={appliedGlare} isHovering={!!isHovering} />
	  <p className="text-center absolute text-black">{card?.name}</p>
	  {card?.description && <p className="text-center absolute text-black top-40">{convertDescriptions(card?.description)}</p>}
    </div>
  );
};

export default Card;
