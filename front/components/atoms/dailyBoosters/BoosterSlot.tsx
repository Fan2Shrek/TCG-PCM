import Image from "../Image";

type BoosterSlotProps = {
  className?: string,
};

export default ({ className }: BoosterSlotProps) => {

    const boosterPath: string = 'menu/booster_pending.jpg';

  return (
    <Image src={boosterPath} alt="Booster" width={32} height={32} className={`${className || ''}`} />
  );
}
