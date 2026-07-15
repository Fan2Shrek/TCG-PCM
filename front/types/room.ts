import { RoomStatus } from "./roomStatus";

export type Room = {
  id: string;
  createdAt: string;
  updatedAt: string;
  status: RoomStatus;
  owner: {
    id: string;
    username: string;
    profilePicturePath?: string | null;
  };
  opponent?: {
    id: string;
    username: string;
    profilePicturePath?: string | null;
  } | null;
  winnerId?: string | null;
  isPrivate: boolean;
  ownerDeck?: {
    id: number;
    name: string;
    isFavorite?: boolean | null;
  } | null;
  opponentDeck?: {
    id: number;
    name: string;
    isFavorite?: boolean | null;
  } | null;
};
