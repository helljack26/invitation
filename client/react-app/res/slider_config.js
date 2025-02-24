
export const secondSliderSettings = {
    speed: 600,
    infinite: true,
    arrows: false,
    centerMode: true,
    draggable: false,
    dots: false,
    className: "center",
    slidesToShow: 3,
    slidesToScroll: 1,
    centerPadding: "150px",
    responsive: [
        {
            breakpoint: 1700,
            settings: {
                centerPadding: "180px",
            }
        },
        {
            breakpoint: 1400,
            settings: {
                centerPadding: "100px",
            }
        },
        {
            breakpoint: 1200,
            settings: {
                centerPadding: "350px",
                slidesToShow: 1,
            }
        },
        {
            breakpoint: 1150,
            settings: {
                centerPadding: "300px",
                slidesToShow: 1,
            }
        },
        {
            breakpoint: 1100,
            settings: {
                slidesToShow: 1,
                centerPadding: "240px",
            }
        },
        {
            breakpoint: 1050,
            settings: {
                slidesToShow: 1,
                centerPadding: "200px",
            }
        },
        {
            breakpoint: 950,
            settings: {
                slidesToShow: 1,
                centerPadding: "150px",
                draggable: true,
            }
        },
        {
            breakpoint: 850,
            settings: {
                slidesToShow: 1,
                centerPadding: "110px",
                draggable: true,
            }
        },
        {
            breakpoint: 768,
            settings: {
                slidesToShow: 1,
                centerPadding: "0px",
                draggable: true,
            }
        }
    ]
};


