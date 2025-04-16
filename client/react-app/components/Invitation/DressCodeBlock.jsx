import I from "../../img/images";
import Image from "next/image"; // Assuming you're using Next.js

export const DressCodeBlock = () => {
	// Define dress code colors with their corresponding images
	const dressCodeWomenColors = [
		{ name: "Коричневий", src: I.dresscode_brown },
		{ name: "Зелений", src: I.dresscode_green },
		{ name: "Айворі", src: I.dresscode_ivory },
		{ name: "Оливковий", src: I.dresscode_olive },
		{ name: "Рожевий", src: I.dresscode_pink },
	];
	const dressCodeMenColors = [
		{ name: "Чорний", src: I.dresscode_black },
		{ name: "Білий", src: I.dresscode_white },
		{ name: "Зелений", src: I.dresscode_green },
	];

	return (
		<section
			id="dressCodeBlock"
			className="dress_code_block"
			data-scroll-section
		>
			<div className="dress_code_block_women_bg">
				{/* Фото наречених. Замініть на свої */}
				<Image
					alt="bg paper texture"
					src={I.bg_heart_1}
					fill
				/>
			</div>
			<div className="dress_code_block_men_bg">
				{/* Фото наречених. Замініть на свої */}
				<Image
					alt="bg paper texture"
					src={I.bg_heart_1}
					fill
				/>
			</div>

			<div className="dress_code_content">
				<h2>Дрес- код</h2>
				<p>
					Будемо вдячні, якщо <b className="pink_text">Жінки</b>{" "}
					підтримають стиль весілля і підберете одяг у наступних кольорах:
				</p>
				<div className="dress_code_block_colors">
					{dressCodeWomenColors.map((color, index) => (
						<div
							key={index}
							className="color_item"
						>
							<Image
								className="colorImage"
								src={color.src}
								alt={color.name}
								height={100}
								width={100}
							/>
						</div>
					))}
				</div>
				<p>
					Будемо вдячні, якщо<b className="green_text">Чоловіки</b>{" "}
					підтримають стиль весілля і підберуть одяг у цих кольорах:
				</p>
				<div className="dress_code_block_colors">
					{dressCodeMenColors.map((color, index) => (
						<div
							key={index}
							className="color_item"
						>
							<Image
								className="colorImage"
								src={color.src}
								alt={color.name}
								height={100}
								width={100}
							/>
						</div>
					))}
				</div>
			</div>
		</section>
	);
};
