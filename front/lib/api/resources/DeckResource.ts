import type { Deck, DeckCollectionResponse } from "@/app/types/deck";

import { ApiClient } from "../api";
import { normalizeDeckCollection } from "@/app/types/deck";

export class DeckResource {
  constructor(private client: ApiClient) {}

  async listMine(): Promise<Deck[]> {
    const response = (await this.client.get(
      "/decks",
    )) as DeckCollectionResponse;
    return normalizeDeckCollection(response);
  }
}
