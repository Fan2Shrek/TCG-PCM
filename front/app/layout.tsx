import type { Metadata } from "next";
import localFont from "next/font/local";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { Toaster } from "sonner";
import { BoosterTokensProvider } from "@/contexts/BoosterTokensContext";
import { RoomProvider } from "@/contexts/RoomContext";

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

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html
      lang="fr"
      className={`${geistSans.variable} ${geistMono.variable} ${geistPixel.variable}`}
    >
      <body className={`antialiased bg-background`}>
        <Toaster />
        <RoomProvider>
          <BoosterTokensProvider>{children}</BoosterTokensProvider>
        </RoomProvider>
      </body>
    </html>
  );
}
