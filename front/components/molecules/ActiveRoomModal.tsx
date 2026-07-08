import { Room } from "@/types/room";
import { RoomStatus } from "@/types/roomStatus";
import { Button } from "@/components/ui/button";

interface ActiveRoomModalProps {
  room: Room | null;
  isOpen: boolean;
  isLoading: boolean;
  onRejoin: () => void;
  onLeave: () => void;
  onClose?: () => void;
}

export default function ActiveRoomModal({
  room,
  isOpen,
  isLoading,
  onRejoin,
  onLeave,
  onClose,
}: ActiveRoomModalProps) {
  if (!isOpen || !room) return null;

  const isPlaying = room.status === RoomStatus.PLAYING;
  const statusLabel =
    room.status === RoomStatus.PLAYING ? "En cours" : "En attente d'adversaire";

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-slate-100 rounded-lg border border-black/40 p-8 max-w-md w-full mx-4">
        <h2 className="text-2xl font-bold text-black mb-4">Partie active</h2>

        <div className="space-y-4 mb-6">
          <div>
            <p className="text-sm text-black/60">Statut</p>
            <p className="text-lg font-semibold text-black">{statusLabel}</p>
          </div>
          <div>
            <p className="text-sm text-black/60">Adversaire</p>
            <p className="text-lg font-semibold text-black">
              {room.opponent ? room.opponent.username : "Aucun adversaire"}
            </p>
          </div>
        </div>

        {isPlaying && (
          <p className="text-sm text-red-600 bg-red-50 border border-red-200 rounded p-3 mb-6">
            ⚠️ Quitter la partie = concéder la victoire à votre adversaire
          </p>
        )}

        <div className="flex gap-3">
          <Button
            onClick={onRejoin}
            disabled={isLoading}
            variant="default"
            size="lg"
            className="flex-1"
          >
            Rejoindre
          </Button>
          <Button
            onClick={onLeave}
            disabled={isLoading}
            variant="destructive"
            size="lg"
            className="flex-1"
          >
            Quitter
          </Button>
        </div>
      </div>
    </div>
  );
}
