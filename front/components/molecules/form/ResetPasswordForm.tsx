"use client";

import { useActionState, useState } from "react";
import { useFormStatus } from "react-dom";

import { resetPasswordAction, type AuthActionState } from "@/lib/actions/auth";
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
      {pending ? "Réinitialisation..." : "Réinitialiser le mot de passe"}
    </Button>
  );
}

export default function ResetPasswordForm({ token }: { token: string }) {
  const [state, formAction] = useActionState(resetPasswordAction, initialState);
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

  if (!token) {
    return (
      <p className="text-sm text-center">
        Ce lien de réinitialisation est invalide. Redemande un lien depuis la
        page{" "}
        <a href="/forgot-password" className="font-semibold text-primary hover:underline">
          mot de passe oublié
        </a>
        .
      </p>
    );
  }

  return (
    <form
      action={formAction}
      onSubmit={handleSubmit}
      className="w-full max-w-sm"
    >
      <FieldGroup>
        <input type="hidden" name="token" value={token} />

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
