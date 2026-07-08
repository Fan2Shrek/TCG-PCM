import { ApiClient } from "../api";

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
    return this.client.get(`/waiting-rooms?page=${page}`);
  }

  async join(id: string) {
    const data = (await this.client.post(`/rooms/${id}/join`)) as {
      mercure_token: string;
    };
    this.setMercureCookie(data.mercure_token);
    return data;
  }
}
