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
    <div
      className={`${sizeClass} aspect-card rounded-sm overflow-hidden ${className}`}
    >
      <Image
        src="/card/card_back.png"
        alt="Card back"
        fill
        className="object-cover"
      />
    </div>
  );
}
