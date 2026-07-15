"use client";

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from "react";
import { useRouter } from "next/navigation";
import { toast } from "sonner";
import client from "@/lib/api/api";
import useMercure from "@/hooks/useMercure";
import { Trade } from "@/types/trade";

type TradeActions = {
  offerCard: (card: string) => Promise<void>;
  confirm: () => Promise<void>;
  cancel: () => Promise<void>;
  selectCard: (card: string | null) => void;
};

type TradeContextValue = {
  trade: Trade | null;
  isLoading: boolean;
  selectedCard: string | null;
  isSubmitting: boolean;
  actions: TradeActions;
};

const TradeContext = createContext<TradeContextValue | undefined>(undefined);

type TradeProviderProps = {
  children: ReactNode;
  tradeId: string;
};

export function TradeProvider({ children, tradeId }: TradeProviderProps) {
  const router = useRouter();
  const [trade, setTrade] = useState<Trade | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedCard, setSelectedCard] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [hubUrl, setHubUrl] = useState<string | null>(null);

  const refetch = useCallback(async () => {
    const next = await client.trade.getById(tradeId);
    setTrade(next);
    return next;
  }, [tradeId]);

  useEffect(() => {
    let cancelled = false;

    const load = async () => {
      await client.trade.getById(tradeId).then(setTrade);
      await client.trade.subscribe(tradeId);
      if (!cancelled) {
        setHubUrl(`${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=trade/${tradeId}`);
      }
    };

    load()
      .catch((error: unknown) => {
        const message = error instanceof Error ? error.message : "Échange introuvable";
        toast.error("Erreur", { description: message });
        router.push("/friends");
      })
      .finally(() => {
        if (!cancelled) setIsLoading(false);
      });

    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [tradeId]);

  useMercure(hubUrl, {
    card_offered: (event: { data?: { by?: string } }) => {
      toast.info(`${event.data?.by ?? "L'autre joueur"} a proposé une carte.`);
      refetch();
    },
    trade_confirm_updated: () => {
      refetch();
    },
    trade_completed: () => {
      toast.success("Échange finalisé !");
      refetch().then(() => router.push("/inventory"));
    },
    trade_cancelled: (event: { data?: { by?: string } }) => {
      toast.info(`${event.data?.by ?? "L'autre joueur"} a annulé l'échange.`);
      refetch().then(() => router.push("/friends"));
    },
  });

  const offerCard = useCallback(
    async (card: string) => {
      setIsSubmitting(true);
      try {
        await client.trade.offerCard(tradeId, card);
        await refetch();
        setSelectedCard(null);
      } catch (error) {
        const message = error instanceof Error ? error.message : "Une erreur est survenue";
        toast.error("Erreur", { description: message });
      } finally {
        setIsSubmitting(false);
      }
    },
    [tradeId, refetch],
  );

  const confirm = useCallback(async () => {
    setIsSubmitting(true);
    try {
      const next = await client.trade.confirm(tradeId);
      setTrade(next);
    } catch (error) {
      const message = error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsSubmitting(false);
    }
  }, [tradeId]);

  const cancel = useCallback(async () => {
    setIsSubmitting(true);
    try {
      await client.trade.cancel(tradeId);
      router.push("/friends");
    } catch (error) {
      const message = error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsSubmitting(false);
    }
  }, [tradeId, router]);

  return (
    <TradeContext.Provider
      value={{
        trade,
        isLoading,
        selectedCard,
        isSubmitting,
        actions: { offerCard, confirm, cancel, selectCard: setSelectedCard },
      }}
    >
      {children}
    </TradeContext.Provider>
  );
}

export function useTrade() {
  const context = useContext(TradeContext);
  if (context === undefined) {
    throw new Error("useTrade must be used within a TradeProvider");
  }
  return context;
}
