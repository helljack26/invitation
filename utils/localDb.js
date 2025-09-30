// utils/localDb.js
// ⚙️ Проста "локальна БД" на localStorage для гостей та авторизації.
// Коментарі — українською для зручності підтримки.

export const LOCAL_KEYS = {
	GUESTS: "wedding.guests",
	AUTH: "wedding.auth",
};

export const HARDCODED_ADMIN = {
	email: "admin@wedding.local",
	password: "love2025",
};

// Початкові (демо) дані гостей, підвантажаться один раз
// utils/localDb.js
const SEED_GUESTS = [
	{
		guest_id: 1,
		unique_path: "mykola-olena", // зручно для лінку /invite/mykola-olena
		first_name: "Микола",
		first_name_plus_1: "Олена", // ← +1
		gender: "", // коли є +1 — стать основного гостя можна лишити порожньою
		rsvp_status: "pending",
		alcohol_preferences: null,
		wine_type: null,
		custom_alcohol: null,
		rsvp_status_plus_one: "pending",
		alcohol_preferences_plus_one: null,
		wine_type_plus_one: null,
		custom_alcohol_plus_one: null,
	},
];

export function ensureSeeded() {
	if (typeof window === "undefined") return;
	if (!localStorage.getItem(LOCAL_KEYS.GUESTS)) {
		localStorage.setItem(LOCAL_KEYS.GUESTS, JSON.stringify(SEED_GUESTS));
	}
}

export function loadGuests() {
	try {
		const raw = localStorage.getItem(LOCAL_KEYS.GUESTS);
		return raw ? JSON.parse(raw) : [];
	} catch {
		return [];
	}
}

export function saveGuests(list) {
	localStorage.setItem(LOCAL_KEYS.GUESTS, JSON.stringify(list));
}

export function getGuestByUniquePath(path) {
	const list = loadGuests();
	return list.find((g) => g.unique_path === path) || null;
}

export function nextGuestId() {
	const list = loadGuests();
	return list.length ? Math.max(...list.map((g) => g.guest_id || 0)) + 1 : 1;
}

export function toSlug(text) {
	return (text || "guest")
		.toString()
		.toLowerCase()
		.replace(/\s+/g, "-")
		.replace(/[^\w\-]+/g, "")
		.replace(/\-\-+/g, "-")
		.replace(/^-+/, "")
		.replace(/-+$/, "");
}
