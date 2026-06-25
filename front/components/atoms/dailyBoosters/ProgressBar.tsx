type ProgressBarProps = {
  progress: number;
  className?: string;
  text?: string;
  startColor?: [number, number, number];
  endColor?: [number, number, number];
};

export default ({
  progress,
  className,
  text,
  startColor = [100, 0, 35],
  endColor = [164, 3, 83],
}: ProgressBarProps) => {
  const clamped = Math.max(0, Math.min(100, progress));

  const progressRatio = clamped / 100;

  const r = Math.round(
    startColor[0] + (endColor[0] - startColor[0]) * progressRatio,
  );
  const g = Math.round(
    startColor[1] + (endColor[1] - startColor[1]) * progressRatio,
  );
  const b = Math.round(
    startColor[2] + (endColor[2] - startColor[2]) * progressRatio,
  );

  const color = `rgb(${r}, ${g}, ${b})`;

  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
  const textColor = luminance > 0.5 ? "black" : "white";
  return (
    <div
      className={`h-4 w-full bg-gray-400 rounded-full border border-white overflow-hidden drop-shadow-lg inner-shadow ${className || ""}`}
    >
      <div
        className={`rounded-full h-full`}
        style={{ width: `${progress}%`, backgroundColor: `${color}` }}
      ></div>

      {text && (
        <div
          className="absolute inset-0 flex items-center justify-center text-xs font-medium"
          style={{ color: textColor }}
        >
          {text}
        </div>
      )}
    </div>
  );
};
