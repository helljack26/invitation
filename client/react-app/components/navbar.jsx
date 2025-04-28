import React, { useState, useEffect, useRef } from "react";
import { observer } from "mobx-react";
import { runInAction } from "mobx";
import GlobalState from "../stores/GlobalState";
import { menuData } from "../res/menuLinks";
import { isOnTop } from "./helpers/isOnTop";
import { detectActiveLink } from "./helpers/detectActiveLink";

export const Navbar = observer(() => {
	const headerBlockRef = useRef(null);
	const scrollY = GlobalState.locoScroll;
	const scroll = GlobalState.scroll;
	const { onTop } = isOnTop(scrollY);
	const activeLink = detectActiveLink({ y: scrollY });

	// wrapper state comes straight from MobX…
	const isOpen = GlobalState.isSideMenuOpen;
	const showSideMenu = () => {
		runInAction(() => {
			GlobalState.isSideMenuOpen = !GlobalState.isSideMenuOpen;
		});
	};

	// only show after mount
	const [mounted, setMounted] = useState(false);
	useEffect(() => {
		setMounted(true);
	}, []);

	return (
		<header
			className={`header ${onTop ? "defaultHeader" : "expandedHeader"}`}
			data-scroll-sticky
			ref={headerBlockRef}
		>
			<div className="header_block">
				<nav className="nav">
					{mounted &&
						menuData.map((link, idx) => {
							const target = document.querySelector(link.linkHash);
							return (
								<a
									key={idx}
									onClick={() => scroll.scrollTo(target)}
									className={
										activeLink === idx ? "navlink_active" : ""
									}
								>
									{link.linkName}
								</a>
							);
						})}
				</nav>

				{/* ▶️ Music Player */}
				{/* <MusicPlayer src="/sounds/...mp3" /> */}

				{/* ↓ our new little wrapper ↓ */}
				<div className="burgerWrapper">
					{/* 1️⃣ inline the sprite symbol once */}
					<svg
						xmlns="http://www.w3.org/2000/svg"
						style={{ display: "none" }}
					>
						<symbol
							id="path"
							viewBox="0 0 44 44"
						>
							<path d="M22,22 L2,22 C2,11 11,2 22,2 C33,2 42,11 42,22" />
						</symbol>
					</svg>

					{/* 2️⃣ the toggle label that drives the animation */}
					<label className="toggle">
						<input
							type="checkbox"
							checked={isOpen}
							onChange={showSideMenu}
						/>
						<div>
							<div>
								<span></span>
								<span></span>
							</div>
							<svg>
								<use xlinkHref="#path" />
							</svg>
							<svg>
								<use xlinkHref="#path" />
							</svg>
						</div>
					</label>
				</div>
				{/* ↑ end wrapper ↑ */}
			</div>
		</header>
	);
});
