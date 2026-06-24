"use client";
import { useMemo } from "react";
import { CardModel, CardWithPosition } from "@/lib/cards/types/card";
import { cardsHandComputeArcParameters, cardsHandComputeCardPosition } from "@/lib/cards/cardUtils";

type ArcParameters = ReturnType<typeof cardsHandComputeArcParameters>;

export function useHandPositions(cards: CardModel[], cardWidthPx: number, hovered: boolean): CardWithPosition[] {
  const positionedCards = useMemo(() => {
    const totalCards = cards.length;
    if (totalCards === 0) {
      return [];
    }

    const maxAngle = 60;

    const normalParams = cardsHandComputeArcParameters(totalCards, cardWidthPx, maxAngle, false);

    const getArcPositions = (params: ArcParameters, effectiveCenter?: number) =>
      cards.map((_card, index) => {
        const { x, y, rotation } = cardsHandComputeCardPosition(index, totalCards, params.arcAngleRadian, params.radius, effectiveCenter);
        return { x, y, rotation };
      });

    type Position = ReturnType<typeof getArcPositions>[number];

    const mapToCardWithPosition = (positions: Position[]) =>
      positions.map((position, index) => ({
        card: cards[index],
        rank: index,
        ...position,
      }));

    const positions = getArcPositions(normalParams);

    if (!hovered) {
      return mapToCardWithPosition(positions);
    } else {
      const baseSpacing = cardWidthPx;
      const maxCards = 7;

      let spacing: number;
      if (totalCards <= maxCards) {
        spacing = baseSpacing;
      } else {
        const maxWidth = (maxCards - 1) * baseSpacing;
        spacing = maxWidth / (totalCards - 1);
      }

      const totalWidth = (totalCards - 1) * spacing;
      const startX = -totalWidth / 2;
      const middleCardIndex = Math.floor(totalCards / 2);
      const centerY = positions[middleCardIndex].y;

      const straightLinePositions = cards.map((_card, index) => ({
        x: startX + index * spacing,
        y: centerY,
        rotation: 0,
      }));

      return mapToCardWithPosition(straightLinePositions);
    }
  }, [cards, cardWidthPx, hovered]);

  return positionedCards;
}
