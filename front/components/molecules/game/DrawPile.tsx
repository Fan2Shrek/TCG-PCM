"use client";

import { useState, useEffect, useRef } from "react";
import DummyFaceDownCard from "@/components/molecules/game/DummyFaceDownCard";
import PileTooltip from "@/components/atoms/PileTooltip";
import { CardSize } from "@/constants/card";
import {
  GAMEBOARD_ANIMATION_DURATION,
  GAMEBOARD_ANIMATION_TIMING,
} from "@/constants/gameArea";
import { emitter } from "@/lib/eventBus";

type DrawPileProps = {
  numCards: number;
  className?: string;
  isCardDragged?: boolean;
  /** id of the currently logged-in player, used to tell whether a draw event belongs to this pile */
  playerId?: string;
  /** true when this pile represents the opponent's deck rather than the logged-in player's own deck */
  isOpponent?: boolean;
};

const SELF_CARD_DRAW_ANIMATION_TIME = 200;
const OPPONENT_CARD_DRAW_ANIMATION_TIME = 600;

export default function DrawPile({
  numCards,
  className = "",
  isCardDragged = false,
  playerId,
  isOpponent = false,
}: DrawPileProps) {
  const [showTooltip, setShowTooltip] = useState(false);
  const [displayNumCards, setDisplayNumCards] = useState(numCards);
  const [animatingIndex, setAnimatingIndex] = useState<number | null>(null);
  const animationTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const direction = isOpponent ? -1 : 1;
  const shadowOffsetX = direction * (displayNumCards * 2 - 6);
  const shadow = `1px 0 rgba(0,0,0,0.66)`;
  const animationTime = isOpponent
    ? OPPONENT_CARD_DRAW_ANIMATION_TIME
    : SELF_CARD_DRAW_ANIMATION_TIME;

  useEffect(() => {
    const handleCardDrawn = (event: { playerId: string }) => {
      const belongsToThisPile = isOpponent
        ? event.playerId !== playerId
        : event.playerId === playerId;

      if (playerId && !belongsToThisPile) return;

      setAnimatingIndex(displayNumCards - 1);

      if (animationTimerRef.current) {
        clearTimeout(animationTimerRef.current);
      }

      animationTimerRef.current = setTimeout(() => {
        emitter.emit("animation:card-draw-complete");
        setAnimatingIndex(null);
        setDisplayNumCards((prev) => prev - 1);
      }, animationTime);
    };

    emitter.on("game:card-drawn", handleCardDrawn);
    return () => {
      const timerId = animationTimerRef.current;
      if (timerId) clearTimeout(timerId);
      emitter.off("game:card-drawn", handleCardDrawn);
    };
  }, [playerId, displayNumCards, isOpponent, animationTime]);

  return (
    <div
      className={`relative w-card-md aspect-card ${className}`}
      onMouseEnter={() => setShowTooltip(true)}
      onMouseLeave={() => setShowTooltip(false)}
      style={{
        transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
        zIndex: displayNumCards,
      }}
    >
      {Array.from({ length: displayNumCards }).map((_, i) => {
        const offsetX = direction * i;
        const offsetY = isCardDragged ? 0 : -i * 1.3;
        const animatedCardOffsetY = offsetY + (isOpponent ? -1200 : 750);
        const isAnimating = i === animatingIndex;
        const isBottomCard = i === 0;

        return (
          <div
            key={i}
            className="absolute"
            style={{
              transform: `scale(${1 + i * 0.01}) translateX(${offsetX}px) translateY(${isAnimating ? animatedCardOffsetY : offsetY}px)`,
              zIndex: i,
              transition: `transform ${GAMEBOARD_ANIMATION_DURATION}ms ${GAMEBOARD_ANIMATION_TIMING}`,
              pointerEvents: "auto",
              ...(isBottomCard && {
                boxShadow: `${shadowOffsetX}px 0 ${shadow}`,
              }),
            }}
          >
            <DummyFaceDownCard
              size={CardSize.MD}
              className={isOpponent ? "rotate-180" : undefined}
            />
          </div>
        );
      })}
      <PileTooltip
        isVisible={showTooltip}
        count={numCards}
        isMirrored={isOpponent}
        label="cartes restantes"
      />
    </div>
  );
}
