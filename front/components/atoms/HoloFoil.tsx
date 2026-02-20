type CardFoilProps = {
  tilt: { x: number; y: number };
  foil: string;
  mask: string;
};

const CardFoil = ({ tilt, foil, mask }: CardFoilProps) => {
  const intensity = 0.4 + Math.sqrt(tilt.x ** 2 + tilt.y ** 2) / 15;

  const highlightX = 50 + tilt.x * 3;
  const highlightY = 50 - tilt.y * 3;

  const angle = -45 + tilt.x * 2;

  return (
    <div className='absolute inset-0 overflow-hidden pointer-events-none select-none'>
      <div
        className={`
        absolute inset-0 opacity-30 bg-cover bg-blend-soft-light-difference
        mask-cover mask-center
        mix-blend-color-dodge
        transition-[filter] duration-300 ease-[cubic-bezier(.2,.9,.2,1)]
      `}
        style={{
          backgroundImage: `
          radial-gradient(
            circle at ${highlightX}% ${highlightY}%,
            rgba(255, 255, 255, 0.9) 0%,
            rgba(0,0,0,0.6) 60%,
            rgba(255,255,255,0.8) 100%
          ),
          linear-gradient(
            ${angle}deg, 
            rgba(0,0,0,0.8) 10%, 
            rgba(255,255,255,0.6), 
            rgba(0,0,0,0.8) 80%
          )
          , url(${foil})
          `,
          maskImage: `url(${mask})`,
          filter: `brightness(0.6) contrast(1.75)`,
          opacity: intensity,
        }}
      />
    </div>
  );
};

export default CardFoil;
