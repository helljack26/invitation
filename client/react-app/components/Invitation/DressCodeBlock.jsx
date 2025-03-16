import I from "../../img/images";
import Image from "next/image"; // Assuming you're using Next.js

export const DressCodeBlock = () => {
	// Define dress code colors with their corresponding images
	const dressCodeColors = [
		{ name: "Коричневий", src: I.dresscode_brown },
		{ name: "Зелений", src: I.dresscode_green },
		{ name: "Айворі", src: I.dresscode_ivory },
		{ name: "Оливковий", src: I.dresscode_olive },
		{ name: "Рожевий", src: I.dresscode_pink },
	];

	return (
		<section
			id="dressCodeBlock"
			className="dressCodeBlock"
			data-scroll-section
		>
			<h2>Дрес-код</h2>
			<p>
				Будемо вдячні, якщо ви підтримаєте стиль весілля і підберете одяг у
				наступних кольорах:
			</p>

			<div className="dressCodeBlock_colors">
				{dressCodeColors.map((color, index) => (
					<div
						key={index}
						className="colorItem"
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
		</section>
	);
};
