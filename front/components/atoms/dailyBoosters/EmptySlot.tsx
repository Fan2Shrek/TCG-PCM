
type EmptySlotProps = {
  className?: string,
};

export default function EmptySlot({ className }: EmptySlotProps) {

  return (
    <div className={`h-12 w-8 border-white border-2 border-dashed rounded-md ${className || ''}`}></div>
  );
}
