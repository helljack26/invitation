// components/Loader.js
import React, { useEffect, useRef } from "react";
import LoaderHeart from "../img/loader_heart.svg";
import LoaderText from "../img/loader_text.svg";
import useLenis from "../hooks/useLenis";

export default function Loader({ onComplete }) {
	const lenis = useLenis();
	const loaderRef = useRef(null);

	const heartWrapperRef = useRef(null);

	useEffect(() => {
		let ctx;
		async function init() {
			const { gsap } = await import("gsap");
			const { DrawSVGPlugin } = await import("gsap/dist/DrawSVGPlugin");
			gsap.registerPlugin(DrawSVGPlugin);

			if (!loaderRef.current || !heartWrapperRef.current) return;

			ctx = gsap.context(() => {
				const heartEl =
					heartWrapperRef.current.querySelector(".loader__heart");
				const textEl = heartWrapperRef.current.querySelector(
					".loader__text-container"
				);
				const paths = textEl.querySelectorAll("path");

				paths.forEach((p) => {
					const len = p.getTotalLength();
					p.style.strokeDasharray = len;
					p.style.strokeDashoffset = len;
				});

				gsap.set(heartWrapperRef.current, {
					opacity: 0,
					scale: 0.8,
					transformOrigin: "center center",
				});
				gsap.set(textEl, { opacity: 0 });

				const tl = gsap.timeline({
					onComplete: () => onComplete && onComplete(),
				});

				tl.to(heartWrapperRef.current, {
					opacity: 1,
					scale: 1,
					duration: 1,
					ease: "power2.out",
				});
				tl.to(textEl, { opacity: 1, duration: 0.8 }, "<");

				tl.to(heartEl, {
					scale: 1.15,
					duration: 0.5,
					ease: "power1.inOut",
					yoyo: true,
					repeat: 1,
				});
				tl.to(heartEl, {
					scale: 1.15,
					duration: 0.5,
					ease: "power1.inOut",
					yoyo: true,
					repeat: 1,
					delay: 0.5,
				});
				
				tl.to(heartEl, {
					scale: 12,
					opacity: 0,
					duration: 1.5,
					ease: "power2.in",
					delay: 0.5,
				});
				tl.to(
					loaderRef.current,
					{
						opacity: 0,
						duration: 1.5,
						pointerEvents: "none",
					},
					"-=0.5"
				);

				gsap.to(paths, {
					strokeDashoffset: 1,
					duration: 5,
					ease: "power3.out",
					stagger: 0.1,
				});
			}, loaderRef.current);
		}

		init();
		return () => ctx && ctx.revert();
	}, [onComplete]);

	return (
		<div
			ref={loaderRef}
			className="loader"
		>
			<div
				ref={heartWrapperRef}
				className="loader__heart-wrapper"
			>
				<LoaderHeart className="loader__heart" />
				<div className="loader__text-container">
					<LoaderText
						className="loader__text"
						width={220}
						height="100%"
					/>
				</div>
			</div>
		</div>
	);
}
