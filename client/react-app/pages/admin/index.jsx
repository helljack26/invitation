import { useEffect, useState } from "react";
import { observer } from "mobx-react-lite";
// Store
import { useAuthStore } from "../../stores/authStore"; // adjust the path if needed
import { useGuestStore } from "../../stores/GuestStore";
import { useRouter } from "next/router";
// Component
import Head from "next/head";
import Link from "next/link";
import AlcoholSummary from "../../components/admin/AlcoholSummary";

const AdminPage = observer(() => {
	// Import auth store and Next.js router
	const { isAuthenticated, checkLoginStatus, logoutUser } = useAuthStore();
	// G
	const {
		guestsList,
		createGuest,
		listGuests,
		deleteGuest,
		updateGuest,
		loading,
		error,
	} = useGuestStore();
	const router = useRouter();
	const [checkingAuth, setCheckingAuth] = useState(true);

	// Check login status on mount
	useEffect(() => {
		// Wrap your checkLoginStatus in an async function
		const doCheck = async () => {
			await checkLoginStatus();
			// Once the store has updated isAuthenticated, we set checkingAuth to false
			setCheckingAuth(false);
		};
		doCheck();
	}, [checkLoginStatus]);

	useEffect(() => {
		// Only redirect if we're done checking AND user is not authenticated
		if (!checkingAuth && !isAuthenticated) {
			router.push("/login");
		}
	}, [checkingAuth, isAuthenticated, router]);

	const [showDeleteModal, setShowDeleteModal] = useState(false);
	const [guestToDelete, setGuestToDelete] = useState(null);

	// ----------------------------------------------------------------
	// STATES FOR CREATING NEW GUEST
	// ----------------------------------------------------------------
	const [newGuestFirstName, setNewGuestFirstName] = useState("");
	const [plusOne, setPlusOne] = useState(false);
	const [plusOneName, setPlusOneName] = useState("");
	const [guestGender, setGuestGender] = useState("");

	// ----------------------------------------------------------------
	// STATES FOR EDITING EXISTING GUEST
	// ----------------------------------------------------------------
	const [showEditModal, setShowEditModal] = useState(false);
	const [editingGuestId, setEditingGuestId] = useState(null);

	// These mirror the creation fields
	const [editGuestFirstName, setEditGuestFirstName] = useState("");
	const [editPlusOne, setEditPlusOne] = useState(false);
	const [editPlusOneName, setEditPlusOneName] = useState("");
	const [editGuestGender, setEditGuestGender] = useState("");

	useEffect(() => {
		// On page load, get the list of guests
		if (isAuthenticated) {
			listGuests();
		}
	}, [isAuthenticated]);

	// ----------------------------------------------------------------
	// CREATE A NEW GUEST
	// ----------------------------------------------------------------
	const handleAddGuest = async () => {
		if (!newGuestFirstName) {
			alert("Будь ласка, заповніть ім'я!");
			return;
		}
		// If guest doesn't have plus one, we need gender
		if (!plusOne && !guestGender) {
			alert("Будь ласка, оберіть стать гостя!");
			return;
		}

		const guestPayload = {
			first_name: newGuestFirstName,
			first_name_plus_1: plusOne ? plusOneName : "",
			...(!plusOne && { gender: guestGender }),
		};

		await createGuest(guestPayload);

		// Clear the fields
		setNewGuestFirstName("");
		setPlusOne(false);
		setPlusOneName("");
		setGuestGender("");

		// Refresh the list
		listGuests();
	};

	// ----------------------------------------------------------------
	// DELETE GUEST LOGIC
	// ----------------------------------------------------------------
	const confirmDelete = (guest) => {
		setGuestToDelete(guest);
		setShowDeleteModal(true);
	};

	const handleDelete = async () => {
		if (guestToDelete) {
			await deleteGuest(guestToDelete.guest_id);
			setGuestToDelete(null);
			setShowDeleteModal(false);
		}
	};

	const handleCancelDelete = () => {
		setGuestToDelete(null);
		setShowDeleteModal(false);
	};

	// ----------------------------------------------------------------
	// EDIT GUEST LOGIC
	// ----------------------------------------------------------------
	const handleEditClick = (guest) => {
		// Store guest ID so we know who we're updating
		setEditingGuestId(guest.guest_id);

		// Populate fields with existing guest data
		setEditGuestFirstName(guest.first_name || "");
		setEditPlusOne(!!guest.first_name_plus_1);
		setEditPlusOneName(guest.first_name_plus_1 || "");
		setEditGuestGender(guest.gender || "");

		setShowEditModal(true);
	};

	const handleUpdateGuest = async () => {
		if (!editGuestFirstName) {
			alert("Будь ласка, заповніть ім'я!");
			return;
		}
		if (!editPlusOne && !editGuestGender) {
			alert("Будь ласка, оберіть стать гостя!");
			return;
		}

		const payload = {
			first_name: editGuestFirstName,
			first_name_plus_1: editPlusOne ? editPlusOneName : "",
			// If there's no plus one, include the gender;
			// otherwise, omit it so as not to overwrite incorrectly.
			...(!editPlusOne && { gender: editGuestGender }),
		};

		await updateGuest(editingGuestId, payload);

		// After success, close modal and refresh list
		setShowEditModal(false);
		setEditingGuestId(null);
		listGuests();
	};

	// ----------------------------------------------------------------
	// STYLING & HELPER
	// ----------------------------------------------------------------
	const getRsvpStyle = (status) => {
		if (status === "accepted") return { color: "green" };
		if (status === "declined") return { color: "red" };
		if (status === "pending") return { color: "orange" };
		return {};
	};
	if (checkingAuth) {
		// Show some loading indicator
		return null;
	}
	if (loading) return <p>Завантаження...</p>;
	if (error) return <p>Помилка: {error.message}</p>;

	return (
		<>
			<Head>
				<title>Гості - Адміністрація</title>
				<link
					rel="stylesheet"
					href="/styles/materialize.min.css"
				/>
				<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
			</Head>
			<div className="container admin_container">
				<h1 className="center-align">Гості</h1>

				{/* LOGOUT BUTTON */}
				<button
					className="logout_btn"
					onClick={async () => {
						await logoutUser();
						router.push("/login");
					}}
				>
					Вийти
				</button>

				{/* CREATE GUEST FORM */}
				<div className="admin_container_form">
					<h2>Додати нового гостя</h2>
					<div className="row">
						<div className="input-field col s12 m4">
							<input
								id="first_name"
								type="text"
								value={newGuestFirstName}
								onChange={(e) => setNewGuestFirstName(e.target.value)}
							/>
							<label
								htmlFor="first_name"
								className={newGuestFirstName ? "active" : ""}
							>
								Ім'я гостя*
							</label>
						</div>

						<div className="center_checkbox_container plus_one_checkbox input-field col s12 m4">
							<label>
								<input
									type="checkbox"
									checked={plusOne}
									onChange={() => setPlusOne(!plusOne)}
								/>
								<span>Додатковий гість?</span>
							</label>
						</div>

						{plusOne ? (
							<div className="right_select_container input-field col s12 m4">
								<input
									id="plus_one_name"
									type="text"
									value={plusOneName}
									onChange={(e) => setPlusOneName(e.target.value)}
								/>
								<label
									htmlFor="plus_one_name"
									className={plusOneName ? "active" : ""}
								>
									Ім'я додаткового гостя
								</label>
							</div>
						) : (
							<div className="right_select_container input-field col s12 m4">
								<select
									value={guestGender}
									onChange={(e) => setGuestGender(e.target.value)}
									className="browser-default"
								>
									<option value="">Оберіть стать гостя</option>
									<option value="male">Чоловік</option>
									<option value="female">Жінка</option>
								</select>
							</div>
						)}
					</div>
					<div
						className="row"
						style={{ marginBottom: "0" }}
					>
						<div className="col s12 center-align">
							<button
								className="btn"
								onClick={handleAddGuest}
							>
								Додати гостя
							</button>
						</div>
					</div>
				</div>
				{/* GUESTS TABLE */}
				<h2 className="center-align">Всі гості</h2>
				<table className="striped responsive-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Url</th>
							<th>Посилання</th>
							<th>Повне ім'я</th>
							<th>Стать</th>
							<th>Статус</th>
							<th>Алкогольні</th>
							<th>Дод. гість</th>
							<th>
								Статус
								<br />
								(дод. гість)
							</th>
							<th>
								Алкоголь
								<br />
								(дод. гість)
							</th>
							<th>Дії</th>
						</tr>
					</thead>
					<tbody>
						{guestsList.map((guest) => (
							<tr key={guest.guest_id}>
								<td>{guest.guest_id}</td>
								<td>{guest.unique_path}</td>
								<td>
									<Link
										target="_blank"
										href={`/invite/${guest.unique_path}`}
									>
										Переглянути запрошення
									</Link>
								</td>
								<td>{guest.first_name}</td>
								<td>
									{guest.gender
										? guest.gender === "male"
											? "Чоловік"
											: "Жінка"
										: "-"}
								</td>
								<td style={getRsvpStyle(guest.rsvp_status)}>
									{guest.rsvp_status === "pending"
										? "Очікує"
										: guest.rsvp_status === "accepted"
										? "Прийнято"
										: guest.rsvp_status === "declined"
										? "Відхилено"
										: "-"}
								</td>
								<td>
									{guest.alcohol_preferences ? (
										<>
											{guest.wine_type ? (
												<>
													<div>{guest.alcohol_preferences}</div>
													<div>
														<i>Тип вина:</i> {guest.wine_type}
													</div>
												</>
											) : (
												<div>{guest.alcohol_preferences}</div>
											)}
											{guest.custom_alcohol && (
												<div>
													<i>Свій варіант:</i>{" "}
													{guest.custom_alcohol}
												</div>
											)}
										</>
									) : (
										"-"
									)}
								</td>
								<td>
									{guest.first_name_plus_1
										? guest.first_name_plus_1
										: "-"}
								</td>
								<td style={getRsvpStyle(guest.rsvp_status_plus_one)}>
									{guest.rsvp_status_plus_one === "pending"
										? "Очікує"
										: guest.rsvp_status_plus_one === "accepted"
										? "Прийнято"
										: guest.rsvp_status_plus_one === "declined"
										? "Відхилено"
										: "-"}
								</td>
								<td>
									{guest.alcohol_preferences_plus_one ? (
										<>
											{guest.wine_type_plus_one ? (
												<>
													<div>
														{guest.alcohol_preferences_plus_one}
													</div>
													<div>
														<i>Тип вина:</i>{" "}
														{guest.wine_type_plus_one}
													</div>
												</>
											) : (
												<div>
													{guest.alcohol_preferences_plus_one}
												</div>
											)}
											{guest.custom_alcohol_plus_one && (
												<div>
													<i>Свій варіант:</i>
													{guest.custom_alcohol_plus_one}
												</div>
											)}
										</>
									) : (
										"-"
									)}
								</td>

								<td className="table_button_row">
									<button
										className="btn edit lighten-1"
										style={{ marginLeft: "0.5rem" }}
										onClick={() => handleEditClick(guest)}
									>
										Редагувати
									</button>
									<button
										className="btn red lighten-1"
										onClick={() => confirmDelete(guest)}
									>
										X
									</button>
								</td>
							</tr>
						))}
					</tbody>
				</table>

				{/* Підсумок по алкоголю */}
				<AlcoholSummary />

				{/* DELETE CONFIRMATION MODAL */}
				{showDeleteModal && (
					<div className="modal">
						<div className="modal-content">
							<h4>Підтвердження</h4>
							<p>Ви впевнені, що хочете видалити гостя?</p>
							<div
								className="modal-footer"
								style={{ padding: "1rem" }}
							>
								<button
									className="btn red lighten-1"
									onClick={handleDelete}
								>
									Так
								</button>
								<button
									className="btn-flat"
									onClick={handleCancelDelete}
								>
									Ні
								</button>
							</div>
						</div>
					</div>
				)}

				{/* EDIT GUEST MODAL */}
				{showEditModal && (
					<div className="modal modal_edit">
						<div className="modal-content">
							<h4>Редагувати гостя</h4>
							<div
								className="row"
								style={{ marginBottom: "1rem" }}
							>
								<div className="input-field col s12 m4">
									<input
										id="edit_first_name"
										type="text"
										value={editGuestFirstName}
										onChange={(e) =>
											setEditGuestFirstName(e.target.value)
										}
									/>
									<label
										htmlFor="edit_first_name"
										className={editGuestFirstName ? "active" : ""}
									>
										Ім'я
									</label>
								</div>

								<div className="input-field col s12 m4 plus_one_checkbox">
									<label>
										<input
											type="checkbox"
											checked={editPlusOne}
											onChange={() => setEditPlusOne(!editPlusOne)}
										/>
										<span>Додатковий гість?</span>
									</label>
								</div>

								{editPlusOne ? (
									<div className="right_select_container input-field col s12 m4">
										<input
											id="edit_plus_one_name"
											type="text"
											value={editPlusOneName}
											onChange={(e) =>
												setEditPlusOneName(e.target.value)
											}
										/>
										<label
											htmlFor="edit_plus_one_name"
											className={editPlusOneName ? "active" : ""}
										>
											Ім'я додаткового гостя
										</label>
									</div>
								) : (
									<div className="right_select_container input-field col s12 m4">
										<select
											value={editGuestGender}
											onChange={(e) =>
												setEditGuestGender(e.target.value)
											}
											className="browser-default"
										>
											<option value="">Оберіть стать гостя</option>
											<option value="male">Чоловік</option>
											<option value="female">Жінка</option>
										</select>
									</div>
								)}
							</div>
							<div
								className="modal-footer"
								style={{ padding: "1rem" }}
							>
								<button
									className="btn"
									onClick={handleUpdateGuest}
								>
									Зберегти
								</button>
								<button
									className="btn-flat"
									onClick={() => setShowEditModal(false)}
								>
									Скасувати
								</button>
							</div>
						</div>
					</div>
				)}
			</div>
		</>
	);
});

export default AdminPage;
