import Image from "./Image";

export type CardBackProps = {
  id: string;
  backImage?: string;
};

const CardBack = ({ id, backImage = "" }: CardBackProps) => (
  <div className="absolute inset-0 backface-hidden rotate-y-180 pointer-events-none select-none">
    <Image
      src={backImage == "" ? "/default_card_back.png" : backImage}
      alt={`${id} back`}
      fill
      className="object-cover"
    />
  </div>
);

export default CardBack;
