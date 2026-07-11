"use client";

import LoadingSpinner from "@/components/atoms/LoadingSpinner";
import Card from "@/components/molecules/Card";
import { CardSize } from "@/constants/card";
import { BasicCard } from "@/lib/cards/types/card";
import { useEffect, useMemo, useState } from "react";

type BoosterPreviewCarouselProps = {
  cards: BasicCard[];
  isVisible: boolean;
  isLoading: boolean;
  shouldRenderCards: boolean;
};

const PREVIEW_CARD_COUNT = 5;

export default function BoosterPreviewCarousel({
  cards,
  isVisible,
  isLoading,
  shouldRenderCards,
}: BoosterPreviewCarouselProps) {
  const [isMarqueeReady, setIsMarqueeReady] = useState(false);
  const [renderedCardCount, setRenderedCardCount] = useState(0);

  const previewCards = useMemo(() => {
    if (isLoading) {
      return cards;
    }

    return cards.slice(0, PREVIEW_CARD_COUNT);
  }, [cards, isLoading]);

  const activeVisibleCards = useMemo(() => {
    const visibleCards = [...previewCards, ...previewCards];

    return visibleCards.map((card) =>
      card.isActive ? card : { ...card, isActive: true },
    );
  }, [previewCards]);

  useEffect(() => {
    if (
      !isVisible ||
      isLoading ||
      !shouldRenderCards ||
      previewCards.length === 0
    ) {
      setIsMarqueeReady(false);
      setRenderedCardCount(0);
      return;
    }

    let frameId1 = 0;
    let frameId2 = 0;

    frameId1 = window.requestAnimationFrame(() => {
      frameId2 = window.requestAnimationFrame(() => {
        setIsMarqueeReady(true);
      });
    });

    return () => {
      window.cancelAnimationFrame(frameId1);
      window.cancelAnimationFrame(frameId2);
    };
  }, [isLoading, isVisible, previewCards.length, shouldRenderCards]);

  useEffect(() => {
    if (
      !isVisible ||
      isLoading ||
      !shouldRenderCards ||
      !isMarqueeReady ||
      activeVisibleCards.length === 0
    ) {
      setRenderedCardCount(0);
      return;
    }

    let animationFrameId = 0;

    const progressivelyRenderCards = () => {
      setRenderedCardCount((currentCount) => {
        const nextCount = Math.min(currentCount + 2, activeVisibleCards.length);

        if (nextCount < activeVisibleCards.length) {
          animationFrameId = window.requestAnimationFrame(
            progressivelyRenderCards,
          );
        }

        return nextCount;
      });
    };

    animationFrameId = window.requestAnimationFrame(progressivelyRenderCards);

    return () => {
      window.cancelAnimationFrame(animationFrameId);
    };
  }, [
    activeVisibleCards.length,
    isLoading,
    isMarqueeReady,
    isVisible,
    shouldRenderCards,
  ]);

  const cardsToRender = activeVisibleCards.slice(0, renderedCardCount);
  const shouldShowPanelSpinner =
    isLoading ||
    (isVisible &&
      (!isMarqueeReady ||
        !shouldRenderCards ||
        (previewCards.length > 0 && cardsToRender.length === 0)));

  return (
    <div className="h-44 overflow-hidden rounded-lg border border-white/20 bg-black/10 py-2">
      {shouldShowPanelSpinner ? (
        <div className="flex h-full items-center justify-center">
          <LoadingSpinner className="h-8 w-8" />
        </div>
      ) : previewCards.length > 0 ? (
        <div
          className="flex w-max items-center gap-4 animate-booster-preview-marquee"
          style={{
            animationPlayState:
              isVisible && renderedCardCount > 0 ? "running" : "paused",
          }}
        >
          {cardsToRender.map((card, index) => (
            <div
              key={`${card.instanceId}-${index}`}
              className="pointer-events-none shrink-0 select-none"
            >
              <Card card={card} size={CardSize.SM} showLoadingUntilReady />
            </div>
          ))}
        </div>
      ) : (
        <div className="flex h-full items-center justify-center">
          <p className="px-4 text-center text-sm opacity-80">
            Aucune carte disponible
          </p>
        </div>
      )}
    </div>
  );
}
