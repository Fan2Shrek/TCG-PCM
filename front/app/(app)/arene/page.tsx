"use client";

import { useEffect, useState, useCallback } from "react";
import { MdRefresh, MdAdd } from "react-icons/md";
import { toast } from "sonner";
import client from "@/lib/api/api";
import { Button } from "@/components/ui/button";
import RoomCard from "@/components/molecules/arena/RoomCard";
import Pagination from "@/components/molecules/Pagination";
import { WaitingRoom, WaitingRoomsResponse } from "@/types/waitingRoom";

const ITEMS_PER_PAGE = 10;

export default function ArenePage() {
  const [rooms, setRooms] = useState<WaitingRoom[]>([]);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalItems, setTotalItems] = useState(0);
  const [isLoading, setIsLoading] = useState(false);

  const fetchRooms = useCallback(async (page: number = 1) => {
    setIsLoading(true);
    try {
      const data = (await client.room.list(page)) as WaitingRoomsResponse;
      setRooms(data["hydra:member"] || []);
      setTotalItems(data["hydra:totalItems"] || 0);
      setCurrentPage(page);
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsLoading(false);
    }
  }, []);

  const joinRoom = async (roomId: string) => {
    try {
      await client.room.join(roomId);
      window.location.href = `/arene/game/${roomId}`;
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    }
  };

  const createRoom = async () => {
    try {
      const res = await client.room.create();
      window.location.href = `/arene/waiting/${res.id}`;
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    }
  };

  useEffect(() => {
    fetchRooms(1);
  }, [fetchRooms]);

  return (
    <div className="flex flex-col items-center justify-center flex-1">
      <div className="w-full max-w-4xl rounded-lg bg-slate-100 border border-black/40 overflow-hidden">
        <div className="p-6">
          <div className="flex items-center justify-between mb-8">
            <h2 className="text-2xl font-semibold text-black">
              Salles disponibles
            </h2>
            <div className="flex gap-4">
              <Button
                onClick={createRoom}
                variant="default"
                size="lg"
                className="px-8"
              >
                <MdAdd className="h-5 w-5" />
                Créer une salle
              </Button>
              <Button
                onClick={() => fetchRooms(currentPage)}
                disabled={isLoading}
                variant="default"
                size="lg"
              >
                <MdRefresh
                  className={`h-5 w-5 ${isLoading ? "animate-spin" : ""}`}
                />
              </Button>
            </div>
          </div>

          <div className="space-y-3 mb-8">
            {rooms.length === 0 ? (
              <div className="rounded-lg border border-black/20 bg-black/5 p-8 text-center">
                <p className="text-black/60">Aucune salle disponible</p>
              </div>
            ) : (
              rooms.map((room) => (
                <RoomCard
                  key={room.id}
                  room={room}
                  onJoin={joinRoom}
                  isLoading={isLoading}
                />
              ))
            )}
          </div>

          <Pagination
            currentPage={currentPage}
            totalItems={totalItems}
            itemsPerPage={ITEMS_PER_PAGE}
            onPageChange={fetchRooms}
            isLoading={isLoading}
          />
        </div>
      </div>
    </div>
  );
}
