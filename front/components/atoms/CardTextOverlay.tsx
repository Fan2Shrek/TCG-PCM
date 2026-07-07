"use client";

import { CSSProperties, useRef } from "react";
import useFitText from "use-fit-text";
import { CardType } from "@/constants/card";
import { convertDescriptions } from "@/lib/game/cardUtils";

type ZoneConfig = {
  x?: number;
  y?: number;
  width?: number;
  height?: number;
};

export type CardTextOverlayProps = {
  cardTitle: string;
  cardDescription: string;
  cardType: CardType;
  cardStats: { hp?: number; attack?: number; cost?: number };
};

const CardTextOverlay = ({
  cardTitle,
  cardDescription,
  cardType,
  cardStats,
}: CardTextOverlayProps) => {
  // Positioning & size for each text block
  const headerConfig: ZoneConfig = {
    y: 4,
    height: 10,
    width: 82,
  };
  const statsConfig: ZoneConfig = {
    y: 48.5,
    height: 12,
    width: cardType === CardType.MONSTER ? 58 : 20,
  };
  const descriptionConfig: ZoneConfig = {
    y: cardType === CardType.CHARACTER ? 54 : 60,
    width: 90,
    height: cardType === CardType.CHARACTER ? 32 : 37,
  };
  const shouldShowStats = cardType !== CardType.CHARACTER;

  const { fontSize: titleFontSize, ref: titleFitRef } = useFitText({
    onFinish: () => undefined,
  });

  const { fontSize: statsFontSize, ref: statsFitRef } = useFitText({
    onFinish: () => undefined,
  });

  const { fontSize: descriptionFontSize, ref: descriptionFitRef } = useFitText({
    onFinish: () => undefined,
  });

  const getZoneStyle = (config?: ZoneConfig): CSSProperties => {
    const baseStyle: CSSProperties = {
      width: config?.width !== undefined ? `${config.width}%` : undefined,
      height: config?.height !== undefined ? `${config.height}%` : undefined,
      top: config?.y !== undefined ? `${config.y}%` : undefined,
    };

    return baseStyle;
  };

  return (
    <div className="absolute inset-0 overflow-hidden font-pixel text-black">
      <div
        ref={titleFitRef}
        className="header-zone absolute overflow-hidden leading-tight text-center left-1/2 -translate-x-1/2"
        style={{ ...getZoneStyle(headerConfig), fontSize: titleFontSize }}
      >
        {cardTitle}
      </div>

      {shouldShowStats && (
        <div
          ref={statsFitRef}
          className="stats-zone absolute left-1/2 -translate-x-1/2"
          style={{ ...getZoneStyle(statsConfig), fontSize: statsFontSize }}
        >
          <div className="flex flex-row gap-px justify-center items-center">
            {cardType === CardType.MONSTER &&
              cardStats.hp &&
              cardStats.attack && (
                <>
                  <span>{cardStats.hp}❤️</span>
                  <span>{cardStats.attack}⚔️</span>
                </>
              )}
            {cardStats.cost !== undefined && <span>{cardStats.cost}🪙</span>}
          </div>
        </div>
      )}

      <div
        ref={descriptionFitRef}
        className="description-zone absolute leading-tight text-center left-1/2 -translate-x-1/2 gap-x-1"
        style={{
          ...getZoneStyle(descriptionConfig),
          fontSize: descriptionFontSize,
        }}
      >
        {cardDescription && convertDescriptions(cardDescription)}
      </div>
    </div>
  );
};

export default CardTextOverlay;
