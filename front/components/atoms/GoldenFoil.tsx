type GoldenFoilProps = {
  tilt: { x: number; y: number };
  foil: string;
  mask: string;
};

const GoldenFoil = ({ tilt, foil, mask }: GoldenFoilProps) => {
  const intensity = 0.4 + Math.sqrt(tilt.x ** 2 + tilt.y ** 2) / 15;

  const highlightX = 50 + tilt.x * 3;
  const highlightY = 50 - tilt.y * 3;

  const angle = -45 + tilt.x * 2;
  const goldAngle = angle + tilt.y * 1.5;

  return (
    <div className="absolute inset-0 overflow-hidden pointer-events-none select-none">
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
            rgba(255,255,255,0.95) 0%,
            rgba(255,255,255,0.4) 20%,
            rgba(0,0,0,0.6) 60%,
          rgba(255,255,255,0.8) 100%
          ),
          repeating-conic-gradient(
            from ${goldAngle}deg,
            rgba(255,255,255,0.08) 0deg,
            rgba(255,215,120,0.12) 8deg,
            rgba(255,255,255,0.08) 16deg
          ),
          linear-gradient(
            ${goldAngle}deg,
            rgba(90, 65, 15, 0.85) 0%,
            rgba(255, 200, 90, 0.9) 30%,
            rgba(255, 245, 200, 1) 50%,
            rgba(255, 200, 90, 0.9) 70%,
            rgba(90, 65, 15, 0.85) 100%
          ),
          url(${foil})
          `,
          maskImage: `url(${mask})`,
          filter: `brightness(0.6) contrast(1.75)`,
          opacity: intensity,
        }}
      />
    </div>
  );
};

export default GoldenFoil;
