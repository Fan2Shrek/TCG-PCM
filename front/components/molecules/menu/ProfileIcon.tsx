import Link from "next/link";

import { getImage } from "@/lib/api/api";

type ProfileIconProps = {
  profilePicturePath?: string;
  username?: string;
  className?: string;
};

export default ({ profilePicturePath, username, className }: ProfileIconProps) => {
  const imagePath = profilePicturePath ? getImage(profilePicturePath) : "/menu/default_profile_picture.webp";

  return (
    <Link href="/profile" className={`flex flex-row items-center gap-2 ${className || ""}`}>
      {username && <span className="text-white font-bold">{username}</span>}
      <div
        className="w-18 h-18 rounded-full bg-cover bg-center"
        style={{ backgroundImage: `url(${imagePath})` }}
      />
    </Link>
  );
};
