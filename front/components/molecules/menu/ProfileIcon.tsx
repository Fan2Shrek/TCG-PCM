type ProfileIconProps = {
  profilePicturePath?: string;
  username?: string;
  className?: string;
};

export default ({
  profilePicturePath,
  username,
  className,
}: ProfileIconProps) => {
  const imagePath = profilePicturePath || "/menu/default_profile_picture.webp";

  return (
    <div className={`flex flex-row items-center gap-2 ${className || ""}`}>
      <div
        className="w-18 h-18 rounded-full bg-cover bg-center"
        style={{ backgroundImage: `url(${imagePath})` }}
      />
    </div>
  );
};
