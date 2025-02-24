import Head from 'next/head'
import { useState, useEffect } from 'react';
import { gsap, Power3 } from "gsap";
import { SmoothScrollProvider } from "../stores/scroll";

import style from '../styles/main.module.scss'

// Components
import { Navbar } from '../components/navbar';
import { SideMenu } from '../components/sideMenu';
import { LeafsFalling } from '../components/leafsFalling';
import { NatureNeedsYou } from '../components/Intro/natureNeedsYou';
import { MembersAndParrot } from '../components/Intro/membersAndParrot';
import { Blog } from '../components/blog';
import { FirstSlider } from '../components/firstSlider';
import { OurTeam } from '../components/ourTeam';
import { TheScaleOf } from '../components/theScaleOf';
import { GetStartedForm } from '../components/getStartedForm';
import { ReadyAccordion } from '../components/readyAccordion';
import { SecondSlider } from '../components/secondSlider';
import { Footer } from '../components/footer';

export default function Home() {
    useEffect(() => {
        gsap.fromTo('.header_block', {
            opacity: 0,
            y: -15,
        }, {
            duration: 2,
            y: 0,
            opacity: 1,
            ease: Power3.easeInOut,
            delay: 0.6,
        })
        gsap.fromTo('.header_button', {
            opacity: 0,
        }, {
            duration: 2,
            opacity: 1,
            ease: Power3.easeInOut,
            delay: 0.6,
        });
    });
    useEffect(() => {
        if (typeof window === 'undefined') return
    }, []);
    return (
        <>
            <Head>
                <title>Rootz</title>
                <link rel="icon" href="/favicon.svg" />
                <meta charSet="utf-8" />
            </Head>

            <LeafsFalling />

            <SmoothScrollProvider>
                <style global jsx>{`
                    @font-face { font-family: "Poppins-Regular"; src: url("/static/fonts/Poppins-Regular/Poppins-Regular.eot"); src: local("☺"), url("/static/fonts/Poppins-Regular/Poppins-Regular.woff") format("woff"), url("/static/fonts/Poppins-Regular/Poppins-Regular.ttf") format("truetype"), url("/static/fonts/Poppins-Regular/Poppins-Regular.svg") format("svg"); font-weight: normal; font-style: normal;  }
                    @font-face { font-family: "Poppins-Bold"; src: url("/static/fonts/Poppins-Bold/Poppins-Bold.eot"); src: local("☺"), url("/static/fonts/Poppins-Bold/Poppins-Bold.woff") format("woff"), url("/static/fonts/Poppins-Bold/Poppins-Bold.ttf") format("truetype"), url("/static/fonts/Poppins-Bold/Poppins-Bold.svg") format("svg"); font-weight: normal; font-style: normal; }
                 `}</style>

                <Navbar />
                <SideMenu />

                <div id='home' className={style.intro} data-scroll-section>
                    <NatureNeedsYou />
                    <MembersAndParrot />
                </div>
                <Blog />
                <FirstSlider />
                <OurTeam />
                <TheScaleOf />
                <GetStartedForm />
                <ReadyAccordion />
                <SecondSlider />
                <Footer />

            </SmoothScrollProvider>

        </>
    )
}
