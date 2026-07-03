import { useState, useEffect, useRef } from "react";
import Card from "./Card";
import { CardSize } from "@/constants/card";
import { CardWithPosition } from "@/lib/cards/types/card";
import { useDebouncedValue } from "@/hooks/useDebounceValue";
import { useDrag } from "@/hooks/useDrag";
import DraggedCard from "./DraggedCard";

type HandCardProps = {
  positionedCard: CardWithPosition;
  cardSize: CardSize;
  totalCards: number;
  onHover: (card: CardWithPosition) => void;
  onLeave: () => void;
  onDragCard?: (e: MouseEvent) => void;
  onDragEnd?: (card: CardWithPosition, pointerPos: { x: number; y: number }) => void;
  isDisabled?: boolean;
  isHandHovered?: boolean;
  isAnimatingDraw?: boolean;
};

const HOVERED_CARD_OFFSET = 30;
const DRAWING_CARD_OFFSET = -200;

export default function HandCard({
  positionedCard,
  cardSize,
  totalCards,
  onHover,
  onLeave,
  onDragCard,
  onDragEnd,
  isDisabled = false,
  isHandHovered = false,
  isAnimatingDraw = false,
}: HandCardProps) {
  const [isHovered, setIsHovered] = useState(false);
  //for drag
  const [cardCenter, setCardCenter] = useState<{ x: number; y: number; z: number } | null>(null);
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
  const showCardElementDebounced = useDebouncedValue(showDraggedCard, 100);

  const displayY = isAnimatingDraw
    ? positionedCard.y - DRAWING_CARD_OFFSET
    : isHandHovered && isHovered
      ? positionedCard.y - HOVERED_CARD_OFFSET
      : positionedCard.y;
  const displayX = positionedCard.x;
  const zIndex = isHovered || isDragging ? totalCards + 1 : positionedCard.rank;

  useEffect(() => {
    if (isDragging) {
      onLeave();
      if (cardRef.current) {
        const rect = cardRef.current.getBoundingClientRect();
        setCardCenter({
          x: rect.left + rect.width / 2,
          y: rect.top + rect.height / 2,
          z: 50,
        });
      }
      //this if for when a card is no longer dragged basically
    } else if (prevDraggingRef.current && !isDragging && cardRef.current && pointerPos) {
      const rect = cardRef.current.getBoundingClientRect();
      setCardCenter({
        x: rect.left + rect.width / 2,
        y: rect.top + rect.height / 2,
        z: zIndex,
      });

      onDragEnd?.(positionedCard, pointerPos);
    }
    prevDraggingRef.current = isDragging;
  }, [isDragging, onLeave, onDragEnd, positionedCard, pointerPos, zIndex]);

  const handleMouseEnter = () => {
    setIsHovered(true);
    onHover(positionedCard);
  };

  const handleMouseLeave = () => {
    setIsHovered(false);
    onLeave();
  };

  const cardElement = (
    <div
      ref={cardRef}
      className={`absolute top-[50%] left-[50%] cursor-grab transition-all ease-in-out duration-100 after:content-['']
        after:absolute after:top-full after:left-1/2 after:-translate-x-1/2 after:w-full after:h-24 after:pointer-events-auto
        ${isDragging || isDropped ? "invisible pointer-events-none" : ""}`}
      style={{
        transform: `
          translate(
            calc(-50% + ${displayX}px),
            calc(50% + ${displayY}px)
          )
        `,
        transition: "all 100ms ease-in-out",
        zIndex,
      }}
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
      onMouseDown={(e) => !isDisabled && handleMouseDown(e)}
    >
      <Card
        card={positionedCard.card}
        size={cardSize}
        tilt={{
          x: 0,
          y: 0,
          z: positionedCard.rotation,
        }}
      />
    </div>
  );

  if (showDraggedCard || showCardElementDebounced) {
    return (
      <>
        {cardElement}
        <DraggedCard
          card={positionedCard.card}
          originPos={cardCenter}
          originSize={cardSize}
          originTilt={{ x: 0, y: 0, z: positionedCard.rotation }}
          pointerPos={showDraggedCard ? pointerPos : null}
          tilt={{ ...tilt, z: positionedCard.rotation }}
          isDropped={isDropped}
        />
      </>
    );
  }

  return cardElement;
}
