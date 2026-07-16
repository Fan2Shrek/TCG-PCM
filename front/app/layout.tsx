import type { Metadata } from "next";
import localFont from "next/font/local";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { Toaster } from "sonner";
import CookieConsentBanner from "@/components/organisms/layout/CookieConsentBanner";
import { BadgesProvider } from "@/contexts/BadgesContext";
import { BoosterTokensProvider } from "@/contexts/BoosterTokensContext";
import { FriendshipProvider } from "@/contexts/FriendshipContext";
import { RoomProvider } from "@/contexts/RoomContext";
import { TradeInviteProvider } from "@/contexts/TradeInviteContext";
import { getCurrentUser } from "@/lib/auth/session";
import { serverApiGet } from "@/lib/api/server";
import { Room } from "@/types/room";

const geistSans = Geist({
  variable: "--font-geist-sans",
  display: "swap",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  display: "swap",
  subsets: ["latin"],
});

const geistPixel = localFont({
  src: "../public/fonts/GeistPixel.ttf",
  variable: "--font-geist-pixel",
  display: "swap",
});

export const metadata: Metadata = {
  title: "Official TCG et tout",
  description: "Bla bla bla",
};

async function getInitialRoom(isAuthenticated: boolean): Promise<Room | null> {
  if (!isAuthenticated) {
    return null;
  }

  try {
    const response = await serverApiGet<Room[] | Room | null>("/me/room");
    return Array.isArray(response) ? (response[0] ?? null) : (response ?? null);
  } catch {
    return null;
  }
}

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const user = await getCurrentUser();
  const isAuthenticated = Boolean(user);
  const initialRoom = await getInitialRoom(isAuthenticated);

  return (
    <html
      lang="fr"
      className={`${geistSans.variable} ${geistMono.variable} ${geistPixel.variable}`}
    >
      <body className={`antialiased bg-background bg-fixed`}>
        <Toaster
          toastOptions={{
            classNames: {
              toast:
                "rounded-2xl! border-2! border-ink-outline! bg-card! shadow-[var(--sticker-shadow)]!",
              title: "font-display! font-extrabold!",
              actionButton:
                "rounded-full! bg-primary! text-primary-foreground!",
              cancelButton: "rounded-full!",
            },
          }}
        />
        <RoomProvider initialRoom={initialRoom} enabled={isAuthenticated}>
          <BoosterTokensProvider enabled={isAuthenticated}>
            <BadgesProvider enabled={isAuthenticated}>
              <FriendshipProvider enabled={isAuthenticated}>
                <TradeInviteProvider enabled={isAuthenticated}>
                  {children}
                </TradeInviteProvider>
              </FriendshipProvider>
            </BadgesProvider>
          </BoosterTokensProvider>
        </RoomProvider>
        <CookieConsentBanner />
      </body>
    </html>
  );
}
