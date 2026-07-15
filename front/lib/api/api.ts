import { logoutAction } from "@/lib/actions/auth";
import { BadgeResource } from "./resources/BadgeResource";
import { BoosterResource } from "./resources/BoosterResource";
import { DeckResource } from "./resources/DeckResource";
import { GameResource } from "./resources/GameResource";
import { RoomResource } from "./resources/RoomResource";
import { UserResource } from "./resources/UserResource";

const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

export class ApiClient {
  badge: BadgeResource;
  booster: BoosterResource;
  deck: DeckResource;
  game: GameResource;
  user: UserResource;
  room: RoomResource;

  constructor(public baseUrl: string) {
    this.badge = new BadgeResource(this);
    this.booster = new BoosterResource(this);
    this.deck = new DeckResource(this);
    this.game = new GameResource(this);
    this.user = new UserResource(this);
    this.room = new RoomResource(this);
  }

  async request<T>(endpoint: string, options?: RequestInit): Promise<T> {
    const response = await fetch(`/bff/proxy${endpoint}`, {
      ...options,
      headers: {
        "Content-Type": "application/json",
        ...options?.headers,
      },
    });

    if (!response.ok) {
      const errorBody = (await response.json().catch(() => null)) as {
        detail?: string;
      } | null;

      if (response.status === 401) {
        void logoutAction();
        throw new Error("Session expirée, reconnexion nécessaire.");
      }

      throw new Error(
        errorBody?.detail ||
          `API request failed with status ${response.status}`,
      );
    }

    if (response.status === 204) {
      return {} as T;
    }

    return response.json();
  }

  async get<T>(endpoint: string): Promise<T> {
    return this.request<T>(endpoint, { method: "GET" });
  }

  async post<T>(endpoint: string, body: unknown = {}): Promise<T> {
    return this.request<T>(endpoint, {
      method: "POST",
      body: JSON.stringify(body),
    });
  }

  async patch<T>(
    endpoint: string,
    body: unknown = {},
    headers?: HeadersInit,
  ): Promise<T> {
    return this.request<T>(endpoint, {
      method: "PATCH",
      body: JSON.stringify(body),
      headers,
    });
  }

  async delete<T>(endpoint: string): Promise<T> {
    return this.request<T>(endpoint, { method: "DELETE" });
  }
}

const client = new ApiClient(baseUrl);

export const getImage = (img: string) => {
  try {
    new URL(img);
    return img;
  } catch {
    return `${baseUrl.replaceAll("api", "")}${img}`;
  }
};

export default client;
