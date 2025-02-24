import { useEffect, useState, useRef } from "react";
import Image from 'next/image'
import { gsap, Power3 } from "gsap";
import CountUp from 'react-countup';
import { ScrollTrigger } from "gsap/dist/ScrollTrigger";

import { isTouch } from '../../res/info';
import style from '../../styles/main.module.scss';
import I from '../../img/images'

export const MembersAndParrot = () => {
    const members = 29128;
    useEffect(() => {
        if (typeof window === 'undefined') return
    }, []);
    gsap.registerPlugin(ScrollTrigger);
    const membersAndParrot_container = useRef(null)
    const parrot_block = useRef(null)
    const members_container = useRef(null)
    const membersAndParrot_leafs1 = useRef(null)
    const membersAndParrot_leafs2 = useRef(null)
    const membersAndParrot_leafs3 = useRef(null)
    const membersAndParrot_leafs4 = useRef(null)
    const membersAndParrot_leafs5 = useRef(null)

    const isMobile = isTouch()
    useEffect(() => {
        setTimeout(() => {
            if (
                !membersAndParrot_container.current ||
                !parrot_block.current ||
                !membersAndParrot_leafs1.current ||
                !membersAndParrot_leafs2.current ||
                !membersAndParrot_leafs3.current ||
                !membersAndParrot_leafs4.current ||
                !membersAndParrot_leafs5.current ||
                !members_container.current
            ) return

            if (!isMobile) {
                gsap.from(membersAndParrot_leafs1.current, {
                    opacity: 0,
                    duration: 3,
                    delay: 1,
                    rotate: 90,
                    y: 70,
                    x: -70,
                    ease: Power3.easeInOut
                })
                gsap.from(membersAndParrot_leafs2.current, {
                    opacity: 0,
                    duration: 3.5,
                    delay: 1,
                    rotate: 90,
                    y: 20,
                    x: -70,
                    ease: Power3.easeInOut
                })
                gsap.from(membersAndParrot_leafs3.current, {
                    opacity: 0,
                    duration: 3.2,
                    delay: 1,
                    rotate: 120,
                    y: 20,
                    x: -70,
                    ease: Power3.easeInOut
                })
                gsap.from(membersAndParrot_leafs4.current, {
                    opacity: 0,
                    duration: 3.7,
                    delay: 1,
                    rotate: 90,
                    y: -20,
                    x: -70,
                    ease: Power3.easeInOut
                })
                gsap.from(membersAndParrot_leafs5.current, {
                    opacity: 0,
                    duration: 3,
                    delay: 1,
                    rotate: 90,
                    y: -50,
                    x: -70,
                    ease: Power3.easeInOut
                })
                gsap.fromTo(members_container.current, {
                    opacity: 0,
                    y: 40,
                    x: -60,
                }, {
                    duration: 4,
                    delay: 0.6,
                    y: 0,
                    x: 0,
                    opacity: 1,
                    ease: Power3.easeInOut
                })
                gsap.fromTo(parrot_block.current, {
                    opacity: 0,
                    y: 40,
                    x: -60,
                }, {
                    duration: 4,
                    y: 0,
                    delay: 0.6,
                    x: 0,
                    opacity: 1,
                    ease: Power3.easeInOut
                })
            }
        })
    });

    return (
        <div className={style.membersAndParrot_container}
            ref={membersAndParrot_container}
        >

            <div className={style.membersAndParrot_leafs_block}
                data-scroll
                data-scroll-speed="2"
                data-scroll-position="top"
                data-scroll-direction="vertical"
            >
                <div className={style.membersAndParrot_leafs_block_container} >
                    <div
                        data-scroll
                        data-scroll-speed="5"
                        data-scroll-position="top"
                        data-scroll-direction="horizontal"
                        className={style.membersAndParrot_leafs1} ref={membersAndParrot_leafs1}>
                        <Image src={I.leafs1} />
                    </div>
                    <div
                        data-scroll
                        data-scroll-speed="3"
                        data-scroll-position="top"
                        data-scroll-direction="horizontal"
                        className={style.membersAndParrot_leafs2} ref={membersAndParrot_leafs2}>
                        <Image src={I.leafs2} />
                    </div>
                    <div
                        data-scroll
                        data-scroll-speed="4"
                        data-scroll-position="left"
                        data-scroll-direction="horizontal"
                        className={style.membersAndParrot_leafs3} ref={membersAndParrot_leafs3}>
                        <Image src={I.leafs3} />
                    </div>
                    <div
                        data-scroll
                        data-scroll-speed="2"
                        data-scroll-position="top"
                        data-scroll-direction="horizontal"
                        className={style.membersAndParrot_leafs4} ref={membersAndParrot_leafs4}>
                        <Image src={I.leafs4} />
                    </div >
                    <div
                        data-scroll
                        data-scroll-speed="3"
                        data-scroll-position="top"
                        data-scroll-direction="horizontal"
                        className={style.membersAndParrot_leafs5} ref={membersAndParrot_leafs5}>
                        <Image src={I.leafs5} />
                    </div>
                </div >
            </div >

            <div
                data-scroll
                data-scroll-speed="2"
                data-scroll-position="top"
                data-scroll-direction="horizontal"
                className={style.membersAndParrot_parrot_block} ref={parrot_block} >
                <Image
                    className={style.membersAndParrot_parrot} src={I.parrot} />
            </div>


            <div className={style.members_container}
                ref={members_container}
                data-scroll
                data-scroll-speed="-1"
                data-scroll-position="top"
                data-scroll-direction="vertical"
            >

                <div className={style.members_block_icon} >
                    <Image src={I.membersIcon} />
                </div>

                <div className={style.members_block_info}>

                    <span className={style.members_block_info_text}>
                        Members
                    </span>
                    <CountUp start={0} end={members} delay={0} separator=" " duration={5}
                        decimals={0}>
                        {({ countUpRef }) => (
                            <span ref={countUpRef} className={style.members_block_info_numbers}>
                            </span>
                        )}
                    </CountUp>
                </div>
            </div>
        </div >
    );
};
