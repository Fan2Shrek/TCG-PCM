"use client";

import { WaitingRoom } from "@/types/waitingRoom";
import { Button } from "@/components/ui/button";

type RoomCardProps = {
  room: WaitingRoom;
  onJoin: (roomId: string) => void;
  isLoading?: boolean;
};

export default function RoomCard({
  room,
  onJoin,
  isLoading = false,
}: RoomCardProps) {
  return (
    <div className="rounded-lg border border-white/20 bg-white/5 p-4 backdrop-blur-sm">
      <div className="flex items-center justify-between">
        <div className="flex-1">
          <p className="text-sm font-medium text-white">{room.player1.name}</p>
          <p className="text-xs text-white/60">
            Créée le {new Date(room.createdAt).toLocaleString()}
          </p>
        </div>
        <Button
          onClick={() => onJoin(room.id)}
          disabled={isLoading}
          variant="default"
          size="sm"
        >
          Rejoindre
        </Button>
      </div>
    </div>
  );
}
