import { useEffect, useState } from "react";
import { observer } from "mobx-react-lite";
import GuestStore from "../../stores/GuestStore";

const AdminPage = observer(() => {
	const [showModal, setShowModal] = useState(false);
	const [guestToDelete, setGuestToDelete] = useState(null);

	// Temporary states for new guest creation
	const [newGuestFirstName, setNewGuestFirstName] = useState("");
	const [newGuestLastName, setNewGuestLastName] = useState("");
	const [plusOne, setPlusOne] = useState(false);
	const [plusOneName, setPlusOneName] = useState("");

	useEffect(() => {
		// On page load, fetch the list of guests
		GuestStore.listGuests();
	}, []);

	const handleAddGuest = async () => {
		if (!newGuestFirstName || !newGuestLastName) {
			alert("Please fill in first and last name!");
			return;
		}

		const guestPayload = {
			first_name: newGuestFirstName,
			last_name: newGuestLastName,
			has_plus_one: plusOne,
			plus_one_name: plusOne ? plusOneName : "",
			// Possibly generate a unique_path or let the backend do it
			unique_path: `${newGuestFirstName}-${newGuestLastName}`.toLowerCase(),
		};

		await GuestStore.createGuest(guestPayload);

		// Clear inputs
		setNewGuestFirstName("");
		setNewGuestLastName("");
		setPlusOne(false);
		setPlusOneName("");

		// Refresh the guest list
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

	// Render a loading or error state if needed
	if (GuestStore.loading) return <p>Loading...</p>;
	if (GuestStore.error) return <p>Error: {GuestStore.error.message}</p>;

	return (
		<div style={{ padding: "20px" }}>
			<h1>Admin - Guest List</h1>

			<h2>Add New Guest</h2>
			<div style={{ marginBottom: "1rem" }}>
				<input
					placeholder="First Name"
					value={newGuestFirstName}
					onChange={(e) => setNewGuestFirstName(e.target.value)}
				/>
				<input
					placeholder="Last Name"
					value={newGuestLastName}
					onChange={(e) => setNewGuestLastName(e.target.value)}
				/>
				<label style={{ marginLeft: "1rem" }}>
					<input
						type="checkbox"
						checked={plusOne}
						onChange={() => setPlusOne(!plusOne)}
					/>
					Plus One?
				</label>
				{plusOne && (
					<input
						style={{ marginLeft: "1rem" }}
						placeholder="Plus One Name"
						value={plusOneName}
						onChange={(e) => setPlusOneName(e.target.value)}
					/>
				)}
				<button
					style={{ marginLeft: "1rem" }}
					onClick={handleAddGuest}
				>
					Add Guest
				</button>
			</div>

			<h2>All Guests</h2>
			<table
				border="1"
				cellPadding="8"
				style={{ borderCollapse: "collapse" }}
			>
				<thead>
					<tr>
						<th>ID</th>
						<th>Unique Path</th>
						<th>Link</th>
						<th>Full Name</th>
						<th>Plus One</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					{GuestStore.guestsList.map((guest) => (
						<tr key={guest.guest_id}>
							<td>{guest.guest_id}</td>
							<td>{guest.unique_path}</td>
							<td>
								<Link href={`/invite/${guest.unique_path}`}>
									View Invitation
								</Link>
							</td>
							<td>
								{guest.first_name} {guest.last_name}
							</td>
							<td>{guest.has_plus_one ? guest.plus_one_name : "No"}</td>
							<td>
								<button onClick={() => confirmDelete(guest)}>
									Remove
								</button>
								{/* 
                  You could also add an "Edit" button here to open a form 
                  that updates the guest with `GuestStore.updateGuest()`.
                */}
							</td>
						</tr>
					))}
				</tbody>
			</table>

			{/* Confirm Deletion Modal */}
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
						<p>Are you sure you want to delete guest?</p>
						<button onClick={handleDelete}>Yes</button>
						<button onClick={handleCancelDelete}>No</button>
					</div>
				</div>
			)}
		</div>
	);
});

export default AdminPage;
