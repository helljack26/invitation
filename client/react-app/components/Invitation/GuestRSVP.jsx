import { observer } from "mobx-react-lite";
import { useUserGuestStore } from "../../stores/UserGuestStore";
import { AlcoholPreferences } from "./AlcoholPreferences";
import { AnimatePresence, motion } from "framer-motion";

export const GuestRSVP = observer(() => {
	const userGuestStore = useUserGuestStore();
	const { guestData, setRSVPStatusInStore, syncRSVPDataToServer } =
		userGuestStore;

	const guest = guestData;
	if (!guest) return null;

	// Update MobX state only
	const handleRSVP = (status, isPlusOne = false) => {
		setRSVPStatusInStore(status, isPlusOne);
	};

	// Define slide animation variants
	const slideVariants = {
		hidden: { height: 0, opacity: 0 },
		visible: { height: "auto", opacity: 1 },
		exit: { height: 0, opacity: 0 },
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

			{/* Animate alcohol preferences for the main guest */}
			<AnimatePresence>
				{guest.rsvp_status === "accepted" && (
					<motion.div
						key="alcohol-main"
						variants={slideVariants}
						initial="hidden"
						animate="visible"
						exit="exit"
						transition={{ duration: 0.3 }}
					>
						<AlcoholPreferences isPlusOne={false} />
					</motion.div>
				)}
			</AnimatePresence>

			{/* Conditional Rendering for Plus One */}
			{guest.first_name_plus_1 && (
				<>
					<div className="guestRow">
						<span>{guest.first_name_plus_1}</span>
						<button
							type="button"
							className={
								guest.rsvp_status_plus_one === "accepted"
									? "selected"
									: ""
							}
							onClick={() => handleRSVP("accepted", true)}
						>
							Так
						</button>
						<button
							type="button"
							className={
								guest.rsvp_status_plus_one === "declined"
									? "selected"
									: ""
							}
							onClick={() => handleRSVP("declined", true)}
						>
							Ні
						</button>
					</div>

					{/* Animate alcohol preferences for plus one */}
					<AnimatePresence>
						{guest.rsvp_status_plus_one === "accepted" && (
							<motion.div
								key="alcohol-plus-one"
								variants={slideVariants}
								initial="hidden"
								animate="visible"
								exit="exit"
								transition={{ duration: 0.3 }}
							>
								<AlcoholPreferences isPlusOne={true} />
							</motion.div>
						)}
					</AnimatePresence>
				</>
			)}
			<button
				type="button"
				onClick={() => syncRSVPDataToServer(true)}
			>
				Відправити відповідь
			</button>
		</section>
	);
});
