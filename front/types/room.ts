import { RoomStatus } from "./roomStatus";

export type Room = {
  id: string;
  createdAt: string;
  updatedAt: string;
  status: RoomStatus;
  owner: {
    id: string;
    username: string;
  };
  opponent?: {
    id: string;
    username: string;
  } | null;
  isPrivate: boolean;
};
