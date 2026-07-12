import { NextRequest, NextResponse } from "next/server";

import { PASSWORD_EXPIRED_COOKIE, SESSION_COOKIE } from "@/lib/auth/constants";

// Pages accessibles sans session, en plus de rester consultables une fois connecté.
const PUBLIC_PATHS = ["/how-to-play"];
// Pages réservées aux visiteurs non connectés (redirigées vers /boosters une fois connecté).
const GUEST_ONLY_PATHS = ["/login", "/register", "/forgot-password", "/reset-password"];
// Page toujours accessible une fois connecté, même avec un mot de passe expiré.
const CHANGE_PASSWORD_PATH = "/change-password";

export function proxy(request: NextRequest) {
  const { pathname } = request.nextUrl;

  const hasSession = request.cookies.has(SESSION_COOKIE);
  const isPublicPath = PUBLIC_PATHS.includes(pathname);
  const isGuestOnlyPath = GUEST_ONLY_PATHS.includes(pathname);

  if (!hasSession && !isPublicPath && !isGuestOnlyPath) {
    return NextResponse.redirect(new URL("/login", request.url));
  }

  if (hasSession && isGuestOnlyPath) {
    return NextResponse.redirect(new URL("/boosters", request.url));
  }

  if (
    hasSession &&
    request.cookies.has(PASSWORD_EXPIRED_COOKIE) &&
    pathname !== CHANGE_PASSWORD_PATH
  ) {
    return NextResponse.redirect(new URL(CHANGE_PASSWORD_PATH, request.url));
  }

  return NextResponse.next();
}

export const config = {
  // Les fichiers statiques de public/ (images, polices...) et les routes API doivent rester
  // accessibles sans redirection : une route API doit répondre en JSON, jamais par une
  // redirection HTML vers /login.
  matcher: ["/((?!api/|_next/static|_next/image|favicon.ico|.*\\.(?:png|jpg|jpeg|gif|webp|svg|ico|ttf|woff|woff2|pdf)$).*)"],
};
