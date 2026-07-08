"use client";

import { usePathname, useRouter } from "next/navigation";
import { RoomStatus } from "@/types/roomStatus";
import { Button } from "@/components/ui/button";
import { useRoom } from "@/contexts/RoomContext";
import client from "@/lib/api/api";
import { toast } from "sonner";
import { useState } from "react";
import ConfirmActionModal from "@/components/molecules/ConfirmActionModal";

export default function ActiveRoomStatus() {
  const router = useRouter();
  const pathname = usePathname();
  const { userRoom, clearRoom } = useRoom();
  const [isLoading, setIsLoading] = useState(false);
  const [showLeavingConfirm, setShowLeavingConfirm] = useState(false);

  const handleRejoin = () => {
    if (!userRoom) return;
    const url =
      userRoom.status === RoomStatus.WAITING
        ? `/rooms/waiting/${userRoom.id}`
        : `/game/${userRoom.id}`;
    router.push(url);
  };

  const handleLeave = async () => {
    if (!userRoom) return;

    if (userRoom.status === RoomStatus.PLAYING) {
      setShowLeavingConfirm(true);
      return;
    }

    await performLeave();
  };

  const performLeave = async () => {
    if (!userRoom) return;

    setIsLoading(true);
    try {
      await client.room.leave(userRoom.id);
      clearRoom();
      setShowLeavingConfirm(false);
      router.push("/rooms");
    } catch (error) {
      console.error("Failed to leave room:", error);
      toast.error("Erreur lors de la sortie de la salle");
    } finally {
      setIsLoading(false);
    }
  };

  console.log(userRoom);
  if (
    !userRoom ||
    (userRoom.status !== RoomStatus.WAITING &&
      userRoom.status !== RoomStatus.PLAYING) ||
    pathname?.startsWith("/rooms/waiting")
  ) {
    return null;
  }

  return (
    <>
      <div className="mt-2 rounded-lg border border-black/20 bg-slate-100 p-3 flex items-center justify-between max-w-md ml-auto">
        <div className="flex items-center gap-3">
          <div className="flex flex-col">
            <span className="text-sm font-medium text-black">
              {userRoom.status === RoomStatus.WAITING
                ? `Partie en attente, ${userRoom.opponent ? 2 : 1}/2 joueur(s)`
                : "Partie en cours"}
            </span>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <Button
            onClick={handleRejoin}
            variant="default"
            size="sm"
            disabled={isLoading}
          >
            Reprendre
          </Button>
          <Button
            onClick={handleLeave}
            variant="destructive"
            size="sm"
            disabled={isLoading}
          >
            Quitter
          </Button>
        </div>
      </div>

      <ConfirmActionModal
        open={showLeavingConfirm}
        title="Confirmer la sortie"
        description="Quitter une partie en cours comptera comme un abandon."
        confirmLabel="Confirmer l'abandon"
        onConfirm={performLeave}
        onCancel={() => setShowLeavingConfirm(false)}
        isLoading={isLoading}
      />
    </>
  );
}
