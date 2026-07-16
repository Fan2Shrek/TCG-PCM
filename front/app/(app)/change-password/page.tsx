import { cookies } from "next/headers";
import { redirect } from "next/navigation";

import ChangePasswordForm from "@/components/molecules/form/ChangePasswordForm";
import { Card } from "@/components/ui/card";
import { getCurrentUser } from "@/lib/auth/session";
import { PASSWORD_EXPIRED_COOKIE } from "@/lib/auth/constants";

export default async function ChangePasswordPage() {
  const sessionUser = await getCurrentUser();
  if (!sessionUser) {
    redirect("/login");
  }

  const store = await cookies();
  const forced = store.has(PASSWORD_EXPIRED_COOKIE);

  return (
    <main className="flex justify-center sm:mt-32">
      <Card className="w-full max-w-md">
        <ChangePasswordForm forced={forced} />
      </Card>
    </main>
  );
}
