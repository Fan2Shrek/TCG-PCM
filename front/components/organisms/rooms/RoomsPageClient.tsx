"use client";

import { useEffect, useState, useCallback, useRef } from "react";
import Link from "next/link";
import { MdRefresh, MdAdd } from "react-icons/md";
import { toast } from "sonner";
import client from "@/lib/api/api";
import { Button } from "@/components/ui/button";
import RoomCard from "@/components/molecules/arena/RoomCard";
import Pagination from "@/components/molecules/Pagination";
import ConfirmActionModal from "@/components/molecules/ConfirmActionModal";
import { Room } from "@/types/room";
import { RoomStatus } from "@/types/roomStatus";
import { useRoom } from "@/contexts/RoomContext";

const ITEMS_PER_PAGE = 10;

type RoomsPageClientProps = {
  initialRooms: Room[];
};

export default function RoomsPageClient({
  initialRooms,
}: RoomsPageClientProps) {
  const { userRoom } = useRoom();
  const [rooms, setRooms] = useState<Room[]>(initialRooms);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalItems, setTotalItems] = useState(initialRooms.length);
  const [isLoading, setIsLoading] = useState(false);
  const [joinById, setJoinById] = useState("");
  const [isJoiningById, setIsJoiningById] = useState(false);
  const [showConfirmation, setShowConfirmation] = useState(false);
  const [pendingRoomId, setPendingRoomId] = useState<string | null>(null);
  const [isConfirmLoading, setIsConfirmLoading] = useState(false);
  const hasHydrated = useRef(false);

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

  useEffect(() => {
    if (!hasHydrated.current) {
      hasHydrated.current = true;
      return;
    }

    fetchRooms(1);
  }, [fetchRooms]);

  const performJoin = async (roomId: string) => {
    try {
      await client.room.join(roomId);
      window.location.href = `/rooms/waiting/${roomId}`;
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    }
  };

  const joinRoom = async (roomId: string) => {
    if (userRoom) {
      setShowConfirmation(true);
      setPendingRoomId(roomId);
    } else {
      await performJoin(roomId);
    }
  };

  const createRoom = async () => {
    if (userRoom) {
      setShowConfirmation(true);
      setPendingRoomId(null);
    } else {
      try {
        const res = await client.room.create();
        window.location.href = `/rooms/waiting/${res.id}`;
      } catch (error) {
        const message =
          error instanceof Error ? error.message : "Une erreur est survenue";
        toast.error("Erreur", { description: message });
      }
    }
  };

  const handleJoinById = async () => {
    if (!joinById.trim()) {
      toast.error("Erreur", { description: "Veuillez entrer un ID de salle" });
      return;
    }

    if (userRoom) {
      setShowConfirmation(true);
      setPendingRoomId(joinById);
    } else {
      setIsJoiningById(true);
      try {
        await client.room.join(joinById);
        window.location.href = `/rooms/waiting/${joinById}`;
      } catch (error) {
        const message =
          error instanceof Error
            ? error.message
            : "Salle non trouvée ou indisponible";
        toast.error("Erreur", { description: message });
      } finally {
        setIsJoiningById(false);
      }
    }
  };

  const handleConfirmJoin = async () => {
    setIsConfirmLoading(true);
    try {
      if (userRoom) {
        await client.room.leave(userRoom.id);
      }

      if (pendingRoomId) {
        await performJoin(pendingRoomId);
      } else {
        const res = await client.room.create();
        window.location.href = `/rooms/waiting/${res.id}`;
      }
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsConfirmLoading(false);
      setShowConfirmation(false);
      setPendingRoomId(null);
    }
  };

  return (
    <div className="flex flex-col items-center justify-center flex-1">
      <div className="w-full max-w-4xl rounded-lg bg-slate-100 border border-black/40 overflow-hidden mx-2">
        <div className="p-6">
          <div className="flex items-center justify-between mb-8 flex-wrap">
            <h2 className="text-2xl font-semibold text-black">
              Joueurs en attente d'adversaires
            </h2>
            <div className="flex gap-4">
              <Button
                asChild
                variant="default"
                size="lg"
                className="px-8 bg-blue-800 text-white hover:bg-blue-600"
              >
                <Link href="/inventory?tab=decks">Gérer mes decks</Link>
              </Button>
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
              rooms
                .filter((room) => !userRoom || room.id !== userRoom.id)
                .map((room) => (
                  <RoomCard
                    key={room.id}
                    room={room}
                    onJoin={joinRoom}
                    isLoading={isLoading}
                  />
                ))
            )}
          </div>

          <div className="mb-6">
            <Pagination
              currentPage={currentPage}
              totalItems={totalItems}
              itemsPerPage={ITEMS_PER_PAGE}
              onPageChange={fetchRooms}
              isLoading={isLoading}
            />
          </div>

          <div className="flex gap-2">
            <input
              type="text"
              value={joinById}
              onChange={(e) => setJoinById(e.target.value)}
              onKeyDown={(e) => e.key === "Enter" && handleJoinById()}
              placeholder="Rejoindre à partir d'un id..."
              className="flex-1 px-3 py-2 rounded bg-white text-black placeholder-black/40 focus:outline-none focus:ring-2 focus:ring-blue-500"
              disabled={isJoiningById}
            />
            <Button
              onClick={handleJoinById}
              disabled={isJoiningById}
              variant="default"
              size="lg"
            >
              Rejoindre
            </Button>
          </div>
        </div>
      </div>

      <ConfirmActionModal
        open={showConfirmation && !!userRoom}
        title={pendingRoomId ? "Rejoindre une salle" : "Créer une salle"}
        description={`Vous êtes actuellement dans une salle. Si vous ${pendingRoomId ? "rejoignez" : "créez"} une nouvelle salle, vous quitterez la salle actuelle.`}
        warning={
          userRoom?.status === RoomStatus.PLAYING
            ? "Cette action comptera comme un abandon."
            : undefined
        }
        confirmLabel="Confirmer"
        onConfirm={handleConfirmJoin}
        onCancel={() => {
          setShowConfirmation(false);
          setPendingRoomId(null);
        }}
        isLoading={isConfirmLoading}
      />
    </div>
  );
}
