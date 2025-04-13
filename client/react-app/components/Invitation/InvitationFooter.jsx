// components/Invitation/InvitationFooter.jsx

export const InvitationFooter = () => {
	return (
		<footer
			id="invitationFooter"
			className="invitation_footer"
			data-scroll-section
		>
			<p>Дякуємо, що розділите з нами радість цього незабутнього дня!</p>
			<p>
				З любов’ю
				<br />
				<strong>Марія і Дмитро</strong>
			</p>
			{/* New paragraph for who created the invitations */}
			<p className="creator">
				© {new Date().getFullYear()} Запрошення створені Нареченими
			</p>
		</footer>
	);
};
