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
			<div className="guestContainer">
				<div className="guestBlock">
					<div className="guestRow">
						<span className="guestName">{guest.first_name}</span>
						<div className="guestRowButtons">
							<button
								type="button"
								className={
									guest.rsvp_status === "accepted" ? "selected" : ""
								}
								onClick={() => handleRSVP("accepted")}
							>
								<span>Так</span>
							</button>
							<button
								type="button"
								className={
									guest.rsvp_status === "declined" ? "selected" : ""
								}
								onClick={() => handleRSVP("declined")}
							>
								<span>Ні</span>
							</button>
						</div>
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
				</div>

				{/* Conditional Rendering for Plus One */}
				{guest.first_name_plus_1 && (
					<div className="guestBlock">
						<div className="guestRow">
							<span className="guestName">
								{guest.first_name_plus_1}
							</span>
							<div className="guestRowButtons">
								<button
									type="button"
									className={
										guest.rsvp_status_plus_one === "accepted"
											? "selected"
											: ""
									}
									onClick={() => handleRSVP("accepted", true)}
								>
									<span>Так</span>
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
									<span>Ні</span>
								</button>
							</div>
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
					</div>
				)}
			</div>

			<button
				type="button"
				onClick={() => syncRSVPDataToServer(true)}
			>
				Відправити відповідь
			</button>
		</section>
	);
});
