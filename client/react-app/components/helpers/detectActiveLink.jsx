import { useState, useEffect } from "react";
import { menuData } from "../../res/menuLinks";

export const detectActiveLink = ({ y }) => {
	const [paragraphPosition, setParagraphPosition] = useState([]);
	const [activeLink, setActiveLink] = useState(0);
	const [windowWidth, setWindowWidth] = useState(window.innerWidth);

	// Update windowWidth on resize
	useEffect(() => {
		const handleResize = () => setWindowWidth(window.innerWidth);
		window.addEventListener("resize", handleResize);
		return () => window.removeEventListener("resize", handleResize);
	}, []);

	useEffect(() => {
		// Map over menuData and retrieve elements by id (removing '#' from linkHash)
		const paragraphs = menuData.map((item) =>
			document.getElementById(item.linkHash.slice(1))
		);
		// Ensure at least one element is available before proceeding
		if (paragraphs.length && paragraphs[0]) {
			const localPosition = paragraphs.map((p, index) => {
				const offsetTop = p.offsetTop - 70;
				// Use 0 for the first element; otherwise, use the computed offset
				const paragraphBegin = index === 0 ? 0 : offsetTop;
				const paragraphEnd = offsetTop + p.offsetHeight;
				return { paragraphBegin, paragraphEnd };
			});
			setParagraphPosition(localPosition);
		}
	}, [windowWidth]);

	useEffect(() => {
		for (let i = 0; i < paragraphPosition.length; i++) {
			const { paragraphBegin, paragraphEnd } = paragraphPosition[i];
			if (paragraphBegin <= y && paragraphEnd >= y) {
				setActiveLink(i);
				return;
			}
		}
		// If no section is currently active, set activeLink to undefined
		setActiveLink(undefined);
	}, [y, paragraphPosition]);

	return activeLink;
};
