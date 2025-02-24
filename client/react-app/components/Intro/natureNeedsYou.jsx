import { useEffect, useRef } from "react";
import Image from 'next/image'
import { gsap, Power3 } from "gsap";

import style from '../../styles/main.module.scss';
import I from '../../img/images';

export const NatureNeedsYou = () => {
    const natureNeedsYou_container = useRef(null)
    const natureNeedsYou_img = useRef(null)
    const natureNeedsYou_text = useRef(null)
    const search_container = useRef(null)
    const search_input = useRef(null)
    const search_button = useRef(null)

    useEffect(() => {
        if (
            !natureNeedsYou_container.current ||
            !natureNeedsYou_img.current ||
            !natureNeedsYou_text.current ||
            !search_container.current ||
            !search_input.current ||
            !search_button.current
        ) return
        gsap.fromTo(natureNeedsYou_container.current, {
            opacity: 0,
        }, {
            duration: 4,
            delay: 0.6,
            opacity: 1,
            ease: Power3.easeInOut
        })
        gsap.fromTo(natureNeedsYou_img.current, {
            y: 20,
            opacity: 0,
        }, {
            duration: 4,
            delay: 0.6,
            opacity: 1,
            y: 0,
            ease: Power3.easeInOut
        })
        gsap.fromTo(natureNeedsYou_text.current, {
            y: 20,
            opacity: 0,
        }, {
            duration: 4,
            delay: 0.6,
            opacity: 1,
            y: 0,
            ease: Power3.easeInOut
        })
        gsap.fromTo(search_container.current, {
            y: 10,
            opacity: 0,
        }, {
            duration: 3,
            opacity: 1,
            y: 0,
            ease: Power3.easeInOut
        })
        gsap.fromTo(search_input.current, {
            opacity: 0,
        }, {
            duration: 3,
            opacity: 1,
            ease: Power3.easeInOut
        })
        gsap.fromTo(search_button.current, {
            opacity: 0,
        }, {
            duration: 3,
            opacity: 1,
            ease: Power3.easeInOut
        })
    });

    return (
        <div
            className={style.natureNeedsYou_container}
            data-scroll
            data-scroll-speed="-1"
            data-scroll-position="top"
            data-scroll-direction="vertical"
            ref={natureNeedsYou_container}
        >
            <div className={style.natureNeedsYou_img} ref={natureNeedsYou_img} >
                <Image src={I.natureNeedsYou} alt="Nature needs you calligraphy" />
            </div>

            <p className={style.natureNeedsYou_text} ref={natureNeedsYou_text}>
                The scale of the challenges facing our planet can
                seem daunting, but we can all do something.
            </p>

            <div className={style.search_container}
                ref={search_container}
            >
                <span className={style.search_icon}>
                    <Image src={I.locationIcon} width={18} height={22} />
                </span>

                <input
                    type="text"
                    placeholder='Find the place to help'
                    className={style.search_input}
                    ref={search_input}

                />

                <button type='button' className={style.search_button}
                    ref={search_button}
                >
                    Search
                </button>
            </div>
        </div>
    );
};
