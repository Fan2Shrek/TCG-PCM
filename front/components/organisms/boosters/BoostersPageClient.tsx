"use client";

import { Booster, InventorySetStat } from "@/app/types/booster";
import LoadingSpinner from "@/components/atoms/LoadingSpinner";
import SelectableBooster from "@/components/molecules/boosters/SelectableBooster";
import BoosterTitleOverlay from "@/components/molecules/boosters/BoosterTitleOverlay";
import CardRevealFlow from "@/components/organisms/boosters/CardRevealFlow";
import Tooltip, { TooltipPosition } from "@/components/molecules/game/tooltip";
import { BoosterType } from "@/constants/booster";
import { useBoosterOpeningFlow } from "@/lib/boosterOpening/hooks/useBoosterOpeningFlow";
import { useBoosterCarousel } from "@/hooks/useBoosterCarousel";
import { useWindowWidth } from "@/hooks/useWindowWidth";
import api from "@/lib/api/api";
import { BasicCard } from "@/lib/cards/types/card";
import { BoosterOpeningPhase } from "@/lib/boosterOpening/phases";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { FaTrophy } from "react-icons/fa";

const BOOSTERS: Booster[] = [
  {
    id: "BTD6",
    boosterType: BoosterType.BTD,
  },
  {
    id: "ORIGINAL",
    boosterType: BoosterType.ORIGINAL,
  },
  {
    id: "TBOI",
    boosterType: BoosterType.ISAAC,
  },
];

const TITLE_IMAGE_BY_TYPE: Record<BoosterType, string> = {
  [BoosterType.BTD]: "/booster/btd_title.webp",
  [BoosterType.ORIGINAL]: "/booster/original_title.webp",
  [BoosterType.ISAAC]: "/booster/isaac_title.webp",
};

const TITLE_TEXT_BY_TYPE: Record<BoosterType, string> = {
  [BoosterType.BTD]: "Bloon Tower Defense",
  [BoosterType.ORIGINAL]: "Original",
  [BoosterType.ISAAC]: "The Binding of Isaac",
};

type BoostersPageClientProps = {
  initialStatsBySet: Record<string, InventorySetStat>;
};

