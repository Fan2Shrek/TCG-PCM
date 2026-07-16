"use client";

import { useActionState, useState } from "react";
import { useFormStatus } from "react-dom";

import { registerAction, type AuthActionState } from "@/lib/actions/auth";
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
      {pending ? "Inscription..." : "S'inscrire"}
    </Button>
  );
}

export default function RegisterForm() {
  const [state, formAction] = useActionState(registerAction, initialState);
  const [clientError, setClientError] = useState<string | null>(null);

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    const formData = new FormData(event.currentTarget);
    const password = String(formData.get("password") || "");
    const confirmPassword = String(formData.get("confirmPassword") || "");

    if (
      password.length < 12 ||
      !/[a-zA-Z]/.test(password) ||
      !/[0-9]/.test(password) ||
      !/[^a-zA-Z0-9]/.test(password)
    ) {
      event.preventDefault();
      setClientError(
        "Le mot de passe doit contenir au moins 12 caractères, avec une lettre, un chiffre et un symbole.",
      );
      return;
    }

    if (password !== confirmPassword) {
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
        <Field>
          <FieldLabel htmlFor="username">Username</FieldLabel>
          <Input id="username" name="username" minLength={3} required />
        </Field>

        <Field>
          <FieldLabel htmlFor="email">Email</FieldLabel>
          <Input id="email" name="email" type="email" required />
        </Field>

        <Field>
          <FieldLabel htmlFor="password">Mot de passe</FieldLabel>
          <Input
            id="password"
            name="password"
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

        <p className="text-sm text-center">
          Déjà un compte ?{" "}
          <a href="/login" className="font-semibold text-primary hover:underline">
            Connecte-toi
          </a>
        </p>
      </FieldGroup>
    </form>
  );
};
