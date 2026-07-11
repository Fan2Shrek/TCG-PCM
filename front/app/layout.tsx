import type { Metadata } from "next";
import localFont from "next/font/local";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { Toaster } from "sonner";
import { BoosterTokensProvider } from "@/contexts/BoosterTokensContext";
import { RoomProvider } from "@/contexts/RoomContext";
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

async function getInitialRoom(): Promise<Room | null> {
  const user = await getCurrentUser();

  if (!user) {
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
  const initialRoom = await getInitialRoom();

  return (
    <html
      lang="fr"
      className={`${geistSans.variable} ${geistMono.variable} ${geistPixel.variable}`}
    >
      <body className={`antialiased bg-background`}>
        <Toaster />
        <RoomProvider initialRoom={initialRoom}>
          <BoosterTokensProvider>{children}</BoosterTokensProvider>
        </RoomProvider>
      </body>
    </html>
  );
}
