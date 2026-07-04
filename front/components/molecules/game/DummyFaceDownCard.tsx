"use client";

import Image from "next/image";
import { CardSize, CardSizeMap } from "@/constants/card";

type DummyFaceDownCardProps = {
  size?: CardSize;
  className?: string;
};

export default function DummyFaceDownCard({
  size = CardSize.MD,
  className = "",
}: DummyFaceDownCardProps) {
  const sizeClass = CardSizeMap[size];

  return (
    <div className={`${sizeClass} aspect-card ${className}`}>
      <Image
        src="/default_card_back.png"
        alt="Card back"
        fill
        style={{
          objectFit: "cover",
        }}
      />
    </div>
  );
}
