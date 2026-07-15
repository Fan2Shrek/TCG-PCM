"use client";

import { use } from "react";
import { TradeProvider } from "@/contexts/TradeContext";
import TradeSessionClient from "@/components/organisms/trade/TradeSessionClient";

export default function TradePage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);

  return (
    <TradeProvider tradeId={id}>
      <TradeSessionClient />
    </TradeProvider>
  );
}
