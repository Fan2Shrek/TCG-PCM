"use client";
import { useMemo } from "react";
import { CardModel, CardWithPosition } from "../types/card";
import {
  cardsHandComputeArcParameters,
  cardsHandComputeCardPosition,
} from "../utils/cardUtils";

type ArcParameters = ReturnType<typeof cardsHandComputeArcParameters>;

export function useHandPositions(
  cards: CardModel[],
  cardWidthPx: number,
  hoveredCard: CardWithPosition | null
): CardWithPosition[] {
  const positionedCards = useMemo(() => {
    const totalCards = cards.length;
    if (totalCards === 0) {
      return [];
    }

    const maxAngle = 60;
    const cardInFocus = !!hoveredCard;

    const normalParams = cardsHandComputeArcParameters(
      totalCards,
      cardWidthPx,
      maxAngle,
      false
    );

    const getPositions = (params: ArcParameters, effectiveCenter?: number) =>
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

    type Position = ReturnType<typeof getPositions>[number];

    const mapToCardWithPosition = (positions: Position[]) =>
      positions.map((position, index) => ({
        card: cards[index],
        rank: index,
        ...position,
      }));

    const positions = getPositions(normalParams);

    if (!cardInFocus) {
      return mapToCardWithPosition(positions);
    } else {
      const fanParams = cardsHandComputeArcParameters(
        totalCards,
        cardWidthPx,
        maxAngle,
        true
      );
      const fannedPositions = getPositions(fanParams, hoveredCard.rank);

      const shiftX =
        positions[hoveredCard.rank].x - fannedPositions[hoveredCard.rank].x;
      const shiftY =
        positions[hoveredCard.rank].y - fannedPositions[hoveredCard.rank].y;

      const shiftedPositions = fannedPositions.map((pos) => ({
        x: pos.x + shiftX,
        y: pos.y + shiftY,
        rotation: pos.rotation,
      }));

      return mapToCardWithPosition(shiftedPositions);
    }
  }, [cards, cardWidthPx, hoveredCard]);

  return positionedCards;
}
