//slick slider for related product
(function ($) {
    "use strict";
    $(document).ready(function () {
        $('div.abp_area .related_item_area .abp_grid').slick({
            dots: false,
            arrows: true,
            prevArrow: '.related_prev',
            nextArrow: '.related_next',
            infinite: true,
            centerMode: false,
            autoplay: true,
            autoplaySpeed: 4000,
            centerPadding: '0',
            slidesToShow: 3,
            slidesToScroll: 1,
            responsive: [
                {
                    breakpoint: 1000,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: false,
                        centerMode: false
                    }
                },
                {
                    breakpoint: 700,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1,
                        infinite: true,
                        dots: false,
                        centerMode: false
                    }
                },
                {
                    breakpoint: 500,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        centerMode: false
                    }
                }
            ]
        });
    });
}(jQuery));