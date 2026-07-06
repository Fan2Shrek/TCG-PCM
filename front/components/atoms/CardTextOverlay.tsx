"use client";

import { CSSProperties, useRef } from "react";
import useFitText from "use-fit-text";
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
  cardType?: CardType;
  cardStats: { hp?: number; attack?: number; cost?: number };
};

const CardTextOverlay = ({
  cardTitle,
  cardDescription,
  cardType,
  cardStats,
}: CardTextOverlayProps) => {
  // Positioning & box for each text block
  const headerConfig: ZoneConfig = {
    y: 4,
    align: TextAlign.CENTER,
    height: 10,
    width: 82,
  };
  const statsConfig: ZoneConfig = {
    y: 49,
    align: TextAlign.CENTER,
    height: 12,
    width: cardType === CardType.MONSTER ? 58 : 20,
  };
  const descriptionConfig: ZoneConfig = {
    y: cardType === CardType.CHARACTER ? 55 : 62,
    align: TextAlign.CENTER,
    width: 90,
    height: cardType === CardType.CHARACTER ? 32 : 35,
  };
  const shouldShowStats = cardType && cardType !== CardType.CHARACTER;

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
    const align = config?.align ?? TextAlign.CENTER;

    const baseStyle: CSSProperties = {
      width: config?.width !== undefined ? `${config.width}%` : undefined,
      height: config?.height !== undefined ? `${config.height}%` : undefined,
      top: config?.y !== undefined ? `${config.y}%` : undefined,
      display: "flex",
      flexDirection: "column",
      justifyContent: "center",
    };

    if (align === TextAlign.CENTER) {
      baseStyle.left = "50%";
      baseStyle.transform = "translateX(-50%)";
    } else if (align === TextAlign.RIGHT) {
      baseStyle.left = "auto";
      baseStyle.right = "0%";
      baseStyle.transform = undefined;
    } else {
      baseStyle.left = "0%";
      baseStyle.transform = undefined;
    }

    return baseStyle;
  };

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
    <div className="absolute inset-0 overflow-hidden font-pixel text-black">
      <div
        ref={titleFitRef}
        className={`header-zone absolute overflow-hidden leading-tight ${getZoneClass(headerConfig)}`}
        style={{ ...getZoneStyle(headerConfig), fontSize: titleFontSize }}
      >
        {cardTitle}
      </div>

      {shouldShowStats && (
        <div
          ref={statsFitRef}
          className={`stats-zone absolute flex flex-col items-center gap-1 leading-tight ${getZoneClass(statsConfig)}`}
          style={{ ...getZoneStyle(statsConfig), fontSize: statsFontSize }}
        >
          {cardType === CardType.MONSTER &&
            cardStats.hp &&
            cardStats.attack && (
              <>
                <div className="w-full whitespace-nowrap text-center">
                  {cardStats.hp}❤️
                </div>
                <div className="w-full whitespace-nowrap text-center">
                  {cardStats.attack}⚔️
                </div>
              </>
            )}
          {cardStats.cost !== undefined && (
            <div className="w-full whitespace-nowrap text-center">
              {cardStats.cost}🪙
            </div>
          )}
        </div>
      )}

      <div
        ref={descriptionFitRef}
        className={`description-zone absolute leading-tight ${getZoneClass(descriptionConfig)}`}
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
