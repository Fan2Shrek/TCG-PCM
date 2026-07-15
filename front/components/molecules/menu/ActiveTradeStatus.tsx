"use client";

import { usePathname, useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { useTradeInvite } from "@/contexts/TradeInviteContext";
import { useCurrentUser } from "@/hooks/useCurrentUser";

export default function ActiveTradeStatus({ className }: { className?: string }) {
  const router = useRouter();
  const pathname = usePathname();
  const { user: currentUser } = useCurrentUser();
  const { activeTrade } = useTradeInvite();

  if (!activeTrade || pathname?.startsWith("/trades/")) {
    return null;
  }

  const otherUsername =
    activeTrade.initiator.username === currentUser?.username
      ? activeTrade.recipient.username
      : activeTrade.initiator.username;

  return (
    <div
      className={`mt-2 rounded-lg border border-black/20 bg-slate-100 p-3 flex items-center justify-between max-w-md ml-auto ${
        className ?? ""
      }`}
    >
      <span className="text-sm font-medium text-black">Échange en cours avec {otherUsername}</span>
      <Button onClick={() => router.push(`/trades/${activeTrade.id}`)} variant="default" size="sm">
        Reprendre
      </Button>
    </div>
  );
}
