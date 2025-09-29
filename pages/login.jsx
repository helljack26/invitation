import React, { useState } from "react";
import { useRouter } from "next/router";
import { observer } from "mobx-react-lite";
import { useAuthStore } from "../stores/authStore"; // adjust the path if needed

const Login = observer(() => {
	const router = useRouter();

	const [email, setEmail] = useState("");
	const [password, setPassword] = useState("");
	const [error, setError] = useState("");

	const authStore = useAuthStore();

	const handleSubmit = async (event) => {
		event.preventDefault();
		setError("");

		const ok = await authStore.loginUser({ email, password });
		if (ok) {
			router.push("/admin");
		} else {
			setError("Невірні дані для входу");
		}
	};

	return (
		<div className="login-container">
			<h1>Вхід</h1>
			{error && <p className="error-message">{error}</p>}
			<form onSubmit={handleSubmit}>
				<label htmlFor="email">Електронна адреса:</label>
				<input
					type="email"
					id="email"
					name="email"
					value={email}
					onChange={(e) => setEmail(e.target.value)}
				/>
				<label htmlFor="password">Пароль:</label>
				<input
					type="password"
					id="password"
					name="password"
					value={password}
					onChange={(e) => setPassword(e.target.value)}
				/>
				<button type="submit">Увійти</button>
			</form>
		</div>
	);
});

export default Login;
