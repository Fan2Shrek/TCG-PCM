"use client";

import { CiStar } from "react-icons/ci";
import { FaStar } from "react-icons/fa";

import type { Deck } from "@/app/types/deck";
import { CardSize } from "@/constants/card";
import type { BasicCard } from "@/lib/cards/types/card";
import CardWithZoom from "@/components/organisms/card/CardWithZoom";

type InventoryDeckItemProps = {
  deck: Deck;
  isExpanded: boolean;
  onToggle: () => void;
  onToggleFavorite: (deckId: number, nextValue: boolean) => void;
  cardsById: Map<string, BasicCard>;
};

export default function InventoryDeckItem({
  deck,
  isExpanded,
  onToggle,
  onToggleFavorite,
  cardsById,
}: InventoryDeckItemProps) {
  const characterCard = cardsById.get(deck.characterCard);
  const isFavorite = Boolean(deck.isFavorite);

  return (
    <section className="overflow-hidden rounded-xl border border-slate-300/70 bg-white/80">
      <div className="flex w-full items-center justify-between px-4 py-3 hover:bg-slate-50">
        <div className="flex min-w-0 items-center gap-2">
          <h3 className="text-base font-semibold text-slate-900">
            {deck.name}
          </h3>
          <button
            type="button"
            aria-label={
              isFavorite ? "Retirer des favoris" : "Ajouter aux favoris"
            }
            title={isFavorite ? "Retirer des favoris" : "Ajouter aux favoris"}
            onClick={() => onToggleFavorite(deck.id, !isFavorite)}
            className="cursor-pointer rounded-md p-1 text-amber-500 transition hover:bg-amber-50 hover:text-amber-600"
          >
            {isFavorite ? (
              <FaStar className="h-5 w-5" />
            ) : (
              <CiStar className="h-5 w-5" />
            )}
          </button>
          {isFavorite ? (
            <span className="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
              Favori
            </span>
          ) : null}
        </div>

        <button
          type="button"
          onClick={onToggle}
          className="cursor-pointer rounded-md px-4 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
        >
          {isExpanded ? "Masquer" : "Voir"}
        </button>
      </div>

      <div
        className={`grid transition-[grid-template-rows,opacity] duration-300 ease-in-out ${
          isExpanded
            ? "grid-rows-[1fr] opacity-100"
            : "grid-rows-[0fr] opacity-0"
        }`}
        aria-hidden={!isExpanded}
      >
        <div className="overflow-hidden">
          <div className="space-y-5 border-t border-slate-200 px-4 py-4">
            <div className="text-center">
              <p className="mb-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                Carte personnage
              </p>
              {characterCard ? (
                <div className="inline-flex flex-col items-center gap-2">
                  <CardWithZoom
                    card={{
                      ...characterCard,
                      isActive: true,
                      effects: [],
                    }}
                    size={CardSize.MD}
                    zoomOnSingleClick
                  />
                  <span className="text-xs text-slate-700">
                    {characterCard.name}
                  </span>
                </div>
              ) : (
                <p className="text-sm text-slate-600">
                  Carte inconnue: {deck.characterCard}
                </p>
              )}
            </div>

            <div>
              <p className="mb-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                CARTES DU DECK
              </p>
              <div className="grid grid-cols-2 justify-items-center gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                {deck.cards.map((cardId, index) => {
                  const card = cardsById.get(cardId);
                  const key = `${cardId}-${index}`;

                  return card ? (
                    <div
                      key={key}
                      className="flex flex-col items-center gap-1 text-center"
                    >
                      <CardWithZoom
                        card={{ ...card, isActive: true, effects: [] }}
                        size={CardSize.SM}
                        zoomOnSingleClick
                      />
                      <span className="text-xs text-slate-700">
                        {card.name}
                      </span>
                    </div>
                  ) : (
                    <div
                      key={key}
                      className="rounded-md border border-slate-300 bg-slate-100 p-2 text-center text-xs text-slate-600"
                    >
                      {cardId}
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
