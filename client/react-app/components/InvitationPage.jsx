import Head from "next/head";
import { useCallback, useEffect, useState, useRef } from "react";
import { useRouter } from "next/router";
import { observer } from "mobx-react-lite";
import { gsap, Power3 } from "gsap";

// MobX Store
import { useUserGuestStore } from "../stores/UserGuestStore";

// Components
import Loader from "./Loader";
import Navbar from "./navbar";
import SideMenu from "./sideMenu";

// -- Wedding Blocks --
import { HeroSection } from "./Invitation/HeroSection";
import { WeddingDateAndTime } from "./Invitation/WeddingDateAndTime";
import { DressCodeBlock } from "./Invitation/DressCodeBlock";
import { GiftBlock } from "./Invitation/GiftBlock";
import { ChatBlock } from "./Invitation/ChatBlock";
import { CoupleBlock } from "./Invitation/CoupleBlock";
import { InvitationFooter } from "./Invitation/InvitationFooter";
import { GuestRSVP } from "./Invitation/GuestRSVP";

import useLenis from "../hooks/useLenis";

const InvitationPage = observer(() => {
	const lenis = useLenis();
	// Scroll to top on mount (and again after loader finishes)
	
	
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
	
	const [showLoader, setShowLoader] = useState(true);
	
	// Fetch the guest whenever the URL param changes
	useEffect(() => {
		document.body.style.overflow = showLoader ? "hidden" : "";
	  }, [showLoader]);
	const hasFetched = useRef(false);

	useEffect(() => {
		if (!uniquePath || hasFetched.current) return;
		getGuestByUniquePath(uniquePath);
		hasFetched.current = true;
	}, [uniquePath, getGuestByUniquePath]);

	// Access guest data from the store
	const guest = guestData;

	// On page unload or unmount, sync the data
	useEffect(() => {
		// Only attach the event once the guest data is loaded
		if (!guestData) return;

		const handleBeforeUnload = (e) => {
			let alertMessage = "";

			// Check main guest
			if (
				guestData.rsvp_status === "accepted" &&
				guestData.alcohol_preferences === "custom" &&
				(!guestData.custom_alcohol ||
					guestData.custom_alcohol.trim() === "")
			) {
				alertMessage = `Будь ласка, введіть ваш варіант напою для ${guestData.first_name}!`;
			}

			// Check plus one
			if (
				guestData.rsvp_status_plus_one === "accepted" &&
				guestData.alcohol_preferences_plus_one === "custom" &&
				(!guestData.custom_alcohol_plus_one ||
					guestData.custom_alcohol_plus_one.trim() === "")
			) {
				alertMessage = `Будь ласка, введіть ваш варіант напою для ${guestData.first_name_plus_1}.`;
			}
			
			if (alertMessage) {
				e.preventDefault();
				e.returnValue = alertMessage;

				// Browsers show a generic message
				return alertMessage;
			}

			// Otherwise, sync data
			syncRSVPDataToServer(true);
		};
		
		window.addEventListener("beforeunload", handleBeforeUnload);
		
		return () => {
			syncRSVPDataToServer(true);
			window.removeEventListener("beforeunload", handleBeforeUnload);
		};
	}, [guestData, syncRSVPDataToServer]);

	// Every minute, check if there are unsynced changes and sync them.
	const stableSyncRSVPDataToServer = useCallback(syncRSVPDataToServer, []);

	useEffect(() => {
		const intervalId = setInterval(() => {
			if (isDirty) {
				stableSyncRSVPDataToServer();
			}
		}, 60000);

		return () => clearInterval(intervalId);
	}, [isDirty, stableSyncRSVPDataToServer]);
	
	useEffect(() => {
		// loader just finished
		if (lenis) {
		lenis.scrollTo(0, { duration: 0 });
		} else {
		window.scrollTo(0, 0);
		}
	  }, [lenis]);
	  
	useEffect(() => {
		if (showLoader) {
			// native
			document.body.style.overflow = "hidden";
		  // Lenis
		  if (lenis?.stop) lenis.stop();
		} else {
		  document.body.style.overflow = "";
		  
		  if (lenis?.start) lenis.start();
		}
	  }, [showLoader, lenis]);

	
	  
	// Loading / Error states
	if (!guest && !loading) return <p>404</p>;

	const baseUrl = "https://maria-dima-wedding.com.ua";

	// ——— same logic as in HeroSection ———
	let greetingPrefix;
	if (guest?.first_name_plus_1) {
		greetingPrefix = "Дорогі";
	} else {
		greetingPrefix = guest?.gender === "male" ? "Дорогий" : "Дорога";
	}

	const displayName = guest?.first_name_plus_1
		? `${guest?.first_name} та ${guest?.first_name_plus_1}`
		: guest?.first_name;

	// ——— OG / Twitter card values ———
	const url = `${baseUrl}${router.asPath}`;
	const title = `${greetingPrefix} ${displayName}`;
	const description = `Щиро запрошуємо ${
		guestData?.first_name_plus_1 ? "Вас" : "Тебе"
	} ${displayName} на наше весілля!`;
	const imageUrl = `${baseUrl}/img/bride_groom_child.png`;

	useEffect(() => {
		if (typeof window === "undefined") return;
	}, []);
	return (
		<>
			{showLoader && <Loader onComplete={() => setShowLoader(false)} />}
			<Head>
				<title>Запрошення на весілля</title>
				<link
					rel="icon"
					href="/favicon.svg"
				/>
				<meta charSet="utf-8" />
			</Head>

			<Navbar />
			<SideMenu />
			{/* Locomotive Scroll or any smooth scroll wrapper */}
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

			{/* Example “floating” navbar / side menu if you want to keep them */}

			{/* Now your invitation blocks in logical order */}

			<div
				id="lenis-wrapper"
				style={{ overflow: showLoader ? "hidden" : "auto" }}
			>
				<div id="lenis-content">
					<HeroSection />
					<WeddingDateAndTime />
					<GuestRSVP />
					<DressCodeBlock />
					<GiftBlock />
					<ChatBlock />
					<CoupleBlock />
					<InvitationFooter />
				</div>
			</div>
		</>
	);
});

export default InvitationPage;
