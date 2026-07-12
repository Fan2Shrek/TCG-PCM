import { authApiGet } from "@/lib/api/authServer";
import BoostersPageClient from "@/components/organisms/boosters/BoostersPageClient";
import { InventorySetStat } from "@/app/types/booster";

export default async function BoostersPage() {
  const stats = await authApiGet<InventorySetStat[]>("/inventory/stats");

  const initialStatsBySet = stats.reduce<Record<string, InventorySetStat>>((acc, setStat) => {
    acc[setStat.set] = setStat;
    return acc;
  }, {});

  return <BoostersPageClient initialStatsBySet={initialStatsBySet} />;
}
