// components/Invitation/ChatBlock.jsx
export const ChatBlock = () => {
	return (
		<section
			id="chatBlock"
			className="chat_block"
			data-scroll-section
		>
			<h2>Чат для гостей</h2>
			<p>
				Приєднуйтесь до нашого телеграм-каналу з новинами! Також діліться
				відео та фото з нашого свята!
			</p>
			<a
				href="https://t.me/yourTelegramChannel" // замініть на ваш канал
				target="_blank"
				rel="noopener noreferrer"
			>
				Приєднатися
			</a>
		</section>
	);
};
