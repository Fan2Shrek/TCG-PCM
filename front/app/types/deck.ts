export type Deck = {
  id: number;
  name: string;
  characterCard: string;
  cards: string[];
  isFavorite: boolean | null;
};

export type DeckCollectionResponse =
  | Deck[]
  | {
      member?: Deck[];
      "hydra:member"?: Deck[];
    };

export function normalizeDeckCollection(
  response: DeckCollectionResponse,
): Deck[] {
  if (Array.isArray(response)) {
    return response;
  }

  if (Array.isArray(response["hydra:member"])) {
    return response["hydra:member"];
  }

  if (Array.isArray(response.member)) {
    return response.member;
  }

  return [];
}
