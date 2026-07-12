"use client";

import {
  createContext,
  useContext,
  useEffect,
  useRef,
  useState,
  ReactNode,
} from "react";
import { Room } from "@/types/room";
import { RoomStatus } from "@/types/roomStatus";
import client from "@/lib/api/api";
import { useCurrentUser } from "@/hooks/useCurrentUser";
import useMercure from "@/hooks/useMercure";

interface RoomContextType {
  userRoom: Room | null;
  isLoading: boolean;
  refetchRoom: () => Promise<void>;
  clearRoom: () => void;
  lastEvent: string | null;
}

const RoomContext = createContext<RoomContextType | undefined>(undefined);

type RoomProviderProps = {
  children: ReactNode;
  initialRoom?: Room | null;
  enabled?: boolean;
};

export function RoomProvider({
  children,
  initialRoom = null,
  enabled = true,
}: RoomProviderProps) {
  const { user: currentUser } = useCurrentUser();
  const [userRoom, setUserRoom] = useState<Room | null>(initialRoom);
  const [isLoading, setIsLoading] = useState(enabled && initialRoom === null);
  const [lastEvent, setLastEvent] = useState<string | null>(null);
  const hasHydrated = useRef(false);

  const clearRoom = () => {
    setUserRoom(null);
    setLastEvent(null);
  };

  const refetchRoom = async () => {
    if (!enabled) {
      setUserRoom(null);
      return;
    }

    try {
      const room = await client.room.getActive();
      setUserRoom(room);
    } catch (error) {
      console.error("Failed to fetch active room:", error);
      setUserRoom(null);
    }
  };

  const [prevEnabled, setPrevEnabled] = useState(enabled);

  // Resets room state when disabled, computed during render
  // (see "Adjusting state in render" in the React docs).
  if (enabled !== prevEnabled) {
    setPrevEnabled(enabled);
    if (!enabled) {
      setUserRoom(null);
      setLastEvent(null);
      setIsLoading(false);
    }
  }

  useEffect(() => {
    if (!enabled) {
      return;
    }

    if (!hasHydrated.current && initialRoom !== null) {
      hasHydrated.current = true;
      return;
    }

    hasHydrated.current = true;
    refetchRoom().then(() => setIsLoading(false));
  }, [enabled, initialRoom]);

  const hubUrl = userRoom
    ? `${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${userRoom.id}`
    : null;

  useMercure(hubUrl, {
    opponent_joined: (event: { data?: { opponent?: Room["opponent"] } }) => {
      setLastEvent("opponent_joined");
      setUserRoom((prevRoom) => {
        if (!prevRoom) return prevRoom;
        return {
          ...prevRoom,
          opponent: event.data?.opponent,
        };
      });
    },
    opponent_left: () => {
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
    },
    opponent_removed: () => {
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
    },
    owner_left: () => {
      const isCurrentUserOwner =
        currentUser?.username === userRoom?.owner.username;
      setLastEvent(isCurrentUserOwner ? null : "owner_left");
      setUserRoom(null);
    },
    game_started: () => {
      setLastEvent("game_started");
      setUserRoom((prevRoom) => {
        if (!prevRoom) return prevRoom;

        return {
          ...prevRoom,
          status: RoomStatus.PLAYING,
        };
      });
    },
    game_finished: (event: { data?: { winnerId?: Room["winnerId"] } }) => {
      setLastEvent("game_finished");
      setUserRoom((prevRoom) => {
        if (!prevRoom) return prevRoom;

        return {
          ...prevRoom,
          status: RoomStatus.FINISHED,
          winnerId: event.data?.winnerId ?? prevRoom.winnerId ?? null,
        };
      });
    },
  });

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
