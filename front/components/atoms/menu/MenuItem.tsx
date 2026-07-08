import { ReactNode } from "react";

type MenuItemProps = {
  label: string;
  icon: ReactNode;
  linkTo?: string;
  onClick?: () => void;
  active: boolean;
  className?: string;
};

export default ({
  label,
  icon,
  linkTo,
  onClick,
  active,
  className,
}: MenuItemProps) => {
  return (
    <li
      className={`flex flex-row flex-nowrap items-center text-white gap-1 ${className || ""}`}
    >
      <span className="text-3xl">{icon}</span>
      {onClick ? (
        <button
          onClick={onClick}
          className={`text-lg font-bold hover:underline whitespace-nowrap cursor-pointer ${active ? "text-yellow-400 decoration-yellow-400" : "text-white"}`}
        >
          {label}
        </button>
      ) : (
        <a
          href={linkTo}
          className={`text-lg font-bold hover:underline whitespace-nowrap ${active ? "text-yellow-400 decoration-yellow-400" : "text-white"}`}
        >
          {label}
        </a>
      )}
    </li>
  );
};
