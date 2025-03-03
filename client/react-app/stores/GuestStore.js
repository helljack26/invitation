import { makeAutoObservable, runInAction } from "mobx";
import axios from "axios";

class GuestStore {
	guestData = null; // For single guest details
	guestsList = []; // For list of guests
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
				"http://127.0.0.1/api/guest/getGuestByUniquePath",
				{ unique_path: uniquePath },
				{
					headers: { "Content-Type": "application/json" },
					withCredentials: false,
				}
			);
			runInAction(() => {
				this.guestData = response.data.guest;
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

	/**
	 * Create a new guest.
	 * Expects an object with guest properties.
	 */
	createGuest = async (guest) => {
		this.loading = true;
		this.error = null;
		try {
			const response = await axios.post("/api/guest/createGuest", guest);
			// Assuming the controller returns { guest_id: newId, ... }
			// Optionally, update guestData or refresh the list.
			this.guestData = response.data;
		} catch (err) {
			this.error = err;
		} finally {
			this.loading = false;
		}
	};

	/**
	 * Update an existing guest.
	 * Expects guest_id and other guest properties.
	 */
	updateGuest = async (guestId, guest) => {
		this.loading = true;
		this.error = null;
		try {
			// Combining guestId and guest properties into one object
			const payload = { guest_id: guestId, ...guest };
			const response = await axios.put("/api/guest/updateGuest", payload);
			// Assuming the controller returns { guest: { ... } }
			this.guestData = response.data.guest;
		} catch (err) {
			this.error = err;
		} finally {
			this.loading = false;
		}
	};

	/**
	 * Retrieve the list of all guests.
	 */
	listGuests = async () => {
		this.loading = true;
		this.error = null;
		try {
			const response = await axios.get("/api/guest/listGuests");
			// Assuming the controller returns { guests: [ ... ] }
			this.guestsList = response.data.guests;
		} catch (err) {
			this.error = err;
		} finally {
			this.loading = false;
		}
	};

	/**
	 * Retrieve a guest by its ID.
	 * Expects a JSON body with { guest_id: value }.
	 */
	getGuestById = async (guestId) => {
		this.loading = true;
		this.error = null;
		try {
			const response = await axios.post("/api/guest/getGuestById", {
				guest_id: guestId,
			});
			// Assuming the controller returns { guest: { ... } }
			this.guestData = response.data.guest;
		} catch (err) {
			this.error = err;
		} finally {
			this.loading = false;
		}
	};

	/**
	 * Delete a guest by its ID.
	 * Expects a JSON body with { guest_id: value }.
	 */
	deleteGuest = async (guestId) => {
		this.loading = true;
		this.error = null;
		try {
			// When using axios.delete with data, it must be provided as the 'data' option.
			await axios.delete("/api/guest/deleteGuest", {
				data: { guest_id: guestId },
			});
			// Optionally, remove the guest from the list if it exists there.
			this.guestsList = this.guestsList.filter(
				(guest) => guest.guest_id !== guestId
			);
		} catch (err) {
			this.error = err;
		} finally {
			this.loading = false;
		}
	};
}

export default new GuestStore();
