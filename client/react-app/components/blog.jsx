import Image from 'next/image'
import Link from 'next/link'

import style from '../styles/main.module.scss';

import I from '../img/images'

const blog = [
    {
        articleId: 1,
        articleImg: I.post1,
        articleHash: 'Stories',
        articleTitle: 'Travel In Japan: The True Experience',
    },
    {
        articleId: 2,
        articleImg: I.post2,
        articleVideoLink: 'https://www.youtube.com/watch?v=q9XAUBwTS7Y',
        articleHash: 'VideoTutor',
        articleTitle: 'Helping Wild Turtles',
    },
    {
        articleId: 3,
        articleImg: I.post3,
        articleHash: 'Photostory',
        articleTitle: 'Threats of Indusrial World',
    },
]
export const Blog = () => {

    return (
        <div id='ourMission' className={style.blog}
            data-scroll-section
        >
            {blog.map((article, id) => {
                const { articleImg, articleVideoLink, articleHash, articleTitle } = article;

                return (
                    <div className={style.article} key={id}
                        data-scroll
                        data-scroll-delay={`${id + 1}`}
                        data-scroll-speed={0.5}
                        data-scroll-position="top"
                        data-scroll-direction="vertical"
                    >

                        <div className={articleVideoLink ? style.article_video : style.article_image}>
                            <Image src={articleImg} alt={articleTitle} priority />
                            {articleVideoLink && <button type='button' className={style.article_video_playBtn}></button>}
                        </div>

                        <div className={style.article_info}>
                            <span className={style.article_info_hash}>#
                                <span className={style.article_info_hash_text}>{articleHash}</span>
                            </span>

                            <span className={style.article_info_title}>{articleTitle}</span>

                            <a className={style.article_info_link}>see more</a>
                        </div>
                    </div>
                )
            })}
        </div>
    );
};
