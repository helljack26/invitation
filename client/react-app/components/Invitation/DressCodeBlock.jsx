// src/components/DressCodeBlock.jsx
import React from "react";
import Image from "next/image";
import I from "../../img/images";

// import masks as _raw_ URLs
import heartMask1 from "../../img/dresscode/dresscode_heart_mask.svg?url";
import heartMask2 from "../../img/dresscode/dresscode_heart_mask_1.svg?url";
import heartMask3 from "../../img/dresscode/dresscode_heart_mask_2.svg?url";

const dressCodeWomenColors = [
	{ name: "Коричневий", src: I.dresscode_brown, mask: heartMask1 },
	{ name: "Зелений", src: I.dresscode_green, mask: heartMask2 },
	{ name: "Айворі", src: I.dresscode_ivory, mask: heartMask3 },
	{ name: "Оливковий", src: I.dresscode_olive, mask: heartMask1 },
	{ name: "Рожевий", src: I.dresscode_pink, mask: heartMask2 },
];

const dressCodeMenColors = [
	{ name: "Чорний", src: I.dresscode_black, mask: heartMask2 },
	{ name: "Білий", src: I.dresscode_white, mask: heartMask3 },
	{ name: "Зелений", src: I.dresscode_green, mask: heartMask1 },
];

const renderSwatches = (colors) =>
	colors.map(({ name, src, mask }, i) => (
		<div
			key={i}
			className="color_item"
		>
			<div
				className="colorImage"
				style={{
					position: "relative",
					WebkitMaskImage: `url(${mask})`,
					maskImage: `url(${mask})`,
					WebkitMaskRepeat: "no-repeat",
					maskRepeat: "no-repeat",
					WebkitMaskPosition: "center",
					maskPosition: "center",
					WebkitMaskSize: "contain",
					maskSize: "contain",
					WebkitMaskMode: "alpha",
					maskMode: "alpha",
				}}
			>
				<Image
					src={src}
					alt={name}
					fill
					style={{ objectFit: "cover" }}
					sizes="100px"
				/>
			</div>
		</div>
	));

export const DressCodeBlock = () => (
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
				<h2>Дрес-код</h2>
				<p>
					Будемо вдячні, якщо <b className="pink_text">Жінки</b>{" "}
					підбиратимуть одяг у таких кольорах:
				</p>
				<div className="dress_code_block_colors block_colors_1">
					{renderSwatches(dressCodeWomenColors)}
				</div>
			</div>

			<div className="dress_code_content_item">
				<div className="dress_code_block_men_bg">
					<I.bg_heart_1 />
				</div>
				<p>
					Будемо вдячні, якщо <b className="green_text">Чоловіки</b>{" "}
					підбиратимуть одяг у таких кольорах:
				</p>
				<div className="dress_code_block_colors block_colors_2">
					{renderSwatches(dressCodeMenColors)}
				</div>
			</div>
		</div>
	</section>
);
