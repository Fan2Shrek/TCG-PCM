"use client";

import * as React from "react";
import { Switch as SwitchPrimitive } from "radix-ui";

interface SwitchProps {
  checked: boolean;
  onChange: (checked: boolean) => void;
  disabled?: boolean;
  id?: string;
}

export const Switch = React.forwardRef<HTMLButtonElement, SwitchProps>(
  ({ checked, onChange, disabled = false, id }, ref) => {
    return (
      <SwitchPrimitive.Root
        ref={ref}
        id={id}
        checked={checked}
        onCheckedChange={onChange}
        disabled={disabled}
        data-slot="switch"
        className="peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-ink-outline bg-white transition-colors outline-none data-[state=checked]:border-white data-[state=checked]:bg-mint focus-visible:ring-3 focus-visible:ring-primary/35 disabled:cursor-not-allowed disabled:opacity-50"
      >
        <SwitchPrimitive.Thumb className="pointer-events-none block size-4 translate-x-0.5 rounded-full border-2 border-ink-outline bg-white shadow-[1px_1px_0_0_var(--color-ink-outline)] transition-transform data-[state=checked]:translate-x-[22px]" />
      </SwitchPrimitive.Root>
    );
  },
);

Switch.displayName = "Switch";
