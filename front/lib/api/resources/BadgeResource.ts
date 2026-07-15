import type { BadgesResponse, UserBadge } from "@/app/types/badge";
import { ApiClient } from "../api";

export class BadgeResource {
  constructor(private client: ApiClient) {}

  async getMine(): Promise<UserBadge[]> {
    const response = await this.client.get<BadgesResponse>("/badges");
    return response.badges;
  }
}
