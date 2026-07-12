import type {
  Deck,
  DeckCollectionResponse,
  DeckCreateResponse,
  DeckDraft,
  DeckLimits,
} from "@/app/types/deck";

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

  async setFavorite(deckId: number, isFavorite: boolean): Promise<void> {
    await this.client.patch(
      `/decks/${deckId}`,
      { isFavorite },
      { "Content-Type": "application/merge-patch+json" },
    );
  }

  async getLimits(): Promise<DeckLimits> {
    return this.client.get("/decks/limits");
  }

  async create(deck: DeckDraft): Promise<DeckCreateResponse> {
    return this.client.post("/decks", deck);
  }

  async update(deckId: number, deck: DeckDraft): Promise<Deck> {
    return this.client.patch(`/decks/${deckId}`, deck, {
      "Content-Type": "application/merge-patch+json",
    });
  }

  async delete(deckId: number): Promise<void> {
    await this.client.delete(`/decks/${deckId}`);
  }
}
