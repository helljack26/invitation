// stores/UserGuestStore.js
import { makeAutoObservable, runInAction } from "mobx";
import axios from "axios";
import { createContext, useContext } from "react";

class UserGuestStore {
	apiUrl = "http://127.0.0.1";
	guestData = null; // For single guest details
	loading = true;
	error = null;
	isDirty = false; // flag to track unsynced changes
	pageTimerStart = null;

	constructor() {
		makeAutoObservable(this);
	}

	/**
	 * Fetch a guest by its unique_path.
	 */
	getGuestByUniquePath = async (uniquePath) => {
		// Start the timer
		this.pageTimerStart = Date.now();
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
			console.log(
				"🚀 ~ UserGuestStore ~ getGuestByUniquePath= ~ response:",
				response
			);
			runInAction(() => {
				this.guestData = response.data.guest;
				this.loading = false;
				this.error = null;
				this.isDirty = false; // fresh data is synced
			});
		} catch (err) {
			runInAction(() => {
				this.error = err;
				this.loading = false;
			});
		}
	};

	/**
	 * ===============================
	 *   LOCAL-ONLY STATE UPDATERS
	 * ===============================
	 */

	// 1) Set RSVP status in store only (no request)
	setRSVPStatusInStore = (status, isPlusOne = false) => {
		if (!this.guestData) return;
		runInAction(() => {
			if (isPlusOne) {
				this.guestData.rsvp_status_plus_one = status;
			} else {
				this.guestData.rsvp_status = status;
			}
			this.isDirty = true;
		});
	};

	// 2) Alcohol preference
	setAlcoholPreferenceInStore = (value, isPlusOne = false) => {
		if (!this.guestData) return;
		runInAction(() => {
			if (isPlusOne) {
				this.guestData.alcohol_preferences_plus_one = value;
			} else {
				this.guestData.alcohol_preferences = value;
			}
			this.isDirty = true;
		});
	};

	// 3) Wine type preference
	setWineTypeInStore = (value, isPlusOne = false) => {
		if (!this.guestData) return;
		runInAction(() => {
			if (isPlusOne) {
				this.guestData.wine_type_plus_one = value;
			} else {
				this.guestData.wine_type = value;
			}
			this.isDirty = true;
		});
	};

	// 4) Custom alcohol text
	setCustomAlcoholInStore = (value, isPlusOne = false) => {
		if (!this.guestData) return;
		runInAction(() => {
			if (isPlusOne) {
				this.guestData.custom_alcohol_plus_one = value;
			} else {
				this.guestData.custom_alcohol = value;
			}
			this.isDirty = true;
		});
	};

	/**
	 * Helper function to format time in hh:mm:ss.
	 */
	formatTime = (milliseconds) => {
		const totalSeconds = Math.floor(milliseconds / 1000);
		const hours = Math.floor(totalSeconds / 3600);
		const minutes = Math.floor((totalSeconds % 3600) / 60);
		const seconds = totalSeconds % 60;
		return (
			hours.toString().padStart(2, "0") +
			":" +
			minutes.toString().padStart(2, "0") +
			":" +
			seconds.toString().padStart(2, "0")
		);
	};

	syncRSVPDataToServer = async () => {
		if (!this.guestData) return;

		// Helper function: returns null if status is "declined" or "pending", else returns the provided value.
		const getValueOrNull = (status, value) =>
			status === "declined" || status === "pending" ? null : value;

		// Calculate time spent on the page in milliseconds
		// Calculate time spent on the page in milliseconds
		const timeSpentMs = this.pageTimerStart
			? Date.now() - this.pageTimerStart
			: 0;
		// Format the time into hh:mm:ss
		const formattedTimeSpent = this.formatTime(timeSpentMs);

		// Helper function for custom fields:
		// Returns null if status is "declined" or "pending", or if the alcohol choice is not "custom".
		const getCustomValueOrNull = (status, alcoholChoice, customValue) =>
			status === "declined" ||
			status === "pending" ||
			alcoholChoice !== "custom"
				? null
				: customValue;

		const payload = {
			unique_path: this.guestData.unique_path,
			rsvp_status: this.guestData.rsvp_status,
			rsvp_status_plus_one: this.guestData.rsvp_status_plus_one,
			alcohol_preferences: getValueOrNull(
				this.guestData.rsvp_status,
				this.guestData.alcohol_preferences
			),
			alcohol_preferences_plus_one: getValueOrNull(
				this.guestData.rsvp_status_plus_one,
				this.guestData.alcohol_preferences_plus_one
			),
			wine_type: getValueOrNull(
				this.guestData.rsvp_status,
				this.guestData.wine_type
			),
			wine_type_plus_one: getValueOrNull(
				this.guestData.rsvp_status_plus_one,
				this.guestData.wine_type_plus_one
			),
			custom_alcohol: getCustomValueOrNull(
				this.guestData.rsvp_status,
				this.guestData.alcohol_preferences,
				this.guestData.custom_alcohol
			),
			custom_alcohol_plus_one: getCustomValueOrNull(
				this.guestData.rsvp_status_plus_one,
				this.guestData.alcohol_preferences_plus_one,
				this.guestData.custom_alcohol_plus_one
			),
			time_spent_formatted: formattedTimeSpent,
		};

		try {
			const response = await axios.post(
				`${this.apiUrl}/api/guest/updateGuestDataByUser`,
				payload,
				{
					headers: { "Content-Type": "application/json" },
					withCredentials: true,
				}
			);
			console.log("syncRSVPDataToServer response", response.data);
			runInAction(() => {
				this.isDirty = false;
			});
		} catch (err) {
			console.log("Error syncing RSVP data:", err);
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
