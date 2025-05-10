import React, { useState, useEffect, useRef } from "react";
import { gsap, Power3 } from "gsap";

const MusicPlayer = ({ src }) => {
	const [isPlaying, setPlaying] = useState(false);
	const [hasAutoplayed, setAutoplayed] = useState(false);

	const audioRef = useRef(null);
	const eqBarsRef = useRef([]);

	// Attempt muted autoplay, then resume on first user interaction
	useEffect(() => {
		const audio = audioRef.current;
		if (!audio) return;

		audio.muted = true;
		const promise = audio.play();
		if (promise?.then) {
			promise.then(() => setAutoplayed(true)).catch(() => {});
		}

		const resumeAudio = () => {
			audio.muted = false;
			audio
				.play()
				.then(() => setPlaying(true))
				.catch(() => {});
			document.removeEventListener("click", resumeAudio);
			document.removeEventListener("touchstart", resumeAudio);
		};

		document.addEventListener("click", resumeAudio);
		document.addEventListener("touchstart", resumeAudio);

		return () => {
			document.removeEventListener("click", resumeAudio);
			document.removeEventListener("touchstart", resumeAudio);
		};
	}, []);

	// Equalizer animation
	useEffect(() => {
		const bars = eqBarsRef.current.filter(Boolean);
		if (!bars.length) return;

		if (isPlaying) {
			gsap.killTweensOf(bars);
			gsap.to(bars, {
				scaleY: () => Math.random() * 0.6 + 0.2,
				duration: 0.3,
				repeat: -1,
				yoyo: true,
				ease: "sine.inOut",
				stagger: { each: 0.1, from: "random" },
			});
		} else {
			gsap.killTweensOf(bars);
			gsap.to(bars, { scaleY: 0, duration: 0.4, ease: Power3.easeInOut });
		}
	}, [isPlaying]);

	// Play / pause toggle
	const toggleAudio = () => {
		const audio = audioRef.current;
		if (!audio) return;

		if (isPlaying) {
			audio.pause();
			setPlaying(false);
		} else {
			if (hasAutoplayed && audio.muted) audio.muted = false;
			audio.play();
			setPlaying(true);
		}
	};

	return (
		<div className="audio-player">
			<button
				onClick={toggleAudio}
				aria-label={isPlaying ? "Pause music" : "Play music"}
				className="audio-player__toggle"
			>
				{isPlaying ? (
					<span className="icon icon--pause" />
				) : (
					<span className="icon icon--play" />
				)}
			</button>

			<div className="equalizer">
				{[0, 1, 2, 3].map((_, i) => (
					<div
						key={i}
						className="eq-bar"
						ref={(el) => (eqBarsRef.current[i] = el)}
					/>
				))}
			</div>

			<audio
				ref={audioRef}
				src={src}
				preload="auto"
				autoPlay
				muted
				playsInline
			/>
		</div>
	);
};

export default MusicPlayer;
