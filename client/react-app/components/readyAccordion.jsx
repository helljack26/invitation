import { useState, useEffect } from 'react'

import AnimateHeight from 'react-animate-height';

import style from '../styles/main.module.scss'

const accordionData = [
	{
		accordionTitle: 'What can I do to protect our planet?',
		accordionText: 'Not to make an open fire in nature and to clean up litter; not to pollute open water bodies; to switch to alternative energy sources; to reduce the use of non-renewable resources',
	},
	{
		accordionTitle: 'How to save nature ecology?',
		accordionText: 'Open water bodies; to switch to alternative energy sources; to reduce the use of non-renewable resources. Not to make an open fire in nature and to clean up litter; not to pollute.',
	},
	{
		accordionTitle: 'What is nature conservation?',
		accordionText: 'Not to make an open not to pollute open water bodies; to switch to alternative energy sources; to reduce the use of non-renewable resources',
	},
];

const AccordionItem = ({ item, isShowDescription, setActiveIndex, index }) => {
	const [height, setHeight] = useState(0);
	const [isRotatePlus, setRotatePlus] = useState(false);

	useEffect(() => {
		if (!isShowDescription) {
			setHeight(0)
			setRotatePlus(true)
		}
	}, [isShowDescription]);

	useEffect(() => {
		if (index === 0) { setHeight('auto') }
	}, []);

	return (
		<div className={style.accordion_item} >
			<button
				className={style.accordion_item_header}
				aria-expanded={height !== 0}
				aria-controls="example-panel"
				onClick={() => { return (setHeight(height === 0 ? 'auto' : 0), setActiveIndex(index)), setRotatePlus(!isRotatePlus) }}
			>
				<span>
					{item.accordionTitle}
				</span>

				<span className={style.accordion_item_button}
					data-scroll
					data-scroll-delay={1}
					data-scroll-position="top"
					data-scroll-direction="vertical"
				>
					<span className={` ${!isRotatePlus && style.rotate180}`}></span>
					<span className={`${!isRotatePlus && style.rotate0} ${isRotatePlus && style.rotate90}`}></span>
				</span>

			</button>

			<AnimateHeight id="example-panel" duration={500} height={height} >
				<p className={`${style.accordion_item_collapsible}`}>
					{item.accordionText}
				</p>
			</AnimateHeight>
		</div >
	)
}

export const ReadyAccordion = () => {
	const [activeIndex, setActiveIndex] = useState(0);

	return (
		<div data-scroll-section>
			<div className={style.readyAccordion}
				data-scroll
				data-scroll-speed={0.2}
				data-scroll-delay={1}
				data-scroll-position="top"
				data-scroll-direction="vertical"
			>
				<div className={style.readyAccordion_col}>
					<h2 >
						Ready To Get Started?
					</h2>
					<p >
						When pattern is mentioned in interior design, it is easy to associate it with a geometric patterned wallpaper or colourful prints on interior fabrics.
					</p>
					<button type="button">
						Contact us
					</button>
				</div>

				<div className={style.readyAccordion_block} >
					{accordionData.map((item, index) => {
						const isShowDescription = index === activeIndex

						return (
							<AccordionItem
								key={index}
								index={index}
								item={item}
								isShowDescription={isShowDescription}
								setActiveIndex={setActiveIndex}
							/>
						);
					})}
				</div>
			</div>
		</div>
	)
}

