// components/Invitation/WeddingDateAndTime.jsx

import React from "react";

// Images
import Image from "next/image";

import I from '../../img/images';

export const WeddingDateAndTime = () => {
	return (
		<section
			id="weddingDateAndTime"
			className="date_time_section"
			data-scroll-section
		>
			<div className="date_time_section_bg">
				{/* Фото наречених. Замініть на свої */}

				<I.bg_heart_2  />

			</div>

			<div className="date_time_section_content">
				<h1>
					День <br /> весіллє
				</h1>
				<div className="schedule_container">
					<div className="date_time_item">
						<div className="time_block">12:30</div>

						<span className="line"></span>

						<div className="event_details">
							<h2>Розпис</h2>
							<p>Подільський ДРАЦС</p>
							<a
								href="https://maps.app.goo.gl/kNo7nHinMg2YSkdo9"
								target="_blank"
								rel="noopener noreferrer"
								className="map_link"
							>
								Дивитись на мапі
							</a>
						</div>
					</div>

					<div className="date_time_item">
						<div className="time_block">14:30</div>

						<span className="line"></span>

						<div className="event_details">
							<h2>БАНКЕТ</h2>
							<p>Колиба House</p>
							<a
								href="https://maps.app.goo.gl/z1gENMq7yweU4EdUA"
								target="_blank"
								rel="noopener noreferrer"
								className="map_link"
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
