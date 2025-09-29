import React, { useEffect, useRef, useState } from "react";
import { observer } from "mobx-react";
import gsap from "gsap";

// Example hearts array, each with a different path and/or color
const heartsData = [
	{
		color: "#ff9aff",
		path: "M16.586 3.432c-1.807-1.807-4.426-1.807-6.233 0-.645.646-1.032 1.527-1.158 2.472-.125-.945-.512-1.826-1.157-2.472-1.807-1.807-4.426-1.807-6.233 0s-1.807 4.426 0 6.233l7.39 7.39c.586.586 1.53.586 2.116 0l7.39-7.39c1.808-1.807 1.808-4.426 0-6.233z",
	},
	{
		color: "#d968d9",
		path: "M12.001 4.529c-1.349-1.771-3.39-2.414-5.055-.749-1.663 1.664-1.021 3.706.75 5.055l4.304 4.305 4.304-4.305c1.771-1.349 2.413-3.391.749-5.055-1.664-1.665-3.706-1.022-5.052.749z",
	},
	{
		color: "#fe58fe",
		path: "M 10 3 C 7.8 0.4 3.8 0.4 1.6 3 C -0.2 5.2 -0.2 9.2 1.6 11.4 L 8.3 18.1 C 9 18.8 10 18.8 10.7 18.1 L 17.4 11.4 C 19.2 9.2 19.2 5.2 17.4 3 C 15.2 0.4 11.2 0.4 9 3 Z",
	},
];

export const HeartsFalling = observer(() => {
	const containerRef = useRef(null);
	const [hearts, setHearts] = useState([]);

	// Function to create a new heart with randomized properties.
	const createHeartData = () => {
		if (!containerRef.current) return null;
		const containerHeight = containerRef.current.clientHeight;
		const id = Date.now() + Math.random();
		const size = Math.floor(Math.random() * 25) + 15; // between 15px and 40px
		const left = Math.random() * 100; // for positioning the heart container (in %)
		const startX = Math.random() * 100;
		const endX = Math.random() * 60 + 20;
		const endY = containerHeight * (Math.random() * 0.15 + 0.8);
		const duration = Math.random() * 3 + 8;
		const delay = 0;
		const rotation = Math.random() * 50 - 25;

		const randomHeart = heartsData[Math.floor(Math.random() * heartsData.length)];

		return {
			id,
			size,
			left,
			startX,
			endX,
			endY,
			duration,
			delay,
			rotation,
			randomHeart,
		};
	};

	// Add a new heart every 500ms, but keep at most 500 hearts
	useEffect(() => {
		const interval = setInterval(() => {
			const newHeart = createHeartData();
			if (newHeart) {
				setHearts((prev) => {
					const updated = [...prev, newHeart];
					return updated.length > 100 ? updated.slice(-100) : updated;
				});
			}
		}, 500);
		return () => clearInterval(interval);
	}, []);

	return (
		<div ref={containerRef} className="falling_hearts">
			{hearts.map((heart) => (
				<Heart key={heart.id} heart={heart} containerRef={containerRef} />
			))}
		</div>
	);
});

const Heart = ({ heart, containerRef }) => {
	const heartRef = useRef(null);

	useEffect(() => {
		if (!heartRef.current || !containerRef.current) return;
		gsap.fromTo(
			heartRef.current,
			{
				xPercent: heart.startX,
				y: -50,
				opacity: 0,
				rotation: 0,
			},
			{
				xPercent: heart.endX,
				y: heart.endY,
				opacity: 1,
				rotation: heart.rotation,
				duration: heart.duration,
				delay: heart.delay,
				ease: "power1.in",
			}
		);
	}, [heart, containerRef]);

	return (
		<div
			ref={heartRef}
			style={{
				position: "absolute",
				top: 0,
				left: `${heart.left}%`,
				width: `${heart.size}px`,
				height: `${heart.size}px`,
			}}
		>
			<svg
				width="100%"
				height="100%"
				viewBox="0 0 20 20"
				fill={heart.randomHeart.color}
				xmlns="http://www.w3.org/2000/svg"
			>
				<path d={heart.randomHeart.path} />
			</svg>
		</div>
	);
};

export default HeartsFalling;
