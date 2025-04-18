import { useState, useEffect, useRef } from "react";
import { gsap, Power3 } from "gsap";
// Store
import { observer } from "mobx-react";
import { runInAction } from "mobx";
import GlobalState from "../stores/GlobalState";

import { menuData } from "../res/menuLinks";
// Helpers
import { isOnTop } from "./helpers/isOnTop";
import { detectActiveLink } from "./helpers/detectActiveLink";
// Component
import MusicPlayer from "./common/MusicPlayer";

export const Navbar = observer(({ isSideMenuOpen }) => {
	const headerBlockRef = useRef(null);
	const scrollY = GlobalState.locoScroll;
	const scroll = GlobalState.scroll;
	const { onTop } = isOnTop(scrollY);
	const activeLink = detectActiveLink({ y: scrollY });
	const [isShowMenu, setShowMenu] = useState(false);

	useEffect(() => {
		if (typeof window !== "undefined") {
			setShowMenu(true);
		}
	}, []);

	// useEffect(() => {
	// 	if (headerBlockRef.current) {
	// 		gsap.fromTo(
	// 			headerBlockRef.current,
	// 			{ opacity: 0, y: -15 },
	// 			{
	// 				duration: 2,
	// 				y: 0,
	// 				opacity: 1,
	// 				ease: Power3.easeInOut,
	// 				delay: 0.6,
	// 			}
	// 		);
	// 	}
	// }, []);

	const showSideMenu = () => {
		runInAction(() => {
			GlobalState.isSideMenuOpen = !GlobalState.isSideMenuOpen;
		});
	};

	return (
		<header
			className={`header ${onTop ? "defaultHeader" : "expandedHeader"}`}
			data-scroll-sticky
			ref={headerBlockRef}
		>
			<div className="header_block">
				<div></div>
				<nav className="nav">
					{isShowMenu &&
						menuData.map((link, id) => {
							const { linkHash, linkName } = link;
							const hash = document.querySelector(linkHash);
							return (
								<a
									key={id}
									onClick={() => scroll.scrollTo(hash)}
									className={activeLink === id ? "navlink_active" : ""}
								>
									{linkName}
								</a>
							);
						})}
				</nav>

				{/* ▶️ Music Player */}
				{/* <MusicPlayer src="/sounds/dana_glover_is_it_you.mp3" /> */}

				<button
					onClick={showSideMenu}
					type="button"
					className="header_burger_btn"
				>
					<span className={isSideMenuOpen ? "burger_btn_open" : ""} />
					<span className={isSideMenuOpen ? "burger_btn_open" : ""} />
					<span className={isSideMenuOpen ? "burger_btn_open" : ""} />
				</button>
			</div>
		</header>
	);
});
