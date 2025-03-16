import { makeAutoObservable, runInAction } from "mobx";
import axios from "axios";

class GuestStore {
	apiUrl = "http://127.0.0.1";
	guestData = null; // For single guest details
	guestsList = []; // For list of guests
	loading = true;
	error = null;

	constructor() {
		makeAutoObservable(this);
	}

	/**
	 * Create a new guest.
	 * Expects an object with guest properties.
	 */
	createGuest = async (guest) => {
		runInAction(() => {
			this.loading = true;
			this.error = null;
		});
		try {
			const response = await axios.post(
				`${this.apiUrl}/api/guest/createGuest`,
				guest
			);
			console.log("🚀 ~ GuestStore ~ createGuest= ~ response:", response);
			if (response.status === 200) {
				alert("Гостя створено успішно");
			}
		} catch (err) {
			console.log("🚀 ~ GuestStore ~ createGuest= ~ err:", err);
			runInAction(() => {
				this.error = err;
			});
		} finally {
			runInAction(() => {
				this.loading = false;
			});
		}
	};

	/**
	 * Update an existing guest.
	 * Expects guest_id and other guest properties.
	 */
	updateGuest = async (guestId, guest) => {
		runInAction(() => {
			this.loading = true;
			this.error = null;
		});
		try {
			// Combining guestId and guest properties into one object
			const payload = { guest_id: guestId, ...guest };
			const response = await axios.put(
				`${this.apiUrl}/api/guest/updateGuest`,
				payload
			);
			// Assuming the controller returns { guest: { ... } }
			this.guestData = response.data.guest;
		} catch (err) {
			console.log("🚀 ~ GuestStore ~ updateGuest= ~ err:", err);
			runInAction(() => {
				this.error = err;
			});
		} finally {
			runInAction(() => {
				this.loading = false;
			});
		}
	};

	/**
	 * Retrieve the list of all guests.
	 */
	listGuests = async () => {
		// this.loading = true;
		runInAction(() => {
			this.error = null;
		});
		try {
			const response = await axios.post(
				`${this.apiUrl}/api/guest/listGuests`,
				{
					headers: { "Content-Type": "application/json" },
					withCredentials: true,
				}
			);
			console.log("🚀 ~ GuestStore ~ listGuests= ~ response:", response);
			// Assuming the controller returns { guests: [ ... ] }
			runInAction(() => {
				this.guestsList = response.data.guests;
			});
		} catch (err) {
			console.log("🚀 ~ GuestStore ~ createGuest= ~ err:", err);
			runInAction(() => {
				this.error = err;
			});
		} finally {
			runInAction(() => {
				this.loading = false;
			});
		}
	};

	/**
	 * Delete a guest by its ID.
	 * Expects a JSON body with { guest_id: value }.
	 */
	deleteGuest = async (guestId) => {
		runInAction(() => {
			this.error = null;
		});
		try {
			// When using axios.delete with data, it must be provided as the 'data' option.
			await axios.delete(`${this.apiUrl}/api/guest/deleteGuest`, {
				data: { guest_id: guestId },
			});
			runInAction(() => {
				// Optionally, remove the guest from the list if it exists there.
				this.guestsList = this.guestsList.filter(
					(guest) => guest.guest_id !== guestId
				);
			});
		} catch (err) {
			console.log("🚀 ~ GuestStore ~ createGuest= ~ err:", err);
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

export default new GuestStore();
