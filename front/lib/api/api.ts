import { BoosterResource } from "./resources/BoosterResource";
import { GameResource } from "./resources/GameResource";
import { RoomResource } from "./resources/RoomResource";
import { UserResource } from "./resources/UserResource";

const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

export class ApiClient {
  booster: BoosterResource;
  game: GameResource;
  user: UserResource;
  room: RoomResource;

  constructor(public baseUrl: string) {
    this.booster = new BoosterResource(this);
    this.game = new GameResource(this);
    this.user = new UserResource(this);
    this.room = new RoomResource(this);
  }

  async request<T>(endpoint: string, options?: RequestInit): Promise<T> {
    const response = await fetch(`/api/proxy${endpoint}`, {
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

      throw new Error(errorBody?.detail || `API request failed with status ${response.status}`);
    }

    if (response.status === 204) {
      return {} as T;
    }

    return response.json();
  }

  async get<T>(endpoint: string): Promise<T> {
    return this.request<T>(endpoint, { method: "GET" });
  }

  async post<T>(endpoint: string, body: any = {}): Promise<T> {
    return this.request<T>(endpoint, {
      method: "POST",
      body: JSON.stringify(body),
    });
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
