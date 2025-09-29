// components/SideMenu.jsx
import React, { useState, useEffect, useRef } from "react";
import { observer } from "mobx-react-lite";
import { runInAction } from "mobx";
import GlobalState from "../stores/GlobalState";
import { menuData } from "../res/menuLinks";
import { isOnTop } from "./helpers/isOnTop";
import { useDetectActiveLink } from "./helpers/useDetectActiveLink";
import { defaultLenisEasing } from "../hooks/useLenis";

const SideMenu = observer(() => {
	const sideMenuRef = useRef(null);

	// mounted flag to guard any DOM usage
	const [mounted, setMounted] = useState(false);
	useEffect(() => {
		setMounted(true);
	}, []);

	// measure header height once on client
	const [headerHeight, setHeaderHeight] = useState(0);
	useEffect(() => {
		if (!mounted) return;
		const h = document.querySelector("header")?.offsetHeight || 0;
		setHeaderHeight(h);
	}, [mounted]);

	// pull scroll data & menu state from MobX
	const { locoScroll, scroll, isSideMenuOpen } = GlobalState;
	const { onTop } = isOnTop(locoScroll);

	// which link is active?
	const activeLink = useDetectActiveLink(headerHeight);

	// backdrop fade state
	const [bgOpen, setBgOpen] = useState(false);
	useEffect(() => {
		if (!mounted) return;
		if (isSideMenuOpen) {
			document.body.classList.add("no_scroll");
			setTimeout(() => setBgOpen(true), 400);
		} else {
			document.body.classList.remove("no_scroll");
			setBgOpen(false);
		}
	}, [isSideMenuOpen, mounted]);

	// open/close action
	const toggleMenu = (open) => {
		runInAction(() => {
			GlobalState.isSideMenuOpen = open;
		});
	};

	// navigate + smooth scroll
	const navigateTo = (hash) => {
		toggleMenu(false);
		if (mounted) document.body.classList.remove("no_scroll");
		if (!scroll) return;
		scroll.scrollTo(hash, {
			offset: -headerHeight,
			duration: 1.0,
			// either drop this entirely to use your default easing:
			easing: defaultLenisEasing,
		});
	};

	return (
		<>
			<div
				className={`sideMenu_bg ${bgOpen ? "sideMenu_Bg_Open" : ""}`}
				onClick={() => toggleMenu(false)}
			/>

			<div
				ref={sideMenuRef}
				className={`sideMenu ${isSideMenuOpen ? "sideMenuOpen" : ""} ${
					onTop ? "sideMenuDefault" : "sideMenuExpand"
				}`}
			>
				<nav className="sideMenu_nav">
					{mounted &&
						menuData.map(({ linkHash, linkName }, idx) => (
							<a
								key={idx}
								onClick={() => navigateTo(linkHash)}
								className={
									activeLink === idx ? "sideMenu_navlink_active" : ""
								}
							>
								{linkName}
							</a>
						))}
				</nav>
			</div>
		</>
	);
});

export default SideMenu;
