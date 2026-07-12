"use client";

import { useMemo, useState } from "react";

import type { CardCollectionEntry } from "@/app/types/collection";
import type { Deck } from "@/app/types/deck";
import { CardSize } from "@/constants/card";
import type { BasicCard } from "@/lib/cards/types/card";
import CardWithZoom from "@/components/organisms/card/CardWithZoom";

type InventoryDecksPanelProps = {
  decks: Deck[];
  entries: CardCollectionEntry[];
};

export default function InventoryDecksPanel({
  decks,
  entries,
}: InventoryDecksPanelProps) {
  const [expandedDeckId, setExpandedDeckId] = useState<number | null>(null);

  const cardsById = useMemo(() => {
    const map = new Map<string, BasicCard>();

    for (const { card } of entries) {
      map.set(card.instanceId, card);
    }

    return map;
  }, [entries]);

  const sortedDecks = useMemo(
    () =>
      [...decks].sort((a, b) => {
        const favoriteDelta =
          Number(Boolean(b.isFavorite)) - Number(Boolean(a.isFavorite));

        if (favoriteDelta !== 0) {
          return favoriteDelta;
        }

        return a.name.localeCompare(b.name, "fr", { sensitivity: "base" });
      }),
    [decks],
  );

  if (sortedDecks.length === 0) {
    return (
      <div className="rounded-xl border border-slate-300/60 bg-white/50 p-8 text-center text-slate-600">
        Aucun deck pour le moment.
      </div>
    );
  }

  return (
    <div className="space-y-3 min-h-[70vh]">
      {sortedDecks.map((deck) => {
        const isExpanded = deck.id === expandedDeckId;
        const characterCard = cardsById.get(deck.characterCard);

        return (
          <section
            key={deck.id}
            className="overflow-hidden rounded-xl border border-slate-300/70 bg-white/80"
          >
            <button
              type="button"
              onClick={() => setExpandedDeckId(isExpanded ? null : deck.id)}
              className="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-slate-50"
            >
              <div className="flex items-center gap-3">
                <h3 className="text-base font-semibold text-slate-900">
                  {deck.name}
                </h3>
                {deck.isFavorite ? (
                  <span className="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
                    Favori
                  </span>
                ) : null}
              </div>
              <span className="text-xs font-medium text-slate-500">
                {isExpanded ? "Masquer" : "Voir"}
              </span>
            </button>

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
                    <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 text-center">
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
                    <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 text-center">
                      CARTES DU DECK
                    </p>
                    <div className="grid justify-items-center grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
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
                            className="rounded-md border border-slate-300 bg-slate-100 p-2 text-xs text-slate-600 text-center"
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
      })}
    </div>
  );
}
