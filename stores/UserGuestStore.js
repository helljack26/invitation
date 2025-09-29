// stores/UserGuestStore.js
import { makeAutoObservable, runInAction } from "mobx";
import { createContext, useContext } from "react";
import {
	ensureSeeded,
	getGuestByUniquePath,
	loadGuests,
	saveGuests,
} from "../utils/localDb";

class UserGuestStore {
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
	// Заміни існуючий метод цим
	getGuestByUniquePath = async (uniquePath) => {
		this.pageTimerStart = Date.now();
		this.error = null;
		try {
			ensureSeeded();
			const guest = getGuestByUniquePath(String(uniquePath));
			if (!guest) {
				throw new Error("Гостя не знайдено");
			}
			runInAction(() => {
				this.guestData = guest;
				this.loading = false;
				this.error = null;
				this.isDirty = false;
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

	// Заміни існуючий метод цим
	syncRSVPDataToServer = async () => {
		if (!this.guestData) return;
		if (!this.isDirty) return;

		try {
			const list = loadGuests();
			const idx = list.findIndex(
				(g) => g.unique_path === this.guestData.unique_path
			);
			if (idx !== -1) {
				// Оновлюємо тільки релевантні поля
				list[idx] = {
					...list[idx],
					rsvp_status: this.guestData.rsvp_status,
					rsvp_status_plus_one: this.guestData.rsvp_status_plus_one,
					alcohol_preferences: this.guestData.alcohol_preferences,
					alcohol_preferences_plus_one:
						this.guestData.alcohol_preferences_plus_one,
					wine_type: this.guestData.wine_type,
					wine_type_plus_one: this.guestData.wine_type_plus_one,
					custom_alcohol: this.guestData.custom_alcohol,
					custom_alcohol_plus_one: this.guestData.custom_alcohol_plus_one,
				};
				saveGuests(list);
			}
			runInAction(() => {
				this.isDirty = false;
			});
		} catch (err) {
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
