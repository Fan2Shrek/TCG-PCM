"use client";

import { useEffect, useRef } from "react";
import fitty from "fitty";
import { CardType } from "@/constants/card";
import { convertDescriptions } from "@/lib/game/cardUtils";

enum TextAlign {
  LEFT = "left",
  CENTER = "center",
  RIGHT = "right",
}

type ZoneConfig = {
  x?: number;
  y?: number;
  width?: number;
  height?: number;
  align?: TextAlign;
};

export type CardTextOverlayProps = {
  cardTitle: string;
  cardDescription: string;
  cardType?: string;
  cardStats: { hp?: number; attack?: number; cost?: number };
};

const CardTextOverlay = ({ cardTitle, cardDescription, cardType, cardStats }: CardTextOverlayProps) => {
  const headerConfig: ZoneConfig = { y: 8, align: TextAlign.CENTER, height: 9, width: 80 };
  const statsConfig: ZoneConfig = { y: 0, align: TextAlign.CENTER };
  const descriptionConfig: ZoneConfig = { y: 0, align: TextAlign.CENTER };
  const headerRef = useRef<HTMLDivElement>(null);
  const statsRef = useRef<HTMLDivElement>(null);
  const descriptionRef = useRef<HTMLDivElement>(null);

  const shouldShowStats = cardType && cardType !== CardType.CHARACTER;

  useEffect(() => {
    const instances: unknown[] = [];

    if (headerRef.current) {
      instances.push(fitty(headerRef.current, { minSize: 8, maxSize: 16 }));
    }

    if (shouldShowStats && statsRef.current) {
      instances.push(fitty(statsRef.current, { minSize: 8, maxSize: 16 }));
    }

    if (descriptionRef.current) {
      instances.push(fitty(descriptionRef.current, { minSize: 8, maxSize: 16 }));
    }

    return () => {
      instances.forEach((instance: unknown) => {
        const fittyInstance = instance as { unsubscribe?: () => void };
        fittyInstance.unsubscribe?.();
      });
    };
  }, [shouldShowStats]);

  const getZoneStyle = (config?: ZoneConfig) => ({
    transform: config?.x !== undefined || config?.y !== undefined ? `translate(${config?.x ?? 0}px, ${config?.y ?? 0}px)` : undefined,
    width: config?.width ? `${config.width}%` : undefined,
    height: config?.height ? `${config.height}%` : undefined,
  });

  const getZoneClass = (config?: ZoneConfig) => {
    const textAlignMap = {
      [TextAlign.LEFT]: "text-left",
      [TextAlign.CENTER]: "text-center",
      [TextAlign.RIGHT]: "text-right",
    };

    const positionMap = {
      [TextAlign.LEFT]: "",
      [TextAlign.CENTER]: "mx-auto",
      [TextAlign.RIGHT]: "ml-auto",
    };

    return `${textAlignMap[config?.align ?? TextAlign.CENTER]} ${positionMap[config?.align ?? TextAlign.CENTER]}`;
  };

  return (
    <div className='absolute inset-0 flex flex-col font-pixel text-black'>
      <div ref={headerRef} className={`header-zone overflow-hidden ${getZoneClass(headerConfig)}`} style={getZoneStyle(headerConfig)}>
        {cardTitle}
      </div>

      {shouldShowStats && (
        <div ref={statsRef} className={`stats-zone my-2 flex flex-col gap-1 ${getZoneClass(statsConfig)}`} style={getZoneStyle(statsConfig)}>
          {cardType === CardType.MONSTER && cardStats.hp && cardStats.attack && (
            <>
              <div>❤️ {cardStats.hp}</div>
              <div>⚔️ {cardStats.attack}</div>
            </>
          )}
          {cardStats.cost !== undefined && <div>🪙 {cardStats.cost}</div>}
        </div>
      )}

      <div ref={descriptionRef} className={`description-zone ${getZoneClass(descriptionConfig)}`} style={getZoneStyle(descriptionConfig)}>
        {cardDescription && convertDescriptions(cardDescription)}
      </div>
    </div>
  );
};

export default CardTextOverlay;
