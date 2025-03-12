// components/Invitation/LocationBlock.jsx
export const LocationBlock = () => {
	return (
		<section
			className="locationBlock"
			data-scroll-section
		>
			<h2>Місце проведення</h2>
			<p>
				Свято відбудеться в затишному ресторані <strong>«Назва»</strong> за
				адресою:
				<br />
				<strong>м. Дубна, вул. Московська, 2</strong>
			</p>
			<a
				href="https://maps.google.com"
				target="_blank"
				rel="noopener noreferrer"
			>
				Подивитися на карті
			</a>
		</section>
	);
};
