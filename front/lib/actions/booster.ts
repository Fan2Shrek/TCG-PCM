"use server";

import { serverApiPost } from "@/lib/api/server";
import type { BasicCard } from "@/lib/cards/types/card";

export async function openBoosterAction(): Promise<{ cards: BasicCard[] }> {
  return serverApiPost<{ cards: BasicCard[] }>("boosters/open");
}
