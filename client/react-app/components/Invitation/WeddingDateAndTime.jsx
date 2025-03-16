// components/Invitation/WeddingDateAndTime.jsx

import React from "react";

export const WeddingDateAndTime = () => {
	return (
		<section
			id="weddingDateAndTime"
			className="dateTimeSection"
			data-scroll-section
		>
			<h2>День весілля</h2>
			<div className="dateTimeItem">
				{/* Optional icon/decoration */}
				<h2>Розпис</h2>
				<p>Подільський РАЦС</p>
				<p>Початок о 12:00</p>
				<a
					href="https://maps.app.goo.gl/kNo7nHinMg2YSkdo9"
					target="_blank"
					rel="noopener noreferrer"
					className="mapLink"
				>
					Дивитись на мапі
				</a>
			</div>

			<div className="dateTimeItem">
				{/* Optional icon/decoration */}
				<h2>БАНКЕТ</h2>
				<p>Колиба House</p>
				<p>Початок о 14:00</p>
				<a
					href="https://maps.app.goo.gl/z1gENMq7yweU4EdUA"
					target="_blank"
					rel="noopener noreferrer"
					className="mapLink"
				>
					Дивитись на мапі
				</a>
			</div>
		</section>
	);
};
