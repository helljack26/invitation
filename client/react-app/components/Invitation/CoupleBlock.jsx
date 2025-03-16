// components/Invitation/CoupleBlock.jsx
import Image from "next/image";

export const CoupleBlock = () => {
	return (
		<section
			id="coupleBlock"
			className="coupleBlock"
			data-scroll-section
		>
			<div className="coupleImages">
				{/* Фото наречених. Замініть на свої */}
				<div className="couplePhoto">
					<Image
						src="/images/bride_and_groom.png"
						alt="Наречена і Наречений"
						width={300}
						height={400}
					/>
				</div>
			</div>
			<h2>Тіли-тіли тісто</h2>
			<p>Наречений та наречена!</p>
		</section>
	);
};
