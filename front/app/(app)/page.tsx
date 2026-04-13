'use client';

import { useEffect, useState } from 'react';
import { redirect, RedirectType } from 'next/navigation'

import api from "@/lib/api/api";
import HandExample from "@/components/organisms/HandExample";

export default function Home() {
  // Examples rendered in a client component to allow event handlers

  const [rooms, setRooms] = useState([]);

  useEffect(() => {
	const fetchGame = async () => {
	  return await api.room.list()
	};

	fetchGame().then((data) => setRooms(data)).catch(console.error)
  }, []);

  const handleJoin = (id: string) => {
	api.room.join(id);

	redirect(`/arene/waiting/${id}`, RedirectType.replace)
  }

  return (
      <main className="flex flex-col items-center gap-12 p-24 sm:items-start">
        <HandExample />
		{rooms && rooms.map((room) => <div key={room.id}>
			<a>{room.owner.username}</a>
			<br />
			<button onClick={() => handleJoin(room.id)}>rejoinde</button>
		</div>)}
      </main>
  );
}
