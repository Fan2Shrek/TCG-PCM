"use server";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";

import { serverApiPost } from "@/lib/api/server";
import { PASSWORD_EXPIRED_COOKIE, SESSION_COOKIE } from "@/lib/auth/constants";

export type AuthActionState = {
  error: string | null;
  message?: string;
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

async function setPasswordExpiredCookie(expired: boolean) {
  const store = await cookies();
  if (!expired) {
    store.delete(PASSWORD_EXPIRED_COOKIE);
    return;
  }

  store.set(PASSWORD_EXPIRED_COOKIE, "1", {
    httpOnly: true,
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax",
    path: "/",
    maxAge: JWT_COOKIE_MAX_AGE,
  });
}

async function login(
  username: string,
  password: string,
): Promise<{ token: string; passwordExpired: boolean }> {
  const response = await serverApiPost<{
    token: string;
    password_expired: boolean;
  }>("/login_check", { username, password });

  return { token: response.token, passwordExpired: response.password_expired };
}

export async function loginAction(
  _prevState: AuthActionState,
  formData: FormData,
): Promise<AuthActionState> {
  const username = String(formData.get("username") || "");
  const password = String(formData.get("password") || "");

  let token: string;
  let passwordExpired: boolean;
  try {
    ({ token, passwordExpired } = await login(username, password));
  } catch (err) {
    return { error: err instanceof Error ? err.message : "mdr ca a explosé" };
  }

  await setSessionCookie(token);
  await setPasswordExpiredCookie(passwordExpired);
  redirect(passwordExpired ? "/change-password" : "/boosters");
}

export async function registerAction(
  _prevState: AuthActionState,
  formData: FormData,
): Promise<AuthActionState> {
  const username = String(formData.get("username") || "");
  const email = String(formData.get("email") || "");
  const password = String(formData.get("password") || "");
  const confirmPassword = String(formData.get("confirmPassword") || "");

  if (password !== confirmPassword) {
    return { error: "Les mots de passe ne correspondent pas." };
  }

  let token: string;
  try {
    await serverApiPost("/register", { username, email, password });
    ({ token } = await login(username, password));
  } catch (err) {
    return {
      error: err instanceof Error ? err.message : "Une erreur est survenue.",
    };
  }

  await setSessionCookie(token);
  redirect("/boosters");
}

export async function forgotPasswordAction(
  _prevState: AuthActionState,
  formData: FormData,
): Promise<AuthActionState> {
  const email = String(formData.get("email") || "");

  try {
    await serverApiPost("/forgot-password", { email });
  } catch {
    // Always report success: the API itself never reveals whether the email exists.
  }

  return {
    error: null,
    message:
      "Si un compte existe avec cet email, un lien de réinitialisation vient de lui être envoyé.",
  };
}

export async function resetPasswordAction(
  _prevState: AuthActionState,
  formData: FormData,
): Promise<AuthActionState> {
  const token = String(formData.get("token") || "");
  const newPassword = String(formData.get("newPassword") || "");
  const confirmPassword = String(formData.get("confirmPassword") || "");

  if (newPassword !== confirmPassword) {
    return { error: "Les mots de passe ne correspondent pas." };
  }

  try {
    await serverApiPost("/reset-password", { token, newPassword });
  } catch (err) {
    return {
      error: err instanceof Error ? err.message : "Une erreur est survenue.",
    };
  }

  redirect("/login");
}

export async function changePasswordAction(
  _prevState: AuthActionState,
  formData: FormData,
): Promise<AuthActionState> {
  const currentPassword = String(formData.get("currentPassword") || "");
  const newPassword = String(formData.get("newPassword") || "");
  const confirmPassword = String(formData.get("confirmPassword") || "");

  if (newPassword !== confirmPassword) {
    return { error: "Les mots de passe ne correspondent pas." };
  }

  try {
    await serverApiPost("/change-password", { currentPassword, newPassword });
  } catch (err) {
    return {
      error: err instanceof Error ? err.message : "Une erreur est survenue.",
    };
  }

  await setPasswordExpiredCookie(false);
  redirect("/boosters");
}

export async function logoutAction(): Promise<void> {
  const store = await cookies();
  store.delete(SESSION_COOKIE);
  store.delete(PASSWORD_EXPIRED_COOKIE);
  store.delete("mercureAuthorization");
  redirect("/login");
}
