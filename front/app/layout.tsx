import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import PendingBoosters from "@/components/organisms/layout/PendingBoosters";
import Menu from "@/components/organisms/menu/Menu";
import Image from "@/components/atoms/Image";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
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

  const logoPath = "menu/logo.png";

  return (
    <html lang="en">
      <body
        className={`${geistSans.variable} ${geistMono.variable} antialiased bg-background`}
      >
        <div className="hidden md:grid grid-cols-3 items-center fixed w-full pt-3 px-5 z-10">
          <PendingBoosters className="justify-self-start" />
          <Image src={logoPath} alt="Logo" width={275} height={275} className="justify-self-center" />
          <Menu className="justify-self-end"/>
        </div>
        {children}
      </body>
    </html>
  );
}
