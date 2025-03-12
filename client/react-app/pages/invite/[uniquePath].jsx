import Head from "next/head";
import { useEffect } from "react";
import { useRouter } from "next/router";
import { observer } from "mobx-react-lite";
import { gsap, Power3 } from "gsap";

// MobX Store
import GuestStore from "../../stores/GuestStore";
// SmoothScrollProvider (Locomotive Scroll or similar)
import { SmoothScrollProvider } from "../../stores/scroll";

// Components
import { Navbar } from "../../components/navbar";
import { SideMenu } from "../../components/sideMenu";
import { LeafsFalling } from "../../components/leafsFalling";

// -- Wedding Blocks --
import { HeroSection } from "../../components/Invitation/HeroSection";
import { WeddingDateAndTime } from "../../components/Invitation/WeddingDateAndTime";
import { LocationBlock } from "../../components/Invitation/LocationBlock";
import { DressCodeBlock } from "../../components/Invitation/DressCodeBlock";
import { GiftBlock } from "../../components/Invitation/GiftBlock";
import { ChatBlock } from "../../components/Invitation/ChatBlock";
import { CoupleBlock } from "../../components/Invitation/CoupleBlock";
import { InvitationFooter } from "../../components/Invitation/InvitationFooter";

const InvitationPage = observer(() => {
	const router = useRouter();
	const { uniquePath } = router.query; // from URL: /invitation/[uniquePath]

	// Fetch the guest whenever the URL param changes
	useEffect(() => {
		if (!uniquePath) return;
		GuestStore.getGuestByUniquePath(uniquePath);
	}, [uniquePath]);

	// Access guest data from the store
	const guest = GuestStore.guestData;

	// GSAP Animations for initial load
	useEffect(() => {
		gsap.fromTo(
			".heroSection",
			{ opacity: 0, y: -15 },
			{
				duration: 2,
				y: 0,
				opacity: 1,
				ease: Power3.easeInOut,
				delay: 0.6,
			}
		);
	}, []);

	// Loading / Error states
	if (GuestStore.loading) return <p></p>;
	if (GuestStore.error) return <p>Error: {GuestStore.error.message}</p>;
	if (!guest) return <p>404</p>;

	return (
		<>
			<Head>
				<title>Запрошення на весілля</title>
				<link
					rel="icon"
					href="/favicon.svg"
				/>
				<meta charSet="utf-8" />
			</Head>

			{/* Optional falling leaves or confetti effect */}
			<LeafsFalling />

			{/* Locomotive Scroll or any smooth scroll wrapper */}
			{/* <SmoothScrollProvider> */}
			<style
				global
				jsx
			>{`
				@font-face {
					font-family: "Poppins-Regular";
					src: url("/static/fonts/Poppins-Regular/Poppins-Regular.eot");
					src: local("☺"),
						url("/static/fonts/Poppins-Regular/Poppins-Regular.woff")
							format("woff"),
						url("/static/fonts/Poppins-Regular/Poppins-Regular.ttf")
							format("truetype"),
						url("/static/fonts/Poppins-Regular/Poppins-Regular.svg")
							format("svg");
					font-weight: normal;
					font-style: normal;
				}
				@font-face {
					font-family: "Poppins-Bold";
					src: url("/static/fonts/Poppins-Bold/Poppins-Bold.eot");
					src: local("☺"),
						url("/static/fonts/Poppins-Bold/Poppins-Bold.woff")
							format("woff"),
						url("/static/fonts/Poppins-Bold/Poppins-Bold.ttf")
							format("truetype"),
						url("/static/fonts/Poppins-Bold/Poppins-Bold.svg")
							format("svg");
					font-weight: normal;
					font-style: normal;
				}
			`}</style>

			{/* Example “floating” navbar / side menu if you want to keep them */}

			{/* Now your invitation blocks in logical order */}
			<div data-scroll-container>
				<Navbar />
				<SideMenu />
				<HeroSection guest={guest} />
				<WeddingDateAndTime />
				<LocationBlock />
				<DressCodeBlock />
				<GiftBlock />
				<ChatBlock />
				<CoupleBlock />
				<InvitationFooter />
			</div>
			{/* </SmoothScrollProvider> */}
		</>
	);
});

export default InvitationPage;
