"use client";

import type { Deck } from "@/app/types/deck";

type DeckSelectProps = {
  decks: Deck[];
  value: string;
  onChange: (deckId: string) => void;
  isLoading?: boolean;
  disabled?: boolean;
};

export default function DeckSelect({
  decks,
  value,
  onChange,
  isLoading = false,
  disabled = false,
}: DeckSelectProps) {
  return (
    <select
      value={value}
      onChange={(event) => onChange(event.target.value)}
      disabled={disabled || isLoading || decks.length === 0}
      className="h-8 w-full max-w-48 rounded-md border border-slate-300 bg-white px-2 text-xs text-slate-900 outline-none focus:ring-2 focus:ring-slate-400 disabled:cursor-not-allowed disabled:opacity-60"
      aria-label="Choisir un deck"
    >
      {decks.length === 0 ? (
        <option value="">Aucun deck disponible</option>
      ) : (
        decks.map((deck) => (
          <option key={deck.id} value={String(deck.id)}>
            {deck.isFavorite ? "★ " : ""}
            {deck.name}
          </option>
        ))
      )}
    </select>
  );
}
