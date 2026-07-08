import PendingBoosters from "@/components/organisms/layout/PendingBoosters";
import Menu from "@/components/organisms/menu/Menu";
import Image from "@/components/atoms/Image";

export default ({ children }: { children: React.ReactNode }) => {
  const logoPath = "/menu/logo.png";

  return (
    <>
      <div className="hidden md:grid grid-cols-3 items-center fixed w-full pt-3 px-5 z-10">
        <PendingBoosters className="justify-self-start" />
        <Image
          src={logoPath}
          alt="Logo"
          width={275}
          height={275}
          className="justify-self-center"
        />
        <Menu className="justify-self-end" />
      </div>

      <div className="md:pt-32 min-h-screen flex flex-col">{children}</div>
    </>
  );
};
