"use client";

import { useEffect, useMemo, useState } from "react";
import { toast } from "sonner";

import type { CardCollectionEntry } from "@/app/types/collection";
import type { Deck, DeckDraft, DeckLimits } from "@/app/types/deck";
import type { BasicCard } from "@/lib/cards/types/card";
import InventoryDeckItem from "@/components/organisms/inventory/InventoryDeckItem";
import InventoryDeckBuilder from "@/components/organisms/inventory/InventoryDeckBuilder";
import { Button } from "@/components/ui/button";
import client from "@/lib/api/api";

type InventoryDecksPanelProps = {
  decks: Deck[];
  entries: CardCollectionEntry[];
  limits: DeckLimits;
};

export default function InventoryDecksPanel({
  decks,
  entries,
  limits,
}: InventoryDecksPanelProps) {
  const [expandedDeckId, setExpandedDeckId] = useState<number | null>(null);
  const [localDecks, setLocalDecks] = useState<Deck[]>(decks);
  const [isCreating, setIsCreating] = useState(false);
  const [isCreatingDeck, setIsCreatingDeck] = useState(false);

  useEffect(() => {
    setLocalDecks(decks);
  }, [decks]);

  const cardsById = useMemo(() => {
    const map = new Map<string, BasicCard>();

    for (const { card } of entries) {
      map.set(card.instanceId, card);
    }

    return map;
  }, [entries]);

  const sortedDecks = useMemo(
    () =>
      [...localDecks].sort((a, b) => {
        const favoriteDelta =
          Number(Boolean(b.isFavorite)) - Number(Boolean(a.isFavorite));

        if (favoriteDelta !== 0) {
          return favoriteDelta;
        }

        return a.name.localeCompare(b.name, "fr", { sensitivity: "base" });
      }),
    [localDecks],
  );

  const handleToggleFavorite = async (deckId: number, nextValue: boolean) => {
    setLocalDecks((prev) =>
      prev.map((deck) =>
        deck.id === deckId ? { ...deck, isFavorite: nextValue } : deck,
      ),
    );

    try {
      await client.deck.setFavorite(deckId, nextValue);
      toast.success(
        nextValue
          ? "Le deck a été ajouté aux favoris."
          : "Le deck a été retiré des favoris.",
      );
    } catch (error) {
      setLocalDecks((prev) =>
        prev.map((deck) =>
          deck.id === deckId ? { ...deck, isFavorite: !nextValue } : deck,
        ),
      );

      const message =
        error instanceof Error
          ? error.message
          : "Impossible de modifier le favori.";
      toast.error("Erreur", { description: message });
    }
  };

  const handleCreateDeck = async (draft: DeckDraft) => {
    setIsCreatingDeck(true);

    try {
      const createdDeck = await client.deck.create(draft);
      setLocalDecks((prev) => [createdDeck, ...prev]);
      setExpandedDeckId(createdDeck.id);
      setIsCreating(false);
      toast.success("Le deck a ete cree.");
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Impossible de creer le deck.";
      toast.error("Erreur", { description: message });
    } finally {
      setIsCreatingDeck(false);
    }
  };

  return (
    <div className="space-y-4 min-h-[70vh]">
      {!isCreating ? (
        <div className="flex justify-end">
          <Button type="button" onClick={() => setIsCreating(true)}>
            Nouveau Deck
          </Button>
        </div>
      ) : null}

      {isCreating ? (
        <InventoryDeckBuilder
          entries={entries}
          limits={limits}
          submitLabel="Créer le deck"
          submitting={isCreatingDeck}
          onCancel={() => setIsCreating(false)}
          onSubmit={handleCreateDeck}
        />
      ) : null}

      {sortedDecks.length === 0 ? (
        <div className="rounded-xl border border-slate-300/60 bg-white/50 p-8 text-center text-slate-600">
          Aucun deck pour le moment.
        </div>
      ) : null}

      {sortedDecks.map((deck) => {
        const isExpanded = deck.id === expandedDeckId;

        return (
          <InventoryDeckItem
            key={deck.id}
            deck={deck}
            isExpanded={isExpanded}
            onToggle={() => setExpandedDeckId(isExpanded ? null : deck.id)}
            onToggleFavorite={handleToggleFavorite}
            cardsById={cardsById}
          />
        );
      })}
    </div>
  );
}
