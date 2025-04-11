// components/Invitation/WeddingDateAndTime.jsx

import React from "react";

// Images
import Image from "next/image";
import I from "../../img/images";

export const WeddingDateAndTime = () => {
	return (
		<section
			id="wedding_date_and_time"
			className="date_time_section"
			data-scroll-section
		>
			<div className="date_time_section_bg">
				{/* Фото наречених. Замініть на свої */}
				<Image
					alt="bg paper texture"
					src={I.bg_heart_2}
					fill
					priority
				/>
			</div>

			<div className="date_time_section_content">
				<h1>
					День <br /> весіллє
				</h1>
				<div className="scheduleContainer">
					<div className="dateTimeItem">
						<div className="timeBlock">12:00</div>

						<span className="line"></span>

						<div className="eventDetails">
							<h2>Розпис</h2>
							<p>Подільський РАЦС</p>
							<a
								href="https://maps.app.goo.gl/kNo7nHinMg2YSkdo9"
								target="_blank"
								rel="noopener noreferrer"
								className="mapLink"
							>
								Дивитись на мапі
							</a>
						</div>
					</div>

					<div className="dateTimeItem">
						<div className="timeBlock">14:00</div>

						<span className="line"></span>

						<div className="eventDetails">
							<h2>БАНКЕТ</h2>
							<p>Колиба House</p>
							<a
								href="https://maps.app.goo.gl/z1gENMq7yweU4EdUA"
								target="_blank"
								rel="noopener noreferrer"
								className="mapLink"
							>
								Дивитись на мапі
							</a>
						</div>
					</div>
				</div>
			</div>
		</section>
	);
};
