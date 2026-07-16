"use client";

import { useMemo, useState } from "react";
import { toast } from "sonner";

import type { CardCollectionEntry } from "@/app/types/collection";
import type { DeckDraft, DeckLimits } from "@/app/types/deck";
import { CardRaririty, CardSize, CardType } from "@/constants/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import CardWithZoom from "@/components/organisms/card/CardWithZoom";

type InventoryDeckBuilderProps = {
  entries: CardCollectionEntry[];
  limits: DeckLimits;
  submitLabel?: string;
  initialDraft?: DeckDraft;
  submitting?: boolean;
  onCancel: () => void;
  onSubmit: (draft: DeckDraft) => Promise<void>;
};

type SelectionMap = Record<string, number>;

const RARITY_LABELS: Record<CardRaririty, string> = {
  [CardRaririty.COMMON]: "Communes",
  [CardRaririty.UNCOMMON]: "Peu communes",
  [CardRaririty.RARE]: "Rares",
  [CardRaririty.EPIC]: "Epiques",
  [CardRaririty.LEGENDARY]: "Legendaires",
};

function buildSelection(cards: string[]): SelectionMap {
  const selection: SelectionMap = {};

  for (const cardId of cards) {
    selection[cardId] = (selection[cardId] ?? 0) + 1;
  }

  return selection;
}

