import { cookies } from "next/headers";
import { redirect } from "next/navigation";

import ChangePasswordForm from "@/components/molecules/form/ChangePasswordForm";
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
    <main className="flex  justify-center sm:mt-32 ">
      <div className="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl border border-black/10">
        <ChangePasswordForm forced={forced} />
      </div>
    </main>
  );
}
