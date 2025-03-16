import { observer } from "mobx-react-lite";
import { useUserGuestStore } from "../../stores/UserGuestStore";

export const GuestRSVP = observer(() => {
	const userGuestStore = useUserGuestStore();
	const { guestData, updateGuestRSVP } = userGuestStore;

	const guest = guestData;

	if (!guest) return null;

	// Function to handle RSVP update for main guest or plus one
	const handleRSVP = (status, isPlusOne = false) => {
		updateGuestRSVP(status, isPlusOne);
	};

	return (
		<section
			id="rsvpBlock"
			className="rsvpBlock"
			data-scroll-section
		>
			<h3>Чи зможете ви приєднатись до святкування разом з нами?</h3>

			{/* Main Guest */}
			<div className="guestRow">
				<span>{guest.first_name}</span>
				<button
					type="button"
					className={guest.rsvp_status === "accepted" ? "selected" : ""}
					onClick={() => handleRSVP("accepted")}
				>
					Так
				</button>
				<button
					type="button"
					className={guest.rsvp_status === "declined" ? "selected" : ""}
					onClick={() => handleRSVP("declined")}
				>
					Ні
				</button>
			</div>

			{/* Conditional Rendering for Plus One */}
			{guest.first_name_plus_1 && (
				<div className="guestRow">
					<span>{guest.first_name_plus_1}</span>
					<button
						type="button"
						className={
							guest.rsvp_status_plus_one === "accepted" ? "selected" : ""
						}
						onClick={() => handleRSVP("accepted", true)}
					>
						Так
					</button>
					<button
						type="button"
						className={
							guest.rsvp_status_plus_one === "declined" ? "selected" : ""
						}
						onClick={() => handleRSVP("declined", true)}
					>
						Ні
					</button>
				</div>
			)}
		</section>
	);
});
