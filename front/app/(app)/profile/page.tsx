import { redirect } from "next/navigation";

import ProfilePictureForm from "@/components/molecules/form/ProfilePictureForm";
import { serverApiGet } from "@/lib/api/server";
import { getCurrentUser } from "@/lib/auth/session";

type UserResponse = {
  username: string;
  profilePicturePath?: string;
};

export default async function Profile() {
  const sessionUser = await getCurrentUser();
  if (!sessionUser) {
    redirect("/login");
  }

  const user = await serverApiGet<UserResponse>("/user");

  return (
    <main className="flex justify-center sm:mt-32">
      <div className="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl border border-black/10 space-y-6">
        <ProfilePictureForm username={user.username} profilePicturePath={user.profilePicturePath} />
        <a href="/change-password" className="block text-sm text-center text-primary hover:underline">
          Changer mon mot de passe
        </a>
      </div>
    </main>
  );
}
