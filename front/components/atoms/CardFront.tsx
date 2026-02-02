import Image from "./image";
import { CardLayer } from "../types/card";

export type CardFrontProps = {
  layers: CardLayer[];
  tilt: { x: number; y: number };
  isHovering: boolean;
};

const CardFront = ({ layers, tilt, isHovering }: CardFrontProps) => (
  <div className="absolute inset-0 backface-hidden pointer-events-none select-none overflow-hidden">
    {layers.map((layer, i) => {
      const depthFactor = (layer.depth / 100) * 5;

      return (
        <Image
          key={i}
          src={layer.src}
          alt={layer.alt ?? `layer-${i}`}
          fill
          className={`object-cover ${!isHovering ? "transition-transform duration-300 ease-[cubic-bezier(.2,.9,.2,1)]" : ""}`}
          style={{
            transform: `translateX(${tilt.y * depthFactor}px) translateY(${tilt.x * depthFactor}px)`,
          }}
        />
      );
    })}
  </div>
);

export default CardFront;