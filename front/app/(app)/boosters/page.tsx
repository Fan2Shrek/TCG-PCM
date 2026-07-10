"use client";

import { Booster, InventorySetStat } from "@/app/types/booster";
import LoadingSpinner from "@/components/atoms/LoadingSpinner";
import SelectableBooster from "@/components/molecules/boosters/SelectableBooster";
import BoosterTitleOverlay from "@/components/molecules/boosters/BoosterTitleOverlay";
import Tooltip, { TooltipPosition } from "@/components/molecules/game/tooltip";
import { BoosterType } from "@/constants/booster";
import { useBoosterCarousel } from "@/hooks/useBoosterCarousel";
import { useWindowWidth } from "@/hooks/useWindowWidth";
import api from "@/lib/api/api";
import { useEffect, useMemo, useState } from "react";
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

export default function BoostersPage() {
  const { frontBooster, rotateTo, getBoosterStyle } =
    useBoosterCarousel(BOOSTERS);
  const screenWidth = useWindowWidth();
  const isSmallScreen = screenWidth < 768;

  const [statsBySet, setStatsBySet] = useState<
    Record<string, InventorySetStat>
  >({});
  const [isLoadingStats, setIsLoadingStats] = useState(true);
  const [isPreviewOpen, setIsPreviewOpen] = useState(false);

  useEffect(() => {
    const loadInventorySetStats = async () => {
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
    };

    loadInventorySetStats();
  }, []);

  const currentSetStats = useMemo(() => {
    return statsBySet[frontBooster.id];
  }, [frontBooster, statsBySet]);

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
      ? "text-slate-400"
      : completionRatio >= 0.5
        ? "text-amber-700"
        : "text-slate-900";

  const closePreview = () => {
    setIsPreviewOpen(false);
  };

  return (
    <div className="relative flex-1 flex flex-col items-center justify-center overflow-hidden">
      <div
        className={`fixed inset-0 h-screen w-screen bg-black/70 transition-opacity duration-500 z-20 ${isPreviewOpen ? "opacity-100" : "opacity-0 pointer-events-none"}`}
        onClick={closePreview}
      />

      <div
        className="relative w-full h-120 z-30"
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
                isVisible={booster.id === frontBooster.id && !isPreviewOpen}
              />

              <SelectableBooster
                booster={booster}
                index={index}
                frontBoosterId={frontBooster.id}
                isPreviewOpen={isPreviewOpen}
                isSmallScreen={isSmallScreen}
                onRotateTo={rotateTo}
                onPreviewChange={setIsPreviewOpen}
              />
            </div>
          );
        })}
      </div>

      <div
        className={`flex flex-col gap-1 items-center z-10 transition-opacity duration-300 ${
          isPreviewOpen ? "opacity-0 pointer-events-none" : "opacity-100"
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
