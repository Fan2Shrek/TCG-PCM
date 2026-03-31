import type { MenuItemType } from "@/components/types/menuItem";
import MenuItem from "@/components/atoms/menu/MenuItem";
import { AiOutlineFolderOpen, AiOutlineStar } from "react-icons/ai";
import { TbPlayCardStar } from "react-icons/tb";
import ProfileIcon from "@/components/molecules/menu/ProfileIcon";

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
  return (
    <nav className={`flex flex-row flex-nowrap rounded-full bg-primary border-2 border-white drop-shadow-lg ${className || ""}`}>
      <ul className="flex items-center gap-2 px-4">
        {menuItems.map((menuItem) => (
          <MenuItem
            key={menuItem.label}
            label={menuItem.label}
            icon={menuItem.icon}
            linkTo={menuItem.linkTo}
            active={false}
          />
        ))}
      </ul>
      <ProfileIcon />
    </nav>
  );
};
