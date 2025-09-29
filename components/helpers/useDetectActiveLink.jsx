// components/helpers/useDetectActiveLink.jsx
import { useState, useEffect } from 'react';
import { menuData }      from '../../res/menuLinks';
import GlobalState       from '../../stores/GlobalState';

/**
 * Returns the index of the menuData section
 * that currently contains the Lenis scroll position + headerOffset.
 * @param {number} headerOffset height in px of your fixed header
 */
export function useDetectActiveLink(headerOffset = 0) {
  const [activeLink, setActiveLink] = useState(undefined);

  useEffect(() => {
    const lenis = GlobalState.scroll;
    if (!lenis) return;

    // handler to run on every Lenis scroll
    const updateActive = () => {
      const y = lenis.scroll; // current scroll Y in px
      // grab the DOM nodes for each section
      const sections = menuData
        .map(({ linkHash }) => document.querySelector(linkHash))
        .filter(Boolean);

      // find first section where scrollY sits between its start/end
      const idx = sections.findIndex(el => {
        const start = el.offsetTop - headerOffset;
        const end   = start + el.offsetHeight;
        return y >= start && y < end;
      });

      setActiveLink(idx >= 0 ? idx : undefined);
    };

    // initial calc + subscribe
    updateActive();
    lenis.on('scroll', updateActive);

    // cleanup on unmount
    return () => {
      lenis.off('scroll', updateActive);
    };
  }, [headerOffset]);

  return activeLink;
}
