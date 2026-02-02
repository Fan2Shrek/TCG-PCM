import Image from "./image";
import { CardLayer } from "../types/card";

export type CardFrontProps = {
  layers: CardLayer[];
};

const CardFront = ({ layers }: CardFrontProps) => (
  <div className="absolute inset-0 backface-hidden">
    {layers.map((layer, i) => (
      <Image
        key={i}
        src={layer.src}
        alt={layer.alt ?? `layer-${i}`}
        fill
        className={`object-cover z-[${layer.depth}]`}
      />
    ))}
  </div>
);

export default CardFront;
