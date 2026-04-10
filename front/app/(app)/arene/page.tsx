'use client';

import api from "@/lib/api/api";
import { redirect, RedirectType } from 'next/navigation'

export default () => {
	const handleCreate = async () => {
		const res = await api.room.create();

		redirect(`/arene/waiting/${res.id}`, RedirectType.replace)
	}

	return <div className="flex flex-col items-center justify-center h-screen">
		<a className="pt-40" onClick={handleCreate}>create</a>
	</div>
}
