'use client';

import { useState } from "react";
import { useRouter } from "next/navigation";

import api from "@/lib/api/api";
import { useAuth } from "@/context/AuthContext";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Field, FieldGroup, FieldLabel, FieldError } from "@/components/ui/field";

export default () => {
	const [data, setData] = useState({ username: "", password: "" });
	const [error, setError] = useState<string | null>(null);
	const { login } = useAuth();
	const router = useRouter();

	const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		const { name, value } = e.target;
		setData(prev => ({ ...prev, [name]: value }))
	}

	const handleSubmit = async (e: React.FormEvent) => {
		e.preventDefault();
		setError(null);

		try {
			await api.auth.register(data.username, data.password);
			const response = await api.auth.login(data.username, data.password);
			login(response.token);
			router.push('/');
		} catch (err) {
			setError(err instanceof Error ? err.message : 'mdr ca a explosé, inscription pas encore branchée');
		}
	}

	return (
		<form onSubmit={handleSubmit} className="w-full max-w-sm">
			<FieldGroup>
				<Field>
					<FieldLabel htmlFor="username">Username</FieldLabel>
					<Input id="username" name="username" onChange={handleChange} />
				</Field>

				<Field>
					<FieldLabel htmlFor="password">Password</FieldLabel>
					<Input id="password" name="password" type="password" onChange={handleChange} />
				</Field>

				{error && <FieldError>{error}</FieldError>}

				<Field>
					<Button type="submit" className="rounded-full">
						S'inscrire
					</Button>
				</Field>

				<p className="text-sm text-center">
					Déjà un compte ? <a href="/login" className="text-primary hover:underline">Connecte-toi</a>
				</p>
			</FieldGroup>
		</form>
	);
}
