type RainbowFoilProps = {
  tilt: { x: number; y: number };
  foil: string;
  mask: string;
};

const RainbowFoil = ({ tilt, foil, mask }: RainbowFoilProps) => {
  const intensity = 0.4 + Math.sqrt(tilt.x ** 2 + tilt.y ** 2) / 15;

  const highlightX = 50 + tilt.x * 3;
  const highlightY = 50 - tilt.y * 3;

  const angle = -45 + tilt.x * 2;
  const hueShift = (tilt.x + tilt.y) * 8;

  return (
    <div className="absolute inset-0 overflow-hidden pointer-events-none select-none">
      <div
        className={`
        absolute inset-0 bg-cover bg-blend-soft-light-difference
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
            repeating-conic-gradient(
              from ${angle + hueShift}deg,
              hsl(0 100% 60%),
              hsl(60 100% 60%),
              hsl(120 100% 60%),
              hsl(180 100% 60%),
              hsl(240 100% 60%),
              hsl(300 100% 60%),
              hsl(360 100% 60%)
            )
          , url(${foil})
          `,
          maskImage: `url(${mask})`,
          filter: `brightness(0.8) contrast(1.75)`,
          opacity: intensity,
        }}
      />
    </div>
  );
};

export default RainbowFoil;
