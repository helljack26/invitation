import Image from "next/image";
import { useState, useEffect } from "react";

import { isOnTop } from "./helpers/isOnTop";
import { detectActiveLink } from "./helpers/detectActiveLink";
import { menuData } from "../res/menuLinks";

import I from "../img/images";
import GlobalState from "../stores/GlobalState";
import { observer } from "mobx-react";
import { runInAction } from "mobx";

export const Navbar = observer(({ isSideMenuOpen }) => {
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
			<div className="header_block">
				<button
					className="logoButton"
					type="button"
					onMouseEnter={showLeafFalling}
				>
					<Image
						className="logo"
						src={I.logo}
						alt="Site logo"
					/>
				</button>

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
					type="button"
					className="header_button"
					onClick={() => {
						scroll.scrollTo("#getStartedForm");
					}}
					style={{ transform: scrollY > 10 ? "scale(0.75)" : "scale(1)" }}
				>
					Apply
				</button>

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
