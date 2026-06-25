type CardFoilProps = {
  tilt: { x: number; y: number };
  glare: { x: number; y: number };
  foil: string;
  mask: string;
  brightness: string;
  isHovering: boolean;
};

const CardFoil = ({ foil, mask, brightness }: CardFoilProps) => (
  <div className="absolute inset-0 overflow-hidden pointer-events-none select-none">
    <div
      className={`
        absolute inset-0 opacity-30 bg-cover bg-blend-soft-light-difference
        mask-cover mask-center
        mix-blend-color-dodge
        transition-[filter] duration-300 ease-[cubic-bezier(.2,.9,.2,1)]
      `}
      style={{
        backgroundImage: `url(${foil})`,
        maskImage: `url(${mask})`,
        filter: `brightness(${brightness}) contrast(1.5) saturate(1)`,
      }}
    />
  </div>
);

export default CardFoil;
