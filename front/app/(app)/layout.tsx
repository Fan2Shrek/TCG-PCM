import PendingBoosters from "@/components/organisms/layout/PendingBoosters";
import Menu from "@/components/organisms/menu/Menu";
import Image from "@/components/atoms/Image";
import { getCurrentUser } from "@/lib/auth/session";

export default async ({ children }: { children: React.ReactNode }) => {
  const logoPath = "menu/logo.png";
  const user = await getCurrentUser();

  return (
      <>
        <div className="hidden md:grid grid-cols-3 items-center fixed w-full pt-3 px-5 z-10">
          <PendingBoosters className="justify-self-start" />
          <Image src={logoPath} alt="Logo" width={275} height={275} className="justify-self-center" />
          <Menu className="justify-self-end" username={user?.username} />
        </div>
        <div className="md:pt-32">
          {children}
        </div>
      </>
  );
}