export default function BoostersPageClient({
  initialStatsBySet,
}: BoostersPageClientProps) {
  const { frontBooster, rotateTo, getBoosterStyle } =
    useBoosterCarousel(BOOSTERS);
  const screenWidth = useWindowWidth();
  const isSmallScreen = screenWidth < 768;

  const [statsBySet, setStatsBySet] =
    useState<Record<string, InventorySetStat>>(initialStatsBySet);
  const [boosterCardsByType, setBoosterCardsByType] = useState<
    Partial<Record<BoosterType, BasicCard[]>>
  >({});
  const [isLoadingStats, setIsLoadingStats] = useState(false);
  const previousFlowActiveRef = useRef(false);
  const {
    phase,
    obtainedCards,
    currentCardIndex,
    isFlowActive,
    isPreviewOpen,
    isRevealRunning,
    openPreview,
    closePreview,
    confirmOpen,
    nextRevealedCard,
    confirmAllCards,
  } = useBoosterOpeningFlow();

  const loadInventorySetStats = useCallback(async () => {
    setIsLoadingStats(true);

    try {
      const response =
        (await api.user.getInventorySetStats()) as InventorySetStat[];

      const recStatsBySet = response.reduce<Record<string, InventorySetStat>>(
        (acc, setStat) => {
          acc[setStat.set] = setStat;
          return acc;
        },
        {},
      );

      setStatsBySet(recStatsBySet);
    } catch (error) {
      console.error(error);
    } finally {
      setIsLoadingStats(false);
    }
  }, []);

  const loadBoosterCardsForType = useCallback(
    async (boosterType: BoosterType) => {
      if (boosterCardsByType[boosterType] !== undefined) {
        return;
      }

      try {
        const response = (await api.booster.getObtainableCards(
          boosterType,
        )) as { cards: BasicCard[] };

        setBoosterCardsByType((previous) => ({
          ...previous,
          [boosterType]: response.cards,
        }));
      } catch (error) {
        console.error(error);
      }
    },
    [boosterCardsByType],
  );

  useEffect(() => {
    if (!isPreviewOpen) {
      return;
    }

    // eslint-disable-next-line react-hooks/set-state-in-effect -- loadBoosterCardsForType awaits a network call before setting state, it isn't synchronous
    void loadBoosterCardsForType(frontBooster.boosterType);
  }, [frontBooster.boosterType, isPreviewOpen, loadBoosterCardsForType]);

  useEffect(() => {
    const wasFlowActive = previousFlowActiveRef.current;

    if (wasFlowActive && !isFlowActive) {
      void loadInventorySetStats();
    }

    previousFlowActiveRef.current = isFlowActive;
  }, [isFlowActive, loadInventorySetStats]);

  const currentSetStats = useMemo(() => {
    return statsBySet[frontBooster.id];
  }, [frontBooster, statsBySet]);

  const previewCards = boosterCardsByType[frontBooster.boosterType] ?? [];
  const previewTitle = TITLE_TEXT_BY_TYPE[frontBooster.boosterType];
  const isPreviewLoading =
    isPreviewOpen && boosterCardsByType[frontBooster.boosterType] === undefined;

  const completionRatio =
    currentSetStats && currentSetStats.totalCards > 0
      ? currentSetStats.ownedCards / currentSetStats.totalCards
      : 0;

  const hasAllCards =
    !!currentSetStats &&
    currentSetStats.totalCards > 0 &&
    currentSetStats.ownedCards >= currentSetStats.totalCards;

  const progressColorClass = hasAllCards
    ? "text-yellow-500"
    : completionRatio >= 0.75
      ? "text-slate-500"
      : completionRatio >= 0.5
        ? "text-amber-700"
        : "text-slate-900";

  const canInteractWithCarousel =
    phase === BoosterOpeningPhase.IDLE || phase === BoosterOpeningPhase.PREVIEW;

  return (
    <div className="relative flex-1 flex flex-col items-center justify-center overflow-hidden">
      <div
        className={`fixed inset-0 h-screen w-screen bg-black/70 transition-opacity duration-500 z-20 ${isFlowActive ? "opacity-100" : "opacity-0 pointer-events-none"}`}
        onClick={isPreviewOpen ? closePreview : undefined}
      />

      <div
        className={`relative mt-22 md:mt-30 w-full h-120 z-30 ${
          canInteractWithCarousel ? "" : "pointer-events-none"
        }`}
        style={{ perspective: 1800 }}
        onClick={isPreviewOpen ? closePreview : undefined}
      >
        {BOOSTERS.map((booster, index) => {
          const boosterStyle = getBoosterStyle(index);

          return (
            <div
              key={booster.id}
              className="absolute transition-all duration-700 ease-out left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2"
              style={{
                ...boosterStyle,
                transformOrigin: "center",
              }}
              onClick={(event) => event.stopPropagation()}
            >
              <BoosterTitleOverlay
                image={TITLE_IMAGE_BY_TYPE[booster.boosterType]}
                alt={`${booster.id} title`}
                isVisible={
                  booster.id === frontBooster.id &&
                  phase === BoosterOpeningPhase.IDLE &&
                  !isPreviewOpen
                }
              />

              <SelectableBooster
                booster={booster}
                index={index}
                frontBoosterId={frontBooster.id}
                isPreviewOpen={isPreviewOpen}
                openingPhase={phase}
                shotCardCount={obtainedCards.length}
                isSmallScreen={isSmallScreen}
                previewCards={previewCards}
                previewTitle={previewTitle}
                ownedCards={currentSetStats?.ownedCards ?? 0}
                totalCards={currentSetStats?.totalCards ?? 0}
                isPreviewLoading={isPreviewLoading}
                onRotateTo={rotateTo}
                onPreviewChange={(open) => {
                  if (open) {
                    openPreview();
                    return;
                  }

                  closePreview();
                }}
                onConfirmOpen={() => {
                  void confirmOpen(frontBooster.boosterType);
                }}
              />
            </div>
          );
        })}
      </div>

      {isRevealRunning ? (
        <CardRevealFlow
          phase={phase}
          cards={obtainedCards}
          currentCardIndex={currentCardIndex}
          onNextCard={nextRevealedCard}
          onConfirmAll={confirmAllCards}
        />
      ) : null}

      <div
        className={`flex flex-col gap-1 items-center z-10 transition-opacity duration-300 mt-12 ${
          isFlowActive ? "opacity-0 pointer-events-none" : "opacity-100"
        }`}
      >
        <p
          className={`text-2xl font-semibold flex items-center justify-center gap-2 ${progressColorClass}`}
        >
          {isLoadingStats ? (
            <LoadingSpinner />
          ) : (
            <>
              {currentSetStats.ownedCards}/{currentSetStats.totalCards}
              {hasAllCards ? <FaTrophy aria-label="set completed" /> : null}
            </>
          )}
        </p>
        <Tooltip
          position={TooltipPosition.BOTTOM_CENTER}
          text="Nombre de cartes du set que vous possédez sur le nombre total de cartes disponibles dans le set. Appuyez sur le booster pour l'ouvrir."
        />
      </div>
    </div>
  );
}
