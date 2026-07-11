import { serverApiGet } from "@/lib/api/server";
import RoomsPageClient from "@/components/organisms/rooms/RoomsPageClient";
import { Room } from "@/types/room";

export default async function RoomsPage() {
  const initialRooms = await serverApiGet<Room[]>("/waiting-rooms?page=1");

  return <RoomsPageClient initialRooms={Array.isArray(initialRooms) ? initialRooms : []} />;
}
