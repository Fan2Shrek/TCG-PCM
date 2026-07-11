"use client";

import {
  createContext,
  useContext,
  useEffect,
  useState,
  ReactNode,
} from "react";
import { Room } from "@/types/room";
import { RoomStatus } from "@/types/roomStatus";
import client from "@/lib/api/api";
import { useCurrentUser } from "@/hooks/useCurrentUser";

interface RoomContextType {
  userRoom: Room | null;
  isLoading: boolean;
  refetchRoom: () => Promise<void>;
  clearRoom: () => void;
  lastEvent: string | null;
}

const RoomContext = createContext<RoomContextType | undefined>(undefined);

export function RoomProvider({ children }: { children: ReactNode }) {
  const { user: currentUser } = useCurrentUser();
  const [userRoom, setUserRoom] = useState<Room | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [lastEvent, setLastEvent] = useState<string | null>(null);

  const clearRoom = () => {
    setUserRoom(null);
    setLastEvent(null);
  };

  const refetchRoom = async () => {
    try {
      const room = await client.room.getActive();
      setUserRoom(room);
    } catch (error) {
      console.error("Failed to fetch active room:", error);
      setUserRoom(null);
    }
  };

  useEffect(() => {
    refetchRoom().then(() => setIsLoading(false));
  }, []);

  useEffect(() => {
    if (!userRoom) return;

    const hubUrl = `${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${userRoom.id}`;
    const eventSource = new EventSource(hubUrl, { withCredentials: true });

    eventSource.onerror = () => {
      eventSource.close();
    };

    eventSource.onmessage = (message) => {
      try {
        const event = JSON.parse(message.data);

        if (event.type === "opponent_joined") {
          setLastEvent("opponent_joined");
          setUserRoom((prevRoom) => {
            if (!prevRoom) return prevRoom;
            return {
              ...prevRoom,
              opponent: event.data?.opponent,
            };
          });
        } else if (event.type === "opponent_left") {
          setLastEvent("opponent_left");
          setUserRoom((prevRoom) => {
            if (!prevRoom) return prevRoom;

            const isCurrentUserOwner =
              currentUser?.username === prevRoom.owner.username;
            if (!isCurrentUserOwner) {
              return null;
            }

            return {
              ...prevRoom,
              opponent: null,
            };
          });
        } else if (event.type === "opponent_removed") {
          setLastEvent("opponent_removed");
          setUserRoom((prevRoom) => {
            if (!prevRoom) return prevRoom;

            const isCurrentUserOwner =
              currentUser?.username === prevRoom.owner.username;
            if (!isCurrentUserOwner) {
              return null;
            }

            return {
              ...prevRoom,
              opponent: null,
            };
          });
        } else if (event.type === "owner_left") {
          const isCurrentUserOwner =
            currentUser?.username === userRoom.owner.username;
          setLastEvent(isCurrentUserOwner ? null : "owner_left");
          setUserRoom(null);
        } else if (event.type === "game_started") {
          setLastEvent("game_started");
          setUserRoom((prevRoom) => {
            if (!prevRoom) return prevRoom;

            return {
              ...prevRoom,
              status: RoomStatus.PLAYING,
            };
          });
        } else if (event.type === "game_finished") {
          setLastEvent("game_finished");
          setUserRoom((prevRoom) => {
            if (!prevRoom) return prevRoom;

            return {
              ...prevRoom,
              status: RoomStatus.FINISHED,
              winnerId: event.data?.winnerId ?? prevRoom.winnerId ?? null,
            };
          });
        }
      } catch (error) {
        console.error("Failed to parse Mercure event:", error);
      }
    };

    return () => {
      eventSource.close();
    };
  }, [userRoom?.id, currentUser?.username]);

  return (
    <RoomContext.Provider
      value={{ userRoom, isLoading, refetchRoom, clearRoom, lastEvent }}
    >
      {children}
    </RoomContext.Provider>
  );
}

export function useRoom() {
  const context = useContext(RoomContext);
  if (context === undefined) {
    throw new Error("useRoom must be used within a RoomProvider");
  }
  return context;
}
