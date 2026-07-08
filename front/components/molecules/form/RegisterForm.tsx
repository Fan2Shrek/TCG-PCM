'use client';

import { useState } from "react";
import { useRouter } from "next/navigation";

import api from "@/lib/api/api";
import { useAuth } from "@/contexts/AuthContext";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Field, FieldGroup, FieldLabel, FieldError } from "@/components/ui/field";

export default () => {
	const [data, setData] = useState({ username: "", password: "", confirmPassword: "" });
	const [error, setError] = useState<string | null>(null);
	const [isLoading, setIsLoading] = useState(false);
	const { login } = useAuth();
	const router = useRouter();

	const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		const { name, value } = e.target;
		setData(prev => ({ ...prev, [name]: value }));
	};

	const handleSubmit = async (e: React.FormEvent) => {
		e.preventDefault();
		setError(null);

		if (data.password !== data.confirmPassword) {
			setError("Les mots de passe ne correspondent pas.");
			return;
		}

		setIsLoading(true);
		try {
			await api.auth.register(data.username, data.password);
			const response = await api.auth.login(data.username, data.password);
			login(response.token);
			router.push('/');
		} catch (err) {
			setError(err instanceof Error ? err.message : "Une erreur est survenue.");
		} finally {
			setIsLoading(false);
		}
	};

	return (
		<form onSubmit={handleSubmit} className="w-full max-w-sm">
			<FieldGroup>
				<Field>
					<FieldLabel htmlFor="username">Username</FieldLabel>
					<Input id="username" name="username" onChange={handleChange} />
				</Field>

				<Field>
					<FieldLabel htmlFor="password">Mot de passe</FieldLabel>
					<Input id="password" name="password" type="password" onChange={handleChange} />
				</Field>

				<Field>
					<FieldLabel htmlFor="confirmPassword">Confirmer le mot de passe</FieldLabel>
					<Input id="confirmPassword" name="confirmPassword" type="password" onChange={handleChange} />
				</Field>

				{error && <FieldError>{error}</FieldError>}

				<Field>
					<Button type="submit" className="rounded-full" disabled={isLoading}>
						{isLoading ? "Inscription..." : "S'inscrire"}
					</Button>
				</Field>

				<p className="text-sm text-center">
					Déjà un compte ? <a href="/login" className="text-primary hover:underline">Connecte-toi</a>
				</p>
			</FieldGroup>
		</form>
	);
}
