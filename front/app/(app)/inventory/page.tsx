import { serverApiGet } from "@/lib/api/server";
import CollectionPageClient from "@/components/organisms/collection/CollectionPageClient";
import { CardCollectionResponse } from "@/app/types/collection";
import {
  DeckCollectionResponse,
  DeckLimits,
  normalizeDeckCollection,
} from "@/app/types/deck";

type InventoryPageProps = {
  searchParams: Promise<{
    tab?: string;
  }>;
};

export default async function InventoryPage({
  searchParams,
}: InventoryPageProps) {
  const { entries } = await serverApiGet<CardCollectionResponse>(
    "/inventory/collection",
  );
  const decksResponse = await serverApiGet<DeckCollectionResponse>("/decks");
  const deckLimits = await serverApiGet<DeckLimits>("/decks/limits");
  const decks = normalizeDeckCollection(decksResponse);
  const { tab } = await searchParams;

  return (
    <CollectionPageClient
      entries={entries}
      decks={decks}
      deckLimits={deckLimits}
      initialTab={tab === "decks" ? "decks" : "cards"}
    />
  );
}
