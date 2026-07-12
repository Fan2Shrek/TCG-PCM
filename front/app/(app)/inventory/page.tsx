import { serverApiGet } from "@/lib/api/server";
import CollectionPageClient from "@/components/organisms/collection/CollectionPageClient";
import { CardCollectionResponse } from "@/app/types/collection";

export default async function InventoryPage() {
  const { entries } = await serverApiGet<CardCollectionResponse>("/inventory/collection");

  return <CollectionPageClient entries={entries} />;
}
