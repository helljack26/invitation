// components/Invitation/GiftBlock.jsx
import Image from "next/image";
import I from "../../img/images";

import GiftImg from "../../img/gift_block_img.svg";

export const GiftBlock = () => {
	return (
		<section
			id="giftBlock"
			className="gift_block"
			data-scroll-section
		>
			<h2>Подарунки</h2>
			<div className="gift_container">
				<div className="gift_container_item">
					<p>Не турбуйтесь про вибір подарунку</p>
					<div className="gift_images_wrapper mobile">
						<GiftImg
							className="gift_img"
							alt="Gift image"
							height={400}
						/>
					</div>
					<p>
						приймаємо будь-які грошові знаки:
						<br /> $, є, грн., visa, mastercard :)
					</p>
				</div>
				<div className="gift_images_wrapper desktop">
					<GiftImg
						className="gift_img"
						alt="Gift image"
						height={350}
					/>
				</div>
			</div>
		</section>
	);
};
