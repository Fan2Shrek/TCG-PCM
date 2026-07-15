import { ApiClient } from "../api";
import { Friendship, FriendshipUser } from "@/types/friendship";

export class FriendResource {
  constructor(private client: ApiClient) {}

  async list() {
    return this.client.get(`/friendships`) as Promise<Friendship[]>;
  }

  async pending() {
    return this.client.get(`/pending-friend-requests`) as Promise<Friendship[]>;
  }

  async search(query: string) {
    if (!query.trim()) return [];
    return this.client.get(`/users/search?q=${encodeURIComponent(query)}`) as Promise<FriendshipUser[]>;
  }

  async send(username: string) {
    return this.client.post(`/friendships`, { username }) as Promise<Friendship>;
  }

  async accept(id: string) {
    return this.client.post(`/friendships/${id}/accept`);
  }

  async decline(id: string) {
    return this.client.post(`/friendships/${id}/decline`);
  }

  async cancel(id: string) {
    return this.client.post(`/friendships/${id}/cancel`);
  }

  async remove(id: string) {
    return this.client.post(`/friendships/${id}/remove`);
  }
}
