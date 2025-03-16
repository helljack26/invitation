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
				{guestData && <h1>Дорогий, {guestData.first_name}!</h1>}
				<h2>
					запрошуємо тебе на наше весілля <br />
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
