// hooks/useLenis.js
import { useEffect, useRef } from "react";
import Lenis from "@studio-freight/lenis";
import GlobalState from "../stores/GlobalState";

export const defaultLenisEasing = (t) =>
	Math.min(1, 1.001 - Math.pow(2, -10 * t));

export default function useLenis() {
	const lenisRef = useRef(null);

	useEffect(() => {
		if (typeof window === "undefined") return;

		// 1) Create Lenis with a soft ease-out curve
		const lenis = new Lenis({
			duration: 1.0, // a bit slower so the scroll “floats” to a stop
			easing: defaultLenisEasing,
			smoothWheel: true,
			smoothTouch: false,
			wheelMultiplier: 0.8, // dial back for finer control
		});
		lenisRef.current = lenis;

		// 2) Seed MobX so your Nav/SideMenu still see scrollTo
		GlobalState.setScroll(0, lenis);

		// 3) RAF loop
		const loop = (time) => {
			lenis.raf(time);
			GlobalState.setScroll(lenis.scroll, lenis);
			requestAnimationFrame(loop);
		};
		requestAnimationFrame(loop);

		// 4) Cleanup
		return () => {
			lenis.destroy();
			lenisRef.current = null;
		};
	}, []);
}
