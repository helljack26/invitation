import { useRouter } from "next/router";
import { useEffect } from "react";
import { observer } from "mobx-react-lite";
import GuestStore from "../../stores/GuestStore";

const InvitationPage = observer(() => {
	const router = useRouter();
	const { uniquePath } = router.query; // from the URL: /invite/<uniquePath>

	// On mount or whenever `uniquePath` changes, fetch guest data
	useEffect(() => {
		if (!uniquePath) return;
		GuestStore.getGuestByUniquePath(uniquePath);
	}, [uniquePath]);

	if (GuestStore.loading) return <p>Loading...</p>;
	if (GuestStore.error) return <p>Error: {GuestStore.error.message}</p>;

	// Guest data from the store after fetching
	const guest = GuestStore.guestData;

	// If no guest found
	if (!guest) {
		return <p>No guest found for this invitation.</p>;
	}

	return (
		<div style={{ padding: "20px" }}>
			<h1>Wedding Invitation</h1>
			<h2>
				Welcome, {guest.first_name} {guest.last_name}!
			</h2>
			{guest.has_plus_one && guest.plus_one_name ? (
				<p>Plus One: {guest.plus_one_name}</p>
			) : (
				<p>No plus one added.</p>
			)}
			{/* 
        Here you can display any additional fields, 
        e.g. wedding details, RSVP button, etc. 
      */}
		</div>
	);
});

export default InvitationPage;
