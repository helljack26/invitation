// components/Invitation/HeroSection.jsx
import { observer } from "mobx-react-lite";
// MobX Store
import { useUserGuestStore } from "../../stores/UserGuestStore";
// Images
import Image from "next/image";
import I from "../../img/images";
import { HeartsFalling } from "../HeartsFalling";

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
			id="hero_section"
			className="hero_section"
			data-scroll-section
		>
			{/* Falling hearts container */}
			<HeartsFalling />

			<div className="paper_texture">
				{/* Фото наречених. Замініть на свої */}
				<Image
					alt="bg paper texture"
					src={I.paper_texture}
					fill
				/>
			</div>

			<div className="hero_content">
				<div className="hero_content_text">
					<span className="hero_content_dear">{greetingPrefix}</span>
					<h1>{displayName}</h1>
					<h2>
						Щиро запрошуємо {guestData.first_name_plus_1 ? "Вас" : "Тебе"}{" "}
						на наше весілля <br />
						Яке відбудеться<br />
						<span>21.06.2025</span>
					</h2>
					<span className="city desktop">Київ</span>
				</div>

				<div className="images_wrapper">
					<Image
						className="child_photo"
						alt="bride groom child"
						src={I.bride_groom_child}
						height={700}
						priority
					/>
				</div>
				<span className="city mobile">Київ</span>
			</div>

			<div className="scroll"></div>
		</section>
	);
});
