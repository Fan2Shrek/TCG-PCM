"use client";

import type { MenuItemType } from "@/types/menuItem";
import MenuItem from "@/components/atoms/menu/MenuItem";
import {
  AiOutlineFolderOpen,
  AiOutlineLogin,
  AiOutlineLogout,
} from "react-icons/ai";
import { TbPlayCardStar, TbSword } from "react-icons/tb";
import { MdAppRegistration } from "react-icons/md";
import ProfileIcon from "@/components/molecules/menu/ProfileIcon";
import { useAuth } from "@/contexts/AuthContext";

type MenuProps = {
  className?: string;
};

const unauthenticatedMenuItems: MenuItemType[] = [
  {
    label: "Connexion",
    icon: <AiOutlineLogin />,
    linkTo: "/login",
  },
  {
    label: "Inscription",
    icon: <MdAppRegistration />,
    linkTo: "/register",
  },
];

const getAuthenticatedMenuItems = (onLogout: () => void): any[] => [
  {
    label: "Boosters",
    icon: <TbPlayCardStar />,
    linkTo: "/boosters",
  },
  {
    label: "Mes cartes",
    icon: <AiOutlineFolderOpen />,
    linkTo: "/inventory",
  },
  {
    label: "Combattre",
    icon: <TbSword />,
    linkTo: "/arene",
  },
  {
    label: "Déconnexion",
    icon: <AiOutlineLogout />,
    onClick: onLogout,
  },
];

export default ({ className }: MenuProps) => {
  const { user, isAuthenticated, logout } = useAuth();

  const menuItems = isAuthenticated
    ? getAuthenticatedMenuItems(logout)
    : unauthenticatedMenuItems;

  return (
    <nav
      className={`flex flex-row flex-nowrap rounded-full bg-primary border-2 border-white drop-shadow-lg min-h-15 ${className || ""}`}
    >
      <ul className="flex items-center gap-2 px-4">
        {menuItems.map((menuItem: any) => (
          <MenuItem
            key={menuItem.label}
            label={menuItem.label}
            icon={menuItem.icon}
            linkTo={menuItem.linkTo}
            onClick={menuItem.onClick}
            active={false}
          />
        ))}
      </ul>
      {isAuthenticated && <ProfileIcon username={user?.username} />}
    </nav>
  );
};
