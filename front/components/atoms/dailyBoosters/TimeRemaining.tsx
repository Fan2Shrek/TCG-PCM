import { FaRegClock } from "react-icons/fa";

type TimeDisplayProps = {
  timeRemaining: number;
  className?: string;
};

export default function TimeRemaining({ timeRemaining, className }: TimeDisplayProps) {
  const hoursEquivalent = timeRemaining / 60;

  let color;

  if (hoursEquivalent > 9) {
    color = "text-red-800";
  } else if (hoursEquivalent > 6) {
    color = "text-orange-800";
  } else if (hoursEquivalent > 3) {
    color = "text-orange-400";
  } else {
    color = "text-green-800";
  }

  const displayTime =
    timeRemaining < 60
      ? `${timeRemaining}m`
      : `${Math.ceil(timeRemaining / 60)}h`;

  return (
    <span
      className={`flex flex-row flex-nowrap text-2xl gap-1 items-start font-bold ${color} ${className || ""}`}
    >
      <FaRegClock /> {displayTime}
    </span>
  );
};
