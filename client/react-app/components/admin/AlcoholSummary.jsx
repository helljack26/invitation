import React, { useMemo, useState } from "react";
import { observer } from "mobx-react-lite";
import GuestStore from "../../stores/GuestStore";

const AlcoholSummary = observer(() => {
	// State to store the hovered beverage detail
	const [hoveredDetail, setHoveredDetail] = useState(null);

	// Compute summary data only when the guest list changes
	const summary = useMemo(() => {
		// Initialize counters and guest lists for default preferences.
		const defaultCounts = {
			Горілка: { count: 0, guests: [] },
			Віскі: { count: 0, guests: [] },
			Ром: { count: 0, guests: [] },
			"Вино біле": {
				сухе: { count: 0, guests: [] },
				напівсолодке: { count: 0, guests: [] },
				unspecified: { count: 0, guests: [] },
			},
			"Вино червоне": {
				сухе: { count: 0, guests: [] },
				напівсолодке: { count: 0, guests: [] },
				unspecified: { count: 0, guests: [] },
			},
		};

		const customList = [];

		// Helper function for processing each alcohol selection
		const processAlcohol = (
			guestName,
			alcohol,
			wineType,
			customAlcohol,
			typeLabel = "Головний"
		) => {
			if (!alcohol) return;
			if (alcohol === "custom") {
				if (customAlcohol) {
					customList.push({
						guestName,
						type: typeLabel,
						preference: customAlcohol,
					});
				}
			} else if (alcohol === "Вино біле" || alcohol === "Вино червоне") {
				// For wine options, determine the subtype
				const subtype =
					wineType === "сухе" || wineType === "напівсолодке"
						? wineType
						: "unspecified";
				defaultCounts[alcohol][subtype].count += 1;
				defaultCounts[alcohol][subtype].guests.push(guestName);
			} else if (defaultCounts[alcohol] !== undefined) {
				defaultCounts[alcohol].count += 1;
				defaultCounts[alcohol].guests.push(guestName);
			}
		};

		// Loop through each guest from the store.
		GuestStore.guestsList.forEach((guest) => {
			// Process main guest's alcohol choice.
			processAlcohol(
				guest.first_name,
				guest.alcohol_preferences,
				guest.wine_type,
				guest.custom_alcohol,
				"Головний"
			);
			// Process plus one if exists.
			if (guest.first_name_plus_1) {
				processAlcohol(
					guest.first_name_plus_1,
					guest.alcohol_preferences_plus_one,
					guest.wine_type_plus_one,
					guest.custom_alcohol_plus_one,
					"Додатковий"
				);
			}
		});

		return { defaultCounts, customList };
	}, [GuestStore.guestsList]);

	// Helper to handle hover events
	const handleMouseEnter = (beverage, subtype = null) => {
		if (subtype) {
			const detail = summary.defaultCounts[beverage][subtype];
			setHoveredDetail({
				title: `${beverage} (${subtype})`,
				guests: detail.guests,
			});
		} else {
			const detail = summary.defaultCounts[beverage];
			setHoveredDetail({
				title: beverage,
				guests: detail.guests,
			});
		}
	};

	const handleMouseLeave = () => {
		setHoveredDetail(null);
	};

	return (
		<div
			className="container"
			style={{ marginTop: "20px", position: "relative" }}
		>
			<div className="card">
				<div className="card-content">
					<span className="card-title">
						Підсумок алкогольних вподобань
					</span>

					<h5>Стандартні варіанти</h5>
					<ul className="collection">
						<li
							className="collection-item"
							onMouseEnter={() => handleMouseEnter("Горілка")}
							onMouseLeave={handleMouseLeave}
						>
							<strong>Горілка:</strong>{" "}
							{summary.defaultCounts["Горілка"].count}
						</li>
						<li
							className="collection-item"
							onMouseEnter={() => handleMouseEnter("Віскі")}
							onMouseLeave={handleMouseLeave}
						>
							<strong>Віскі:</strong>{" "}
							{summary.defaultCounts["Віскі"].count}
						</li>
						<li
							className="collection-item"
							onMouseEnter={() => handleMouseEnter("Ром")}
							onMouseLeave={handleMouseLeave}
						>
							<strong>Ром:</strong> {summary.defaultCounts["Ром"].count}
						</li>
						<li className="collection-item">
							<strong>Вино біле:</strong>
							<ul>
								<li
									onMouseEnter={() =>
										handleMouseEnter("Вино біле", "сухе")
									}
									onMouseLeave={handleMouseLeave}
								>
									<em>сухе:</em>{" "}
									{summary.defaultCounts["Вино біле"]["сухе"].count}
								</li>
								<li
									onMouseEnter={() =>
										handleMouseEnter("Вино біле", "напівсолодке")
									}
									onMouseLeave={handleMouseLeave}
								>
									<em>напівсолодке:</em>{" "}
									{
										summary.defaultCounts["Вино біле"]["напівсолодке"]
											.count
									}
								</li>
								<li
									onMouseEnter={() =>
										handleMouseEnter("Вино біле", "unspecified")
									}
									onMouseLeave={handleMouseLeave}
								>
									<em>Невказано:</em>{" "}
									{
										summary.defaultCounts["Вино біле"].unspecified
											.count
									}
								</li>
							</ul>
						</li>
						<li className="collection-item">
							<strong>Вино червоне:</strong>
							<ul>
								<li
									onMouseEnter={() =>
										handleMouseEnter("Вино червоне", "сухе")
									}
									onMouseLeave={handleMouseLeave}
								>
									<em>сухе:</em>{" "}
									{summary.defaultCounts["Вино червоне"]["сухе"].count}
								</li>
								<li
									onMouseEnter={() =>
										handleMouseEnter("Вино червоне", "напівсолодке")
									}
									onMouseLeave={handleMouseLeave}
								>
									<em>напівсолодке:</em>{" "}
									{
										summary.defaultCounts["Вино червоне"][
											"напівсолодке"
										].count
									}
								</li>
								<li
									onMouseEnter={() =>
										handleMouseEnter("Вино червоне", "unspecified")
									}
									onMouseLeave={handleMouseLeave}
								>
									<em>Невказано:</em>{" "}
									{
										summary.defaultCounts["Вино червоне"].unspecified
											.count
									}
								</li>
							</ul>
						</li>
					</ul>

					<h5>Користувацькі варіанти</h5>
					{summary.customList.length > 0 ? (
						<table className="striped">
							<thead>
								<tr>
									<th>Ім'я гостя</th>
									<th>Тип</th>
									<th>Вказаний напій</th>
								</tr>
							</thead>
							<tbody>
								{summary.customList.map((item, index) => (
									<tr key={index}>
										<td>{item.guestName}</td>
										<td>{item.type}</td>
										<td>{item.preference}</td>
									</tr>
								))}
							</tbody>
						</table>
					) : (
						<p>Немає користувацьких вподобань</p>
					)}
				</div>
			</div>

			{/* Hover details panel */}
			{hoveredDetail && hoveredDetail.guests.length > 0 && (
				<div
					className="card-panel grey lighten-4"
					style={{
						position: "absolute",
						top: "10px",
						right: "10px",
						zIndex: 1000,
						maxWidth: "250px",
					}}
				>
					<span
						className="card-title"
						style={{ fontSize: "16px", marginBottom: "5px" }}
					>
						{hoveredDetail.title}
					</span>
					<ul>
						{hoveredDetail.guests.map((name, idx) => (
							<li
								key={idx}
								style={{ fontSize: "14px" }}
							>
								{name}
							</li>
						))}
					</ul>
				</div>
			)}
		</div>
	);
});

export default AlcoholSummary;
