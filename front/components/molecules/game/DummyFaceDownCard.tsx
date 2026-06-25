"use client";

import Image from "next/image";
import { CardSize, CardSizeMap } from "@/constants/card";

type DummyFaceDownCardProps = {
  size?: CardSize;
  id: string;
};

export default function DummyFaceDownCard({ size = CardSize.MD, id }: DummyFaceDownCardProps) {
  const sizeClass = CardSizeMap[size];

  return (
    <div className={`${sizeClass} aspect-card rounded-lg overflow-hidden`}>
      <Image
        key={id}
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
