"use client";

import { use, useState } from "react";
import { useRouter } from "next/navigation";
import useMercure from "@/hooks/useMercure";

import api from "@/lib/api/api";
import { Button } from "@/components/ui/button";

export default ({ params }: { params: Promise<{ id: string }> }) => {
  const { id } = use(params);
  const router = useRouter();

  const [err, setErr] = useState<string | null>(null);
  const [opp, setOpp] = useState<string | null>(null);

  const handleStart = async () => {
    try {
      api.room.start(id);

      r(id);
    } catch (e) {
      setErr(e.message);
    }
  };

  // Can't use redirect here bc next sucks ass
  const r = (id) => {
    router.push(`/game/${id}`);
  };

  const handleCopy = () => {
    navigator.clipboard.writeText(id);
  };

  useMercure(`${process.env.NEXT_PUBLIC_MERCURE_URL}?topic=game/${id}`, {
    opponent_joined: (message: { data: { opponent: string } }) => {
      setOpp(message.data.opponent);
    },
  });

  return <div className="flex flex-col items-center justify-end h-screen">
	  {err}
	  <br />
	  {opp ? `opponent: ${opp}` : "waiting for opponent..."}
	  <br />
	  {id}
	  <Button onClick={handleCopy} className="rounded-full">copy id</Button>
	  <Button onClick={handleStart} className="rounded-full">start</Button>
  </div>
}
