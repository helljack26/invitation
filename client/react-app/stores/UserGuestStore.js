import { makeAutoObservable, runInAction } from "mobx";
import axios from "axios";
import { createContext, useContext } from "react";

class UserGuestStore {
	apiUrl = "http://127.0.0.1";
	guestData = null; // For single guest details
	loading = false;
	error = null;

	constructor() {
		makeAutoObservable(this);
	}

	/**
	 * Fetch a guest by its unique_path.
	 * Expects a JSON body with { unique_path: "value" }.
	 */
	getGuestByUniquePath = async (uniquePath) => {
		this.loading = true;
		this.error = null;
		try {
			const response = await axios.post(
				`${this.apiUrl}/api/guest/getGuestByUniquePath`,
				{ unique_path: uniquePath },
				{
					headers: { "Content-Type": "application/json" },
					withCredentials: true,
				}
			);
			console.log(response);

			runInAction(() => {
				this.guestData = response.data.guest;
				console.log(this.guestData);
			});
		} catch (err) {
			console.log("Error:", err);
			runInAction(() => {
				this.error = err;
			});
		} finally {
			runInAction(() => {
				this.loading = false;
			});
		}
	};

	// Update RSVP in the store and send request to backend
	updateGuestRSVP = async (status, isPlusOne = false) => {
		this.error = null;

		if (!this.guestData) return;

		// Build payload with unique_path and the specific status field.
		const payload = {
			unique_path: this.guestData.unique_path,
		};

		if (isPlusOne) {
			payload.rsvp_status_plus_one = status;
		} else {
			payload.rsvp_status = status;
		}

		try {
			const response = await axios.post(
				`${this.apiUrl}/api/guest/updateGuestSingleColumn`,
				payload,
				{
					headers: { "Content-Type": "application/json" },
					withCredentials: true,
				}
			);
			console.log(response);

			runInAction(() => {
				// Update state after a successful API request.
				if (isPlusOne) {
					this.guestData.rsvp_status_plus_one = status;
				} else {
					this.guestData.rsvp_status = status;
				}
			});
		} catch (err) {
			console.log("Error:", err);
			runInAction(() => {
				this.error = err;
			});
		}
	};
}

const userGuestStore = new UserGuestStore();

export const UserGuestStoreContext = createContext(userGuestStore);
export const useUserGuestStore = () => useContext(UserGuestStoreContext);

export default userGuestStore;