export default function InventoryDeckBuilder({
  entries,
  limits,
  submitLabel = "Créer le deck",
  initialDraft,
  submitting = false,
  onCancel,
  onSubmit,
}: InventoryDeckBuilderProps) {
  const ownedEntries = useMemo(
    () => entries.filter(({ quantity }) => quantity > 0),
    [entries],
  );

  const characterEntries = useMemo(
    () =>
      ownedEntries
        .filter(({ card }) => card.type === CardType.CHARACTER)
        .sort((a, b) => a.card.name.localeCompare(b.card.name, "fr")),
    [ownedEntries],
  );

  const playableEntries = useMemo(
    () =>
      ownedEntries
        .filter(({ card }) => card.type !== CardType.CHARACTER)
        .sort((a, b) => a.card.name.localeCompare(b.card.name, "fr")),
    [ownedEntries],
  );

  const entriesById = useMemo(
    () => new Map(ownedEntries.map((entry) => [entry.card.instanceId, entry])),
    [ownedEntries],
  );

  const [name, setName] = useState(initialDraft?.name ?? "");
  const [characterCard, setCharacterCard] = useState(
    initialDraft?.characterCard ?? "",
  );
  const [selectedCards, setSelectedCards] = useState<SelectionMap>(
    buildSelection(initialDraft?.cards ?? []),
  );

  const rarityCounts = useMemo(() => {
    const counts: Record<CardRaririty, number> = {
      [CardRaririty.COMMON]: 0,
      [CardRaririty.UNCOMMON]: 0,
      [CardRaririty.RARE]: 0,
      [CardRaririty.EPIC]: 0,
      [CardRaririty.LEGENDARY]: 0,
    };

    for (const [cardId, count] of Object.entries(selectedCards)) {
      if (count <= 0) {
        continue;
      }

      const rarity = entriesById.get(cardId)?.card.rarity;
      if (!rarity) {
        continue;
      }

      counts[rarity] += count;
    }

    return counts;
  }, [entriesById, selectedCards]);

  const totalCards = useMemo(
    () => Object.values(selectedCards).reduce((sum, count) => sum + count, 0),
    [selectedCards],
  );

  const rarityLimit = (rarity: CardRaririty) =>
    limits.rarityLimits[rarity] ?? limits.deckSize;

  const incrementCard = (cardId: string) => {
    const entry = entriesById.get(cardId);
    if (!entry) {
      return;
    }

    const current = selectedCards[cardId] ?? 0;
    const maximumByOwnership = Math.min(entry.quantity, limits.maxCardCopies);

    if (current >= maximumByOwnership) {
      return;
    }

    if (totalCards >= limits.deckSize) {
      return;
    }

    const rarity = entry.card.rarity;
    if (rarityCounts[rarity] >= rarityLimit(rarity)) {
      return;
    }

    setSelectedCards((prev) => ({
      ...prev,
      [cardId]: (prev[cardId] ?? 0) + 1,
    }));
  };

  const decrementCard = (cardId: string) => {
    setSelectedCards((prev) => {
      const current = prev[cardId] ?? 0;
      if (current <= 0) {
        return prev;
      }

      if (current === 1) {
        const { [cardId]: _removed, ...next } = prev;
        return next;
      }

      return {
        ...prev,
        [cardId]: current - 1,
      };
    });
  };

  const canSubmit =
    name.trim().length > 0 &&
    characterCard.length > 0 &&
    totalCards === limits.deckSize;

  const handleSubmit = async () => {
    if (!canSubmit) {
      toast.error("Deck incomplet", {
        description:
          "Choisis un personnage, un nom et exactement le nombre requis de cartes.",
      });
      return;
    }

    const cards = Object.entries(selectedCards).flatMap(([cardId, count]) =>
      Array.from({ length: count }, () => cardId),
    );

    await onSubmit({
      name: name.trim(),
      characterCard,
      cards,
      isFavorite: initialDraft?.isFavorite ?? false,
    });
  };

  return (
    <section className="space-y-5 rounded-2xl border-2 border-ink-outline bg-card p-4 shadow-[var(--sticker-shadow)]">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <Input
          value={name}
          onChange={(event) => setName(event.target.value)}
          placeholder="Nom du deck"
          className="sm:max-w-sm"
          disabled={submitting}
        />

        <div className="flex flex-wrap items-center gap-2 sm:ml-auto">
          <Button
            type="button"
            variant="outline"
            onClick={onCancel}
            disabled={submitting}
          >
            Annuler
          </Button>
          <Button
            type="button"
            onClick={handleSubmit}
            disabled={!canSubmit || submitting}
          >
            {submitting ? "Enregistrement..." : submitLabel}
          </Button>
        </div>
      </div>

      <div className="grid gap-2 rounded-xl border-2 border-ink-outline bg-muted p-3 text-sm sm:grid-cols-3 lg:grid-cols-6">
        <p className="font-display font-extrabold">
          Cartes: {totalCards} / {limits.deckSize}
        </p>
        {Object.values(CardRaririty).map((rarity) => (
          <p key={rarity}>
            {RARITY_LABELS[rarity]}: {rarityCounts[rarity]} /{" "}
            {rarityLimit(rarity)}
          </p>
        ))}
      </div>

      <div className="space-y-3">
        <h3 className="font-display text-sm font-extrabold uppercase tracking-wide text-muted-foreground">
          Personnage (1 selection)
        </h3>
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
          {characterEntries.map(({ card }) => {
            const isSelected = card.instanceId === characterCard;

            return (
              <div
                key={card.instanceId}
                className={`rounded-xl border-2 p-2 text-left transition ${
                  isSelected
                    ? "border-mint bg-mint/15"
                    : "border-ink-outline bg-card"
                }`}
              >
                <div className="mx-auto w-fit">
                  <CardWithZoom
                    card={{ ...card, isActive: true, effects: [] }}
                    size={CardSize.SM}
                    zoomOnSingleClick
                  />
                </div>
                <p className="mt-2 truncate text-xs font-medium">
                  {card.name}
                </p>

                <Button
                  type="button"
                  size="sm"
                  variant={isSelected ? "default" : "outline"}
                  className="mt-2 w-full"
                  onClick={() => setCharacterCard(card.instanceId)}
                  disabled={submitting || isSelected}
                >
                  {isSelected ? "Choisi" : "Choisir"}
                </Button>
              </div>
            );
          })}
        </div>
      </div>

      <div className="space-y-3">
        <h3 className="font-display text-sm font-extrabold uppercase tracking-wide text-muted-foreground">
          Cartes jouables
        </h3>
        <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
          {playableEntries.map(({ card, quantity }) => {
            const selected = selectedCards[card.instanceId] ?? 0;
            const maxCopies = Math.min(quantity, limits.maxCardCopies);
            const rarity = card.rarity;
            const reachedRarityLimit =
              rarityCounts[rarity] >= rarityLimit(rarity);
            const canIncrement =
              !submitting &&
              selected < maxCopies &&
              totalCards < limits.deckSize &&
              !reachedRarityLimit;

            return (
              <div
                key={card.instanceId}
                className="rounded-xl border-2 border-ink-outline bg-card p-2"
              >
                <div className="mx-auto w-fit">
                  <CardWithZoom
                    card={{ ...card, isActive: true, effects: [] }}
                    size={CardSize.SM}
                    zoomOnSingleClick
                  />
                </div>

                <p className="mt-2 truncate text-xs font-medium">
                  {card.name}
                </p>
                <p className="text-xs text-muted-foreground">
                  Deck: {selected} / {maxCopies} | Collection: {quantity}
                </p>

                <div className="mt-2 flex items-center justify-between gap-2">
                  <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    onClick={() => decrementCard(card.instanceId)}
                    disabled={submitting || selected <= 0}
                    className="flex-1"
                  >
                    -1
                  </Button>
                  <Button
                    type="button"
                    size="sm"
                    onClick={() => incrementCard(card.instanceId)}
                    disabled={!canIncrement}
                    className="flex-1"
                  >
                    +1
                  </Button>
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
