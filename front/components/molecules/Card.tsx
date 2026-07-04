"use client";

import React from "react";
import CardFront from "../atoms/CardFront";
import CardBack from "../atoms/CardBack";
import CardGlare from "../atoms/CardGlare";
import {
  CardRaririty,
  CardSize,
  CardSizeMap,
  FoilEffects,
} from "@/constants/card";
import { BasicCard } from "@/lib/cards/types/card";
import { DEFAULT_TILT, DEFAULT_GLARE } from "@/lib/cards/cardUtils";
import { getImage } from "@/lib/api/api";

import { convertDescriptions } from "@/lib/game/cardUtils";

export type CardViewProps = {
  card: BasicCard;
  size?: CardSize;
  tilt?: { x: number; y: number; z: number };
  glare?: { x: number; y: number };
  isHovering?: boolean;
  style?: React.CSSProperties;
  className?: string;
};

const Card = ({
  card,
  size = CardSize.MD,
  tilt,
  glare,
  isHovering,
  style,
  className,
}: CardViewProps) => {
  const cardSizeInfo = CardSizeMap[size];
  const appliedTilt = tilt ?? DEFAULT_TILT;
  const appliedGlare = glare ?? DEFAULT_GLARE;
  const foilEffect =
    card.rarity === CardRaririty.LEGENDARY
      ? FoilEffects.RAINBOW
      : card.rarity === CardRaririty.EPIC
        ? FoilEffects.GOLDEN
        : card.rarity === CardRaririty.RARE
          ? FoilEffects.HOLO
          : null;

  const cardLayers = [
    {
      src: card?.image && getImage(card.image),
      depth: -1,
      foilEffect: null,
      foil: null,
      mask: null,
    },
    {
      src: "/default_card_front.png",
      depth: 0,
      foilEffect: foilEffect,
      foil: foilEffect && "/default_card_foil.webp",
      mask: foilEffect && "/default_card_mask.webp",
    },
    {
      src: "/default_card_bg.png",
      depth: -5,
      foilEffect: null,
      foil: null,
      mask: null,
    },
  ];

  if (!card?.isActive) {
    cardLayers.push({
      src: "/cross.webp",
      depth: 20,
      foilEffect: null,
      foil: null,
      mask: null,
    });
  }

  return (
    <div
      id={card?.instanceId}
      className={`relative rounded-sm aspect-card ${cardSizeInfo} transform-3d transform-gpu will-change-transform user-select-none${className ?? ""}`}
      style={
        {
          transform: `perspective(1000px) rotateX(${appliedTilt.x}deg) rotateY(${appliedTilt.y}deg) rotateZ(${appliedTilt.z}deg)`,
          ...(style ?? {}),
        } as React.CSSProperties
      }
    >
      <CardFront
        layers={cardLayers}
        tilt={appliedTilt}
        glare={appliedGlare}
        isHovering={!!isHovering}
      />
      <CardBack />
      <CardGlare glare={appliedGlare} isHovering={!!isHovering} />
      <p className="text-center absolute text-black">{card?.name}</p>
      {card?.description && (
        <p className="text-center absolute text-black top-40">
          {convertDescriptions(card?.description)}
        </p>
      )}
    </div>
  );
};

export default Card;
