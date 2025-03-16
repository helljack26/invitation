import { useEffect, useState } from "react";
import { observer } from "mobx-react-lite";
import GuestStore from "../../stores/GuestStore";
import { transliterate } from "../../components/helpers/transliterate";
// import { Link } from "react-router-dom";
import Head from "next/head";
import Link from "next/link";
import AlcoholSummary from "../../components/admin/AlcoholSummary";

const AdminPage = observer(() => {
	const [showModal, setShowModal] = useState(false);
	const [guestToDelete, setGuestToDelete] = useState(null);

	// Тимчасові стани для створення нового гостя
	const [newGuestFirstName, setNewGuestFirstName] = useState("");
	const [plusOne, setPlusOne] = useState(false);
	const [plusOneName, setPlusOneName] = useState("");
	// Стейт для вибору статі гостя (якщо окремий гість)
	const [guestGender, setGuestGender] = useState("");

	useEffect(() => {
		// При завантаженні сторінки отримуємо список гостей
		GuestStore.listGuests();
	}, []);

	const handleAddGuest = async () => {
		if (!newGuestFirstName) {
			alert("Будь ласка, заповніть ім'я!");
			return;
		}
		// Якщо гість без plus one, потрібно обрати стать
		if (!plusOne && !guestGender) {
			alert("Будь ласка, оберіть стать гостя!");
			return;
		}
		const guestPayload = {
			first_name: newGuestFirstName,
			first_name_plus_1: plusOne ? plusOneName : "",
			...(!plusOne && { gender: guestGender }),
		};

		await GuestStore.createGuest(guestPayload);

		// Очистити поля введення
		setNewGuestFirstName("");
		setPlusOne(false);
		setPlusOneName("");
		setGuestGender("");

		// Оновити список гостей
		GuestStore.listGuests();
	};

	const confirmDelete = (guest) => {
		setGuestToDelete(guest);
		setShowModal(true);
	};

	const handleDelete = async () => {
		if (guestToDelete) {
			await GuestStore.deleteGuest(guestToDelete.guest_id);
			setGuestToDelete(null);
			setShowModal(false);
		}
	};

	const handleCancelDelete = () => {
		setGuestToDelete(null);
		setShowModal(false);
	};

	const getRsvpStyle = (status) => {
		if (status === "accepted") return { color: "green" };
		if (status === "declined") return { color: "red" };
		if (status === "pending") return { color: "orange" };
		return {};
	};

	if (GuestStore.loading) return <p>Завантаження...</p>;
	if (GuestStore.error) return <p>Помилка: {GuestStore.error.message}</p>;

	return (
		<>
			<Head>
				<link
					rel="stylesheet"
					href="/styles/materialize.min.css"
				/>
				<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
			</Head>
			<div
				className="container admin_container"
				style={{ paddingTop: "20px" }}
			>
				<h1 className="center-align">Гості</h1>
				<div className="admin_container_form">
					<h2>Додати нового гостя</h2>
					<div
						className="row"
						style={{ marginBottom: "1rem" }}
					>
						<div className="input-field col s12 m4">
							<input
								id="first_name"
								type="text"
								value={newGuestFirstName}
								onChange={(e) => setNewGuestFirstName(e.target.value)}
							/>
							<label htmlFor="first_name">Ім'я</label>
						</div>

						<div className="input-field col s12 m4">
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
							<div className="input-field col s12 m4">
								<input
									id="plus_one_name"
									type="text"
									value={plusOneName}
									onChange={(e) => setPlusOneName(e.target.value)}
								/>
								<label htmlFor="plus_one_name">
									Ім'я додаткового гостя
								</label>
							</div>
						) : (
							<div className="input-field col s12 m4">
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
					<div className="row">
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

				<h2 className="center-align">Всі гості</h2>
				<table className="striped responsive-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Унікальний шлях</th>
							<th>Посилання</th>
							<th>Повне ім'я</th>
							<th>Стать</th>
							<th>Статус</th>
							<th>Алкогольні вподобання</th>
							<th>Додатковий гість</th>
							<th>Статус додаткового гостя</th>
							<th>Алкоголь (дод. гість)</th>
							<th>Дії</th>
						</tr>
					</thead>
					<tbody>
						{GuestStore.guestsList.map((guest) => (
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
								{/* Render the RSVP status with Ukrainian translations and styles */}
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
													<div>Тип вина: {guest.wine_type}</div>
												</>
											) : (
												<div>{guest.alcohol_preferences}</div>
											)}
											{guest.custom_alcohol && (
												<div>
													Свій варіант: {guest.custom_alcohol}
												</div>
											)}
										</>
									) : (
										"-"
									)}
								</td>
								<td>
									{guest?.first_name_plus_1
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
														Тип вина: {guest.wine_type_plus_one}
													</div>
												</>
											) : (
												<div>
													{guest.alcohol_preferences_plus_one}
												</div>
											)}
											{guest.custom_alcohol_plus_one && (
												<div>
													Свій варіант:{" "}
													{guest.custom_alcohol_plus_one}
												</div>
											)}
										</>
									) : (
										"-"
									)}
								</td>

								<td>
									<button
										className="btn red lighten-1"
										onClick={() => confirmDelete(guest)}
									>
										Видалити
									</button>
									{/*
                  Можна також додати кнопку "Редагувати" для оновлення гостя через GuestStore.updateGuest().
                */}
								</td>
							</tr>
						))}
					</tbody>
				</table>

				{/* Підсумок по алкоголю */}
				<AlcoholSummary />
				{/* Модальне вікно підтвердження видалення */}
				{showModal && (
					<div
						className="modal"
						style={{
							display: "block",
							position: "fixed",
							top: 0,
							left: 0,
							width: "100vw",
							height: "100vh",
							background: "rgba(0,0,0,0.3)",
						}}
					>
						<div
							className="modal-content"
							style={{ padding: "2rem" }}
						>
							<h4>Підтвердження</h4>
							<p>Ви впевнені, що хочете видалити гостя?</p>
						</div>
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
				)}
			</div>
		</>
	);
});

export default AdminPage;
