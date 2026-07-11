import Link from "next/link";
import { ReactNode } from "react";

type DesktopMenuItemsProps = {
  label: string;
  icon: ReactNode;
  linkTo?: string;
  onClick?: () => void;
  active: boolean;
  className?: string;
};

export default function DesktopMenuItems({
  label,
  icon,
  linkTo,
  onClick,
  active,
  className,
}: DesktopMenuItemsProps) {
  return (
    <li className={`flex items-center gap-1 text-white ${className ?? ""}`}>
      <span className="text-3xl">{icon}</span>

      {onClick ? (
        <button
          onClick={onClick}
          className={`text-lg font-bold whitespace-nowrap cursor-pointer underline-offset-4 ${
            active ? "underline" : "hover:underline"
          }`}
        >
          {label}
        </button>
      ) : (
        <Link
          href={linkTo!}
          className={`text-lg font-bold whitespace-nowrap underline-offset-4 ${
            active ? "underline" : "hover:underline"
          }`}
        >
          {label}
        </Link>
      )}
    </li>
  );
}
