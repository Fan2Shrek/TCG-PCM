"use server";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";

import { serverApiPost } from "@/lib/api/server";
import { SESSION_COOKIE } from "@/lib/auth/constants";

export type AuthActionState = {
  error: string | null;
};

const JWT_COOKIE_MAX_AGE = 60 * 60 * 24;

async function setSessionCookie(token: string) {
  const store = await cookies();
  store.set(SESSION_COOKIE, token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax",
    path: "/",
    maxAge: JWT_COOKIE_MAX_AGE,
  });
}

async function login(username: string, password: string): Promise<string> {
  const response = await serverApiPost<{ token: string }>("/login_check", {
    username,
    password,
  });
  return response.token;
}

export async function loginAction(
  _prevState: AuthActionState,
  formData: FormData,
): Promise<AuthActionState> {
  const username = String(formData.get("username") || "");
  const password = String(formData.get("password") || "");

  let token: string;
  try {
    token = await login(username, password);
  } catch (err) {
    return { error: err instanceof Error ? err.message : "mdr ca a explosé" };
  }

  await setSessionCookie(token);
  redirect("/boosters");
}

export async function registerAction(
  _prevState: AuthActionState,
  formData: FormData,
): Promise<AuthActionState> {
  const username = String(formData.get("username") || "");
  const password = String(formData.get("password") || "");
  const confirmPassword = String(formData.get("confirmPassword") || "");

  if (password !== confirmPassword) {
    return { error: "Les mots de passe ne correspondent pas." };
  }

  let token: string;
  try {
    await serverApiPost("/register", { username, password });
    token = await login(username, password);
  } catch (err) {
    return {
      error: err instanceof Error ? err.message : "Une erreur est survenue.",
    };
  }

  await setSessionCookie(token);
  redirect("/boosters");
}

export async function logoutAction(): Promise<void> {
  const store = await cookies();
  store.delete(SESSION_COOKIE);
  store.delete("mercureAuthorization");
  redirect("/login");
}
