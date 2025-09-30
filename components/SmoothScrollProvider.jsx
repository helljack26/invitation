// components/SmoothScrollProvider.jsx
import React, { useRef, useLayoutEffect } from 'react';
import GlobalState from '../stores/GlobalState';

let instance = null;

export default function SmoothScrollProvider({ children }) {
  const containerRef = useRef(null);
  const didInit      = useRef(false);

  useLayoutEffect(() => {
    // 1) Don’t run on the server, and only once:
    if (typeof window === 'undefined' || didInit.current) return;
    didInit.current = true;

    let loco = null;
    import('locomotive-scroll').then((mod) => {
      const LocomotiveScroll = mod.default;
      loco = new LocomotiveScroll({
        el: containerRef.current,
        smooth: true,
        lerp: 0.1,
        smartphone: { smooth: false },
        tablet:     { smooth: false, breakpoint: 1200 },
      });
      instance = loco;

      loco.on('scroll', ({ scroll }) => {
        GlobalState.setScroll(scroll.y, loco);
      });
    });

    // 2) Clean up if unmounted (so you don’t leak DOM / listeners)
    return () => {
      if (loco) {
        loco.destroy();
        instance = null;
      }
    };
  }, []);

  return (
    <main data-scroll-container ref={containerRef}>
      {children}
    </main>
  );
}
