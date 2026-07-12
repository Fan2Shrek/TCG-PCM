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
            className={`cursor-pointer rounded-t-lg border border-transparent font-semibold will-change-transform transition-[transform,background-color,color,box-shadow] duration-200 ease-out ${
              isActive
                ? "px-5 py-2 text-sm scale-105 bg-slate-100 text-slate-900 shadow-[0_-8px_16px_-12px_rgba(15,23,42,0.75)]"
                : "px-4 py-2 text-xs bg-slate-300/70 text-slate-700 hover:bg-slate-300 hover:text-slate-900"
            }`}
          >
            {TAB_LABELS[tab]}
          </button>
        );
      })}
    </div>
  );
}
