import { ApiClient } from "../api";
import { Room } from "@/types/room";

export class RoomResource {
  constructor(private client: ApiClient) {}

  private setMercureCookie(token: string) {
    document.cookie = `mercureAuthorization=${token}; path=/; max-age=3600; secure; samesite=strict`;
  }

  async create() {
    const res = (await this.client.post("/rooms/create")) as {
      mercure_token: string;
      id: string;
    };
    this.setMercureCookie(res.mercure_token);
    return res;
  }

  async start(id: string) {
    return this.client.post(`/rooms/${id}/start`);
  }

  async list(page: number = 1) {
    return this.client.get(`/waiting-rooms?page=${page}`) as Promise<Room[]>;
  }

  async getActive(): Promise<Room | null> {
    const response = (await this.client.get(`/me/room`)) as Room[] | Room | null;
    return Array.isArray(response) ? response[0] || null : response || null;
  }

  async join(id: string) {
    const data = (await this.client.post(`/rooms/${id}/join`)) as {
      mercure_token: string;
    };
    this.setMercureCookie(data.mercure_token);
    return data;
  }

  async togglePrivate(id: string, isPrivate: boolean) {
    return this.client.post(`/rooms/${id}/toggle-private`, { isPrivate });
  }

  async getById(id: string) {
    return this.client.get(`/rooms/${id}`) as Promise<Room>;
  }

  async leave(id: string) {
    return this.client.post(`/rooms/${id}/leave`, {});
  }

  async removeOpponent(id: string) {
    return this.client.post(`/rooms/${id}/remove-opponent`, {});
  }

  async changeDeck(id: string, deckId: string | number) {
    return this.client.post(`/rooms/${id}/change_deck`, {
      deck: `/api/decks/${deckId}`,
    });
  }
}
