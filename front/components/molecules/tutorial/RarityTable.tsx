import { CardRaririty } from "@/constants/card";
import type { DeckLimits } from "@/app/types/deck";

type RarityRow = {
  rarity: CardRaririty;
  label: string;
  dotClass: string;
  cost: string;
};

const RARITY_ROWS: RarityRow[] = [
  {
    rarity: CardRaririty.COMMON,
    label: "Commune",
    dotClass: "bg-zinc-500",
    cost: "1 pièce",
  },
  {
    rarity: CardRaririty.UNCOMMON,
    label: "Peu commune",
    dotClass: "bg-emerald-600",
    cost: "2 pièces",
  },
  {
    rarity: CardRaririty.RARE,
    label: "Rare",
    dotClass: "bg-sky-600",
    cost: "3 pièces",
  },
  {
    rarity: CardRaririty.EPIC,
    label: "Épique",
    dotClass: "bg-rose-600",
    cost: "4 pièces",
  },
  {
    rarity: CardRaririty.LEGENDARY,
    label: "Légendaire",
    dotClass: "bg-amber-500",
    cost: "5 pièces",
  },
];

function formatDeckLimit(rarityLimit: number, deckSize: number): string {
  if (rarityLimit >= deckSize) {
    return "Illimitée";
  }

  return `${rarityLimit} max`;
}

type RarityTableProps = {
  limits: DeckLimits;
};

export default function RarityTable({ limits }: RarityTableProps) {
  const deckSize = limits.deckSize;

  return (
    <div className="overflow-x-auto">
      <table className="w-full min-w-105 border-collapse overflow-hidden rounded-xl border-2 border-ink-outline text-sm">
        <thead>
          <tr className="bg-muted text-left text-muted-foreground">
            <th className="p-3">Rareté</th>
            <th className="p-3">Coût de base</th>
            <th className="p-3">Limite par deck (50 cartes)</th>
          </tr>
        </thead>
        <tbody>
          {RARITY_ROWS.map((row) => (
            <tr
              key={row.rarity}
              className="border-t border-ink-outline odd:bg-muted"
            >
              <td className="flex items-center gap-2 p-3 font-semibold">
                <span className={`h-3 w-3 rounded-full ${row.dotClass}`} />
                {row.label}
              </td>
              <td className="p-3">{row.cost}</td>
              <td className="p-3">
                {formatDeckLimit(
                  limits.rarityLimits[row.rarity] ?? deckSize,
                  deckSize,
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
      <p className="mt-2 text-xs text-muted-foreground">
        Le personnage de votre deck est toujours de rareté Rare et ne compte pas
        dans les {deckSize} cartes. Vous ne pouvez pas avoir plus de{" "}
        {limits.maxCardCopies} exemplaires de la même carte dans un deck.
      </p>
    </div>
  );
}
