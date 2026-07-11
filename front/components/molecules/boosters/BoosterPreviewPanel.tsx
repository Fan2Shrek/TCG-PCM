"use client";

import { BasicCard } from "@/lib/cards/types/card";
import { Button } from "@/components/ui/button";
import BoosterPreviewCarousel from "./BoosterPreviewCarousel";

type BoosterPreviewPanelProps = {
  isVisible: boolean;
  isSmallScreen: boolean;
  title: string;
  cards: BasicCard[];
  ownedCards: number;
  totalCards: number;
  isLoading: boolean;
  shouldRenderCards?: boolean;
  onBack: () => void;
};

export default function BoosterPreviewPanel({
  isVisible,
  isSmallScreen,
  title,
  cards,
  ownedCards,
  totalCards,
  isLoading,
  shouldRenderCards = true,
  onBack,
}: BoosterPreviewPanelProps) {
  return (
    <div
      className={`absolute transition-opacity duration-700 ease-in-out
        ${
          isSmallScreen
            ? "left-1/2 -translate-x-1/2 top-[calc(50%+190px)] w-[min(90vw,24rem)]"
            : "left-24 top-1/2 -translate-y-1/2 w-[min(40vw,26rem)]"
        } ${isVisible ? "opacity-100" : "opacity-0 pointer-events-none"}`}
      onClick={(event) => event.stopPropagation()}
    >
      <div
        className={`border-2 border-white bg-primary text-white rounded-xl p-4 transition-transform duration-700 ease-in-out flex flex-col gap-4 overflow-hidden ${
          isVisible ? "translate-x-0" : "translate-x-[120vw]"
        }`}
      >
        <div className="flex flex-col gap-1 text-center">
          <h3 className="text-xl font-bold uppercase tracking-wide">{title}</h3>
          <p className="text-sm opacity-80">
            {ownedCards}/{totalCards} cartes possédées
          </p>
        </div>

        <BoosterPreviewCarousel
          cards={cards}
          isVisible={isVisible}
          isLoading={isLoading}
          shouldRenderCards={shouldRenderCards}
        />

        <Button
          type="button"
          variant="secondary"
          size="sm"
          className="self-end"
          onClick={onBack}
        >
          Retour
        </Button>
      </div>
    </div>
  );
}
