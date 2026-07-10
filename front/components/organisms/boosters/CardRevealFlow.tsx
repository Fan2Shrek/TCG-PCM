"use client";

import InteractiveCard from "@/components/molecules/InteractiveCard";
import { Button } from "@/components/ui/button";
import { CardRaririty } from "@/constants/card";
import { CardSize } from "@/constants/card";
import { useWindowWidth } from "@/hooks/useWindowWidth";
import { BoosterOpeningPhase } from "@/lib/boosterOpening/phases";
import { CARD_REVEAL_TRANSITION_MS } from "@/lib/boosterOpening/timings";
import { BasicCard } from "@/lib/cards/types/card";
import { useEffect, useMemo, useState } from "react";
import Confetti from "react-confetti";

type CardRevealFlowProps = {
  cards: BasicCard[];
  phase: BoosterOpeningPhase;
  currentCardIndex: number;
  onNextCard: () => void;
  onConfirmAll: () => void;
};

const CONFETTI_BURST_DURATION_MS = 7000;
const CONFETTI_SOURCE_WIDTH = 240;
const CONFETTI_SOURCE_HEIGHT = 220;
const CONFETTI_PIECES_BY_RARITY: Partial<Record<CardRaririty, number>> = {
  [CardRaririty.UNCOMMON]: 80,
  [CardRaririty.RARE]: 140,
  [CardRaririty.EPIC]: 220,
  [CardRaririty.LEGENDARY]: 320,
};

export default function CardRevealFlow({
  cards,
  phase,
  currentCardIndex,
  onNextCard,
  onConfirmAll,
}: CardRevealFlowProps) {
  const screenWidth = useWindowWidth();
  const isSmallScreen = screenWidth < 1024;
  const [isCardLeaving, setIsCardLeaving] = useState(false);
  const [showConfetti, setShowConfetti] = useState(false);
  const [confettiPieces, setConfettiPieces] = useState(0);
  const [viewportSize, setViewportSize] = useState({ width: 0, height: 0 });

  const currentCard = cards[currentCardIndex];

  useEffect(() => {
    const updateViewportSize = () => {
      setViewportSize({ width: window.innerWidth, height: window.innerHeight });
    };

    updateViewportSize();
    window.addEventListener("resize", updateViewportSize);
    return () => window.removeEventListener("resize", updateViewportSize);
  }, []);

  useEffect(() => {
    if (phase !== BoosterOpeningPhase.REVEAL_SINGLE || !currentCard) {
      setShowConfetti(false);
      setConfettiPieces(0);
      return;
    }

    const pieces = CONFETTI_PIECES_BY_RARITY[currentCard.rarity] ?? 0;

    if (pieces === 0) {
      setShowConfetti(false);
      setConfettiPieces(0);
      return;
    }

    setConfettiPieces(pieces);
    setShowConfetti(true);
    const timer = window.setTimeout(() => {
      setShowConfetti(false);
      setConfettiPieces(0);
    }, CONFETTI_BURST_DURATION_MS);

    return () => window.clearTimeout(timer);
  }, [currentCard, phase]);

  const isSingleRevealVisible = phase === BoosterOpeningPhase.REVEAL_SINGLE;
  const isAllCardsVisible =
    phase === BoosterOpeningPhase.REVEAL_ALL ||
    phase === BoosterOpeningPhase.CONFIRM_EXIT;

  const cardsContainerClassName = useMemo(() => {
    if (phase === BoosterOpeningPhase.CONFIRM_EXIT) {
      return "animate-cards-results-exit";
    }

    if (phase === BoosterOpeningPhase.REVEAL_ALL) {
      return "animate-cards-results-enter";
    }

    return "opacity-100";
  }, [phase]);

  const handleNext = () => {
    if (!currentCard || isCardLeaving) {
      return;
    }

    setIsCardLeaving(true);
    window.setTimeout(() => {
      setIsCardLeaving(false);
      onNextCard();
    }, CARD_REVEAL_TRANSITION_MS);
  };

  const handleOutsideClick = () => {
    if (phase === BoosterOpeningPhase.REVEAL_SINGLE) {
      handleNext();
      return;
    }

    if (phase === BoosterOpeningPhase.REVEAL_ALL) {
      onConfirmAll();
    }
  };

  if (!isSingleRevealVisible && !isAllCardsVisible) {
    return null;
  }

  return (
    <div
      className="fixed inset-0 z-70 flex flex-col items-center justify-center px-4 pointer-events-auto"
      onClick={handleOutsideClick}
    >
      {showConfetti ? (
        <Confetti
          width={viewportSize.width}
          height={viewportSize.height}
          recycle={true}
          run={showConfetti}
          gravity={0.12}
          numberOfPieces={confettiPieces}
          style={{ zIndex: 0, pointerEvents: "none" }}
          confettiSource={{
            x: viewportSize.width / 2 - CONFETTI_SOURCE_WIDTH / 2,
            y: viewportSize.height / 2 - CONFETTI_SOURCE_HEIGHT / 2,
            w: CONFETTI_SOURCE_WIDTH,
            h: CONFETTI_SOURCE_HEIGHT,
          }}
        />
      ) : null}

      {isSingleRevealVisible && currentCard ? (
        <div
          className="relative z-10 flex flex-col items-center gap-4"
          onClick={(event) => event.stopPropagation()}
        >
          <div
            className={
              isCardLeaving
                ? "animate-card-reveal-out"
                : "animate-card-reveal-in"
            }
          >
            <InteractiveCard
              card={currentCard}
              size={isSmallScreen ? CardSize.XL : CardSize.XLL}
            />
          </div>

          <Button onClick={handleNext} size="lg">
            Suivant
          </Button>
        </div>
      ) : null}

      {isAllCardsVisible ? (
        <div
          onClick={(event) => event.stopPropagation()}
          className={`relative z-10 w-full max-w-6xl flex flex-col items-center gap-6 transition-opacity duration-500 ${cardsContainerClassName}`}
        >
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 md:gap-5 lg:gap-6 place-items-center">
            {cards.map((card, index) => (
              <InteractiveCard
                key={card.instanceId ?? `${card.name}-${index}`}
                card={card}
                size={isSmallScreen ? CardSize.SM : CardSize.XL}
              />
            ))}
          </div>

          {phase === BoosterOpeningPhase.REVEAL_ALL ? (
            <Button onClick={onConfirmAll} size="lg">
              Confirmer
            </Button>
          ) : null}
        </div>
      ) : null}
    </div>
  );
}
