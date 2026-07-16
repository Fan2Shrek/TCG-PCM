"use client";

import { useActionState } from "react";
import { useFormStatus } from "react-dom";

import { forgotPasswordAction, type AuthActionState } from "@/lib/actions/auth";
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
      {pending ? "Envoi..." : "Envoyer le lien"}
    </Button>
  );
}

export default function ForgotPasswordForm() {
  const [state, formAction] = useActionState(forgotPasswordAction, initialState);

  return (
    <form action={formAction} className="w-full max-w-sm">
      <FieldGroup>
        <Field>
          <FieldLabel htmlFor="email">Email</FieldLabel>
          <Input id="email" name="email" type="email" required />
        </Field>

        {state.error && <FieldError>{state.error}</FieldError>}
        {state.message && (
          <p className="text-sm text-center text-muted-foreground">
            {state.message}
          </p>
        )}

        <Field>
          <SubmitButton />
        </Field>

        <p className="text-sm text-center">
          <a href="/login" className="font-semibold text-primary hover:underline">
            Retour à la connexion
          </a>
        </p>
      </FieldGroup>
    </form>
  );
};
