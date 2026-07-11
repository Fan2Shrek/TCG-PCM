import Image from "./Image";
import HoloFoil from "./HoloFoil";
import RainbowFoil from "./RainbowFoil";
import GoldenFoil from "./GoldenFoil";
import CardTextOverlay from "./CardTextOverlay";
import { Fragment } from "react/jsx-runtime";
import { useEffect, useMemo, useRef, useState } from "react";
import { CardLayer } from "@/lib/cards/types/card";
import { CardType } from "@/constants/card";

export type CardFrontProps = {
  layers: CardLayer[];
  tilt: { x: number; y: number };
  glare: { x: number; y: number };
  isHovering: boolean;
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
  cardTitle,
  cardDescription,
  cardType,
  cardStats,
  onReadyStateChange,
}: CardFrontProps) => {
  const [isTextReady, setIsTextReady] = useState(false);
  const [isImagesReady, setIsImagesReady] = useState(false);
  const loadedLayerIndexesRef = useRef<Set<number>>(new Set());
  const sortedLayers = useMemo(
    () => [...layers].sort((a, b) => a.depth - b.depth),
    [layers],
  );
  const loadableLayerIndexes = useMemo(
    () =>
      sortedLayers
        .map((layer, index) => (layer.src ? index : null))
        .filter((index): index is number => index !== null),
    [sortedLayers],
  );
  const loadableLayersKey = useMemo(
    () =>
      sortedLayers
        .map((layer, index) => `${index}:${String(layer.src)}`)
        .join("|"),
    [sortedLayers],
  );

  useEffect(() => {
    loadedLayerIndexesRef.current = new Set();
    setIsTextReady(false);
    setIsImagesReady(loadableLayerIndexes.length === 0);
  }, [loadableLayersKey, loadableLayerIndexes.length]);

  useEffect(() => {
    if (!onReadyStateChange) {
      return;
    }

    onReadyStateChange(isTextReady && isImagesReady);
  }, [isImagesReady, isTextReady, onReadyStateChange]);

  const markLayerAsSettled = (layerIndex: number) => {
    loadedLayerIndexesRef.current.add(layerIndex);

    if (loadedLayerIndexesRef.current.size >= loadableLayerIndexes.length) {
      setIsImagesReady(true);
    }
  };

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
              onLoad={() => markLayerAsSettled(i)}
              onError={() => markLayerAsSettled(i)}
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
