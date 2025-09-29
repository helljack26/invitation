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
			<div className="dress_code_content">
				<div className="dress_code_content_item">
					<div className="dress_code_block_women_bg">
						<I.bg_heart_1 />
					</div>

					<h2>Дрес- код</h2>
					<p>
						Будемо вдячні, якщо <b className="pink_text">Жінки </b>
						підтримають стиль весілля і підберуть одяг у наступних
						кольорах:
					</p>
					<div className="dress_code_block_colors block_colors_1">
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
				</div>
				<div className="dress_code_content_item">
					<div className="dress_code_block_men_bg">
						<I.bg_heart_1 />
					</div>
					<p>
						Будемо вдячні, якщо<b className="green_text">Чоловіки</b>{" "}
						підтримають стиль весілля і підберуть одяг у цих кольорах:
					</p>
					<div className="dress_code_block_colors block_colors_2">
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
			</div>
		</section>
	);
};
