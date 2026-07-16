import { redirect } from "next/navigation";

import ProfilePictureForm from "@/components/molecules/form/ProfilePictureForm";
import { Card } from "@/components/ui/card";
import { authApiGet } from "@/lib/api/authServer";
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

  const user = await authApiGet<UserResponse>("/user");

  return (
    <main className="flex justify-center sm:mt-32">
      <Card className="w-full max-w-md">
        <ProfilePictureForm username={user.username} profilePicturePath={user.profilePicturePath} />
        <a href="/change-password" className="block text-sm text-center font-semibold text-primary hover:underline">
          Changer mon mot de passe
        </a>
      </Card>
    </main>
  );
}
