import React, { useRef, useEffect } from "react";

const ConfettiButton = ({
	onClick,
	selected,
	soundSrc,
	buttonText = "Так",
}) => {
	const canvasRef = useRef(null);
	const buttonRef = useRef(null);
	const audioRef = useRef(null);

	// Refs to hold active confetti and sequin instances
	const confetti = useRef([]);
	const sequins = useRef([]);

	// Settings
	const confettiCount = 20;
	const sequinCount = 10;
	const gravityConfetti = 0.3;
	const gravitySequins = 0.55;
	const dragConfetti = 0.075;
	const dragSequins = 0.02;
	const terminalVelocity = 3;

	// Helper: random number between min and max
	const randomRange = (min, max) => Math.random() * (max - min) + min;

	// Helper to initialize confetto velocity with a weighted vertical spread
	const initConfettoVelocity = (xRange, yRange) => {
		const x = randomRange(xRange[0], xRange[1]);
		const range = yRange[1] - yRange[0] + 1;
		let y =
			yRange[1] -
			Math.abs(randomRange(0, range) + randomRange(0, range) - range);
		if (y >= yRange[1] - 1) {
			y += Math.random() < 0.25 ? randomRange(1, 3) : 0;
		}
		return { x, y: -y };
	};

	// Colors palette (each confetto uses a "front" and "back" color for a flipping effect)
	const colors = [
		{ front: "#ff9aff", back: "#3a865b" },
		{ front: "#ff48ff", back: "#276542" },
		{ front: "#e715e7", back: "#00cb58" },
	];

	// Confetto "class"
	class Confetto {
		constructor() {
			this.randomModifier = randomRange(0, 99);
			this.color = colors[Math.floor(randomRange(0, colors.length))];
			this.dimensions = {
				x: randomRange(5, 9),
				y: randomRange(8, 15),
			};
			// Get the button's bounding rectangle from the DOM
			const button = buttonRef.current;
			const rect = button.getBoundingClientRect();
			// Start the confetto near the center of the button
			this.position = {
				x: randomRange(
					rect.left + rect.width / 2 - button.offsetWidth / 4,
					rect.left + rect.width / 2 + button.offsetWidth / 4
				),
				y: randomRange(
					rect.top + rect.height / 2 + 8,
					rect.top + rect.height / 2 + 1.5 * button.offsetHeight - 8
				),
			};
			this.rotation = randomRange(0, 2 * Math.PI);
			this.scale = { x: 1, y: 1 };
			this.velocity = initConfettoVelocity([-9, 9], [6, 11]);
		}
		update() {
			// Apply drag and gravity, then update the position
			this.velocity.x -= this.velocity.x * dragConfetti;
			this.velocity.y = Math.min(
				this.velocity.y + gravityConfetti,
				terminalVelocity
			);
			this.velocity.x +=
				Math.random() > 0.5 ? Math.random() : -Math.random();
			this.position.x += this.velocity.x;
			this.position.y += this.velocity.y;
			// Creates a flipping effect
			this.scale.y = Math.cos(
				(this.position.y + this.randomModifier) * 0.09
			);
		}
	}

	// Sequin "class"
	class Sequin {
		constructor() {
			this.color = colors[Math.floor(randomRange(0, colors.length))].back;
			this.radius = randomRange(1, 2);
			const button = buttonRef.current;
			const rect = button.getBoundingClientRect();
			this.position = {
				x: randomRange(
					rect.left + rect.width / 2 - button.offsetWidth / 3,
					rect.left + rect.width / 2 + button.offsetWidth / 3
				),
				y: randomRange(
					rect.top + rect.height / 2 + 8,
					rect.top + rect.height / 2 + 1.5 * button.offsetHeight - 8
				),
			};
			this.velocity = {
				x: randomRange(-6, 6),
				y: randomRange(-8, -12),
			};
		}
		update() {
			this.velocity.x -= this.velocity.x * dragSequins;
			this.velocity.y += gravitySequins;
			this.position.x += this.velocity.x;
			this.position.y += this.velocity.y;
		}
	}

	// Create a burst by instantiating confetti and sequin objects
	const initBurst = () => {
		for (let i = 0; i < confettiCount; i++) {
			confetti.current.push(new Confetto());
		}
		for (let i = 0; i < sequinCount; i++) {
			sequins.current.push(new Sequin());
		}
	};

	// Render loop (updates and redraws the canvas)
	const render = () => {
		const canvas = canvasRef.current;
		if (!canvas) return;
		const ctx = canvas.getContext("2d");
		ctx.clearRect(0, 0, canvas.width, canvas.height);

		// Draw confetti pieces
		confetti.current.forEach((confetto) => {
			const width = confetto.dimensions.x * confetto.scale.x;
			const height = confetto.dimensions.y * confetto.scale.y;
			ctx.save();
			ctx.translate(confetto.position.x, confetto.position.y);
			ctx.rotate(confetto.rotation);
			confetto.update();
			ctx.fillStyle =
				confetto.scale.y > 0 ? confetto.color.front : confetto.color.back;
			ctx.fillRect(-width / 2, -height / 2, width, height);
			ctx.restore();

			// Clear the confetti overlapping the button
			const button = buttonRef.current;
			const rect = button.getBoundingClientRect();
			ctx.clearRect(rect.left, rect.top, rect.width, rect.height);
		});

		// Draw sequins
		sequins.current.forEach((sequin) => {
			ctx.save();
			ctx.translate(sequin.position.x, sequin.position.y);
			sequin.update();
			ctx.fillStyle = sequin.color;
			ctx.beginPath();
			ctx.arc(0, 0, sequin.radius, 0, 2 * Math.PI);
			ctx.fill();
			ctx.restore();

			const button = buttonRef.current;
			const rect = button.getBoundingClientRect();
			ctx.clearRect(rect.left, rect.top, rect.width, rect.height);
		});

		// Remove elements that have fallen off the canvas
		confetti.current = confetti.current.filter(
			(confetto) => confetto.position.y < canvas.height
		);
		sequins.current = sequins.current.filter(
			(sequin) => sequin.position.y < canvas.height
		);

		requestAnimationFrame(render);
	};

	// Setup the canvas size and begin the render loop.
	useEffect(() => {
		const canvas = canvasRef.current;
		const resizeCanvas = () => {
			if (canvas) {
				canvas.width = window.innerWidth;
				canvas.height = window.innerHeight;
			}
		};
		resizeCanvas();
		window.addEventListener("resize", resizeCanvas);
		requestAnimationFrame(render);
		return () => window.removeEventListener("resize", resizeCanvas);
	}, []);

	// Handler for the button click: play sound, trigger burst and call parent's onClick
	const handleClick = () => {
		// Play sound
		if (audioRef.current) {
			audioRef.current.currentTime = 0;
			audioRef.current.play();
		}
		// Trigger confetti burst animation
		initBurst();
		// Inform the parent component about the click (update RSVP status)
		if (onClick) {
			onClick();
		}
	};

	// Коментар (укр): прибираємо зовнішній <div>, щоб <button>
	// був прямим дочірнім елементом .guestRowButtons і стилі на
	// селектор типу ".guestRowButtons > button.selected" коректно спрацьовували.

	return (
		<>
			<button
				type="button"
				ref={buttonRef}
				onClick={handleClick}
				className={selected ? "selected" : ""}
				style={{ display: "block", cursor: "pointer" }}
			>
				<span>{buttonText}</span>
			</button>

			<audio
				ref={audioRef}
				src={soundSrc}
				preload="auto"
			/>

			<canvas
				ref={canvasRef}
				style={{
					position: "fixed",
					top: 0,
					left: 0,
					pointerEvents: "none",
					width: "100%",
					height: "100%",
					zIndex: 0,
				}}
			/>
		</>
	);
};

export default ConfettiButton;
