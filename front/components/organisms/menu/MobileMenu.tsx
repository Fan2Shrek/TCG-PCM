"use client";

import { usePathname } from "next/navigation";
import {
  AiOutlineFolderOpen,
  AiOutlineLogin,
  AiOutlineLogout,
} from "react-icons/ai";
import { MdAppRegistration } from "react-icons/md";
import { TbPlayCardStar, TbSword, TbBook2, TbTrophy } from "react-icons/tb";
import { logoutAction } from "@/lib/actions/auth";
import ActiveRoomStatus from "@/components/molecules/menu/ActiveRoomStatus";
import MobileMenuItem from "@/components/atoms/menu/MobileMenuItem";

type MobileMenuProps = {
  username?: string;
  className?: string;
};

type MobileMenuItem = {
  label: string;
  icon: React.ReactNode;
  linkTo?: string;
  onClick?: () => void;
};

const isActiveMenuItem = (pathname: string, linkTo?: string): boolean => {
  if (!linkTo) {
    return false;
  }

  if (linkTo === "/rooms") {
    return pathname.startsWith("/rooms");
  }

  return pathname === linkTo || pathname.startsWith(`${linkTo}/`);
};

const getGuestItems = (): MobileMenuItem[] => [
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

const getAuthItems = (onLogout: () => void): MobileMenuItem[] => [
  {
    label: "Règles",
    icon: <TbBook2 />,
    linkTo: "/how-to-play",
  },
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
    label: "Déconnexion",
    icon: <AiOutlineLogout />,
    onClick: onLogout,
  },
];

export default function MobileMenu({ username, className }: MobileMenuProps) {
  const pathname = usePathname();
  const isAuthenticated = !!username;

  const handleLogout = async () => {
    await logoutAction();
  };

  const items = isAuthenticated ? getAuthItems(handleLogout) : getGuestItems();

  return (
    <div className={className ?? ""}>
      <div className="w-full">
        {isAuthenticated && <ActiveRoomStatus />}

        <nav className="w-full rounded-full bg-primary border-2 border-white drop-shadow-lg min-h-15 flex flex-row items-center mt-3">
          <ul className="flex w-full items-center justify-center gap-3 px-4">
            {items.map((item) => {
              const isActive = isActiveMenuItem(pathname, item.linkTo);

              return (
                <MobileMenuItem
                  key={item.label}
                  label={item.label}
                  icon={item.icon}
                  linkTo={item.linkTo}
                  onClick={item.onClick}
                  active={isActive}
                />
              );
            })}
          </ul>
        </nav>
      </div>
    </div>
  );
}
