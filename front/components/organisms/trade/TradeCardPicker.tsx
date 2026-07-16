"use client";

import { useEffect, useMemo, useState } from "react";
import { toast } from "sonner";
import client from "@/lib/api/api";
import Card from "@/components/molecules/Card";
import CardQuantityBadge from "@/components/atoms/collection/CardQuantityBadge";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
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
    <Dialog open onOpenChange={(next) => !next && onClose()}>
      <DialogContent className="max-h-[80vh] max-w-2xl overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Choisir une carte à proposer</DialogTitle>
        </DialogHeader>

        {isLoading ? (
          <p className="text-muted-foreground">Chargement...</p>
        ) : 0 === ownedEntries.length ? (
          <p className="text-muted-foreground">Vous ne possédez aucune carte à échanger.</p>
        ) : (
          <div className="grid grid-cols-3 gap-4 sm:grid-cols-4 md:grid-cols-5">
            {ownedEntries.map(({ card, quantity }) => (
              <button
                key={card.instanceId}
                onClick={() => onSelect(card.instanceId)}
                className="relative flex flex-col items-center gap-1 rounded-xl p-1 transition hover:bg-muted"
              >
                <Card card={card} size={CardSize.SM} />
                <CardQuantityBadge quantity={quantity} className="absolute top-1 right-1" />
              </button>
            ))}
          </div>
        )}
      </DialogContent>
    </Dialog>
  );
}
