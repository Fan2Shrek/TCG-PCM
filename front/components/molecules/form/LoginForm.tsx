'use client';

import { useState } from "react";

import TextInput from "../../atoms/form/TextInput";
import api from "../../../lib/api/api";

export default () => {
	const [data, setData] = useState({})

	const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
		const { name, value } = e.target;
		setData(prev => ({ ...prev, [name]: value }))
	}

	const handleSubmit = async (e: React.FormEvent) => {
		e.preventDefault();
		const response = await api.auth.login(data.username, data.password)
		const token = response.token;
		document.cookie = `token=${token}; path=/; max-age=36000; secure; samesite=strict`;
	}

	return <form>
		<TextInput label="username" onChange={handleChange} />
		<TextInput label="password" type='password' onChange={handleChange} />
		<button type="submit" onClick={handleSubmit} className="mt-4 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
		  Login
		</button>
	</form>
}
