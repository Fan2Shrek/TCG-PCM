'use client';

import api from "@/lib/api/api";
import { redirect, RedirectType } from 'next/navigation'

export default () => {
	const handleCreate = async () => {
		const res = await api.room.create();
		document.cookie = `mercureAuthorization=${res.mercure_token}; path=/; max-age=3600; secure; samesite=strict`;

		redirect(`/arene/waiting/${res.id}`, RedirectType.replace)
	}

	return <div className="flex flex-col items-center justify-center h-screen">
		<a className="pt-40" onClick={handleCreate}>create</a>
	</div>
}
