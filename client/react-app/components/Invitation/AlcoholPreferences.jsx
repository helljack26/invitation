import React from "react";
import { observer } from "mobx-react-lite";
import { useUserGuestStore } from "../../stores/UserGuestStore";
import { AnimatePresence, motion } from "framer-motion";

export const AlcoholPreferences = observer(({ isPlusOne }) => {
	const {
		guestData,
		setAlcoholPreferenceInStore,
		setWineTypeInStore,
		setCustomAlcoholInStore,
	} = useUserGuestStore();

	const currentAlcoholChoice = isPlusOne
		? guestData.alcohol_preferences_plus_one
		: guestData.alcohol_preferences;
	const currentWineType = isPlusOne
		? guestData.wine_type_plus_one
		: guestData.wine_type;
	const currentCustomAlcohol = isPlusOne
		? guestData.custom_alcohol_plus_one
		: guestData.custom_alcohol;

	const handleAlcoholChange = (e) => {
		const chosenValue = e.target.value;
		setAlcoholPreferenceInStore(chosenValue, isPlusOne);

		// Set default wine type for wine options
		if (chosenValue === "Вино біле" || chosenValue === "Вино червоне") {
			setWineTypeInStore("сухе", isPlusOne);
		} else {
			setWineTypeInStore(null, isPlusOne);
		}
	};

	const handleWineTypeChange = (e) => {
		setWineTypeInStore(e.target.value, isPlusOne);
	};

	const handleCustomChange = (e) => {
		setCustomAlcoholInStore(e.target.value, isPlusOne);
	};

	const slideVariants = {
		hidden: { height: 0, opacity: 0 },
		visible: { height: "auto", opacity: 1 },
		exit: { height: 0, opacity: 0 },
	};

	return (
		<div className="alcoholPreferences">
			<p>Виберіть улюблений напій:</p>

			{/* Безалкогольні напої */}
			<label>
				<input
					type="radio"
					name={`alcohol-${isPlusOne ? "plusOne" : "main"}`}
					value="Безалкогольні напої"
					checked={currentAlcoholChoice === "Безалкогольні напої"}
					onChange={handleAlcoholChange}
				/>
				<span className="customRadio"></span>
				<span>Безалкогольні напої</span>
			</label>

			{/* Radio buttons for main alcohol choice */}
			<label>
				<input
					type="radio"
					name={`alcohol-${isPlusOne ? "plusOne" : "main"}`}
					value="Горілка"
					checked={currentAlcoholChoice === "Горілка"}
					onChange={handleAlcoholChange}
				/>
				<span className="customRadio"></span>
				<span>Горілка</span>
			</label>

			<label>
				<input
					type="radio"
					name={`alcohol-${isPlusOne ? "plusOne" : "main"}`}
					value="Віскі"
					checked={currentAlcoholChoice === "Віскі"}
					onChange={handleAlcoholChange}
				/>
				<span className="customRadio"></span>
				<span>Віскі</span>
			</label>

			<label>
				<input
					type="radio"
					name={`alcohol-${isPlusOne ? "plusOne" : "main"}`}
					value="Ром"
					checked={currentAlcoholChoice === "Ром"}
					onChange={handleAlcoholChange}
				/>
				<span className="customRadio"></span>
				<span>Ром</span>
			</label>

			<label>
				<input
					type="radio"
					name={`alcohol-${isPlusOne ? "plusOne" : "main"}`}
					value="Вино біле"
					checked={currentAlcoholChoice === "Вино біле"}
					onChange={handleAlcoholChange}
				/>
				<span className="customRadio"></span>
				<span>Вино біле</span>
			</label>

			{/* Animate wine type block for white wine */}
			<AnimatePresence>
				{currentAlcoholChoice === "Вино біле" && (
					<motion.div
						variants={slideVariants}
						initial="hidden"
						animate="visible"
						exit="exit"
						transition={{ duration: 0.3 }}
						className="wineTypeBlock"
					>
						<p>Оберіть тип вина:</p>
						<label>
							<input
								type="radio"
								name={`wineType-${isPlusOne ? "plusOne" : "main"}`}
								value="сухе"
								checked={currentWineType === "сухе"}
								onChange={handleWineTypeChange}
							/>
							<span className="customRadio"></span>
							<span>сухе</span>
						</label>
						<label>
							<input
								type="radio"
								name={`wineType-${isPlusOne ? "plusOne" : "main"}`}
								value="напівсолодке"
								checked={currentWineType === "напівсолодке"}
								onChange={handleWineTypeChange}
							/>
							<span className="customRadio"></span>
							<span>напівсолодке</span>
						</label>
					</motion.div>
				)}
			</AnimatePresence>

			<label>
				<input
					type="radio"
					name={`alcohol-${isPlusOne ? "plusOne" : "main"}`}
					value="Вино червоне"
					checked={currentAlcoholChoice === "Вино червоне"}
					onChange={handleAlcoholChange}
				/>
				<span className="customRadio"></span>
				<span>Вино червоне</span>
			</label>

			{/* Animate wine type block for red wine */}
			<AnimatePresence>
				{currentAlcoholChoice === "Вино червоне" && (
					<motion.div
						variants={slideVariants}
						initial="hidden"
						animate="visible"
						exit="exit"
						transition={{ duration: 0.3 }}
						className="wineTypeBlock"
					>
						<p>Оберіть тип вина:</p>
						<label>
							<input
								type="radio"
								name={`wineType-${isPlusOne ? "plusOne" : "main"}`}
								value="сухе"
								checked={currentWineType === "сухе"}
								onChange={handleWineTypeChange}
							/>
							<span className="customRadio"></span>
							<span>сухе</span>
						</label>
						<label>
							<input
								type="radio"
								name={`wineType-${isPlusOne ? "plusOne" : "main"}`}
								value="напівсолодке"
								checked={currentWineType === "напівсолодке"}
								onChange={handleWineTypeChange}
							/>
							<span className="customRadio"></span>
							<span>напівсолодке</span>
						</label>
					</motion.div>
				)}
			</AnimatePresence>
		</div>
	);
});
