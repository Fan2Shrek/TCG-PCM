import React, { useState, useEffect } from "react";
import Card from "./Card";
import { CardSize, CardWithPosition } from "../types/card";
import { useDebouncedValue } from "../hooks/useDebounceValue";
import { useDrag } from "../hooks/useDrag";

type HandCardProps = {
  positionedCard: CardWithPosition;
  hoverYOffset: number;
  cardSize: CardSize;
  hoverCardSize: CardSize;
  totalCards: number;
  onHover: (card: CardWithPosition) => void;
  onLeave: () => void;
  onDragCard: (e: MouseEvent) => void;
  onDragEnd: (card: CardWithPosition) => void;
};

export default function HandCard({
  positionedCard,
  hoverYOffset,
  cardSize,
  hoverCardSize,
  totalCards,
  onHover,
  onLeave,
  onDragCard,
  onDragEnd,
}: HandCardProps) {
  const [isHovered, setIsHovered] = useState(false);
  const [pendingIsHovered, setPendingIsHovered] = useState(isHovered);
  const debouncedIsHovered = useDebouncedValue(pendingIsHovered, 100);

  const { isDragging, dragOffset, tilt, handleMouseDown } = useDrag({
    onDrag: onDragCard,
    onDragEnd: () => onDragEnd(positionedCard),
  });

  const handleMouseEnter = () => {
    if (isDragging) return;
    setPendingIsHovered(true);
    onHover(positionedCard);
  };

  const handleMouseLeave = () => {
    if (isDragging) return;
    setPendingIsHovered(false);
    onLeave();
  };

  useEffect(() => {
    setIsHovered(debouncedIsHovered);
  }, [debouncedIsHovered]);

  useEffect(() => {
    if (isDragging) {
      setPendingIsHovered(false);
      setIsHovered(false);
      onLeave();
    }
  }, [isDragging, onLeave]);

  const displayY = isHovered
    ? positionedCard.y - hoverYOffset
    : positionedCard.y;
  const displayX = positionedCard.x;
  const zIndex = isHovered || isDragging ? totalCards + 1 : positionedCard.rank;

  return (
    <div
      className={`absolute top-[50%] left-[50%] ${
        isDragging
          ? "cursor-grabbing"
          : "cursor-grab transition-all ease-in-out duration-100"
      }`}
      style={{
        transform: `
          translate(
            calc(-50% + ${displayX + (dragOffset?.x || 0)}px),
            calc(50% + ${displayY + (dragOffset?.y || 0)}px)
          )
        `,
        zIndex,
      }}
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
      onMouseDown={handleMouseDown}
    >
      <Card
        card={positionedCard.card}
        size={isHovered || isDragging ? hoverCardSize : cardSize}
        tilt={{
          x: tilt.x,
          y: tilt.y,
          z: isDragging ? 0 : positionedCard.rotation,
        }}
      />
    </div>
  );
}
