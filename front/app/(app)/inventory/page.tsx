import { serverApiGet } from "@/lib/api/server";
import CollectionPageClient from "@/components/organisms/collection/CollectionPageClient";
import { CardCollectionResponse } from "@/app/types/collection";
import {
  DeckCollectionResponse,
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
  const decks = normalizeDeckCollection(decksResponse);
  const { tab } = await searchParams;

  return (
    <CollectionPageClient
      entries={entries}
      decks={decks}
      initialTab={tab === "decks" ? "decks" : "cards"}
    />
  );
}
