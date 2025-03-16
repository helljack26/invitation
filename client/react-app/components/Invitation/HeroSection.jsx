// components/Invitation/HeroSection.jsx
import { observer } from "mobx-react-lite";
// MobX Store
import { useUserGuestStore } from "../../stores/UserGuestStore";
// Images
import Image from "next/image";
import I from "../../img/images";

export const HeroSection = observer(() => {
	const userGuestStore = useUserGuestStore();
	const { guestData } = userGuestStore;

	// If guestData isn't loaded yet, you might want to return null or a loading state.
	if (!guestData) return null;

	// Determine greeting based on whether guestData.first_name_plus_1 exists.
	let greetingPrefix;
	if (guestData.first_name_plus_1) {
		greetingPrefix = "Дорогі";
	} else {
		// Assume guestData.gender is either "male" or "female"
		greetingPrefix = guestData.gender === "male" ? "Дорогий" : "Дорога";
	}

	// Build the display name: if first_name_plus_1 exists, join the names.
	const displayName = guestData.first_name_plus_1
		? `${guestData.first_name} та ${guestData.first_name_plus_1}`
		: guestData.first_name;

	return (
		<section
			id="heroSection"
			className="heroSection"
			data-scroll-section
		>
			{/* Falling hearts container */}
			<div className="fallingHearts">
				<div className="heart"></div>
				<div className="heart"></div>
				<div className="heart"></div>
				<div className="heart"></div>
				<div className="heart"></div>
				{/* ... add more hearts if desired */}
			</div>

			<div className="heroContent">
				<span className="heroContent_dear">{greetingPrefix}</span>
				<h1>{displayName}</h1>
				<h2>
					Щиро запрошуємо {guestData.first_name_plus_1 ? "Вас" : "Тебе"} на
					наше весілля <br />
					<span>1.03.2025</span>
				</h2>

				<div className="imagesWrapper">
					<Image
						className="childPhoto"
						alt="bride groom child"
						src={I.bride_groom_child}
						height={400}
						priority
					/>
				</div>
				<span className="city">Київ</span>
			</div>
		</section>
	);
});
