import { serverApiGet } from "@/lib/api/server";
import JoinRoomButton from "@/components/molecules/JoinRoomButton";

type Room = {
  id: string;
  owner: { username: string };
};

export default async function Home() {
  const rooms = await serverApiGet<Room[]>("/waiting-rooms");

  return (
    <main className="flex flex-col items-center gap-12 p-24 sm:items-start">
      {rooms.map((room) => (
        <div
          key={room.id}
          className="hover:rotate-[30deg] transition-transform"
        >
          <a>{room.owner.username}</a>
          <br />
          <JoinRoomButton roomId={room.id} />
        </div>
      ))}
    </main>
  );
}
