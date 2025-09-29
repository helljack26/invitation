// stores/GuestStore.js
import React from "react";
import { makeAutoObservable, runInAction } from "mobx";
import {
	ensureSeeded,
	loadGuests,
	saveGuests,
	nextGuestId,
	toSlug,
} from "../utils/localDb";

class Guest {
	guestData = null;
	guestsList = [];
	loading = true;
	error = null;

	constructor() {
		makeAutoObservable(this);
	}

	// Створення гостя у localStorage
	createGuest = async (guest) => {
		runInAction(() => {
			this.error = null;
		});
		try {
			ensureSeeded();
			const list = loadGuests();
			const id = nextGuestId();
			const unique_path =
				guest.unique_path || `${toSlug(guest.first_name || "guest")}-${id}`;

			const newGuest = {
				guest_id: id,
				unique_path,
				first_name: guest.first_name || "",
				first_name_plus_1: guest.first_name_plus_1 || "",
				gender: guest.gender || "",
				rsvp_status: "pending",
				alcohol_preferences: null,
				wine_type: null,
				custom_alcohol: null,
				rsvp_status_plus_one: "pending",
				alcohol_preferences_plus_one: null,
				wine_type_plus_one: null,
				custom_alcohol_plus_one: null,
			};

			list.push(newGuest);
			saveGuests(list);
			alert("Гостя створено успішно");
		} catch (err) {
			runInAction(() => {
				this.error = err;
			});
		} finally {
			runInAction(() => {
				this.loading = false;
			});
		}
	};

	// Оновлення гостя у localStorage
	updateGuest = async (guestId, patch) => {
		runInAction(() => {
			this.error = null;
		});
		try {
			ensureSeeded();
			const list = loadGuests();
			const idx = list.findIndex((g) => g.guest_id === guestId);
			if (idx !== -1) {
				list[idx] = { ...list[idx], ...patch };
				saveGuests(list);
				this.guestData = list[idx];
			}
		} catch (err) {
			runInAction(() => {
				this.error = err;
			});
		}
	};

	// Список гостей з localStorage
	listGuests = async () => {
		runInAction(() => {
			this.error = null;
		});
		try {
			ensureSeeded();
			const list = loadGuests();
			runInAction(() => {
				this.guestsList = list;
			});
		} catch (err) {
			runInAction(() => {
				this.error = err;
			});
		} finally {
			runInAction(() => {
				this.loading = false;
			});
		}
	};

	// Видалення гостя з localStorage
	deleteGuest = async (guestId) => {
		runInAction(() => {
			this.error = null;
		});
		try {
			ensureSeeded();
			const list = loadGuests().filter((g) => g.guest_id !== guestId);
			saveGuests(list);
			runInAction(() => {
				this.guestsList = list;
			});
		} catch (err) {
			runInAction(() => {
				this.error = err;
			});
		} finally {
			runInAction(() => {
				this.loading = false;
			});
		}
	};
}

const GuestStore = new Guest();

export const GuestStoreContext = React.createContext(GuestStore);
export const useGuestStore = () => React.useContext(GuestStoreContext);
export default GuestStore;
