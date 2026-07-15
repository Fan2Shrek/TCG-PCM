"use client";

import type { ReactNode } from "react";
import { usePathname } from "next/navigation";
import DesktopMenuItems from "@/components/atoms/menu/DesktopMenuItems";
import DesktopMenuDropdown from "@/components/atoms/menu/DesktopMenuDropdown";
import {
  AiOutlineFolderOpen,
  AiOutlineLogin,
  AiOutlineLogout,
} from "react-icons/ai";
import { TbPlayCardStar, TbSword, TbBook2, TbTrophy, TbUsers } from "react-icons/tb";
import { MdAppRegistration } from "react-icons/md";
import ProfileIcon from "@/components/molecules/menu/ProfileIcon";
import ActiveRoomStatus from "@/components/molecules/menu/ActiveRoomStatus";
import ActiveTradeStatus from "@/components/molecules/menu/ActiveTradeStatus";
import { logoutAction } from "@/lib/actions/auth";
import { useFriendship } from "@/contexts/FriendshipContext";

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
    label: "Règles",
    icon: <TbBook2 />,
    linkTo: "/how-to-play",
  },
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

const getAuthenticatedMenuItems = (pendingFriendRequests: number): MenuItemData[] => [
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
    label: "Succès",
    icon: <TbTrophy />,
    linkTo: "/badges",
  },
  {
    label: "Jouer",
    icon: <TbSword />,
    linkTo: "/rooms",
  },
  {
    label: pendingFriendRequests > 0 ? `Amis (${pendingFriendRequests})` : "Amis",
    icon: <TbUsers />,
    linkTo: "/friends",
  },
];

const getAuthenticatedDropdownItems = (onLogout: () => void): MenuItemData[] => [
  {
    label: "Règles",
    icon: <TbBook2 />,
    linkTo: "/how-to-play",
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
  const { pendingRequests } = useFriendship();

  const handleLogout = async () => {
    await logoutAction();
  };

  const menuItems = isAuthenticated ? getAuthenticatedMenuItems(pendingRequests.length) : unauthenticatedMenuItems;
  const dropdownItems = isAuthenticated ? getAuthenticatedDropdownItems(handleLogout) : [];

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
          {dropdownItems.length > 0 && (
            <DesktopMenuDropdown
              items={dropdownItems}
              isItemActive={(linkTo) => isActiveMenuItem(pathname, linkTo)}
            />
          )}
        </ul>
        {isAuthenticated && <ProfileIcon username={username} profilePicturePath={profilePicturePath} />}
      </nav>

      {isAuthenticated && <ActiveRoomStatus />}
      {isAuthenticated && <ActiveTradeStatus />}
    </div>
  );
}
