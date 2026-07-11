import Image from "@/components/atoms/Image";

type BoosterTitleOverlayProps = {
  image: string;
  alt: string;
  isVisible: boolean;
};

export default function BoosterTitleOverlay({
  image,
  alt,
  isVisible,
}: BoosterTitleOverlayProps) {
  return (
    <div
      className={`absolute -top-32 left-1/2 h-32 w-56 -translate-x-1/2 pointer-events-none z-20 transition-opacity duration-500 animate-booster-title-float ${isVisible ? "opacity-100" : "opacity-0"}`}
    >
      <Image
        src={image}
        alt={alt}
        fill
        className="object-contain object-bottom
          mask-[linear-gradient(to_right,transparent,black_20%,black_80%,transparent),linear-gradient(to_bottom,transparent,black_20%,black_80%,transparent)]
          mask-intersect"
      />
    </div>
  );
}
