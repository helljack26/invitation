// stores/authStore.js
import React from "react";
import { observable, action, makeObservable } from "mobx";
import { LOCAL_KEYS, HARDCODED_ADMIN } from "../utils/localDb";

class Auth {
	isAuthenticated = false;

	constructor() {
		makeObservable(this, {
			isAuthenticated: observable,
			setAuthenticated: action,
			loginUser: action,
			logoutUser: action,
			checkLoginStatus: action,
		});

		if (typeof window !== "undefined") {
			this.isAuthenticated =
				localStorage.getItem(LOCAL_KEYS.AUTH) === "true";
		}
	}

	setAuthenticated(value) {
		this.isAuthenticated = value;
		if (typeof window !== "undefined") {
			localStorage.setItem(LOCAL_KEYS.AUTH, value ? "true" : "false");
		}
	}

	// Локальний логін: звіряємо з HARDCODED_ADMIN
	loginUser = async ({ email, password }) => {
		const ok =
			email === HARDCODED_ADMIN.email &&
			password === HARDCODED_ADMIN.password;
		this.setAuthenticated(ok);
		return ok;
	};

	// Локальний логаут
	logoutUser = async () => {
		this.setAuthenticated(false);
	};

	// Перевірка статусу логіну з localStorage
	checkLoginStatus = async () => {
		if (typeof window === "undefined") return false;
		this.setAuthenticated(localStorage.getItem(LOCAL_KEYS.AUTH) === "true");
		return this.isAuthenticated;
	};

	// Реєстрація тут не потрібна — адмін один і захардкожений
	signupUser = async () => {
		return 405;
	};
}

const AuthStore = new Auth();

export const AuthStoreContext = React.createContext(AuthStore);
export const useAuthStore = () => React.useContext(AuthStoreContext);
export default AuthStore;
