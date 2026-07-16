"use client";

import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";

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
  return (
    <Dialog open={open} onOpenChange={(next) => !next && onCancel()}>
      <DialogContent
        showCloseButton={false}
        onPointerDownOutside={(e) => e.preventDefault()}
        onEscapeKeyDown={(e) => e.preventDefault()}
      >
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
          <DialogDescription asChild>
            <div className="space-y-2">
              <p>{description}</p>
              {warning && (
                <p className="font-semibold text-cherry">{warning}</p>
              )}
            </div>
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
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
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
