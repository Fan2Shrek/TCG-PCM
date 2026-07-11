import Link from "next/link";
import { ReactNode } from "react";

type MenuItemProps = {
  label: string;
  icon: ReactNode;
  linkTo?: string;
  onClick?: () => void;
  active: boolean;
  className?: string;
};

export default function MenuItem({
  label,
  icon,
  linkTo,
  onClick,
  active,
  className,
}: MenuItemProps) {
  return (
    <li className={`flex items-center gap-1 text-white ${className ?? ""}`}>
      <span className="text-3xl">{icon}</span>

      {onClick ? (
        <button
          onClick={onClick}
          className={`text-lg font-bold hover:underline whitespace-nowrap cursor-pointer ${
            active ? "text-yellow-400 decoration-yellow-400" : "text-white"
          }`}
        >
          {label}
        </button>
      ) : (
        <Link
          href={linkTo!}
          className={`text-lg font-bold hover:underline whitespace-nowrap ${
            active ? "text-yellow-400 decoration-yellow-400" : "text-white"
          }`}
        >
          {label}
        </Link>
      )}
    </li>
  );
}
