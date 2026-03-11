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

	const handleSubmit = (e: React.FormEvent) => {
		e.preventDefault();
		api.auth.login(data.username, data.password)
	}

	return <form>
		<TextInput label="username" onChange={handleChange} />
		<TextInput label="password" type='password' onChange={handleChange} />
		<button type="submit" onClick={handleSubmit} className="mt-4 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
		  Login
		</button>
	</form>
}
