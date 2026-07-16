"use client";

type InventoryTab = "cards" | "decks";

type InventoryTabsProps = {
  value: InventoryTab;
  onChange: (tab: InventoryTab) => void;
};

const TAB_LABELS: Record<InventoryTab, string> = {
  cards: "Mes cartes",
  decks: "Mes decks",
};

export default function InventoryTabs({ value, onChange }: InventoryTabsProps) {
  return (
    <div
      className="inline-flex items-end gap-1"
      role="tablist"
      aria-label="Navigation inventaire"
    >
      {(Object.keys(TAB_LABELS) as InventoryTab[]).map((tab) => {
        const isActive = value === tab;

        return (
          <button
            key={tab}
            type="button"
            role="tab"
            aria-selected={isActive}
            onClick={() => onChange(tab)}
            className={`cursor-pointer rounded-t-2xl border-2 border-ink-outline border-b-0 font-display font-extrabold will-change-transform transition-[transform,background-color,color] duration-200 ease-out ${
              isActive
                ? "px-5 py-2 text-sm scale-105 bg-card"
                : "px-4 py-2 text-xs bg-muted text-muted-foreground hover:bg-card"
            }`}
          >
            {TAB_LABELS[tab]}
          </button>
        );
      })}
    </div>
  );
}
