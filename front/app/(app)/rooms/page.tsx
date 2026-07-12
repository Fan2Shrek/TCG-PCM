import { authApiGet } from "@/lib/api/authServer";
import RoomsPageClient from "@/components/organisms/rooms/RoomsPageClient";
import { Room } from "@/types/room";

export default async function RoomsPage() {
  const initialRooms = await authApiGet<Room[]>("/waiting-rooms?page=1");

  return <RoomsPageClient initialRooms={Array.isArray(initialRooms) ? initialRooms : []} />;
}
