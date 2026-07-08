"use server";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";

import { serverApiPost } from "@/lib/api/server";

export async function joinRoomAction(roomId: string): Promise<void> {
  const response = await serverApiPost<{ mercure_token: string }>(`/rooms/${roomId}/join`);

  const store = await cookies();
  store.set("mercureAuthorization", response.mercure_token, {
    path: "/",
    maxAge: 60 * 60,
    secure: process.env.NODE_ENV === "production",
    sameSite: "strict",
  });

  redirect(`/arene/waiting/${roomId}`);
}
