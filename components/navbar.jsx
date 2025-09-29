// components/Navbar.jsx
import React, { useRef, useState, useEffect } from "react";
import { observer } from "mobx-react-lite";
import { runInAction } from "mobx";
import GlobalState from "../stores/GlobalState";
import { menuData } from "../res/menuLinks";
import { isOnTop } from "./helpers/isOnTop";
import { useDetectActiveLink } from "./helpers/useDetectActiveLink";
import { defaultLenisEasing } from "../hooks/useLenis";

const Navbar = observer(() => {
  const headerRef = useRef(null);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  const { locoScroll, scroll, isSideMenuOpen } = GlobalState;
  const { onTop } = isOnTop(locoScroll);
  const headerHeight = headerRef.current?.offsetHeight || 0;
  const activeLink = useDetectActiveLink(headerHeight);

  const toggleMenu = () => {
    runInAction(() => {
      GlobalState.isSideMenuOpen = !GlobalState.isSideMenuOpen;
    });
  };

  const handleNavClick = (hash) => {
    if (!scroll) return;
    scroll.scrollTo(hash, {
      offset: -headerHeight,
      duration: 1.2,
      easing: defaultLenisEasing,
    });
  };

  return (
    <header
      ref={headerRef}
      className={`header ${onTop ? "defaultHeader" : "expandedHeader"}`}
      data-scroll-ignore
    >
      <div className="header_block">
        <nav className="nav">
          {mounted &&
            menuData.map((link, idx) => (
              <a
                key={idx}
                onClick={() => handleNavClick(link.linkHash)}
                className={activeLink === idx ? "navlink_active" : ""}
              >
                {link.linkName}
              </a>
            ))}
        </nav>

        {/* burger menu */}
        <div className="burgerWrapper" data-scroll-ignore>
          <svg xmlns="http://www.w3.org/2000/svg" style={{ display: "none" }}>
            <symbol id="path" viewBox="0 0 44 44">
              <path d="M22,22 L2,22 C2,11 11,2 22,2 C33,2 42,11 42,22" />
            </symbol>
          </svg>

          <label className="toggle">
            <input
              type="checkbox"
			  checked={Boolean(isSideMenuOpen)}
              onChange={toggleMenu}
            />
            <div>
              <div><span></span><span></span></div>
              <svg><use xlinkHref="#path" /></svg>
              <svg><use xlinkHref="#path" /></svg>
            </div>
          </label>
        </div>
      </div>
    </header>
  );
});

export default Navbar;
