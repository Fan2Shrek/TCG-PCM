"use client";

import { useActionState, useState } from "react";
import { useFormStatus } from "react-dom";

import { changePasswordAction, type AuthActionState } from "@/lib/actions/auth";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Field,
  FieldGroup,
  FieldLabel,
  FieldError,
} from "@/components/ui/field";

const initialState: AuthActionState = { error: null };

function SubmitButton() {
  const { pending } = useFormStatus();

  return (
    <Button type="submit" disabled={pending}>
      {pending ? "Mise à jour..." : "Mettre à jour le mot de passe"}
    </Button>
  );
}

export default function ChangePasswordForm({ forced }: { forced: boolean }) {
  const [state, formAction] = useActionState(changePasswordAction, initialState);
  const [clientError, setClientError] = useState<string | null>(null);

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    const formData = new FormData(event.currentTarget);
    const newPassword = String(formData.get("newPassword") || "");
    const confirmPassword = String(formData.get("confirmPassword") || "");

    if (
      newPassword.length < 12 ||
      !/[a-zA-Z]/.test(newPassword) ||
      !/[0-9]/.test(newPassword) ||
      !/[^a-zA-Z0-9]/.test(newPassword)
    ) {
      event.preventDefault();
      setClientError(
        "Le mot de passe doit contenir au moins 12 caractères, avec une lettre, un chiffre et un symbole.",
      );
      return;
    }

    if (newPassword !== confirmPassword) {
      event.preventDefault();
      setClientError("Les mots de passe ne correspondent pas.");
      return;
    }

    setClientError(null);
  };

  return (
    <form
      action={formAction}
      onSubmit={handleSubmit}
      className="w-full max-w-sm"
    >
      <FieldGroup>
        {forced && (
          <p className="text-sm text-center text-muted-foreground">
            Votre mot de passe a plus de 60 jours, vous devez le renouveler
            pour continuer.
          </p>
        )}

        <Field>
          <FieldLabel htmlFor="currentPassword">
            Mot de passe actuel
          </FieldLabel>
          <Input
            id="currentPassword"
            name="currentPassword"
            type="password"
            required
          />
        </Field>

        <Field>
          <FieldLabel htmlFor="newPassword">Nouveau mot de passe</FieldLabel>
          <Input
            id="newPassword"
            name="newPassword"
            type="password"
            minLength={12}
            required
          />
        </Field>

        <Field>
          <FieldLabel htmlFor="confirmPassword">
            Confirmer le mot de passe
          </FieldLabel>
          <Input
            id="confirmPassword"
            name="confirmPassword"
            type="password"
            minLength={12}
            required
          />
        </Field>

        {(clientError ?? state.error) && (
          <FieldError>{clientError ?? state.error}</FieldError>
        )}

        <Field>
          <SubmitButton />
        </Field>
      </FieldGroup>
    </form>
  );
};
