import loadingIcon from "@/public/loading.svg";
import Image from "@/components/atoms/Image";

type LoadingSpinnerProps = {
  className?: string;
};

export default function LoadingSpinner({ className }: LoadingSpinnerProps) {
  return (
    <Image
      src={loadingIcon}
      alt="Loading"
      className={`h-6 w-6 animate-spin ${className ?? ""}`}
    />
  );
}
