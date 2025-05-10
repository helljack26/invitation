"use client";

import { useEffect, useRef } from "react";
import LoaderHeart from "../img/loader_heart.svg";
import LoaderText from "../img/loader_text.svg";

export default function Loader() {
  const loaderRef         = useRef(null);
  const heartWrapperRef   = useRef(null);
  const heartRef   = useRef(null);
  const textRef           = useRef(null);

  useEffect(() => {
    let ctx;

    async function init() {
      const { gsap }           = await import("gsap");
      const { DrawSVGPlugin }  = await import("gsap/dist/DrawSVGPlugin");
      gsap.registerPlugin(DrawSVGPlugin);

      const heartWrapperEl = heartWrapperRef.current;
      const heartEl = heartRef.current;
      const textEl         = textRef.current;
      const paths          = textEl.querySelectorAll("path");

      paths.forEach((p) => {
        const len = p.getTotalLength();
        p.style.strokeDasharray  = len;
        p.style.strokeDashoffset = len;
      });

	  ctx = gsap.context(() => {
		// initial states
		gsap.set(heartWrapperEl, {
		  opacity:         0,
		  scale:           0.8,
		  transformOrigin: "center center",
		});
		gsap.set(textEl, { opacity: 0 });
	  
		const tl = gsap.timeline();
	  
		// 1) fade in heart + text
		tl.to(heartWrapperEl, {
		  opacity:  1,
		  scale:    1,
		  duration: 1,
		  ease:     "power2.out",
		});
		tl.to(textEl, { opacity: 1, duration: 0.8 }, "<");
	  
		// 2) two fast beats
		tl.to(heartEl, {
		  scale:    1.15,
		  duration: 0.5,
		  ease:     "power1.inOut",
		  yoyo:     true,
		  repeat:   1,      // 2 pulses total
		});
	  
	  
		// 4) two more fast beats
		tl.to(heartEl, {
		  scale:    1.15,
		  duration: 0.5,
		  ease:     "power1.inOut",
		  yoyo:     true,
		  repeat:   1,
		  delay: 0.5
		});
	  
		// 5) after a short hold, blow up + fade out
		tl.to(heartEl, {
		  delay:    0.5,
		  scale:    12,
		  opacity:  0,
		  duration: 1.5,
		  ease:     "power2.in",
		});
		tl.to(loaderRef.current, {
		  opacity:       0,
		  duration:      1.5,
		  pointerEvents: "none",
		}, "-=0.5");
	  
		// keep your text draw-on loop running in parallel…
		gsap.to(paths, {
		  strokeDashoffset: 1,
		  duration:         5,
		  ease:             "power3.out",
		  stagger:          0.1,
	
		});
	  }, loaderRef);
	  
    }

    init();
    return () => ctx && ctx.revert();
  }, []);

  return (
    <div ref={loaderRef} className="loader">
      <div className="loader__heart-wrapper" ref={heartWrapperRef}>
        <LoaderHeart className="loader__heart"  ref={heartRef}/>
        <div ref={textRef} className="loader__text-container">
          <LoaderText className="loader__text" width={220} height={'100%'}/>
        </div>
      </div>
    </div>
  );
}
