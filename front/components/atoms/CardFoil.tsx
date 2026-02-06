type CardFoilProps = {
  tilt: { x: number; y: number };
  glare: { x: number; y: number };
  foil: string;
  mask: string;
  brightness: string;
  isHovering: boolean;
};

const CardFoil = ({ tilt, foil, mask, isHovering, brightness }: CardFoilProps) => (
  <div className="absolute inset-0 overflow-hidden pointer-events-none select-none">
    <div
      className={`
        absolute inset-0 opacity-30
        bg-glare-effect bg-cover
        bg-blend-soft-light-difference
        filter contrast-150 saturate-100 mix-blend-color-dodge
        transition-[filter] duration-300 ease-[cubic-bezier(.2,.9,.2,1)]
      `}
      style={{
        backgroundImage: `url(${foil})`,
        filter: `brightness(${brightness}) contrast(1.5) saturate(1)`,
      }}
    />

    <div
      className="absolute inset-0 opacity-30 bg-blend-soft-light-difference bg-cover"
      style={{
        backgroundImage: `url(${mask})`,
      }}
    />
  </div>
);

export default CardFoil;
