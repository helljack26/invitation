// components/Invitation/ChatBlock.jsx
export const ChatBlock = () => {
	return (
		<section
			id="chatBlock"
			className="chatBlock"
			data-scroll-section
		>
			<h2>Чат для гостей</h2>
			<p>
				Приєднуйтеся до нашого Telegram-каналу та діліться новинами, фото й
				відео з нашого свята!
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
