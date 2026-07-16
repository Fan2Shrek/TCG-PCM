"use client";

import { useEffect, useState } from "react";
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
  onEdit: (deckId: number) => void;
  onDelete: (deckId: number) => void;
  onToggleFavorite: (deckId: number, nextValue: boolean) => void;
  isDeleting?: boolean;
  cardsById: Map<string, BasicCard>;
};

export default function InventoryDeckItem({
  deck,
  isExpanded,
  onToggle,
  onEdit,
  onDelete,
  onToggleFavorite,
  isDeleting = false,
  cardsById,
}: InventoryDeckItemProps) {
  const [isDeleteConfirming, setIsDeleteConfirming] = useState(false);
  const characterCard = cardsById.get(deck.characterCard);
  const isFavorite = Boolean(deck.isFavorite);

  useEffect(() => {
    if (!isDeleteConfirming) {
      return;
    }

    const timeout = window.setTimeout(() => {
      setIsDeleteConfirming(false);
    }, 2500);

    return () => window.clearTimeout(timeout);
  }, [isDeleteConfirming]);

  const handleDeleteClick = () => {
    if (isDeleting) {
      return;
    }

    if (!isDeleteConfirming) {
      setIsDeleteConfirming(true);
      return;
    }

    onDelete(deck.id);
    setIsDeleteConfirming(false);
  };

  return (
    <section className="overflow-hidden rounded-xl border border-slate-300/70 bg-white/80">
      <div className="flex flex-wrap w-full items-center justify-between px-4 py-3 hover:bg-slate-50">
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

        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={() => onEdit(deck.id)}
            className="cursor-pointer rounded-md border border-slate-300 bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-200"
          >
            Modifier
          </button>
          <button
            type="button"
            onClick={handleDeleteClick}
            disabled={isDeleting}
            className={`cursor-pointer rounded-md border px-3 py-1.5 text-sm font-semibold transition ${
              isDeleteConfirming
                ? "border-red-700 bg-red-600 text-white hover:bg-red-700"
                : "border-red-300 bg-red-100 text-red-700 hover:bg-red-200"
            } disabled:cursor-not-allowed disabled:opacity-60`}
          >
            {isDeleting
              ? "Suppression..."
              : isDeleteConfirming
                ? "Confirmer"
                : "Supprimer"}
          </button>
          <button
            type="button"
            onClick={onToggle}
            className="cursor-pointer rounded-md px-4 py-1.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
          >
            {isExpanded ? "Masquer" : "Voir"}
          </button>
        </div>
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
          {isExpanded ? (
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
          ) : null}
        </div>
      </div>
    </section>
  );
}
