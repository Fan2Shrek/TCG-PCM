import { authApiGet } from "@/lib/api/authServer";
import BadgesPageClient from "@/components/organisms/badges/BadgesPageClient";
import type { BadgesResponse } from "@/app/types/badge";

export default async function BadgesPage() {
  const { badges } = await authApiGet<BadgesResponse>("/badges");

  return <BadgesPageClient initialBadges={badges} />;
}
