import { BoosterType } from "@/constants/booster";

export type Booster = {
  id: string;
  boosterType: BoosterType;
  description: string;
};
