"use client";

import { useState, useEffect, useRef } from "react";
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
  const [isTouchOpenedZoom, setIsTouchOpenedZoom] = useState(false);
  const lastTouchTapAtRef = useRef(0);
  const openedAtRef = useRef(0);

  const openZoom = (
    event?: { stopPropagation: () => void },
    openedByTouch = false,
  ) => {
    event?.stopPropagation();
    setIsTouchOpenedZoom(openedByTouch);
    openedAtRef.current = Date.now();
    setIsZoomed(true);
  };

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

          openZoom(event, false);
        }}
        onDoubleClick={(event) => {
          openZoom(event, false);
        }}
        onPointerUp={(event) => {
          if (event.pointerType !== "touch") {
            return;
          }

          const now = Date.now();
          const elapsed = now - lastTouchTapAtRef.current;
          lastTouchTapAtRef.current = now;

          if (elapsed > 0 && elapsed <= 320) {
            event.preventDefault();
            openZoom(event, true);
          }
        }}
      >
        <Card card={card} size={size} showLoadingUntilReady />
      </div>

      {isZoomed &&
        createPortal(
          <div
            className="fixed inset-0 z-1000 flex items-center justify-center bg-black/70 p-4"
            onClick={() => {
              if (Date.now() - openedAtRef.current < 200) {
                return;
              }

              setIsTouchOpenedZoom(false);
              setIsZoomed(false);
            }}
          >
            <div onClick={(event) => event.stopPropagation()}>
              <InteractiveCard
                card={card}
                size={CardSize.XLL}
                showLoadingUntilReady
                disableFlip={isTouchOpenedZoom}
              />
            </div>
          </div>,
          document.body,
        )}
    </>
  );
}
