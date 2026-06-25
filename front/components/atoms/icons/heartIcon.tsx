type HeartIconProps = {
  color?: string;
  size?: number;
};

export default function HeartIcon({ color = "red", size = 64 }: HeartIconProps) {
  return (
    <svg viewBox="0 0 32 29.6" width={size} height={size}>
      <path
        d="M23.6,0c-2.9,0-5.6,1.5-7.6,4C13.9,1.5,11.2,0,8.3,0C3.7,0,0,3.7,0,8.3
           c0,4.7,4.2,8.6,10.5,14.5l5,4.6l5-4.6C27.8,16.9,32,13,32,8.3C32,3.7,28.3,0,23.6,0z"
        fill={color}
      />
    </svg>
  );
}
