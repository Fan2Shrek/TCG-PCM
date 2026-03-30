import Image from "./Image";

export type CardBackProps = {
  backImage?: string;
  id: string;
};

const CardBack = ({ backImage, id }: CardBackProps) => (
  <div className="absolute inset-0 backface-hidden rotate-y-180 pointer-events-none select-none">
    <Image
      src={backImage ?? "/defaultCardBack.png"}
      alt={`${id} back`}
      fill
      className="object-cover"
    />
  </div>
);

export default CardBack;
