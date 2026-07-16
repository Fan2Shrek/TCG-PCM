import * as React from "react";
import { cva, type VariantProps } from "class-variance-authority";
import { Slot } from "radix-ui";

import { cn } from "@/lib/utils";

const buttonVariants = cva(
  "group/button inline-flex shrink-0 items-center justify-center rounded-full border-2 bg-clip-padding font-display font-bold whitespace-nowrap transition-all outline-none select-none cursor-pointer shadow-[var(--sticker-shadow-sm)] hover:-translate-y-px active:not-aria-[haspopup]:translate-y-px active:not-aria-[haspopup]:shadow-none focus-visible:ring-3 focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none aria-invalid:border-destructive aria-invalid:ring-3 aria-invalid:ring-destructive/20 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
  {
    variants: {
      variant: {
        default:
          "border-white bg-primary text-primary-foreground hover:bg-primary/90",
        outline:
          "border-ink-outline bg-background text-foreground hover:bg-muted aria-expanded:bg-muted",
        secondary:
          "border-white bg-sky-400 text-ink-outline hover:bg-sky-400/85",
        ghost:
          "border-transparent shadow-none hover:border-ink-outline hover:bg-muted hover:shadow-[var(--sticker-shadow-sm)] active:shadow-none",
        destructive: "border-white bg-cherry text-white hover:bg-cherry/85",
        link: "border-transparent shadow-none font-sans font-medium text-primary underline-offset-4 hover:underline hover:translate-y-0 active:translate-x-0 active:translate-y-0",
      },
      size: {
        default:
          "h-9 gap-1.5 px-3.5 text-sm has-data-[icon=inline-end]:pr-2.5 has-data-[icon=inline-start]:pl-2.5",
        xs: "h-7 gap-1 border px-2.5 text-xs shadow-none has-data-[icon=inline-end]:pr-1.5 has-data-[icon=inline-start]:pl-1.5 [&_svg:not([class*='size-'])]:size-3",
        sm: "h-8 gap-1 px-3 text-[0.8rem] has-data-[icon=inline-end]:pr-2 has-data-[icon=inline-start]:pl-2 [&_svg:not([class*='size-'])]:size-3.5",
        lg: "h-10 gap-1.5 px-4 text-base has-data-[icon=inline-end]:pr-3 has-data-[icon=inline-start]:pl-3",
        icon: "size-9",
        "icon-xs": "size-7 border [&_svg:not([class*='size-'])]:size-3",
        "icon-sm": "size-8",
        "icon-lg": "size-10",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
);

function Button({
  className,
  variant = "default",
  size = "default",
  asChild = false,
  ...props
}: React.ComponentProps<"button"> &
  VariantProps<typeof buttonVariants> & {
    asChild?: boolean;
  }) {
  const Comp = asChild ? Slot.Root : "button";

  return (
    <Comp
      data-slot="button"
      data-variant={variant}
      data-size={size}
      className={cn(buttonVariants({ variant, size, className }))}
      {...props}
    />
  );
}

export { Button, buttonVariants };
