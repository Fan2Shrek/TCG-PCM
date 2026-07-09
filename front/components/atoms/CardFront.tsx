import Image from "./Image";
import HoloFoil from "./HoloFoil";
import RainbowFoil from "./RainbowFoil";
import GoldenFoil from "./GoldenFoil";
import CardTextOverlay from "./CardTextOverlay";
import { Fragment } from "react/jsx-runtime";
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
};

const CardFront = ({
  layers,
  tilt,
  isHovering,
  cardTitle,
  cardDescription,
  cardType,
  cardStats,
}: CardFrontProps) => (
  <div className="absolute inset-0 overflow-hidden backface-hidden select-none rounded-sm">
    {[...layers]
      .sort((a, b) => a.depth - b.depth)
      .map((layer, i) => {
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
      cardTitle={cardTitle}
      cardDescription={cardDescription}
      cardType={cardType}
      cardStats={cardStats}
    />
  </div>
);

export default CardFront;
