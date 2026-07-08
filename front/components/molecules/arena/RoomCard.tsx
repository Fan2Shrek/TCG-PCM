"use client";

import { Room } from "@/types/room";
import { Button } from "@/components/ui/button";
import { LogIn } from "lucide-react";

type RoomCardProps = {
  room: Room;
  onJoin: (roomId: string) => void;
  isLoading?: boolean;
};

export default function RoomCard({
  room,
  onJoin,
  isLoading = false,
}: RoomCardProps) {
  return (
    <div className="rounded-lg border border-black/20 bg-black/5 p-4">
      <div className="flex items-center justify-between">
        <div className="flex-1">
          <p className="text-sm text-black">{room.owner.username}</p>
        </div>
        <Button
          onClick={() => onJoin(room.id)}
          disabled={isLoading}
          variant="secondary"
          size="icon"
        >
          <LogIn className="h-4 w-4" />
        </Button>
      </div>
    </div>
  );
}
