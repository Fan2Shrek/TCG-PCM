"use client";

import React from "react";
import CardFront from "../atoms/CardFront";
import CardBack from "../atoms/CardBack";
import CardGlare from "../atoms/CardGlare";
import {
  CardRaririty,
  CardSet,
  CardType,
  CardSize,
  CardSizeMap,
  FoilEffects,
} from "@/constants/card";
import { BasicCard, CardLayer } from "@/lib/cards/types/card";
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
  const setFolderName = (cardSet: CardSet): string => {
    switch (cardSet) {
      case CardSet.BTD6:
        return "btd";
      case CardSet.TBOI:
        return "isaac";
      case CardSet.ORIGINAL:
      default:
        return "original";
    }
  };

  const buildFrontSrcs = (cardSet: CardSet) => {
    const folder = setFolderName(cardSet);
    return {
      main: `/card/${folder}/card_front_${folder}_main.png`,
      header: `/card/${folder}/card_front_${folder}_header.png`,
      outline: `/card/${folder}/card_front_${folder}_outline.png`,
      fullStats: `/card/${folder}/card_front_${folder}_full_stats.png`,
      smallStats: `/card/${folder}/card_front_${folder}_small_stats.png`,
    };
  };

  const getStatsSrc = (type: CardType | undefined, cardSet: CardSet) => {
    const srcs = buildFrontSrcs(cardSet);
    if (!type) return srcs.fullStats;
    switch (type) {
      case CardType.MONSTER:
        return srcs.fullStats;
      case CardType.PASSIVE:
      case CardType.CONSUMABLE:
        return srcs.smallStats;
      case CardType.CHARACTER:
      default:
        return null;
    }
  };

  const frontSrcs = buildFrontSrcs(card.set);

  const cardLayers: CardLayer[] = [
    {
      src: card?.image && getImage(card.image),
      depth: -5,
      foilEffect: null,
      foil: null,
      mask: null,
    },
    {
      src: frontSrcs.main,
      depth: 0,
      foilEffect: foilEffect,
      foil: foilEffect && "/card/card_foil.png",
      mask: foilEffect && "/card/card_mask.png",
    },
    {
      src: frontSrcs.header,
      depth: 1,
      foilEffect: null,
      foil: null,
      mask: null,
    },
    {
      src: frontSrcs.outline,
      depth: 1,
      foilEffect: null,
      foil: null,
      mask: null,
    },
    {
      src: "/card/card_bg_default.png",
      depth: -10,
      foilEffect: null,
      foil: null,
      mask: null,
    },
  ];

  const statsSrc = getStatsSrc(card.type, card.set);
  if (statsSrc) {
    cardLayers.push({
      src: statsSrc,
      depth: 1,
      foilEffect: null,
      foil: null,
      mask: null,
    });
  }

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
      <p className="text-center absolute text-black font-pixel">{card?.name}</p>
      {card?.description && (
        <p className="text-center absolute text-black top-40 font-pixel">
          {convertDescriptions(card?.description)}
        </p>
      )}
    </div>
  );
};

export default Card;
