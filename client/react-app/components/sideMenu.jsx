import { useState, useEffect, useRef } from 'react';
import { isOnTop } from './helpers/isOnTop';
import { detectActiveLink } from './helpers/detectActiveLink';

import style from '../styles/main.module.scss';

import { menuData } from '../res/menuLinks';
import { observer } from 'mobx-react'
import GlobalState from '../stores/GlobalState'
import { runInAction } from 'mobx';

export const SideMenu = observer(() => {
  const sideMenuRef = useRef(null);

  const scrollY = GlobalState.locoScroll;
  const scroll = GlobalState.scroll;
  const { onTop } = isOnTop(scrollY);
  const activeLink = detectActiveLink({ y: scrollY });

  const [isAnimateBackground, setAnimateBackground] = useState(false);

  const isOpen = GlobalState.isSideMenuOpen
  const [isShowMenu, setShowMenu] = useState(false);
  useEffect(() => {
    if (typeof window !== 'undefined') {
      setShowMenu(true)
    }
  }, []);

  useEffect(() => {
    if (isOpen) {
      document.body.classList.add("no_scroll")
      setTimeout(() => { setAnimateBackground(true) }, 400);
    } else {
      document.body.classList.remove("no_scroll")
      setAnimateBackground(false)
    }
  }, [isOpen]);

  const showSideMenu = (bool) => {
    runInAction(() => {
      GlobalState.isSideMenuOpen = bool;
    })
  }

  const navigateTo = (hash) => {
    document.body.classList.remove("no_scroll")
    showSideMenu(false)
    scroll.scrollTo(hash)
  }

  return (
    <>
      <div className={`${style.sideMenu_bg} ${isAnimateBackground ? style.sideMenu_Bg_Open : null} `}
        onClick={() => { showSideMenu(false) }} data-scroll-section></div>
      <div className={`${style.sideMenu} ${isOpen ? style.sideMenuOpen : null} ${onTop ? style.sideMenuDefault : style.sideMenuExpand}`}>

        <nav className={style.sideMenu_nav} ref={sideMenuRef}>
          {isShowMenu &&
            menuData.map((link, id) => {
              const { linkHash, linkName } = link;
              let hash = document.querySelector(`${linkHash}`)

              return (
                <a
                  key={id}
                  onClick={() => { navigateTo(hash) }}
                  className={activeLink === id ? style.sideMenu_navlink_active : null}
                >{linkName}
                </a>
              )
            })}
        </nav>

        <button
          onClick={() => { navigateTo('#getStartedForm') }}
          type='button' className={style.sideMenu_header_button}>
          Apply
        </button>
      </div >
    </>

  );
})