"use client";

import { use, useState } from "react";
import useMercure from "@/hooks/useMercure";
import { startRoomAction } from "@/lib/actions/room";
import { Button } from "@/components/ui/button";

const WaitingPage = ({ params }: { params: Promise<{ id: string }> }) => {
  const { id } = use(params);

  const [err, setErr] = useState<string | null>(null);
  const [opponent, setOpponent] = useState<string | null>(null);

  const handleStart = async () => {
    try {
      await startRoomAction(id);
    } catch (error) {
      setErr(error instanceof Error ? error.message : "Failed to start game");
    }
  };

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(id);
    } catch {
      setErr("Failed to copy room ID");
    }
  };

  useMercure(`${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${id}`, {
    opponent_joined: (message: { data: { opponent: string } }) => {
      setOpponent(message.data.opponent);
    },
  });

  return (
    <div className='flex flex-col items-center justify-center gap-4 h-screen'>
      {err && <p className='text-red-500'>{err}</p>}
      <div className='text-center'>
        <p className='text-lg'>{opponent ? `Opponent: ${opponent}` : "Waiting for opponent..."}</p>
        <p className='text-sm text-gray-500 mt-2'>Room ID: {id}</p>
      </div>
      <div className='flex gap-2'>
        <Button onClick={handleCopy} className='rounded-full'>
          Copy ID
        </Button>
        <Button onClick={handleStart} className='rounded-full'>
          Start Game
        </Button>
      </div>
    </div>
  );
};

export default WaitingPage;
