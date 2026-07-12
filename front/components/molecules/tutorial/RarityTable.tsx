import { CardRaririty } from "@/constants/card";

type RarityRow = {
  rarity: CardRaririty;
  label: string;
  dotClass: string;
  cost: string;
  deckLimit: string;
};

const RARITY_ROWS: RarityRow[] = [
  { rarity: CardRaririty.COMMON, label: "Commune", dotClass: "bg-zinc-500", cost: "1 pièce", deckLimit: "Illimitée" },
  { rarity: CardRaririty.UNCOMMON, label: "Peu commune", dotClass: "bg-emerald-600", cost: "2 pièces", deckLimit: "7 max" },
  { rarity: CardRaririty.RARE, label: "Rare", dotClass: "bg-sky-600", cost: "3 pièces", deckLimit: "6 max" },
  { rarity: CardRaririty.EPIC, label: "Épique", dotClass: "bg-rose-600", cost: "4 pièces", deckLimit: "5 max" },
  { rarity: CardRaririty.LEGENDARY, label: "Légendaire", dotClass: "bg-amber-500", cost: "5 pièces", deckLimit: "3 max" },
];

export default function RarityTable() {
  return (
    <div className="overflow-x-auto">
      <table className="w-full min-w-[420px] border-collapse overflow-hidden rounded-xl text-sm">
        <thead>
          <tr className="bg-black/10 text-left text-black/70">
            <th className="p-3">Rareté</th>
            <th className="p-3">Coût de base</th>
            <th className="p-3">Limite par deck (50 cartes)</th>
          </tr>
        </thead>
        <tbody>
          {RARITY_ROWS.map((row) => (
            <tr key={row.rarity} className="border-t border-black/10 odd:bg-black/5">
              <td className="flex items-center gap-2 p-3 font-semibold">
                <span className={`h-3 w-3 rounded-full ${row.dotClass}`} />
                {row.label}
              </td>
              <td className="p-3">{row.cost}</td>
              <td className="p-3">{row.deckLimit}</td>
            </tr>
          ))}
        </tbody>
      </table>
      <p className="mt-2 text-xs text-black/50">
        Le personnage de votre deck est toujours de rareté Rare et ne compte pas dans les 50 cartes.
      </p>
    </div>
  );
}
