"use client";

import {
  CSSProperties,
  useCallback,
  useContext,
  useEffect,
  useState,
} from "react";
import useFitText from "use-fit-text";
import { CardRaririty, CardType } from "@/constants/card";
import { convertDescriptions } from "@/lib/game/cardUtils";
import { GameContext } from "@/contexts/GameContext";

type ZoneConfig = {
  x?: number;
  y?: number;
  width?: number;
  height?: number;
};

const RARITY_LABEL_TEXT_OUTLINE: CSSProperties = {
  textShadow:
    "0.5px 0 0 rgba(255,255,255,0.9), -0.5px 0 0 rgba(255,255,255,0.9), 0 0.5px 0 rgba(255,255,255,0.9), 0 -0.5px 0 rgba(255,255,255,0.9)",
};

export type CardTextOverlayProps = {
  readinessKey: string;
  cardTitle: string;
  cardDescription: string;
  cardType: CardType;
  cardRarity: CardRaririty;
  cardStats: { hp?: number; attack?: number; cost?: number };
  onLayoutReady?: () => void;
};

const CARD_TYPE_LABELS: Record<CardType, string> = {
  [CardType.CHARACTER]: "PERSONNAGE",
  [CardType.MONSTER]: "MONSTRE",
  [CardType.PASSIVE]: "PASSIF",
  [CardType.CONSUMABLE]: "CONSUMABLE",
};

const CARD_RARITY_COLORS: Record<CardRaririty, string> = {
  [CardRaririty.COMMON]: "text-zinc-600",
  [CardRaririty.UNCOMMON]: "text-emerald-700",
  [CardRaririty.RARE]: "text-sky-700",
  [CardRaririty.EPIC]: "text-rose-700",
  [CardRaririty.LEGENDARY]: "text-amber-700",
};

