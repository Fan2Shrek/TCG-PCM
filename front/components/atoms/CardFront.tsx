import Image from "./Image";
import { CardLayer } from "@/lib/cards/types/card";
import HoloFoil from "./HoloFoil";
import RainbowFoil from "./RainbowFoil";
import GoldenFoil from "./GoldenFoil";
import { Fragment } from "react/jsx-runtime";

export type CardFrontProps = {
  layers: CardLayer[];
  tilt: { x: number; y: number };
  glare: { x: number; y: number };
  isHovering: boolean;
};

const CardFront = ({ layers, tilt, isHovering }: CardFrontProps) => (
  <div className="absolute inset-0 backface-hidden pointer-events-none select-none overflow-hidden">
    {layers.map((layer, i) => {
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
  </div>
);

export default CardFront;
