"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";

type GameActionButtonsProps = {
  isLoggedPlayerTurn: boolean;
  showCancel?: boolean;
  onCancel?: () => void;
  onEndTurn: () => void;
  onForfeit: () => void;
};

const FORFEIT_CONFIRMATION_TIMEOUT = 3000;

export default function GameActionButtons({
  isLoggedPlayerTurn,
  showCancel = false,
  onCancel,
  onEndTurn,
  onForfeit,
}: GameActionButtonsProps) {
  const [forfeitConfirm, setForfeitConfirm] = useState(false);

  const handleForfeitClick = () => {
    if (!forfeitConfirm) {
      setForfeitConfirm(true);
      setTimeout(() => {
        setForfeitConfirm(false);
      }, FORFEIT_CONFIRMATION_TIMEOUT);
    }

    if (forfeitConfirm) {
      onForfeit();
    }
  };
  return (
    <div className="flex flex-col-reverse lg:flex-col gap-3 min-w-3xs">
      {showCancel && onCancel && (
        <Button type="button" size="lg" variant="outline" onClick={onCancel}>
          Annuler
        </Button>
      )}
      {isLoggedPlayerTurn && (
        <Button type="button" size="lg" onClick={onEndTurn}>
          Fin de tour
        </Button>
      )}
      <Button
        type="button"
        size="lg"
        variant="destructive"
        onClick={handleForfeitClick}
      >
        {forfeitConfirm ? "Êtes-vous sûr ?" : "Abandonner"}
      </Button>
    </div>
  );
}
