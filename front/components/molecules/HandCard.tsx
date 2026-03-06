import React, { useState, useEffect, useCallback, useRef } from "react";
import Card from "./Card";
import { CardSize, CardWithPosition } from "../types/card";
import { useDebouncedValue } from "../hooks/useDebounceValue";

type HandCardProps = {
  positionedCard: CardWithPosition;
  hoverYOffset: number;
  cardSize: CardSize;
  hoverCardSize: CardSize;
  totalCards: number;
  onHover: (card: CardWithPosition) => void;
  onLeave: () => void;
  onDragCard: (e: React.MouseEvent) => void;
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
}: HandCardProps) {
  const [isHovered, setIsHovered] = useState(false);
  const [pendingIsHovered, setPendingIsHovered] = useState(isHovered);
  const debouncedIsHovered = useDebouncedValue(pendingIsHovered, 100);

  const [dragOffset, setDragOffset] = useState<{ x: number; y: number } | null>(null);
  const [dragStart, setDragStart] = useState<{ x: number; y: number } | null>(null);
  
  const [tilt, setTilt] = useState({ x: 0, y: 0 });
  const prevPos = useRef<{ x: number; y: number } | null>(null);
  const resetTiltTimer = useRef<number | null>(null);

  const isDragged = !!dragOffset;

  const handleMouseEnter = () => {
    if (isDragged) return;

    setPendingIsHovered(true);
    onHover(positionedCard);
  };

  useEffect(() => {
    setIsHovered(debouncedIsHovered);
  }, [debouncedIsHovered]);

  const handleMouseLeave = () => {
    console.log("leave card", positionedCard);
    if (isDragged) return;

    setPendingIsHovered(false);
    onLeave();
  };

  const handleMouseDown = (e: React.MouseEvent) => {
    e.preventDefault();
    setDragStart({ x: e.clientX, y: e.clientY });
    setDragOffset({ x: 0, y: 0 });
  };

  const handleMouseMove = useCallback(
    (e: MouseEvent) => {
      if (!dragStart) return;
      
      const x = e.clientX - dragStart.x;
      const y = e.clientY - dragStart.y;

      setDragOffset({ x,y });

      if (prevPos.current){
        const dx = x - prevPos.current.x;
        const dy = y - prevPos.current.y;

        const tiltX = Math.max(-50, Math.min(50, -dy));
        const tiltY = Math.max(-50, Math.min(50, dx));

        setTilt({ x: tiltX, y: tiltY });
      }

      prevPos.current = { x, y };

      if (resetTiltTimer.current) clearTimeout(resetTiltTimer.current);

      resetTiltTimer.current = window.setTimeout(() => {
        setTilt({ x: 0, y: 0 });
      }, 200);
      
      onDragCard(e as unknown as React.MouseEvent);
    },
    [dragStart]
  );

  const handleMouseUp = useCallback(() => {
    if (dragOffset) {
      setDragOffset(null);
      setDragStart(null);
      setTilt({ x: 0, y: 0 });
      prevPos.current = null;
    }

    setIsHovered(false);
    onLeave();
  }, [dragOffset]);

  useEffect(() => {
    if (!dragOffset) return;

    window.addEventListener("mousemove", handleMouseMove);
    window.addEventListener("mouseup", handleMouseUp);

    return () => {
      window.removeEventListener("mousemove", handleMouseMove);
      window.removeEventListener("mouseup", handleMouseUp);
    };
  }, [dragOffset, handleMouseMove, handleMouseUp]);

  const displayY = isHovered ? positionedCard.y - hoverYOffset : positionedCard.y;
  const displayX = positionedCard.x;
  const zIndex = isHovered || isDragged ? totalCards + 1 : positionedCard.rank;

  return (
    <div
      style={{
        position: "absolute",
        left: "50%",
        top: "50%",
        cursor: isDragged ? "grabbing" : "grab",
        transform: `
          translate(
            calc(-50% + ${displayX + (dragOffset?.x || 0)}px),
            calc(50% + ${displayY + (dragOffset?.y || 0)}px)
          )
        `,
        zIndex,
      }}
      className={isDragged ? "" : "transition-all ease-in-out duration-100"}
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
      onMouseDown={handleMouseDown}
    >
      <Card
        card={positionedCard.card}
        size={isHovered || isDragged ? hoverCardSize : cardSize}
        tilt={{ x: tilt.x, y: tilt.y, z: isDragged ? 0 : positionedCard.rotation }}
      />
    </div>
  );
}
