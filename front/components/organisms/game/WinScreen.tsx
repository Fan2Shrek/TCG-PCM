"use client";

import { useEffect, useState } from "react";
import Confetti from "react-confetti";
import { Button } from "@/components/ui/button";
import { FaHouse } from "react-icons/fa6";
import Image from "@/components/atoms/Image";

type WinScreenProps = {
  winnerName: string;
  userName: string;
  onBackHome: () => void;
};

export default function WinScreen({
  winnerName,
  userName,
  onBackHome,
}: WinScreenProps) {
  const [size, setSize] = useState({ width: 0, height: 0 });

  useEffect(() => {
    const updateSize = () => {
      setSize({ width: window.innerWidth, height: window.innerHeight });
    };

    updateSize();
    window.addEventListener("resize", updateSize);

    return () => window.removeEventListener("resize", updateSize);
  }, []);

  return (
    <div className="fixed inset-0 z-1000 flex items-center justify-center bg-black/70 p-4">
      <Confetti
        width={size.width}
        height={size.height}
        numberOfPieces={300}
        recycle={false}
        gravity={0.2}
        confettiSource={{
          x: 0,
          y: -50,
          w: size.width / 3,
          h: 10,
        }}
      />
      <Confetti
        width={size.width}
        height={size.height}
        numberOfPieces={300}
        recycle={false}
        gravity={0.2}
        confettiSource={{
          x: (size.width * 2) / 3,
          y: -50,
          w: size.width / 3,
          h: 10,
        }}
      />

      <div className="relative flex flex-col z-10 max-w-3xl items-center gap-3 rounded-3xl border-4 border-white bg-gold p-4 shadow-[var(--sticker-shadow-lg)] text-ink-outline md:gap-6 md:p-8">
        <div className="flex flex-row items-center justify-center gap-6 text-center">
          <Image
            src="/isaac-tboi.gif"
            alt="Isaac animation"
            width={100}
            height={100}
            className="md:block"
          />
          <h2 className="font-display text-3xl font-extrabold text-ink-outline md:text-5xl">
            {winnerName === userName
              ? "Vous avez gagné !"
              : `${winnerName} a gagné !`}
          </h2>
          <Image
            src="/isaac-tboi.gif"
            alt="Isaac animation"
            width={100}
            height={100}
            className="md:block"
          />
        </div>
        <Button size="lg" variant="secondary" onClick={onBackHome}>
          <FaHouse />
          Retour à l&apos;accueil
        </Button>
      </div>
    </div>
  );
}
