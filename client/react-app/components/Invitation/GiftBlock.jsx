// components/Invitation/GiftBlock.jsx
import Image from "next/image";
import I from "../../img/images";

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
					<div className="gift_images_wrapper">
						<Image
							className="gift_img mobile"
							alt="Gift image"
							src={I.gift_block_img}
							height={400}
						/>
					</div>
					<p>
						приймаємо будь-які грошові знаки:
						<br /> $, є, грн., visa, mastercard :)
					</p>
				</div>
				<div className="gift_images_wrapper">
					<Image
						className="gift_img desktop"
						alt="Gift image"
						src={I.gift_block_img}
						height={350}
					/>
				</div>
			</div>
		</section>
	);
};
