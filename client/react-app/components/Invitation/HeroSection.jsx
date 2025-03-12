// components/Invitation/HeroSection.jsx
import Image from "next/image";

export const HeroSection = ({ guest }) => {
	return (
		<section
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
				<h1>
					Приглашаем Вас на нашу свадьбу <br />
					<span>1.03.2025</span>
				</h1>
				{guest && <p>Дорогий, {guest.name}!</p>}

				<div className="imagesWrapper">
					<div className="childPhoto">
						<Image
							src="/images/bride_child.png"
							alt="Bride as a child"
							width={150}
							height={200}
						/>
					</div>
					<div className="childPhoto">
						<Image
							src="/images/groom_child.png"
							alt="Groom as a child"
							width={150}
							height={200}
						/>
					</div>
				</div>
			</div>
		</section>
	);
};