const CardTextOverlay = ({
  readinessKey,
  cardTitle,
  cardDescription,
  cardType,
  cardRarity,
  cardStats,
  onLayoutReady,
}: CardTextOverlayProps) => {
  const { gameData } = useContext(GameContext);

  // Positioning & size for each text block
  const headerConfig: ZoneConfig = {
    y: 4,
    height: 10,
    width: 82,
  };
  const statsConfig: ZoneConfig = {
    y: 50,
    height: 10,
    width: cardType === CardType.MONSTER ? 58 : 20,
  };
  const descriptionConfig: ZoneConfig = {
    y: cardType === CardType.CHARACTER ? 54 : 60,
    width: 90,
    height: cardType === CardType.CHARACTER ? 34 : 28,
  };
  const shouldShowStats = cardType !== CardType.CHARACTER;
  const [isTitleReady, setIsTitleReady] = useState(false);
  const [isStatsReady, setIsStatsReady] = useState(!shouldShowStats);
  const [isDescriptionReady, setIsDescriptionReady] = useState(false);
  const [isTypeReady, setIsTypeReady] = useState(false);
  const [isRarityReady, setIsRarityReady] = useState(false);
  const [hasNotifiedReady, setHasNotifiedReady] = useState(false);

  const contentKey = [
    readinessKey,
    cardDescription,
    cardTitle,
    shouldShowStats,
    cardType,
    cardRarity,
    cardStats.attack,
    cardStats.cost,
    cardStats.hp,
  ].join("|");
  const [prevContentKey, setPrevContentKey] = useState(contentKey);

  // Resets readiness so the DOM measurement callbacks below can recompute it for the
  // new content, computed during render (see "Adjusting state in render" in the React docs).
  if (contentKey !== prevContentKey) {
    setPrevContentKey(contentKey);
    setHasNotifiedReady(false);
    setIsTitleReady(cardTitle.trim().length === 0);
    setIsStatsReady(!shouldShowStats);
    setIsDescriptionReady(cardDescription.trim().length === 0);
    setIsTypeReady(false);
    setIsRarityReady(false);
  }

  const isLayoutReady =
    isTitleReady &&
    isStatsReady &&
    isDescriptionReady &&
    isTypeReady &&
    isRarityReady;

  // Latches once all zones are ready, computed during render
  // (see "Adjusting state in render" in the React docs).
  if (isLayoutReady && !hasNotifiedReady) {
    setHasNotifiedReady(true);
  }

  useEffect(() => {
    if (hasNotifiedReady) {
      onLayoutReady?.();
    }
  }, [hasNotifiedReady, onLayoutReady]);

  const handleTitleFitFinished = useCallback(() => {
    setIsTitleReady(true);
  }, []);

  const handleStatsFitFinished = useCallback(() => {
    setIsStatsReady(true);
  }, []);

  const handleDescriptionFitFinished = useCallback(() => {
    setIsDescriptionReady(true);
  }, []);

  const handleTypeFitFinished = useCallback(() => {
    setIsTypeReady(true);
  }, []);

  const handleRarityFitFinished = useCallback(() => {
    setIsRarityReady(true);
  }, []);

  const { fontSize: titleFontSize, ref: titleFitRef } = useFitText({
    onFinish: handleTitleFitFinished,
    maxFontSize: 200,
  });

  const { fontSize: statsFontSize, ref: statsFitRef } = useFitText({
    onFinish: handleStatsFitFinished,
    maxFontSize: 200,
  });

  const { fontSize: descriptionFontSize, ref: descriptionFitRef } = useFitText({
    onFinish: handleDescriptionFitFinished,
    maxFontSize: 200,
  });

  const { fontSize: typeFontSize, ref: typeFitRef } = useFitText({
    onFinish: handleTypeFitFinished,
    maxFontSize: 200,
  });

  const { fontSize: rarityFontSize, ref: rarityFitRef } = useFitText({
    onFinish: handleRarityFitFinished,
    maxFontSize: 200,
  });

  const getZoneStyle = (config?: ZoneConfig): CSSProperties => {
    const baseStyle: CSSProperties = {
      width: config?.width !== undefined ? `${config.width}%` : undefined,
      height: config?.height !== undefined ? `${config.height}%` : undefined,
      top: config?.y !== undefined ? `${config.y}%` : undefined,
    };

    return baseStyle;
  };

  const statsKey = [cardStats.hp, cardStats.attack, cardStats.cost].join("-");

  return (
    <div className="absolute inset-0 overflow-hidden font-pixel text-black">
      <div
        key={cardTitle}
        ref={titleFitRef}
        className="header-zone absolute overflow-hidden leading-tight text-center left-1/2 -translate-x-1/2"
        style={{ ...getZoneStyle(headerConfig), fontSize: titleFontSize }}
      >
        {cardTitle}
      </div>

      {shouldShowStats && (
        <div
          key={statsKey}
          ref={statsFitRef}
          className="stats-zone absolute left-1/2 -translate-x-1/2"
          style={{ ...getZoneStyle(statsConfig), fontSize: statsFontSize }}
        >
          <div className="flex flex-row gap-px justify-center items-center">
            {cardType === CardType.MONSTER && cardStats.hp !== undefined && (
              <span>{cardStats.hp}❤️</span>
            )}
            {cardType === CardType.MONSTER &&
              cardStats.attack !== undefined && (
                <span>{cardStats.attack}⚔️</span>
              )}
            {cardStats.cost !== undefined && <span>{cardStats.cost}🪙</span>}
          </div>
        </div>
      )}

      <div
        key={cardDescription}
        ref={descriptionFitRef}
        className="description-zone absolute leading-tight text-center left-1/2 -translate-x-1/2 gap-x-1"
        style={{
          ...getZoneStyle(descriptionConfig),
          fontSize: `calc(${descriptionFontSize} * 0.93)`,
        }}
      >
        {cardDescription && convertDescriptions(cardDescription, gameData)}
      </div>

      <div
        key={cardType}
        ref={typeFitRef}
        className="absolute bottom-[4%] right-[6%] leading-none tracking-tight text-black/80 font-bold text-end"
        style={{
          ...RARITY_LABEL_TEXT_OUTLINE,
          fontSize: typeFontSize,
          width: "35%",
          height: "5%",
        }}
      >
        {CARD_TYPE_LABELS[cardType]}
      </div>

      <div
        key={cardRarity}
        ref={rarityFitRef}
        className={`absolute bottom-[4%] left-[6%] leading-none tracking-tight font-bold uppercase text-start ${CARD_RARITY_COLORS[cardRarity]}`}
        style={{
          ...RARITY_LABEL_TEXT_OUTLINE,
          fontSize: rarityFontSize,
          width: "35%",
          height: "5%",
        }}
      >
        {cardRarity}
      </div>
    </div>
  );
};

export default CardTextOverlay;
