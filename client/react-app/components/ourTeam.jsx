import Image from 'next/image'
import Link from 'next/link'

import style from '../styles/main.module.scss';

import I from '../img/images'

const teamData = [
    {
        teamId: 1,
        teamImg: I.team1,
        teamMask: I.teamMask1,
    },
    {
        teamId: 2,
        teamImg: I.team2,
        teamMask: I.teamMask2,
    },
    {
        teamId: 3,
        teamImg: I.team3,
        teamMask: I.teamMask3,
    },
]
export const OurTeam = () => {

    return (
        <div id='team' className={style.ourTeam} data-scroll-section>
            <h2
                data-scroll
                data-scroll-delay={1}
                data-scroll-speed={0.2}
                data-scroll-position="top"
                data-scroll-direction="vertical"
                className={style.ourTeam_title}>Our Top Team</h2>
            <p
                data-scroll
                data-scroll-delay={1}
                data-scroll-speed={0.2}
                data-scroll-position="top"
                data-scroll-direction="vertical"
                className={style.ourTeam_text}>Learn more about how you can save our planet's nature. </p>

            <div className={style.ourTeam_row}
                data-scroll
                data-scroll-delay={1}
                data-scroll-speed={0.2}
                data-scroll-position="top"
                data-scroll-direction="vertical"
            >
                <div className={style.ourTeam_row_block}>

                    {teamData.map((teammate, id) => {
                        return (
                            <span className={style.ourTeam_row_item} key={id} >
                                <Image src={teammate.teamImg} priority />
                            </span>
                        )
                    })}
                </div>

                <span
                    className={style.chain_between_team_left}>
                    <Image src={I.chainBetweenTeam} priority />
                </span>

                <span
                    className={style.chain_between_team_right}>
                    <Image src={I.chainBetweenTeam} priority />
                </span>
            </div>
        </div>

    );
};
