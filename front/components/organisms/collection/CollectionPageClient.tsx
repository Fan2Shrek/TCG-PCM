"use client";

import { useState } from "react";
import { CardCollectionEntry } from "@/app/types/collection";
import { Deck, DeckLimits } from "@/app/types/deck";
import InventoryTabs from "@/components/organisms/inventory/InventoryTabs";
import InventoryCardsPanel from "@/components/organisms/inventory/InventoryCardsPanel";
import InventoryDecksPanel from "@/components/organisms/inventory/InventoryDecksPanel";

type CollectionPageClientProps = {
  entries: CardCollectionEntry[];
  decks: Deck[];
  deckLimits: DeckLimits;
  initialTab?: "cards" | "decks";
};

export default function CollectionPageClient({
  entries,
  decks,
  deckLimits,
  initialTab = "cards",
}: CollectionPageClientProps) {
  const [activeTab, setActiveTab] = useState<"cards" | "decks">(initialTab);

  return (
    <div className="mx-2 my-4 sm:mx-4">
      <div className="ml-1 inline-flex z-20">
        <InventoryTabs value={activeTab} onChange={setActiveTab} />
      </div>

      <div className="flex flex-col gap-6 rounded-tr-2xl rounded-b-2xl border-2 border-slate-400/40 bg-slate-200/75 p-6 shadow-[0_14px_40px_-22px_rgba(15,23,42,0.55)] backdrop-blur-sm">
        {activeTab === "cards" ? (
          <InventoryCardsPanel entries={entries} />
        ) : null}

        {activeTab === "decks" ? (
          <InventoryDecksPanel
            decks={decks}
            entries={entries}
            limits={deckLimits}
          />
        ) : null}
      </div>
    </div>
  );
}
