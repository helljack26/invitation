import { useEffect, useState, useRef } from 'react'
import Image from 'next/image'

import I from '../img/images'
import style from '../styles/main.module.scss';

import Slider from "react-slick";
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import { secondSliderSettings } from "../res/slider_config";

const firstSliderData = [
  {
    slideImage: I.oceanSlide,
    slideTitle: 'Save water',
    slideText: 'Lorem, ipsum dolor sit amet consectetur adipisicing elit. Aut, repellat!',
  },
  {
    slideImage: I.treesSlide,
    slideTitle: 'Plant trees',
    slideText: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Eius, cupiditate cumque.',
  },
  {
    slideImage: I.windmillSlide,
    slideTitle: 'Save energy',
    slideText: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Esse, eum debitis! Maxime.',
  },
  {
    slideImage: I.oceanSlide,
    slideTitle: 'Save water',
    slideText: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate, inventore.',
  },
  {
    slideImage: I.waterSlide,
    slideTitle: 'Avoid plastic',
    slideText: 'Taking on the issue of ensuring access to safe water requires worldwide effort.',
  },
  {
    slideImage: I.plantSlide,
    slideTitle: 'Choose organic',
    slideText: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Optio distinctio ratione explicabo!',
  },
];

export const SecondSlider = () => {
  const [sliderData, setState] = useState([])
  const [currentPosition, setCurrentPosition] = useState(1)

  useEffect(() => {
    if (currentPosition > sliderData.length) {
      setCurrentPosition(1)
    }
    if (currentPosition === 0) {
      setCurrentPosition(sliderData.length)
    }
  }, [currentPosition]);

  const sliderRef = useRef(null)
  const sliderSettings = {
    afterChange: current => setCurrentPosition(current + 1),
    ...secondSliderSettings,
  }

  const maskClasses = [style.secondSlider_imageBg_1, style.secondSlider_imageBg_2, style.secondSlider_imageBg_3, style.secondSlider_imageBg_4, style.secondSlider_imageBg_5, style.secondSlider_imageBg_3, style.secondSlider_imageBg_2, style.secondSlider_imageBg_1, style.secondSlider_imageBg_5, style.secondSlider_imageBg_4]

  const isMoreThanOneSlide = sliderData && sliderData.length > 1

  if (sliderData.length === 0) {
    setState(firstSliderData)
  }

  return (
    <div data-scroll-section>
      <section className={style.secondSlider}
        data-scroll
        data-scroll-delay={1}
        data-scroll-speed={0.2}
        data-scroll-position="top"
        data-scroll-direction="vertical"
      >
        <div className={style.secondSlider_block}>

          <Slider
            ref={sliderRef}
            {...sliderSettings}>
            {sliderData &&
              sliderData.map((c, id) => {
                const isCenter = currentPosition === id + 1

                return (
                  <div key={id}>
                    <div className={style.secondSlider_item}  >
                      <div className={`${style.secondSlider_centeredBg} ${isCenter ? style.bigCenteredBg : style.smallCenteredBg}`}></div>

                      <div className={`${maskClasses[id]} ${isCenter ? style.shiftToTopImg : style.shiftCenteredImg}`}>
                        <Image src={c.slideImage} layout='fill' priority />
                      </div>

                      <div className={style.secondSlider_item_col}>
                        <h2 className={style.secondSlider_item_col_title}
                          style={{ color: !isCenter ? 'white' : '#1E1A20' }}>
                          {c.slideTitle}
                        </h2>
                        <p className={`${style.secondSlider_item_col_text} ${isCenter ? style.bigCenteredBg : style.smallCenteredBg}`}
                          style={{ opacity: isCenter ? 1 : 0, }}	>
                          {c.slideText}
                        </p>
                      </div>
                    </div>
                  </div>
                )
              })}
          </Slider>

        </div>

        {isMoreThanOneSlide &&

          <div className={style.secondSlider_controls}>
            <button
              className={style.slider_controls_left}
              onClick={() => { (sliderRef.current).slickPrev(); }}>
              <Image src={I.sliderArrow} width={9} height={16} />
            </button>

            <div className={style.secondSlider_position}>
              <span>{currentPosition}</span><span>/ {sliderData.length}</span>
            </div>

            <button
              className={style.slider_controls_right}
              onClick={() => { (sliderRef.current).slickNext(); }}>
              <Image src={I.sliderArrow} width={9} height={16} />
            </button>
          </div>
        }
      </section >
    </div>
  )
}

