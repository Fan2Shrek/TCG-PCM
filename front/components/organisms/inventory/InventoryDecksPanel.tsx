"use client";

import { useMemo, useState } from "react";
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
  const [prevDecks, setPrevDecks] = useState(decks);
  const [isCreating, setIsCreating] = useState(false);
  const [editingDeckId, setEditingDeckId] = useState<number | null>(null);
  const [isCreatingDeck, setIsCreatingDeck] = useState(false);
  const [isSavingDeck, setIsSavingDeck] = useState(false);
  const [deletingDeckId, setDeletingDeckId] = useState<number | null>(null);

  // Resyncs local editable copy whenever the server-provided decks prop changes,
  // without an extra effect-driven render (see "Adjusting state in render" in the React docs).
  if (decks !== prevDecks) {
    setPrevDecks(decks);
    setLocalDecks(decks);
  }

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

  const editingDeck = useMemo(
    () => localDecks.find((deck) => deck.id === editingDeckId) ?? null,
    [editingDeckId, localDecks],
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
      toast.success("Deck créé.");
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Impossible de creer le deck.";
      toast.error("Erreur", { description: message });
    } finally {
      setIsCreatingDeck(false);
    }
  };

  const handleEditDeck = (deckId: number) => {
    setIsCreating(false);
    setEditingDeckId(deckId);
  };

  const handleSaveEditedDeck = async (draft: DeckDraft) => {
    if (!editingDeck) {
      return;
    }

    setIsSavingDeck(true);

    try {
      const updatedDeck = await client.deck.update(editingDeck.id, draft);
      setLocalDecks((prev) =>
        prev.map((deck) => (deck.id === updatedDeck.id ? updatedDeck : deck)),
      );
      setEditingDeckId(null);
      toast.success("Le deck a été modifié.");
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Impossible de modifier le deck.";
      toast.error("Erreur", { description: message });
    } finally {
      setIsSavingDeck(false);
    }
  };

  const handleDeleteDeck = async (deckId: number) => {
    if (localDecks.length <= 1) {
      toast.error("Suppression impossible", {
        description: "Vous devez conserver au moins un deck.",
      });
      return;
    }

    setDeletingDeckId(deckId);

    try {
      await client.deck.delete(deckId);
      setLocalDecks((prev) => prev.filter((deck) => deck.id !== deckId));
      if (expandedDeckId === deckId) {
        setExpandedDeckId(null);
      }
      if (editingDeckId === deckId) {
        setEditingDeckId(null);
      }
      toast.success("Le deck a été supprimé.");
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : "Impossible de supprimer le deck.";
      toast.error("Erreur", { description: message });
    } finally {
      setDeletingDeckId(null);
    }
  };

  const isBuilderOpen = isCreating || null !== editingDeck;

  return (
    <div className="space-y-4 min-h-[70vh]">
      {!isBuilderOpen ? (
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

      {editingDeck ? (
        <InventoryDeckBuilder
          entries={entries}
          limits={limits}
          submitLabel="Enregistrer les modifications"
          initialDraft={{
            name: editingDeck.name,
            characterCard: editingDeck.characterCard,
            cards: editingDeck.cards,
            isFavorite: Boolean(editingDeck.isFavorite),
          }}
          submitting={isSavingDeck}
          onCancel={() => setEditingDeckId(null)}
          onSubmit={handleSaveEditedDeck}
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
            onEdit={handleEditDeck}
            onDelete={handleDeleteDeck}
            onToggleFavorite={handleToggleFavorite}
            isDeleting={deletingDeckId === deck.id}
            cardsById={cardsById}
          />
        );
      })}
    </div>
  );
}
