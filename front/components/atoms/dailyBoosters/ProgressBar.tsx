type ProgressBarProps = {
  progress: number
  className?: string,
  text?: string;
};

export default ({ progress, className, text }: ProgressBarProps) => {

  const clamped = Math.max(0, Math.min(100, progress));

  const startColor: [number, number, number] = [100, 0, 35];
  const endColor: [number, number, number] = [164, 3, 83];

  const t = clamped / 100;

  const r = Math.round(startColor[0] + (endColor[0] - startColor[0]) * t);
  const g = Math.round(startColor[1] + (endColor[1] - startColor[1]) * t);
  const b = Math.round(startColor[2] + (endColor[2] - startColor[2]) * t);

  const color = `rgb(${r}, ${g}, ${b})`;

  return (
    <div className={`h-4 w-full bg-gray-300 rounded-full border border-white overflow-hidden drop-shadow-lg inner-shadow ${className || ''}`}>
        <div className={`rounded-full h-full`} style={{ width: `${progress}%`, backgroundColor: `${color}` }}></div>

		{text && (
			<div className="absolute inset-0 flex items-center justify-center text-xs font-medium text-black">
				{text}
			</div>
		)}
    </div>
  );
}
