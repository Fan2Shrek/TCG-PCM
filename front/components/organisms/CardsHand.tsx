import { useCallback, useEffect, useMemo } from "react";
import Card from "../molecules/Card";
import { CardModel, CardSize, CardWithPosition } from "../types/card";
import { useState } from "react";
import { getCardWidthRem, remToPx, cardsHandComputeArcParameters, cardsHandComputeCardPosition } from "../utils/cardUtils";

const CARD_ASPECT_RATIO = 5 / 7;
const calculateCardHeightRem = (widthRem: number) => widthRem * CARD_ASPECT_RATIO;

export type CardsHandProps = {
  cards: CardModel[];
  cardSize?: CardSize;
  hoverCardSize?: CardSize;
  className?: string;
};

export default function CardsHand({ cards, cardSize = 'md', hoverCardSize = 'lg', className = '' }: CardsHandProps) {

  const cardWidthRem = getCardWidthRem(cardSize);
  const hoverCardWidthRem = getCardWidthRem(hoverCardSize);
  const cardWidthPx = remToPx(cardWidthRem);
  
  const hoverYOffset = useMemo(() => {
    const normalHeightRem = calculateCardHeightRem(cardWidthRem);
    const hoverHeightRem = calculateCardHeightRem(hoverCardWidthRem);
    const heightDiffRem = hoverHeightRem - normalHeightRem;
    return remToPx(heightDiffRem * 2);
  }, [cardWidthRem, hoverCardWidthRem]);
  
  const [positionedCards, setPositionedCards] = useState<CardWithPosition[]>([]);
  const [hoveredCard, setHoveredCard] = useState<{
    index: number | null;
    card: CardWithPosition | null;
  }>({ index: null, card: null });
  
  const calculatePositions = useCallback(() => {
    const totalCards = cards.length;
    if (totalCards === 0) {
      setPositionedCards([]);
      return;
    }

    const maxAngle = 60;
    const cardInFocus = !!hoveredCard.card;
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
      }
    );

    const mapToCardWithPosition = (positions: any[]) =>
      positions.map((position, index) => ({
        card: cards[index],
        rank: index,
        ...position,
      })
    );

    const positions = getPositions(normalParams);

    if (!cardInFocus) {
      setPositionedCards(mapToCardWithPosition(positions));
    } else {
      const hoveredIndex = hoveredCard?.index ?? 0;
      
      const fanParams = cardsHandComputeArcParameters(totalCards, cardWidthPx, maxAngle, true);
      const fannedPositions = getPositions(fanParams, hoveredCard.card?.rank);

      // Calculate the shift needed to keep the hovered card in the same position
      const shiftX = positions[hoveredIndex].x - fannedPositions[hoveredIndex].x;
      const shiftY = positions[hoveredIndex].y - fannedPositions[hoveredIndex].y;

      const shiftedPositions = fannedPositions.map((position) => ({
        x: position.x + shiftX,
        y: position.y + shiftY,
        rotation: position.rotation,
      }));

      setPositionedCards(mapToCardWithPosition(shiftedPositions));
    }
  }, [cards, cardWidthPx, cardSize, hoveredCard]);

  useEffect(() => {
    calculatePositions();
  }, [calculatePositions]);

  useEffect(() => {
    const handleResize = () => {
      calculatePositions();
    };

    window.addEventListener('resize', handleResize);

    return () => {
      window.removeEventListener('resize', handleResize);
    };
  }, [calculatePositions]);

  const handleCardClick = (card: CardWithPosition) => {

  }

  const handleCardHover = (card: CardWithPosition, index: number) => {
    setHoveredCard({ 
      index, 
      card: { 
        ...card, 
        y: hoveredCard.card?.y ?? card.y 
      } 
    });
    console.log( hoveredCard );
  }

  const handleCardLeave = () => {
    setHoveredCard({ index: null, card: null });
  }

  return (
    <div className={`relative w-full h-82 ${className}`}>
      {positionedCards.map((positionedCard, index) => {
        const isHovered = hoveredCard.index === index;
        const displayY = isHovered ? positionedCard.y - hoverYOffset : positionedCard.y;
        
        return (
        <div
          key={positionedCard.rank}
          className="transition-all duration-100 ease-in-out"
          style={{
            position: 'absolute',
            left: '50%',
            top: '50%',
            transform: `
              translate(
                calc(-50% + ${positionedCard.x}px),
                calc(50% + ${displayY}px)
              )
            `,
            zIndex: isHovered ? cards.length + 1 : positionedCard.rank,
          }}
          onMouseEnter={() => handleCardHover(positionedCard, index)}
          onMouseLeave={handleCardLeave}
          onClick={() => handleCardClick(positionedCard)}
        >
          <Card
            key={index}
            card={positionedCard.card}
            size={isHovered ? hoverCardSize : cardSize}
            tilt={{ x: 0, y: 0, z: positionedCard.rotation }}
          />
        </div>
        );
      })}
    </div>
  );
}
