import PendingBoosters from "@/components/organisms/layout/PendingBoosters";
import DesktopMenu from "@/components/organisms/menu/DesktopMenu";
import MobileMenu from "@/components/organisms/menu/MobileMenu";
import ProfileIcon from "@/components/molecules/menu/ProfileIcon";
import { serverApiGet } from "@/lib/api/server";
import { getCurrentUser } from "@/lib/auth/session";

export default async ({ children }: { children: React.ReactNode }) => {
  const user = await getCurrentUser();
  const profilePicturePath = user
    ? (await serverApiGet<{ profilePicturePath?: string }>("/user").catch(() => null))?.profilePicturePath
    : undefined;

  return (
    <>
      <div className="hidden md:grid grid-cols-2 items-center fixed w-full pt-3 px-5 z-80">
        <div>{user && <PendingBoosters className="justify-self-start" />}</div>
        <DesktopMenu className="justify-self-end" username={user?.username} profilePicturePath={profilePicturePath} />
      </div>
      {user && (
        <div className="md:hidden fixed top-2 left-0 right-0 z-80 flex items-start justify-between px-3">
          <PendingBoosters className="max-w-sm pt-2" />
          <ProfileIcon username={user.username} profilePicturePath={profilePicturePath} className="shrink-0" />
        </div>
      )}
      <div className="md:hidden fixed bottom-0 left-0 right-0 z-80 px-2 pb-[calc(env(safe-area-inset-bottom)+0.5rem)]">
        <MobileMenu username={user?.username} />
      </div>
      <div className="pt-24 pb-18 not-only-of-type:md:pt-32 md:pb-0 min-h-screen flex flex-col">
        {children}
      </div>
    </>
  );
};
