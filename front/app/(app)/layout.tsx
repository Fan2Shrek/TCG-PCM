"use client";

import { useEffect, useState } from "react";
import { useRouter, usePathname } from "next/navigation";
import { useAuth } from "@/contexts/AuthContext";
import PendingBoosters from "@/components/organisms/layout/PendingBoosters";
import Menu from "@/components/organisms/menu/Menu";
import Image from "@/components/atoms/Image";
import ActiveRoomModal from "@/components/molecules/ActiveRoomModal";
import { toast, Toaster } from "sonner";
import api from "@/lib/api/api";
import { Room } from "@/types/room";
import { RoomStatus } from "@/types/roomStatus";

export default ({ children }: { children: React.ReactNode }) => {
  const router = useRouter();
  const pathname = usePathname();
  const { user: currentUser } = useAuth();
  const logoPath = "/menu/logo.png";

  const [userRoom, setUserRoom] = useState<Room | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const isGameRoute = pathname?.startsWith("/game");
  const isWaitingRoomRoute = pathname?.startsWith("/rooms/waiting");

  useEffect(() => {
    const checkUserRoom = async () => {
      if (!currentUser || isGameRoute || isWaitingRoomRoute) {
        setIsLoading(false);
        return;
      }

      try {
        const activeRoom = await api.room.getActive();
        if (activeRoom?.id) {
          setUserRoom(activeRoom);
          setShowModal(true);
        }
      } catch (error) {
        console.error("Error checking user room:", error);
      } finally {
        setIsLoading(false);
      }
    };

    checkUserRoom();
  }, [currentUser, isGameRoute, isWaitingRoomRoute]);

  const handleRejoin = async () => {
    if (!userRoom) return;

    try {
      router.push(`/rooms/waiting/${userRoom.id}`);
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    }
  };

  const handleLeave = async () => {
    if (!userRoom) return;

    try {
      setIsLoading(true);
      await api.room.leave(userRoom.id);

      const status =
        userRoom.status === RoomStatus.PLAYING
          ? "Vous avez concédé la partie"
          : "Vous avez quitté la salle";

      toast.success(status);
      setShowModal(false);
      setUserRoom(null);
    } catch (error) {
      const message =
        error instanceof Error ? error.message : "Une erreur est survenue";
      toast.error("Erreur", { description: message });
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <>
      <Toaster />
      <div className="hidden md:grid grid-cols-3 items-center fixed w-full pt-3 px-5 z-10">
        <PendingBoosters className="justify-self-start" />
        <Image
          src={logoPath}
          alt="Logo"
          width={275}
          height={275}
          className="justify-self-center"
        />
        <Menu className="justify-self-end" />
      </div>

      <ActiveRoomModal
        room={userRoom}
        isOpen={showModal}
        isLoading={isLoading}
        onRejoin={handleRejoin}
        onLeave={handleLeave}
      />

      <div className="md:pt-32 min-h-screen flex flex-col">{children}</div>
    </>
  );
};
