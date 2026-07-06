"use client";

import { CSSProperties, useEffect, useRef } from "react";
import { CardType } from "@/constants/card";
import { convertDescriptions } from "@/lib/game/cardUtils";

enum TextAlign {
  LEFT = "left",
  CENTER = "center",
  RIGHT = "right",
}

enum TextZoneMode {
  TITLE = "title",
  STATS = "stats",
  DESCRIPTION = "description",
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
  const headerRef = useRef<HTMLDivElement>(null);
  const statsRef = useRef<HTMLDivElement>(null);
  const descriptionRef = useRef<HTMLDivElement>(null);

  const shouldShowStats = cardType && cardType !== CardType.CHARACTER;

  useEffect(() => {
    const applyZoneTypography = (
      element: HTMLDivElement | null,
      mode: TextZoneMode,
    ) => {
      if (!element) {
        return;
      }

      const rect = element.getBoundingClientRect();
      const width = rect.width;
      const height = rect.height;

      const fontConfig = {
        [TextZoneMode.TITLE]: {
          min: 6,
          max: 18,
          widthFactor: 0.16,
          heightFactor: 0.8,
        },
        [TextZoneMode.STATS]: {
          min: 18,
          max: 18,
          widthFactor: 0.18,
          heightFactor: 0.7,
        },
        [TextZoneMode.DESCRIPTION]: {
          min: 6,
          max: 16,
          widthFactor: 0.14,
          heightFactor: 0.24,
        },
      }[mode];

      const fontSize = Math.min(
        fontConfig.max,
        Math.max(
          fontConfig.min,
          Math.min(
            width * fontConfig.widthFactor,
            height * fontConfig.heightFactor,
          ),
        ),
      );

      const overflowed =
        element.scrollHeight > element.clientHeight ||
        element.scrollWidth > element.clientWidth;
      const resolvedFontSize = Math.max(fontSize, fontConfig.min);

      element.style.fontSize = `${resolvedFontSize}px`;
      element.style.lineHeight = "1.05";
      element.style.whiteSpace =
        mode === TextZoneMode.STATS ? "nowrap" : "normal";
      element.style.overflowWrap =
        mode === TextZoneMode.STATS ? "normal" : "anywhere";
      element.style.wordBreak =
        mode === TextZoneMode.STATS ? "normal" : "break-word";
    };

    const elements = [
      { element: headerRef.current, mode: TextZoneMode.TITLE },
      {
        element: shouldShowStats ? statsRef.current : null,
        mode: TextZoneMode.STATS,
      },
      { element: descriptionRef.current, mode: TextZoneMode.DESCRIPTION },
    ];

    const applyStyles = () => {
      elements.forEach(({ element, mode }) =>
        applyZoneTypography(element, mode),
      );
    };

    const resizeObserver = new ResizeObserver(applyStyles);

    elements.forEach(({ element }) => {
      if (element) {
        resizeObserver.observe(element);
      }
    });

    const frame = window.requestAnimationFrame(applyStyles);

    return () => {
      window.cancelAnimationFrame(frame);
      resizeObserver.disconnect();
    };
  }, [
    cardTitle,
    cardDescription,
    cardType,
    cardStats.hp,
    cardStats.attack,
    cardStats.cost,
    shouldShowStats,
  ]);

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
        ref={headerRef}
        className={`header-zone absolute overflow-hidden wrap-break-word whitespace-normal leading-tight ${getZoneClass(headerConfig)}`}
        style={getZoneStyle(headerConfig)}
      >
        {cardTitle}
      </div>

      {shouldShowStats && (
        <div
          ref={statsRef}
          className={`stats-zone absolute flex flex-col items-center gap-1 leading-tight ${getZoneClass(statsConfig)}`}
          style={getZoneStyle(statsConfig)}
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
        ref={descriptionRef}
        className={`description-zone absolute wrap-break-word whitespace-normal leading-tight ${getZoneClass(descriptionConfig)}`}
        style={getZoneStyle(descriptionConfig)}
      >
        {cardDescription && convertDescriptions(cardDescription)}
      </div>
    </div>
  );
};

export default CardTextOverlay;
