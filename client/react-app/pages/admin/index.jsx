import { useEffect, useState } from "react";
import { observer } from "mobx-react-lite";
import GuestStore from "../../stores/GuestStore";
import { transliterate } from "../../components/helpers/transliterate";
// import { Link } from "react-router-dom";

const AdminPage = observer(() => {
	const [showModal, setShowModal] = useState(false);
	const [guestToDelete, setGuestToDelete] = useState(null);

	// Тимчасові стани для створення нового гостя
	const [newGuestFirstName, setNewGuestFirstName] = useState("");
	const [newGuestLastName, setNewGuestLastName] = useState("");
	const [plusOne, setPlusOne] = useState(false);
	const [plusOneName, setPlusOneName] = useState("");

	useEffect(() => {
		// При завантаженні сторінки отримуємо список гостей
		GuestStore.listGuests();
	}, []);

	const handleAddGuest = async () => {
		if (!newGuestFirstName || !newGuestLastName) {
			alert("Будь ласка, заповніть ім'я та прізвище!");
			return;
		}

		const guestPayload = {
			first_name: newGuestFirstName,
			last_name: newGuestLastName,
			has_plus_one: plusOne,
			plus_one_name: plusOne ? plusOneName : "",
			// Можливо, згенерувати унікальний шлях або дозволити backend зробити це
			unique_path: `${transliterate(newGuestFirstName)}_${transliterate(
				newGuestLastName
			)}`.toLowerCase(),
		};

		await GuestStore.createGuest(guestPayload);

		// Очистити поля введення
		setNewGuestFirstName("");
		setNewGuestLastName("");
		setPlusOne(false);
		setPlusOneName("");

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

	// Відображення стану завантаження або помилки, якщо потрібно
	if (GuestStore.loading) return <p>Завантаження...</p>;
	if (GuestStore.error) return <p>Помилка: {GuestStore.error.message}</p>;

	return (
		<div style={{ padding: "20px" }}>
			<h1>Адміністратор - Список гостей</h1>

			<h2>Додати нового гостя</h2>
			<div style={{ marginBottom: "1rem" }}>
				<input
					placeholder="Ім'я"
					value={newGuestFirstName}
					onChange={(e) => setNewGuestFirstName(e.target.value)}
				/>
				<input
					placeholder="Прізвище"
					value={newGuestLastName}
					onChange={(e) => setNewGuestLastName(e.target.value)}
				/>
				<label style={{ marginLeft: "1rem" }}>
					<input
						type="checkbox"
						checked={plusOne}
						onChange={() => setPlusOne(!plusOne)}
					/>
					Додатковий гість?
				</label>
				{plusOne && (
					<input
						style={{ marginLeft: "1rem" }}
						placeholder="Ім'я додаткового гостя"
						value={plusOneName}
						onChange={(e) => setPlusOneName(e.target.value)}
					/>
				)}
				<button
					style={{ marginLeft: "1rem" }}
					onClick={handleAddGuest}
				>
					Додати гостя
				</button>
			</div>

			<h2>Всі гості</h2>
			<table
				border="1"
				cellPadding="8"
				style={{ borderCollapse: "collapse" }}
			>
				<thead>
					<tr>
						<th>ID</th>
						<th>Унікальний шлях</th>
						<th>Посилання</th>
						<th>Повне ім'я</th>
						<th>Додатковий гість</th>
						<th>Дії</th>
					</tr>
				</thead>
				<tbody>
					{GuestStore.guestsList.map((guest) => (
						<tr key={guest.guest_id}>
							<td>{guest.guest_id}</td>
							<td>{guest.unique_path}</td>
							<td>
								{/* <Link href={`/invite/${guest.unique_path}`}>
									Переглянути запрошення
								</Link> */}
							</td>
							<td>
								{guest.first_name} {guest.last_name}
							</td>
							<td>{guest.has_plus_one ? guest.plus_one_name : "Ні"}</td>
							<td>
								<button onClick={() => confirmDelete(guest)}>
									Видалити
								</button>
								{/*
                  Ви також можете додати кнопку "Редагувати", яка відкриває форму 
                  для оновлення гостя за допомогою `GuestStore.updateGuest()`.
                */}
							</td>
						</tr>
					))}
				</tbody>
			</table>

			{/* Модальне вікно підтвердження видалення */}
			{showModal && (
				<div
					style={{
						position: "fixed",
						left: 0,
						top: 0,
						width: "100vw",
						height: "100vh",
						background: "rgba(0,0,0,0.3)",
						display: "flex",
						justifyContent: "center",
						alignItems: "center",
					}}
				>
					<div style={{ background: "#fff", padding: "2rem" }}>
						<p>Ви впевнені, що хочете видалити гостя?</p>
						<button onClick={handleDelete}>Так</button>
						<button onClick={handleCancelDelete}>Ні</button>
					</div>
				</div>
			)}
		</div>
	);
});

export default AdminPage;
