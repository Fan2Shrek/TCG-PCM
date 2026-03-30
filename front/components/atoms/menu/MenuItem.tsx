import { ReactNode } from "react";

type MenuItemProps = {
  label: string,
  icon: ReactNode,
  linkTo: string,
  active: boolean,
  className?: string,
};

export default ({ label, icon, linkTo, active, className }: MenuItemProps) => {

  return (
    <li className={`flex flex-row flex-nowrap items-center text-white ${className || ''}`}>
      <span className="text-3xl">{icon}</span>
      <a
        href={linkTo}
        className={`text-lg font-bold hover:underline ${active ? 'text-yellow-400 decoration-yellow-400' : 'text-white'}`}
      >
        {label}
      </a>
    </li>
  );
}
