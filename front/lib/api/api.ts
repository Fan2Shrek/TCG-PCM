import { AuthResource } from "./resources/AuthResource";
import { BoosterResource } from "./resources/BoosterResource";
import { GameResource } from "./resources/GameResource";
import { RoomResource } from "./resources/RoomResource";
import { UserResource } from "./resources/UserResource";
import { getToken } from "@/lib/utils";

export class ApiClient {
	auth: AuthResource;
  	booster: BoosterResource;
	game: GameResource;
	user: UserResource;
	room: RoomResource;

	constructor(
	  private baseUrl: string,
	) {

		this.auth = new AuthResource(this);
		this.booster = new BoosterResource(this);
		this.game = new GameResource(this);
		this.user = new UserResource(this);
		this.room = new RoomResource(this);
	}

	async request<T>(endpoint: string, options?: RequestInit): Promise<T> {
	    const token = getToken();
	    const headers: HeadersInit = {
		  "Content-Type": "application/json",
		  ...(token && { Authorization: `Bearer ${token}` }),
		};
		const response = await fetch(`${this.baseUrl}${endpoint}`, {
		  ...options,
		  headers,
		});

		if (!response.ok) {
			if (response.status === 400) {
        const errorBody = (await response.json().catch(() => null)) as {
          detail?: string;
        } | null;

        if (errorBody?.detail) {
          throw new Error(errorBody.detail);
        }
      }

			throw new Error(`API request failed with status ${response.status}`);
		}

		if (response.status === 204) {
			return {} as T;
		}

		return response.json();
	}

	async get<T>(endpoint: string): Promise<T> {
		return this.request<T>(endpoint, { method: 'GET' });
	}

	async post<T>(endpoint: string, body: any = {}): Promise<T> {
		return this.request<T>(endpoint, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(body),
		});
	}
}

const client = new ApiClient(
  process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api',
);

export const getImage = (img: string) => {
  try {
    new URL(img);
    return img;
  } catch {
	return `${client.baseUrl.replaceAll("api", "")}${img}`;
  }
};

export default client;
