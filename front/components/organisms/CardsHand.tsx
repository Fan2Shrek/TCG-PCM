"use client";

import { useCallback, useEffect, useMemo, useState } from "react";
import { CardModel, CardSize, CardWithPosition } from "../types/card";
import { getCardWidthRem, remToPx, getCardAspectRatio, cardsHandComputeArcParameters, cardsHandComputeCardPosition } from "../utils/cardUtils";
import CardInHand from "../molecules/CardInHand";
import { useDebouncedValue } from "../hooks/useDebounceValue";

export type CardsHandProps = {
  cards: CardModel[];
  cardSize?: CardSize;
  hoverCardSize?: CardSize;
  className?: string;
};

export default function CardsHand({
  cards,
  cardSize = "md",
  hoverCardSize = "lg",
  className = "",
}: CardsHandProps) {
  const cardWidthRem = getCardWidthRem(cardSize);
  const hoverCardWidthRem = getCardWidthRem(hoverCardSize);
  const cardWidthPx = remToPx(cardWidthRem);
  const cardASpectRatio = getCardAspectRatio();

  const hoverYOffset = useMemo(() => {
    const normalHeightRem = cardWidthRem * cardASpectRatio;
    const hoverHeightRem = hoverCardWidthRem * cardASpectRatio;
    return remToPx((hoverHeightRem - normalHeightRem) * 2);
  }, [cardWidthRem, hoverCardWidthRem, cardASpectRatio]);

  const [positionedCards, setPositionedCards] = useState<CardWithPosition[]>([]);

  //debounce here to avoid constant card switching when hand is fanning out
  const [hoveredCard, setHoveredCard] = useState<CardWithPosition | null>(null);
  const [pendingHoveredCard, setPendingHoveredCard] = useState<CardWithPosition | null>(null);
  const debouncedHoveredCard = useDebouncedValue(pendingHoveredCard, 50);

  const calculatePositions = useCallback(() => {
    const totalCards = cards.length;
    if (totalCards === 0) {
      setPositionedCards([]);
      return;
    }

    const maxAngle = 60;
    const cardInFocus = !!hoveredCard;

    const normalParams = cardsHandComputeArcParameters(totalCards, cardWidthPx, maxAngle, false);

    const getPositions = (params: any, effectiveCenter?: number) =>
      cards.map((_card, index) => {
        const { x, y, rotation } = cardsHandComputeCardPosition(
          index,
          totalCards,
          params.arcAngleRadian,
          params.radius,
          effectiveCenter
        );
        return { x, y, rotation };
      });

    const mapToCardWithPosition = (positions: any[]) =>
      positions.map((position, index) => ({
        card: cards[index],
        rank: index,
        ...position,
      }));

    const positions = getPositions(normalParams);

    if (!cardInFocus) {
      setPositionedCards(mapToCardWithPosition(positions));
    } else {
      const fanParams = cardsHandComputeArcParameters(totalCards, cardWidthPx, maxAngle, true);
      const fannedPositions = getPositions(fanParams, hoveredCard.rank);

      const shiftX = positions[hoveredCard.rank].x - fannedPositions[hoveredCard.rank].x;
      const shiftY = positions[hoveredCard.rank].y - fannedPositions[hoveredCard.rank].y;

      const shiftedPositions = fannedPositions.map((pos) => ({
        x: pos.x + shiftX,
        y: pos.y + shiftY,
        rotation: pos.rotation,
      }));

      setPositionedCards(mapToCardWithPosition(shiftedPositions));
    }
  }, [cards, cardWidthPx, hoveredCard]);

  useEffect(() => calculatePositions(), [calculatePositions]);

  useEffect(() => {
    const handleResize = () => calculatePositions();
    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, [calculatePositions]);

  const handleCardDrag = (e: React.MouseEvent) => {
    //TODO implement card play through drag and drop
    console.log(e);
  }

  const handleCardClick = (card: CardWithPosition) => {
    console.log(card);
  };

  const handleCardHover = (card: CardWithPosition) => {
    setPendingHoveredCard({ ...card, y: hoveredCard?.y ?? card.y });
  };

  const handleCardLeave = () => setPendingHoveredCard(null);
  
  useEffect(() => {
    setHoveredCard(debouncedHoveredCard);
  }, [debouncedHoveredCard]);

  return (
    <div className={`relative w-82 h-82 ${className}`}>
      {positionedCards.map((positionedCard) => (
        <CardInHand
          key={positionedCard.card.id}
          positionedCard={positionedCard}
          hoverYOffset={hoverYOffset}
          cardSize={cardSize}
          hoverCardSize={hoverCardSize}
          totalCards={cards.length}
          onHover={handleCardHover}
          onLeave={handleCardLeave}
          onClick={handleCardClick}
          onDragCard={handleCardDrag}
        />
      ))}
    </div>
  );
}
