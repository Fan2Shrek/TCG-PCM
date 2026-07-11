import Link from "next/link";
import { ReactNode } from "react";

type MobileMenuItemProps = {
  label: string;
  icon: ReactNode;
  linkTo?: string;
  onClick?: () => void;
  active: boolean;
};

export default function MobileMenuItem({
  label,
  icon,
  linkTo,
  onClick,
  active,
}: MobileMenuItemProps) {
  const itemClass = `flex items-center text-white text-lg font-bold whitespace-nowrap underline-offset-4 ${
    active ? "underline gap-1" : "hover:underline gap-0"
  }`;

  const content = (
    <>
      <span className="text-2xl">{icon}</span>
      {active && <span>{label}</span>}
    </>
  );

  return (
    <li className="flex items-center text-white">
      {onClick ? (
        <button onClick={onClick} className={`${itemClass} cursor-pointer`}>
          {content}
        </button>
      ) : (
        <Link href={linkTo!} className={itemClass}>
          {content}
        </Link>
      )}
    </li>
  );
}
