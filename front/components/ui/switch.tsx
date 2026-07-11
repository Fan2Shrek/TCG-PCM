import React from "react";

interface SwitchProps {
  checked: boolean;
  onChange: (checked: boolean) => void;
  disabled?: boolean;
  id?: string;
}

export const Switch = React.forwardRef<HTMLInputElement, SwitchProps>(
  ({ checked, onChange, disabled = false, id }, ref) => {
    return (
      <label className="inline-flex items-center cursor-pointer">
        <input
          ref={ref}
          id={id}
          type="checkbox"
          checked={checked}
          onChange={(e) => onChange(e.target.checked)}
          disabled={disabled}
          className="sr-only peer"
        />
        <div
          className={`relative w-11 h-6 rounded-full transition-colors ${
            checked ? "bg-green-600" : "bg-gray-300"
          } ${disabled ? "opacity-50 cursor-not-allowed" : ""} peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500`}
        >
          <div
            className={`absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform ${
              checked ? "translate-x-5" : ""
            }`}
          />
        </div>
      </label>
    );
  },
);

Switch.displayName = "Switch";
