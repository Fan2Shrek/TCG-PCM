import type { Metadata } from "next";
import localFont from "next/font/local";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { Toaster } from "sonner";
import CookieConsentBanner from "@/components/organisms/layout/CookieConsentBanner";

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

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
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
        {children}
        <CookieConsentBanner />
      </body>
    </html>
  );
}
