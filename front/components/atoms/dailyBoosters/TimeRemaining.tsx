
import { FaRegClock } from "react-icons/fa";

type TimeDisplayProps = {
  timeRemaining: number,
  className?: string,
};

export default ({ timeRemaining, className }: TimeDisplayProps) => {

  let color;

  if (timeRemaining > 9) {
    color = "text-red-800";
  } else if (timeRemaining > 6) {
    color = "text-orange-800";
  } else if (timeRemaining > 3) {
    color = "text-orange-400";
  } else {
    color = "text-green-800";
  }

  return (
    <span className={`flex flex-row flex-nowrap text-2xl gap-1 items-start font-bold ${color} ${className || ''}`}>
      <FaRegClock /> {timeRemaining}h
    </span>
  );
}
