import Head from "next/head";
import { useCallback, useEffect,  useState} from "react";
import { useRouter } from "next/router";
import { observer } from "mobx-react-lite";
import { gsap, Power3 } from "gsap";

// MobX Store
import { useUserGuestStore } from "../../stores/UserGuestStore";

// SmoothScrollProvider (Locomotive Scroll or similar)
import { SmoothScrollProvider } from "../../stores/scroll";

// Components
import Loader from "../../components/Loader";
import { Navbar } from "../../components/navbar";
import { SideMenu } from "../../components/sideMenu";
import { HeartsFalling } from "../../components/HeartsFalling";

// -- Wedding Blocks --
import { HeroSection } from "../../components/Invitation/HeroSection";
import { WeddingDateAndTime } from "../../components/Invitation/WeddingDateAndTime";
import { DressCodeBlock } from "../../components/Invitation/DressCodeBlock";
import { GiftBlock } from "../../components/Invitation/GiftBlock";
import { ChatBlock } from "../../components/Invitation/ChatBlock";
import { CoupleBlock } from "../../components/Invitation/CoupleBlock";
import { InvitationFooter } from "../../components/Invitation/InvitationFooter";
import { GuestRSVP } from "../../components/Invitation/GuestRSVP";

const InvitationPage = observer(() => {
	const router = useRouter();
	const { uniquePath } = router.query; // from URL: /invitation/[uniquePath]
	const userGuestStore = useUserGuestStore();
	const {
		guestData,
		getGuestByUniquePath,
		syncRSVPDataToServer,
		loading,
		error,
		isDirty,
	} = userGuestStore;
  
  const [loaded, setLoaded] = useState(false);
	// Fetch the guest whenever the URL param changes

	useEffect(() => {
		if (!uniquePath) return;
		getGuestByUniquePath(uniquePath);
	}, [uniquePath]);

	// Access guest data from the store
	const guest = guestData;

	// On page unload or unmount, sync the data
	// useEffect(() => {
	// 	// Only attach the event once the guest data is loaded
	// 	if (!guestData) return;

	// 	const handleBeforeUnload = (e) => {
	// 		let alertMessage = "";

	// 		// Check main guest
	// 		if (
	// 			guestData.rsvp_status === "accepted" &&
	// 			guestData.alcohol_preferences === "custom" &&
	// 			(!guestData.custom_alcohol ||
	// 				guestData.custom_alcohol.trim() === "")
	// 		) {
	// 			alertMessage = `Будь ласка, введіть ваш варіант напою для ${guestData.first_name}!`;
	// 		}

	// 		// Check plus one
	// 		if (
	// 			guestData.rsvp_status_plus_one === "accepted" &&
	// 			guestData.alcohol_preferences_plus_one === "custom" &&
	// 			(!guestData.custom_alcohol_plus_one ||
	// 				guestData.custom_alcohol_plus_one.trim() === "")
	// 		) {
	// 			alertMessage = `Будь ласка, введіть ваш варіант напою для ${guestData.first_name_plus_1}.`;
	// 		}

	// 		if (alertMessage) {
	// 			e.preventDefault();
	// 			e.returnValue = alertMessage;

	// 			// Browsers show a generic message
	// 			return alertMessage;
	// 		}

	// 		// Otherwise, sync data
	// 		syncRSVPDataToServer(true);
	// 	};

	// 	window.addEventListener("beforeunload", handleBeforeUnload);

	// 	return () => {
	// 		syncRSVPDataToServer(true);
	// 		window.removeEventListener("beforeunload", handleBeforeUnload);
	// 	};
	// }, [guestData, syncRSVPDataToServer]);

	// Every minute, check if there are unsynced changes and sync them.
	// const stableSyncRSVPDataToServer = useCallback(syncRSVPDataToServer, []);

	// useEffect(() => {
	// 	const intervalId = setInterval(() => {
	// 		if (isDirty) {
	// 			stableSyncRSVPDataToServer();
	// 		}
	// 	}, 60000);

	// 	return () => clearInterval(intervalId);
	// }, [isDirty, stableSyncRSVPDataToServer]);

	// GSAP Animations for initial load
	// useEffect(() => {
	// 	gsap.fromTo(
	// 		".hero_section",
	// 		{ opacity: 0, y: -15 },
	// 		{
	// 			duration: 2,
	// 			y: 0,
	// 			opacity: 1,
	// 			ease: Power3.easeInOut,
	// 			delay: 0.6,
	// 		}
	// 	);
	// }, []);
	useEffect(() => {
		if (typeof window === "undefined") return;
	}, []);

	// Loading / Error states
	if (loading) return <p></p>;
	if (error) return <p>Error: {error.message}</p>;
	if (!guest) return <p>404</p>;

	return (
		<>
    {!loaded && <Loader onComplete={() => setLoaded(true)} />}
			<Head>
				<title>Запрошення на весілля</title>
				<link
					rel="icon"
					href="/favicon.svg"
				/>
				<meta charSet="utf-8" />
			</Head>

			{/* Locomotive Scroll or any smooth scroll wrapper */}
			{/* <SmoothScrollProvider> */}
			<style
				global
				jsx
			>{`
				@font-face {
					font-family: "CarloMelowSans";
					src: url("/static/fonts/CarloMelowSans/CarloMelowSans.eot");
					src: url("/static/fonts/CarloMelowSans/CarloMelowSans.eot")
							format("embedded-opentype"),
						url("/static/fonts/CarloMelowSans/CarloMelowSans.woff2")
							format("woff2"),
						url("/static/fonts/CarloMelowSans/CarloMelowSans.woff")
							format("woff"),
						url("/static/fonts/CarloMelowSans/CarloMelowSans.ttf")
							format("truetype"),
						url("/static/fonts/CarloMelowSans/CarloMelowSans.svg#CarloMelowSans")
							format("svg");
				}
			`}</style>

			<Navbar />
			<SideMenu />
			{/* Example “floating” navbar / side menu if you want to keep them */}

			{/* Now your invitation blocks in logical order */}
			<main data-scroll-container>
				<HeroSection />
				<WeddingDateAndTime />
				<GuestRSVP />
				<DressCodeBlock />
				<GiftBlock />
				<ChatBlock />
				<CoupleBlock />
				<InvitationFooter />
			</main>
			{/* </SmoothScrollProvider> */}
		</>
	);
});

export default InvitationPage;
