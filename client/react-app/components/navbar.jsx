import Image from 'next/image';
import { useState, useEffect } from 'react';

import style from '../styles/main.module.scss';
import { isOnTop } from './helpers/isOnTop';
import { detectActiveLink } from './helpers/detectActiveLink';
import { menuData } from '../res/menuLinks';

import I from '../img/images'
import GlobalState from '../stores/GlobalState'
import { observer } from 'mobx-react'
import { runInAction } from 'mobx';

export const Navbar = observer(({ isSideMenuOpen }) => {
    const scrollY = GlobalState.locoScroll;
    const scroll = GlobalState.scroll;
    const { onTop } = isOnTop(scrollY);
    const activeLink = detectActiveLink({ y: scrollY });
    const [isShowMenu, setShowMenu] = useState(false);

    useEffect(() => {
        if (typeof window !== 'undefined') {
            setShowMenu(true)
        }
    }, []);

    const showLeafFalling = () => {
        runInAction(() => {
            GlobalState.isShowLeafFalling = !GlobalState.isShowLeafFalling;
        })
    }
    const showSideMenu = () => {
        runInAction(() => {
            GlobalState.isSideMenuOpen = !GlobalState.isSideMenuOpen;
        })
    }

    return (
        <header
            className={`${style.header} ${onTop ? style.defaultHeader : style.expandedHeader}`}
            data-scroll-sticky>
            <div className={`${style.header_block} header_block`}>
                <button
                    className={style.logoButton}
                    type='button'
                    onMouseEnter={() => showLeafFalling()}
                >
                    <Image
                        className={style.logo}
                        src={I.logo}
                        alt="Site logo"
                    />
                </button>

                <nav className={style.nav}>
                    {isShowMenu &&
                        menuData.map((link, id) => {
                            const { linkHash, linkName } = link;
                            let hash = document.querySelector(`${linkHash}`)
                            return (
                                <a key={id} onClick={() => { scroll.scrollTo(hash) }}
                                    className={activeLink === id ? style.navlink_active : ''}>
                                    {linkName}
                                </a>
                            )
                        })
                    }
                </nav>

                <button type='button' className={`${style.header_button} header_button`}
                    onClick={() => { scroll.scrollTo('#getStartedForm') }}
                    style={{ transform: scrollY > 10 ? 'scale(0.75)' : 'scale(1)' }}
                >
                    Apply
                </button>

                <button
                    onClick={() => { showSideMenu() }}
                    type='button' className={style.header_burgerBtn}>
                    <span className={isSideMenuOpen ? style.burgerBtn_open : null}></span>
                    <span className={isSideMenuOpen ? style.burgerBtn_open : null}></span>
                    <span className={isSideMenuOpen ? style.burgerBtn_open : null}></span>
                </button >
            </div >

        </header >
    );
})