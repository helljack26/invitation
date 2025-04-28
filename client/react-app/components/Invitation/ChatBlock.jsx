import I from "../../img/images";
import Image from "next/image"; // Assuming you're using Next.js

// components/Invitation/ChatBlock.jsx
export const ChatBlock = () => {
	return (
		<section
			id="chatBlock"
			className="chat_block"
			data-scroll-section
		>
			<div className="chat_block_bg">
				{/* Фото наречених. Замініть на свої */}
				<Image
					alt="bg big heart"
					src={I.bg_heart_3}
					fill
				/>
			</div>

			<div className="chat_block_content">
				<h2>Чат для гостей</h2>
				<p>
					Приєднуйтесь до нашого телеграм-каналу з новинами! <br />
					Також діліться відео та фото з нашого свята!
				</p>
				<div className="chat_block_btn_wrapper">
					<a
						href="https://t.me/yourTelegramChannel" // замініть на ваш канал
						target="_blank"
						rel="noopener noreferrer"
					>
						<span>Приєднатися</span>
					</a>
					<div className="chat_block_arrow">
						{/* Фото наречених. Замініть на свої */}
						<Image
							alt="swirl arrow bottom right icon"
							src={I.swirl_arrow_icon}
						/>
					</div>
				</div>
			</div>
		</section>
	);
};
