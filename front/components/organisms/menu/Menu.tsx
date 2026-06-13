'use client';

import type { MenuItemType } from "@/components/types/menuItem";
import MenuItem from "@/components/atoms/menu/MenuItem";
import { AiOutlineFolderOpen, AiOutlineStar } from "react-icons/ai";
import { TbPlayCardStar } from "react-icons/tb";
import ProfileIcon from "@/components/molecules/menu/ProfileIcon";
import { useAuth } from "@/context/AuthContext";
import { Button } from "@/components/ui/button";

type MenuProps = {
  className?: string;
};

const menuItems: MenuItemType[] = [
  {
    label: "Boosters",
    icon: <TbPlayCardStar />,
    linkTo: "/boosters",
  },
  {
    label: "Collection",
    icon: <AiOutlineFolderOpen />,
    linkTo: "/inventory",
  },
  {
    label: "Arene",
    icon: <AiOutlineStar />,
    linkTo: "/arene",
  },
];

export default ({ className }: MenuProps) => {
  const { user, isAuthenticated, logout } = useAuth();

  return (
    <nav className={`flex flex-row flex-nowrap rounded-full bg-primary border-2 border-white drop-shadow-lg ${className || ""}`}>
      <ul className="flex items-center gap-2 px-4">
        {isAuthenticated && menuItems.map((menuItem) => (
          <MenuItem
            key={menuItem.label}
            label={menuItem.label}
            icon={menuItem.icon}
            linkTo={menuItem.linkTo}
            active={false}
          />
        ))}
        {isAuthenticated ? (
          <li className="flex items-center">
            <Button variant="ghost" onClick={logout} className="text-white text-lg font-bold hover:bg-white/20 hover:text-white">
              Déconnexion
            </Button>
          </li>
        ) : (
          <>
            <li className="flex items-center">
              <Button asChild variant="ghost" className="text-white text-lg font-bold hover:bg-white/20 hover:text-white">
                <a href="/login">Login</a>
              </Button>
            </li>
            <li className="flex items-center">
              <Button asChild variant="ghost" className="text-white text-lg font-bold hover:bg-white/20 hover:text-white">
                <a href="/register">Register</a>
              </Button>
            </li>
          </>
        )}
      </ul>
      {isAuthenticated && <ProfileIcon username={user?.username} />}
    </nav>
  );
};
