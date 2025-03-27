import React from "react";

import { observable, action, makeObservable } from "mobx";
import axios from "axios";
import { authAxiosConfig } from "./authAxiosConfig";

class Auth {
	apiUrl = "http://127.0.0.1";
	isAuthenticated = false;
	appPath = process.env.REACT_APP_PATH;
	axiosConfig = authAxiosConfig;

	constructor() {
		makeObservable(this, {
			isAuthenticated: observable,
			setAuthenticated: action,
			loginUser: action,
			logoutUser: action,
			checkLoginStatus: action,
			signupUser: action,
		});
	}

	setAuthenticated(value) {
		this.isAuthenticated = value;
	}

	// Login
	loginUser = async (data) => {
		try {
			const loginUrl = `${this.apiUrl}/api/auth/login`;
			// Send a request to your server to check authentication status
			const response = await axios.post(loginUrl, data, authAxiosConfig);
			console.log("🚀 ~ Auth ~ loginUser= ~ response:", response);
			if (response) {
				// Handle the response data here
				return response;
			}
		} catch (error) {
			return error.response;
		}
	};

	// Logout
	logoutUser = async () => {
		try {
			const logoutUrl = `${this.apiUrl}/api/auth/logout`;

			// Send a request to your server to check authentication status
			const response = await axios.post(logoutUrl, "", this.axiosConfig);

			// Handle the response data here
			if (response.status === 200) {
				this.setAuthenticated(false);
			}
		} catch (error) {
			// Handle any errors here
		}
	};

	// Check login status
	checkLoginStatus = async () => {
		try {
			const checkUrl = `${this.apiUrl}/api/auth/authenticate`;
			// Send a request to your server to check authentication status
			const response = await axios.post(checkUrl, [], this.axiosConfig);
			console.log("🚀 ~ Auth ~ checkLoginStatus= ~ response:", response);

			if (response) {
				if (response.data.authenticated) {
					// User is already authenticated
					this.setAuthenticated(true);
				} else {
					this.setAuthenticated(false);
				}
			}
		} catch (error) {
			console.error("Error checking login status:", error);
			this.setAuthenticated(false);
		}
	};

	// Signup
	signupUser = async (formData, multilanguageStore) => {
		const loginUrl = `${this.apiUrl}/api/auth/register`;

		try {
			// Send a request to your server to check authentication status
			const response = await axios.post(
				loginUrl,
				formData,
				this.axiosConfig
			);

			// Handle the response data here
			if (response.status) {
				return response.status;
			}
		} catch (error) {
			if (error.response.status === 400) {
				return multilanguageStore.getTranslation(
					"signup.registrationError"
				);
			} else if (error.response.status === 409) {
				return multilanguageStore.getTranslation(
					"signup.duplicateUserError"
				);
			} else if (error.response.status === 406) {
				return multilanguageStore.getTranslation("signup.emptyFieldsError");
			} else if (error.response.status === 405) {
				return multilanguageStore.getTranslation("signup.noDataError");
			} else {
				console.log(error.message);
			}
		}
	};
}

const AuthStore = new Auth();

export const AuthStoreContext = React.createContext(AuthStore);
export const useAuthStore = () => React.useContext(AuthStoreContext);
