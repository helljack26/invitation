import Image from "next/image";
import { useState, useEffect, useRef } from "react";

import { isOnTop } from "./helpers/isOnTop";
import { detectActiveLink } from "./helpers/detectActiveLink";
import { menuData } from "../res/menuLinks";

import I from "../img/images";
import GlobalState from "../stores/GlobalState";
import { observer } from "mobx-react";
import { runInAction } from "mobx";
import { gsap, Power3 } from "gsap";

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

	//
	useEffect(() => {
		if (headerBlockRef.current) {
			gsap.fromTo(
				headerBlockRef.current,
				{ opacity: 0, y: -15 },
				{
					duration: 2,
					y: 0,
					opacity: 1,
					ease: Power3.easeInOut,
					delay: 0.6,
				}
			);
		}
	}, []);
	const showLeafFalling = () => {
		runInAction(() => {
			GlobalState.isShowLeafFalling = !GlobalState.isShowLeafFalling;
		});
	};

	const showSideMenu = () => {
		runInAction(() => {
			GlobalState.isSideMenuOpen = !GlobalState.isSideMenuOpen;
		});
	};

	return (
		<header
			className={`header ${onTop ? "defaultHeader" : "expandedHeader"}`}
			data-scroll-sticky
		>
			<div
				className="header_block"
				ref={headerBlockRef}
			>
				<div></div>
				<nav className="nav">
					{isShowMenu &&
						menuData.map((link, id) => {
							const { linkHash, linkName } = link;
							let hash = document.querySelector(`${linkHash}`);
							return (
								<a
									key={id}
									onClick={() => {
										scroll.scrollTo(hash);
									}}
									className={activeLink === id ? "navlink_active" : ""}
								>
									{linkName}
								</a>
							);
						})}
				</nav>

				<button
					onClick={showSideMenu}
					type="button"
					className="header_burgerBtn"
				>
					<span className={isSideMenuOpen ? "burgerBtn_open" : ""}></span>
					<span className={isSideMenuOpen ? "burgerBtn_open" : ""}></span>
					<span className={isSideMenuOpen ? "burgerBtn_open" : ""}></span>
				</button>
			</div>
		</header>
	);
});
