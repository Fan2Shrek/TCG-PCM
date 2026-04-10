'use client'

import { use, useState } from 'react'
import { useRouter } from 'next/navigation'

import api from "@/lib/api/api";

export default ({ params }: { params: Promise<{ id: string }> }) =>  {
  const { id } = use(params)
  const router = useRouter()

  const [err, setErr] = useState<string|null>(null)

  const handleStart = async () => {
	try {
	  api.room.start(id);

	  r(id);
	} catch (e) {
	  setErr(e.message)
	}
  };

  // Can't use redirect here bc next sucks ass
  const r = (id) => {
	router.push(`/game/${id}`)
  }

  const handleCopy = () => {
  	navigator.clipboard.writeText(id);
  }

  return <div className="flex flex-col items-center justify-end h-screen">
	  {err}
	  <br />
	  {id}
	  <button onClick={handleCopy}>copy id</button>
	  <button onClick={handleStart}>start</button>
  </div>
}
