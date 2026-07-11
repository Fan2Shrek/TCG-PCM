"use client";

import { useActionState } from "react";
import { useFormStatus } from "react-dom";

import { loginAction, type AuthActionState } from "@/lib/actions/auth";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Field, FieldGroup, FieldLabel, FieldError } from "@/components/ui/field";

const initialState: AuthActionState = { error: null };

function SubmitButton() {
	const { pending } = useFormStatus();

	return (
		<Button type="submit" className="rounded-full" disabled={pending}>
			{pending ? "Connexion..." : "Login"}
		</Button>
	);
}

export default () => {
	const [state, formAction] = useActionState(loginAction, initialState);

	return (
		<form action={formAction} className="w-full max-w-sm">
			<FieldGroup>
				<Field>
					<FieldLabel htmlFor="username">Username</FieldLabel>
					<Input id="username" name="username" />
				</Field>

				<Field>
					<FieldLabel htmlFor="password">Password</FieldLabel>
					<Input id="password" name="password" type="password" />
				</Field>

				{state.error && <FieldError>{state.error}</FieldError>}

				<Field>
					<SubmitButton />
				</Field>

				<p className="text-sm text-center">
					Pas de compte ? <a href="/register" className="text-primary hover:underline">Inscris-toi</a>
				</p>
			</FieldGroup>
		</form>
	);
}
