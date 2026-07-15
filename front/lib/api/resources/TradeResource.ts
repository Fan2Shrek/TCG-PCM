import { ApiClient } from "../api";
import { Trade } from "@/types/trade";

export class TradeResource {
  constructor(private client: ApiClient) {}

  private setMercureCookie(token: string) {
    document.cookie = `mercureAuthorization=${token}; path=/; max-age=3600; secure; samesite=strict`;
  }

  async create(friendId: number) {
    const res = (await this.client.post("/trades", { friendId })) as {
      id: string;
      mercure_token: string;
    };
    this.setMercureCookie(res.mercure_token);
    return res;
  }

  async getById(id: string) {
    return this.client.get(`/trades/${id}`) as Promise<Trade>;
  }

  async getActive(): Promise<Trade | null> {
    const response = (await this.client.get(`/me/trade`)) as Trade[] | Trade | null;
    return Array.isArray(response) ? response[0] || null : response || null;
  }

  async subscribe(id: string) {
    const res = (await this.client.post(`/trades/${id}/subscribe`)) as {
      mercure_url: string;
      mercure_token: string;
    };
    this.setMercureCookie(res.mercure_token);
    return res;
  }

  async offerCard(id: string, card: string) {
    return this.client.post(`/trades/${id}/offer`, { card });
  }

  async confirm(id: string) {
    return this.client.post(`/trades/${id}/confirm`) as Promise<Trade>;
  }

  async cancel(id: string) {
    return this.client.post(`/trades/${id}/cancel`);
  }
}
