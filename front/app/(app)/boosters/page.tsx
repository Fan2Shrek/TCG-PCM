import { serverApiGet } from "@/lib/api/server";
import BoostersPageClient from "@/components/organisms/boosters/BoostersPageClient";
import { InventorySetStat } from "@/app/types/booster";

export default async function BoostersPage() {
  const stats = await serverApiGet<InventorySetStat[]>("/inventory/stats");

  const initialStatsBySet = stats.reduce<Record<string, InventorySetStat>>((acc, setStat) => {
    acc[setStat.set] = setStat;
    return acc;
  }, {});

  return <BoostersPageClient initialStatsBySet={initialStatsBySet} />;
}
