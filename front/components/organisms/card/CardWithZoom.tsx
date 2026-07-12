"use client";

import { useState, useEffect } from "react";
import { createPortal } from "react-dom";
import Card from "@/components/molecules/Card";
import InteractiveCard from "@/components/molecules/InteractiveCard";
import { BasicCard } from "@/lib/cards/types/card";
import { CardSize } from "@/constants/card";

export type CardWithZoomProps = {
  card: BasicCard;
  size?: CardSize;
  zoomOnSingleClick?: boolean;
};

export default function CardWithZoom({
  card,
  size = CardSize.MD,
  zoomOnSingleClick = false,
}: CardWithZoomProps) {
  const [isZoomed, setIsZoomed] = useState(false);

  useEffect(() => {
    if (!isZoomed) {
      return;
    }

    const handleEscape = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setIsZoomed(false);
      }
    };

    window.addEventListener("keydown", handleEscape);
    return () => window.removeEventListener("keydown", handleEscape);
  }, [isZoomed]);

  return (
    <>
      <div
        className="cursor-pointer"
        onClick={(event) => {
          if (!zoomOnSingleClick) {
            return;
          }

          event.stopPropagation();
          setIsZoomed(true);
        }}
        onDoubleClick={(event) => {
          event.stopPropagation();
          setIsZoomed(true);
        }}
      >
        <Card card={card} size={size} showLoadingUntilReady />
      </div>

      {isZoomed &&
        createPortal(
          <div
            className="fixed inset-0 z-1000 flex items-center justify-center bg-black/70 p-4"
            onClick={() => setIsZoomed(false)}
          >
            <div onClick={(event) => event.stopPropagation()}>
              <InteractiveCard
                card={card}
                size={CardSize.XLL}
                showLoadingUntilReady
              />
            </div>
          </div>,
          document.body,
        )}
    </>
  );
}
