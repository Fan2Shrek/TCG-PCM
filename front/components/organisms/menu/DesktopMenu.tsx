"use client";

import type { ReactNode } from "react";
import { usePathname } from "next/navigation";
import DesktopMenuItems from "@/components/atoms/menu/DesktopMenuItems";
import {
  AiOutlineFolderOpen,
  AiOutlineLogin,
  AiOutlineLogout,
} from "react-icons/ai";
import { TbPlayCardStar, TbSword } from "react-icons/tb";
import { MdAppRegistration } from "react-icons/md";
import ProfileIcon from "@/components/molecules/menu/ProfileIcon";
import ActiveRoomStatus from "@/components/molecules/menu/ActiveRoomStatus";
import { logoutAction } from "@/lib/actions/auth";

type DesktopMenuProps = {
  className?: string;
  username?: string;
  profilePicturePath?: string;
};

type MenuItemData = {
  label: string;
  icon: ReactNode;
  linkTo?: string;
  onClick?: () => void;
};

const unauthenticatedMenuItems: MenuItemData[] = [
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

const getAuthenticatedMenuItems = (onLogout: () => void): MenuItemData[] => [
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
    label: "Jouer",
    icon: <TbSword />,
    linkTo: "/rooms",
  },
  {
    label: "Déconnexion",
    icon: <AiOutlineLogout />,
    onClick: onLogout,
  },
];

const isActiveMenuItem = (pathname: string, linkTo?: string): boolean => {
  if (!linkTo) {
    return false;
  }

  if ("/rooms" === linkTo) {
    return pathname.startsWith("/rooms");
  }

  return pathname === linkTo || pathname.startsWith(`${linkTo}/`);
};

export default function DesktopMenu({ className, username, profilePicturePath }: DesktopMenuProps) {
  const isAuthenticated = !!username;
  const pathname = usePathname();

  const handleLogout = async () => {
    await logoutAction();
  };

  const menuItems = isAuthenticated
    ? getAuthenticatedMenuItems(handleLogout)
    : unauthenticatedMenuItems;

  return (
    <div>
      <nav
        className={`flex flex-row flex-nowrap rounded-full bg-primary border-2 border-white drop-shadow-lg min-h-15 ${className || ""}`}
      >
        <ul className="flex items-center gap-2 px-4">
          {menuItems.map((menuItem) => (
            <DesktopMenuItems
              key={menuItem.label}
              label={menuItem.label}
              icon={menuItem.icon}
              linkTo={menuItem.linkTo}
              onClick={menuItem.onClick}
              active={isActiveMenuItem(pathname, menuItem.linkTo)}
            />
          ))}
        </ul>
        {isAuthenticated && <ProfileIcon username={username} profilePicturePath={profilePicturePath} />}
      </nav>

      {isAuthenticated && <ActiveRoomStatus />}
    </div>
  );
}
