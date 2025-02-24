
import { useState, useEffect } from 'react';

export const detectActiveLink = ({ y }) => {
    const [paragraphPosition, setParagraphPosition] = useState([])

    const [activeLink, setActiveLink] = useState(0);

    // For update block position
    const [windowWidth, setWindowWidth] = useState(0);
    useEffect(() => {
        function resize() { setWindowWidth(window.innerWidth) }
        window.onresize = resize;
    }, [])

    useEffect(() => {
        const home = document.getElementById('home')
        const ourMission = document.getElementById('ourMission')
        const places = document.getElementById('places')
        const team = document.getElementById('team')
        if (team || windowWidth) {
            const paragraphs = [home, ourMission, places, team]
            const localPosition = []

            paragraphs.forEach((p, id) => {
                const offsetTop = p.offsetTop - 70

                const isFirst = id === 0 ? 0 : offsetTop
                const isLast = offsetTop + p.offsetHeight

                const newParagraphPosition = {
                    paragraphBegin: isFirst,
                    paragraphEnd: isLast,
                }
                localPosition.push(newParagraphPosition)
            });
            setParagraphPosition(localPosition)
        }
    }, [windowWidth])

    useEffect(() => {
        for (let i = 0; i < paragraphPosition.length; i++) {
            const elem = paragraphPosition[i];
            if (elem.paragraphBegin <= y && elem.paragraphEnd >= y) {
                setActiveLink(i)
            }
            if (i === paragraphPosition.length - 1 && elem.paragraphEnd < y) {
                setActiveLink(undefined)
            }
        }
    }, [y])

    return activeLink
}