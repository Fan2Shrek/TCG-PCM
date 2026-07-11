"use client";

import { BoosterType } from "@/constants/booster";
import { useBoosterTokensContext } from "@/contexts/BoosterTokensContext";
import {
  BoosterOpeningPhase,
  isOpeningAnimationPhase,
  isRevealPhase,
} from "@/lib/boosterOpening/phases";
import {
  CONFIRM_EXIT_MS,
  OPENING_DROP_EMPTY_BOOSTER_MS,
  OPENING_DROP_TOP_MS,
  OPENING_SHOOT_BACK_CARDS_MS,
} from "@/lib/boosterOpening/timings";
import api from "@/lib/api/api";
import { BasicCard } from "@/lib/cards/types/card";
import { useCallback, useEffect, useRef, useState } from "react";
import { toast } from "sonner";

type OpenBoosterResponse = {
  cards: BasicCard[];
};

export function useBoosterOpeningFlow() {
  const { tokens, consumeOne } = useBoosterTokensContext();
  const [phase, setPhase] = useState<BoosterOpeningPhase>(
    BoosterOpeningPhase.IDLE,
  );
  const [obtainedCards, setObtainedCards] = useState<BasicCard[]>([]);
  const [currentCardIndex, setCurrentCardIndex] = useState(0);
  const [isOpeningRequestInFlight, setIsOpeningRequestInFlight] =
    useState(false);
  const timerRef = useRef<number | null>(null);

  const clearTimer = useCallback(() => {
    if (timerRef.current !== null) {
      window.clearTimeout(timerRef.current);
      timerRef.current = null;
    }
  }, []);

  const reset = useCallback(() => {
    clearTimer();
    setObtainedCards([]);
    setCurrentCardIndex(0);
    setIsOpeningRequestInFlight(false);
    setPhase(BoosterOpeningPhase.IDLE);
  }, [clearTimer]);

  useEffect(() => {
    clearTimer();

    if (phase === BoosterOpeningPhase.OPENING_DROP_TOP) {
      timerRef.current = window.setTimeout(() => {
        setPhase(BoosterOpeningPhase.OPENING_SHOOT_BACK_CARDS);
      }, OPENING_DROP_TOP_MS);
    }

    if (phase === BoosterOpeningPhase.OPENING_SHOOT_BACK_CARDS) {
      timerRef.current = window.setTimeout(() => {
        setPhase(BoosterOpeningPhase.OPENING_DROP_EMPTY_BOOSTER);
      }, OPENING_SHOOT_BACK_CARDS_MS);
    }

    if (phase === BoosterOpeningPhase.OPENING_DROP_EMPTY_BOOSTER) {
      timerRef.current = window.setTimeout(() => {
        setPhase(BoosterOpeningPhase.REVEAL_SINGLE);
      }, OPENING_DROP_EMPTY_BOOSTER_MS);
    }

    if (phase === BoosterOpeningPhase.CONFIRM_EXIT) {
      timerRef.current = window.setTimeout(() => {
        reset();
      }, CONFIRM_EXIT_MS);
    }

    return clearTimer;
  }, [clearTimer, phase, reset]);

  const openPreview = useCallback(() => {
    if (phase !== BoosterOpeningPhase.IDLE) {
      return;
    }

    setPhase(BoosterOpeningPhase.PREVIEW);
  }, [phase]);

  const closePreview = useCallback(() => {
    if (phase !== BoosterOpeningPhase.PREVIEW || isOpeningRequestInFlight) {
      return;
    }

    setPhase(BoosterOpeningPhase.IDLE);
  }, [isOpeningRequestInFlight, phase]);

  const confirmOpen = useCallback(
    async (boosterType: BoosterType) => {
      if (phase !== BoosterOpeningPhase.PREVIEW || isOpeningRequestInFlight) {
        return;
      }

      if (tokens <= 0) {
        toast.error("Vous n'avez plus de jetons de booster");
        return;
      }

      setIsOpeningRequestInFlight(true);

      try {
        const response = (await api.booster.open(
          boosterType,
        )) as OpenBoosterResponse;

        if (!Array.isArray(response.cards) || response.cards.length === 0) {
          throw new Error("Aucune carte recue");
        }

        const openedCards = response.cards.map((card, index) => ({
          ...card,
          isActive: true,
          instanceId: card.instanceId ?? `${card.name}-${Date.now()}-${index}`,
          effects: card.effects ?? [],
        }));

        consumeOne();
        setObtainedCards(openedCards);
        setCurrentCardIndex(0);
        setPhase(BoosterOpeningPhase.OPENING_DROP_TOP);
      } catch (error) {
        const message =
          error instanceof Error
            ? error.message
            : "Impossible d'ouvrir le booster";
        toast.error("Ouverture du booster impossible", {
          description: message,
        });
      } finally {
        setIsOpeningRequestInFlight(false);
      }
    },
    [consumeOne, isOpeningRequestInFlight, phase, tokens],
  );

  const nextRevealedCard = useCallback(() => {
    if (phase !== BoosterOpeningPhase.REVEAL_SINGLE) {
      return;
    }

    setCurrentCardIndex((previous) => {
      if (previous >= obtainedCards.length - 1) {
        setPhase(BoosterOpeningPhase.REVEAL_ALL);
        return previous;
      }

      return previous + 1;
    });
  }, [obtainedCards.length, phase]);

  const confirmAllCards = useCallback(() => {
    if (phase !== BoosterOpeningPhase.REVEAL_ALL) {
      return;
    }

    setPhase(BoosterOpeningPhase.CONFIRM_EXIT);
  }, [phase]);

  const isPreviewOpen = phase === BoosterOpeningPhase.PREVIEW;
  const isFlowActive = phase !== BoosterOpeningPhase.IDLE;

  return {
    phase,
    obtainedCards,
    currentCardIndex,
    isOpeningRequestInFlight,
    isPreviewOpen,
    isFlowActive,
    isOpeningAnimationRunning: isOpeningAnimationPhase(phase),
    isRevealRunning: isRevealPhase(phase),
    openPreview,
    closePreview,
    confirmOpen,
    nextRevealedCard,
    confirmAllCards,
    reset,
  };
}
