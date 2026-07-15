"use client";

import { useEffect, useMemo, useState } from "react";
import { toast } from "sonner";
import client from "@/lib/api/api";
import Card from "@/components/molecules/Card";
import CardQuantityBadge from "@/components/atoms/collection/CardQuantityBadge";
import { CardSize } from "@/constants/card";
import type { CardCollectionEntry } from "@/app/types/collection";

type TradeCardPickerProps = {
  onSelect: (cardId: string) => void;
  onClose: () => void;
};

export default function TradeCardPicker({ onSelect, onClose }: TradeCardPickerProps) {
  const [entries, setEntries] = useState<CardCollectionEntry[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    client.user
      .getInventoryCollection()
      .then(({ entries }) => {
        if (!cancelled) setEntries(entries);
      })
      .catch(() => {
        toast.error("Erreur", { description: "Impossible de charger votre collection" });
      })
      .finally(() => {
        if (!cancelled) setIsLoading(false);
      });

    return () => {
      cancelled = true;
    };
  }, []);

  const ownedEntries = useMemo(() => entries.filter(({ quantity }) => quantity > 0), [entries]);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div className="max-h-[80vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6">
        <div className="mb-4 flex items-center justify-between">
          <h3 className="text-lg font-semibold text-black">Choisir une carte à proposer</h3>
          <button onClick={onClose} className="text-sm text-black/60 hover:underline">
            Fermer
          </button>
        </div>

        {isLoading ? (
          <p className="text-black/60">Chargement...</p>
        ) : 0 === ownedEntries.length ? (
          <p className="text-black/60">Vous ne possédez aucune carte à échanger.</p>
        ) : (
          <div className="grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-5">
            {ownedEntries.map(({ card, quantity }) => (
              <button
                key={card.instanceId}
                onClick={() => onSelect(card.instanceId)}
                className="relative flex flex-col items-center gap-1 rounded-lg p-1 hover:bg-black/5"
              >
                <Card card={card} size={CardSize.SM} />
                <CardQuantityBadge quantity={quantity} className="absolute top-1 right-1" />
              </button>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
