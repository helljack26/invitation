import { makeAutoObservable } from "mobx";
import axios from "axios";

class BackendStore {
	data = null;
	loading = false;
	error = null;

	constructor() {
		makeAutoObservable(this);
	}

	fetchData = async () => {
		this.loading = true;
		this.error = null;
		try {
			const response = await axios.get("/api/data");
			this.data = response.data;
		} catch (err) {
			this.error = err;
		} finally {
			this.loading = false;
		}
	};
}

export default new BackendStore();
