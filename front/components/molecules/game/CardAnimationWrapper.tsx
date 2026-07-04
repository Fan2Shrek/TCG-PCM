"use client";

import { useEffect, useState, type CSSProperties, type ReactNode } from "react";
import { BasicCard } from "@/lib/cards/types/card";
import { emitter } from "@/lib/eventBus";

const CARD_PLAY_ANIMATION_TIME = 300;

type CardAnimationWrapperProps = {
  card: BasicCard;
  children: ReactNode;
  className?: string;
  style?: CSSProperties;
  isSelected?: boolean;
  isActive?: boolean;
  isOpponentSide?: boolean;
};

function getCardAnimationStyle(
  isPlaying: boolean,
  isSelected: boolean,
  isActive: boolean,
  isOpponentSide: boolean,
) {
  if (isPlaying) {
    const playOffset = isOpponentSide ? "-200px" : "200px";
    return {
      transform: `scale(1.1) translateZ(80px) translateY(${playOffset})`,
      boxShadow:
        "0 50px 40px rgba(0, 0, 0, 0.5), 0 10px 20px rgba(0, 0, 0, 0.3)",
      transition: "transform 300ms ease-in",
    } as CSSProperties;
  }

  if (isSelected) {
    return {
      transform: "scale(1.1) translateZ(80px) translateY(-40px)",
      boxShadow:
        "0 50px 40px rgba(0, 0, 0, 0.5), 0 10px 20px rgba(0, 0, 0, 0.3)",
    } as CSSProperties;
  }

  return {
    transform: `scale(1) translateZ(0) translateY(0)${!isActive ? " rotateZ(90deg)" : ""}`,
  } as CSSProperties;
}

export default function CardAnimationWrapper({
  card,
  children,
  className,
  style,
  isSelected = false,
  isActive = true,
  isOpponentSide = false,
}: CardAnimationWrapperProps) {
  const [playingCardIds, setPlayingCardIds] = useState<Set<string>>(new Set());

  useEffect(() => {
    const handleCardAnimated = (event: { card: { instanceId: string } }) => {
      setPlayingCardIds((prev) => new Set(prev).add(event.card.instanceId));

      setTimeout(() => {
        setPlayingCardIds((prev) => {
          const next = new Set(prev);
          next.delete(event.card.instanceId);
          return next;
        });
      }, CARD_PLAY_ANIMATION_TIME);
    };

    emitter.on("card:played", handleCardAnimated);
    emitter.on("card:discarded", handleCardAnimated);

    return () => {
      emitter.off("card:played", handleCardAnimated);
      emitter.off("card:discarded", handleCardAnimated);
    };
  }, []);

  const isPlaying = playingCardIds.has(card.instanceId);

  return (
    <div
      className={className}
      style={{
        ...getCardAnimationStyle(
          isPlaying,
          isSelected,
          isActive,
          isOpponentSide,
        ),
        ...style,
      }}
    >
      {children}
    </div>
  );
}
