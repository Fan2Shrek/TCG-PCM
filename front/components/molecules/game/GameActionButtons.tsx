"use client";

import { useState } from "react";

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
  const baseButtonClassName =
    "rounded-lg px-6 py-2 text-base font-semibold text-white shadow-md transition-all duration-150 hover:-translate-y-px active:translate-y-0 cursor-pointer";
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
        <button
          type="button"
          onClick={onCancel}
          className={`${baseButtonClassName} bg-slate-600 hover:bg-slate-500`}
        >
          Annuler
        </button>
      )}
      {isLoggedPlayerTurn && (
        <button
          type="button"
          onClick={onEndTurn}
          className={`${baseButtonClassName} bg-emerald-600 hover:bg-emerald-500`}
        >
          Fin de tour
        </button>
      )}
      <button
        type="button"
        onClick={handleForfeitClick}
        className={`${baseButtonClassName} bg-red-600 hover:bg-red-500`}
      >
        {forfeitConfirm ? "Êtes-vous sûr ?" : "Abandonner"}
      </button>
    </div>
  );
}
