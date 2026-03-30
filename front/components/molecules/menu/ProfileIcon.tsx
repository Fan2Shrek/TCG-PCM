type ProfileIconProps = {
  profilePicturePath?: string;
  className?: string;
};

export default ({ profilePicturePath, className }: ProfileIconProps) => {
  const imagePath = profilePicturePath || "menu/default_profile_picture.webp";

  return (
    <div
      className={`w-18 h-18 rounded-full bg-cover bg-center ${className || ""}`}
      style={{ backgroundImage: `url(${imagePath})` }}
    />
  );
};
