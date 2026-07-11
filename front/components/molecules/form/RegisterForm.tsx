"use client";

import { useActionState } from "react";
import { useFormStatus } from "react-dom";

import { registerAction, type AuthActionState } from "@/lib/actions/auth";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Field, FieldGroup, FieldLabel, FieldError } from "@/components/ui/field";

const initialState: AuthActionState = { error: null };

function SubmitButton() {
	const { pending } = useFormStatus();

	return (
		<Button type="submit" className="rounded-full" disabled={pending}>
			{pending ? "Inscription..." : "S'inscrire"}
		</Button>
	);
}

export default () => {
	const [state, formAction] = useActionState(registerAction, initialState);

	return (
		<form action={formAction} className="w-full max-w-sm">
			<FieldGroup>
				<Field>
					<FieldLabel htmlFor="username">Username</FieldLabel>
					<Input id="username" name="username" />
				</Field>

				<Field>
					<FieldLabel htmlFor="password">Mot de passe</FieldLabel>
					<Input id="password" name="password" type="password" />
				</Field>

				<Field>
					<FieldLabel htmlFor="confirmPassword">Confirmer le mot de passe</FieldLabel>
					<Input id="confirmPassword" name="confirmPassword" type="password" />
				</Field>

				{state.error && <FieldError>{state.error}</FieldError>}

				<Field>
					<SubmitButton />
				</Field>

				<p className="text-sm text-center">
					Déjà un compte ? <a href="/login" className="text-primary hover:underline">Connecte-toi</a>
				</p>
			</FieldGroup>
		</form>
	);
}
