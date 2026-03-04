import { useCallback, useEffect, useRef } from "react";
import Card from "../molecules/Card";
import { CardModel, CardSize, CardInHand } from "../types/card";
import { useState } from "react";
import { getCardWidthRem, remToPx, getCardAspectRatio, cardsHandComputeArcParameters, cardsHandComputeCardPosition } from "../utils/cardUtils";
export type CardsHandProps = {
  cards: CardModel[];
  onCardClick?: (cardId: string) => void;
  cardSize?: CardSize;
  className?: string;
};

export default function CardsHand({ cards, onCardClick, cardSize = 'md', className }: CardsHandProps) {

  const cardWidthRem = getCardWidthRem(cardSize);
  const cardWidthPx = remToPx(cardWidthRem);
  
  const [positionedCards, setPositionedCards] = useState<CardInHand[]>([]);
  const [hoveredCardIndex, setHoveredCardIndex] = useState<number | null>(null);

  const calculatePositions = useCallback(() => {
    const totalCards = cards.length;
    if (totalCards === 0) {
      setPositionedCards([]);
      return;
    }

    const screenWidth = typeof window !== 'undefined' ? window.innerWidth : 0;
    const maxAngle = screenWidth < 768 ? 60 : 120;

    const { arcAngleRadian, radius } = cardsHandComputeArcParameters(totalCards, cardWidthPx, maxAngle);

    setPositionedCards(
      cards.map((card, index) => {
        const { x, y, rotation } = cardsHandComputeCardPosition(index, totalCards, arcAngleRadian, radius);
        return {
          card,
          position: index,
          x,
          y,
          rotation,
        };
      })
    );
  }, [cards, cardWidthPx, cardSize]);

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

  const handleCardHover = (cardIdentifier: string, cardIndex: number) => {
    console.log(`Hovering card ${cardIdentifier} at index ${cardIndex}`);
    setHoveredCardIndex(cardIndex);
  }

  const handleCardLeave = () => {
    setHoveredCardIndex(null);
  }

  return (
    <div className={`relative w-full h-82 ${className}`}>
      {positionedCards.map((positionedCard, index) => (
        <div
          key={positionedCard.position}
          className="transition-all duration-100 ease-in-out"
          style={{
            position: 'absolute',
            left: '50%',
            top: '50%',
            transform: `
              translate(
                calc(-50% + ${positionedCard.x}px),
                calc(50% + ${positionedCard.y}px)
              )
            `,
            zIndex: positionedCard.position,
          }}
          onMouseEnter={() => handleCardHover(positionedCard.card.id, index)}
          onMouseLeave={handleCardLeave}
        >          
          <Card
            key={index}
            card={positionedCard.card}
            size={cardSize}
            tilt={{ x: 0, y: 0, z: positionedCard.rotation }}
          />
        </div>
      ))}
    </div>
  );
}
