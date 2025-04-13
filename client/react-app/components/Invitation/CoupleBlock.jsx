"use client"; // If you're using Next.js 13+ app router, you might need this at the top
import { useEffect, useState } from "react";
import Image from "next/image";
import I from "../../img/images";

export const CoupleBlock = () => {
	const [timeLeft, setTimeLeft] = useState({
		days: 0,
		hours: 0,
		minutes: 0,
		seconds: 0,
	});

	useEffect(() => {
		// Target date/time: June 21, 2025 at 12:00
		const targetDate = new Date("2025-06-21T12:00:00");

		// Update the timer every second
		const timer = setInterval(() => {
			const now = new Date();
			const difference = targetDate - now;

			if (difference > 0) {
				const days = Math.floor(difference / (1000 * 60 * 60 * 24));
				const hours = Math.floor((difference / (1000 * 60 * 60)) % 24);
				const minutes = Math.floor((difference / (1000 * 60)) % 60);
				const seconds = Math.floor((difference / 1000) % 60);

				setTimeLeft({ days, hours, minutes, seconds });
			} else {
				// Once the date is reached or passed, clear the timer
				clearInterval(timer);
			}
		}, 1000);

		// Cleanup the interval on component unmount
		return () => clearInterval(timer);
	}, []);

	return (
		<section
			id="coupleBlock"
			className="couple_block"
			data-scroll-section
		>
			<div className="couple_images">
				{/* Фото наречених. Замініть на свої */}
				<Image
					className="couple_photo"
					alt="bride groom child"
					src={I.couple}
					priority
				/>
			</div>

			{/* Countdown Timer */}
			<div className="timer">
				<div className="timer_item">
					<span>{timeLeft.days}</span>
					<span>Днів</span>
				</div>
				<div className="timer_item">
					<span>{timeLeft.hours}</span>
					<span>Годин</span>
				</div>
				<div className="timer_item">
					<span>{timeLeft.minutes}</span>
					<span>Хвилин</span>
				</div>
				<div className="timer_item">
					<span>{timeLeft.seconds}</span>
					<span>Секунд</span>
				</div>
			</div>

			{/* You can add additional text or styles to mimic the design in the image */}
			<p>… і ми будемо дружені!</p>
		</section>
	);
};
