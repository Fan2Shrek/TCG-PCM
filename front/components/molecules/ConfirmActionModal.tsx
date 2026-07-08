"use client";

import { Button } from "@/components/ui/button";

type ConfirmActionModalProps = {
  open: boolean;
  title: string;
  description: string;
  warning?: string;
  confirmLabel: string;
  cancelLabel?: string;
  onConfirm: () => void;
  onCancel: () => void;
  isLoading?: boolean;
};

export default function ConfirmActionModal({
  open,
  title,
  description,
  warning,
  confirmLabel,
  cancelLabel = "Annuler",
  onConfirm,
  onCancel,
  isLoading = false,
}: ConfirmActionModalProps) {
  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div className="max-w-sm rounded-lg bg-white p-6">
        <h3 className="mb-2 text-lg font-semibold text-black">{title}</h3>
        <div className="mb-4 space-y-2 text-sm text-black/70">
          <p>{description}</p>
          {warning && <p className="font-medium text-red-600">{warning}</p>}
        </div>
        <div className="flex justify-end gap-2">
          <Button
            onClick={onCancel}
            variant="outline"
            size="sm"
            disabled={isLoading}
          >
            {cancelLabel}
          </Button>
          <Button
            onClick={onConfirm}
            variant="destructive"
            size="sm"
            disabled={isLoading}
          >
            {confirmLabel}
          </Button>
        </div>
      </div>
    </div>
  );
}
