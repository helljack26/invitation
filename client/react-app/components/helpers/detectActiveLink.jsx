// components/helpers/detectActiveLink.jsx
import { useState, useEffect } from "react";
import { menuData } from "../../res/menuLinks";

export const detectActiveLink = ({ y }) => {
	const [paragraphPosition, setParagraphPosition] = useState([]);
	const [activeLink, setActiveLink] = useState(0);
	const [windowWidth, setWindowWidth] = useState(0);

	// 1) Initialize & update windowWidth only on the client
	useEffect(() => {
		if (typeof window === "undefined") return; // bail on server

		const handleResize = () => {
			setWindowWidth(window.innerWidth);
		};

		// set initial width & listen for changes
		handleResize();
		window.addEventListener("resize", handleResize);
		return () => window.removeEventListener("resize", handleResize);
	}, []);

	// 2) When windowWidth changes, recalc paragraph positions
	useEffect(() => {
		// find each section’s top & bottom
		const paragraphs = menuData
			.map((item) => document.getElementById(item.linkHash.slice(1)))
			.filter(Boolean);

		const positions = paragraphs.map((el, i) => {
			const offsetTop = el.offsetTop - 70;
			return {
				paragraphBegin: i === 0 ? 0 : offsetTop,
				paragraphEnd: offsetTop + el.offsetHeight,
			};
		});

		setParagraphPosition(positions);
	}, [windowWidth]);

	// 3) On scroll y-change, pick the active section
	useEffect(() => {
		for (let i = 0; i < paragraphPosition.length; i++) {
			const { paragraphBegin, paragraphEnd } = paragraphPosition[i];
			if (y >= paragraphBegin && y <= paragraphEnd) {
				setActiveLink(i);
				return;
			}
		}
		// none matched
		setActiveLink(undefined);
	}, [y, paragraphPosition]);

	return activeLink;
};
