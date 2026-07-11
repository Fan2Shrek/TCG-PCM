import Image from "./Image";
import HoloFoil from "./HoloFoil";
import RainbowFoil from "./RainbowFoil";
import GoldenFoil from "./GoldenFoil";
import CardTextOverlay from "./CardTextOverlay";
import { Fragment } from "react/jsx-runtime";
import { useEffect, useMemo, useState } from "react";
import { CardLayer } from "@/lib/cards/types/card";
import { CardType } from "@/constants/card";

export type CardFrontProps = {
  layers: CardLayer[];
  tilt: { x: number; y: number };
  glare: { x: number; y: number };
  isHovering: boolean;
  readinessKey: string;
  cardTitle: string;
  cardDescription: string;
  cardType: CardType;
  cardStats: { hp?: number; attack?: number; cost?: number };
  onReadyStateChange?: (isReady: boolean) => void;
};

const CardFront = ({
  layers,
  tilt,
  isHovering,
  readinessKey,
  cardTitle,
  cardDescription,
  cardType,
  cardStats,
  onReadyStateChange,
}: CardFrontProps) => {
  const [isTextReady, setIsTextReady] = useState(false);
  const [isImagesReady, setIsImagesReady] = useState(false);
  const sortedLayers = useMemo(
    () => [...layers].sort((a, b) => a.depth - b.depth),
    [layers],
  );
  const requiredLayerIndexes = useMemo(
    () =>
      sortedLayers
        .map((layer, index) => (layer.src ? index : null))
        .filter((index): index is number => index !== null),
    [sortedLayers],
  );
  const requiredLayerSrcs = useMemo(
    () =>
      requiredLayerIndexes
        .map((index) => sortedLayers[index]?.src)
        .filter(
          (src): src is string => typeof src === "string" && src.length > 0,
        ),
    [requiredLayerIndexes, sortedLayers],
  );
  const requiredLayersKey = useMemo(
    () => requiredLayerSrcs.map((src, index) => `${index}:${src}`).join("|"),
    [requiredLayerSrcs],
  );

  useEffect(() => {
    if (requiredLayerSrcs.length === 0) {
      setIsImagesReady(true);
      return;
    }

    setIsImagesReady(false);
    let settledCount = 0;

    requiredLayerSrcs.forEach((src) => {
      const probe = new window.Image();

      const settle = () => {
        settledCount += 1;
        if (settledCount >= requiredLayerSrcs.length) {
          setIsImagesReady(true);
        }
      };

      probe.onload = settle;
      probe.onerror = settle;
      probe.src = src;

      if (probe.complete) {
        settle();
      }
    });
  }, [requiredLayersKey]);

  useEffect(() => {
    setIsTextReady(false);
  }, [readinessKey]);

  useEffect(() => {
    if (!onReadyStateChange) {
      return;
    }

    onReadyStateChange(isTextReady && isImagesReady);
  }, [isImagesReady, isTextReady, onReadyStateChange]);

  return (
    <div className="absolute inset-0 overflow-hidden backface-hidden select-none rounded-sm">
      {sortedLayers.map((layer, i) => {
        const depthFactor = (layer.depth / 100) * 5;

        const { foilEffect, foil, mask } = layer;

        const foilComponentMap = {
          Holographic: HoloFoil,
          Rainbow: RainbowFoil,
          Golden: GoldenFoil,
        };
        const FoilComponent = foilEffect ? foilComponentMap[foilEffect] : null;

        return (
          <Fragment key={i}>
            <Image
              src={layer.src}
              alt={layer.alt ?? `layer-${i}`}
              fill
              className={`object-cover ${!isHovering ? "transition-transform duration-300 ease-[cubic-bezier(.2,.9,.2,1)]" : ""} z-0`}
              style={{
                transform: `translateX(${tilt.y * depthFactor}px) translateY(${tilt.x * depthFactor}px)`,
              }}
            />
            {FoilComponent && foil && mask && (
              <FoilComponent tilt={tilt} foil={foil} mask={mask} />
            )}
          </Fragment>
        );
      })}
      <CardTextOverlay
        key={readinessKey}
        readinessKey={readinessKey}
        cardTitle={cardTitle}
        cardDescription={cardDescription}
        cardType={cardType}
        cardStats={cardStats}
        onLayoutReady={() => setIsTextReady(true)}
      />
    </div>
  );
};

export default CardFront;
