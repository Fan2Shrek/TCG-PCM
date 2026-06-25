import type { ReactNode } from "react";

export type MenuItemType = {
  label: string;
  icon: ReactNode;
  linkTo: string;
  active?: boolean;
};