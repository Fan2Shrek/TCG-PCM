import { useState, useCallback } from "react";
import { toast } from "sonner";
import client from "@/lib/api/api";
import { Room } from "@/types/room";

export function useWaitingRooms() {
  const [rooms, setRooms] = useState<Room[]>([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const [isLoading, setIsLoading] = useState(false);

  const fetchRooms = useCallback(async (page: number = 1) => {
    setIsLoading(true);
    try {
      const data = await client.room.list(page);
      setRooms(Array.isArray(data) ? data : []);
      setTotalItems(data?.length || 0);
      setCurrentPage(page);
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsLoading(false);
    }
  }, []);

  const goToPage = useCallback(
    (page: number) => {
      fetchRooms(page);
    },
    [fetchRooms],
  );

  const joinRoom = useCallback(async (roomId: string) => {
    try {
      const data = (await client.room.join(roomId)) as {
        mercure_token: string;
      };
      document.cookie = `mercureAuthorization=${data.mercure_token}; path=/; max-age=3600; secure; samesite=strict`;
      window.location.href = `/rooms/game/${roomId}`;
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    }
  }, []);

  const createRoom = useCallback(async () => {
    try {
      const res = (await client.room.create()) as {
        mercure_token: string;
        id: string;
      };
      document.cookie = `mercureAuthorization=${res.mercure_token}; path=/; max-age=3600; secure; samesite=strict`;
      window.location.href = `/rooms/waiting/${res.id}`;
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    }
  }, []);

  return {
    rooms,
    isLoading,
    currentPage,
    totalItems,
    itemsPerPage: 10,
    fetchRooms,
    goToPage,
    joinRoom,
    createRoom,
  };
}
