import React, { useState, useEffect, useRef } from "react";
import Card from "./Card";
import { CardSize } from "@/constants/card";
import { CardWithPosition } from "@/lib/cards/types/card";
import { useDebouncedValue } from "@/hooks/useDebounceValue";
import { useDrag } from "@/hooks/useDrag";
import DraggedCard from "./DraggedCard";

type HandCardProps = {
  positionedCard: CardWithPosition;
  hoverYOffset: number;
  cardSize: CardSize;
  hoverCardSize: CardSize;
  totalCards: number;
  onHover: (card: CardWithPosition) => void;
  onLeave: () => void;
  onDragCard?: (e: MouseEvent) => void;
  onDragEnd?: (
    card: CardWithPosition,
    pointerPos: { x: number; y: number },
  ) => void;
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
  //for drag
  const [cardCenter, setCardCenter] = useState<{ x: number; y: number } | null>(
    null,
  );
  const [isDropped, setIsDropped] = useState(false);

  const cardRef = useRef<HTMLDivElement | null>(null);
  const prevDraggingRef = useRef(false);

  const { isDragging, pointerPos, tilt, handleMouseDown } = useDrag({
    onDrag: onDragCard,
    onDragEnd: () => {
      setIsDropped(true);
      const t = window.setTimeout(() => setIsDropped(false), 300);
      return () => window.clearTimeout(t);
    },
    card: positionedCard.card,
  });

  const showDraggedCard = isDragging || (isDropped && pointerPos);

  const debouncedIsHovered = useDebouncedValue(pendingIsHovered, 100);
  const displayY = isHovered
    ? positionedCard.y - hoverYOffset
    : positionedCard.y;
  const displayX = positionedCard.x;
  const zIndex = isHovered || isDragging ? totalCards + 1 : positionedCard.rank;

  useEffect(() => {
    if (isDragging) {
      setPendingIsHovered(false);
      setIsHovered(false);
      onLeave();
      //this if for when a card is no longer dragged basically
    } else if (
      prevDraggingRef.current &&
      !isDragging &&
      cardRef.current &&
      pointerPos
    ) {
      const rect = cardRef.current.getBoundingClientRect();
      setCardCenter({
        x: rect.left + rect.width / 2,
        y: rect.top + rect.height / 2,
      });

      onDragEnd?.(positionedCard, pointerPos);
    }
    prevDraggingRef.current = isDragging;
  }, [isDragging]);

  useEffect(() => {
    setIsHovered(debouncedIsHovered);
  }, [debouncedIsHovered]);

  const handleMouseEnter = () => {
    setPendingIsHovered(true);
    onHover(positionedCard);
  };

  const handleMouseLeave = () => {
    setPendingIsHovered(false);
    onLeave();
  };

  const cardElement = (
    <div
      ref={cardRef}
      className={`absolute top-[50%] left-[50%] cursor-grab transition-all ease-in-out duration-100 ${
        isDragging || isDropped ? "invisible pointer-events-none" : ""
      }`}
      style={{
        transform: `
          translate(
            calc(-50% + ${displayX}px),
            calc(50% + ${displayY}px)
          )
        `,
        zIndex,
      }}
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
      onMouseDown={(e) => handleMouseDown(e)}
    >
      <Card
        card={positionedCard.card}
        size={isHovered ? hoverCardSize : cardSize}
        tilt={{
          x: 0,
          y: 0,
          z: positionedCard.rotation,
        }}
      />
    </div>
  );

  if (showDraggedCard) {
    return (
      <>
        {cardElement}
        <DraggedCard
          card={positionedCard.card}
          originPos={cardCenter}
          originSize={cardSize}
          originTilt={{ x: 0, y: 0, z: positionedCard.rotation }}
          pointerPos={showDraggedCard ? pointerPos : null}
          tilt={tilt}
          isDropped={isDropped}
        />
      </>
    );
  }

  return cardElement;
}
