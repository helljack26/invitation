export const authAxiosConfig = {
	withCredentials: true,
	headers: {
		"Content-Type": "application/json",
		common: {
			Authorization: true,
		},
	},
};
