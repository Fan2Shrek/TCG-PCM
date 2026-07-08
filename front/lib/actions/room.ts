"use server";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";

import { serverApiPost } from "@/lib/api/server";

async function setMercureCookie(token: string) {
  const store = await cookies();
  store.set("mercureAuthorization", token, {
    path: "/",
    maxAge: 60 * 60,
    secure: process.env.NODE_ENV === "production",
    sameSite: "strict",
  });
}

export async function createRoomAction(): Promise<void> {
  const response = await serverApiPost<{ id: string; mercure_token: string }>("/rooms/create");

  await setMercureCookie(response.mercure_token);

  redirect(`/arene/waiting/${response.id}`);
}

export async function joinRoomAction(roomId: string): Promise<void> {
  const response = await serverApiPost<{ mercure_token: string }>(`/rooms/${roomId}/join`);

  await setMercureCookie(response.mercure_token);

  redirect(`/arene/waiting/${roomId}`);
}

export async function startRoomAction(roomId: string): Promise<void> {
  await serverApiPost(`/rooms/${roomId}/start`);

  redirect(`/game/${roomId}`);
}
