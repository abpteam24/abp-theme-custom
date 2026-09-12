
(function ($) {
    "use strict";
    $(document).on('click', 'div.abp_area .abp_product_price [data-price-target]', function () {
        if (!$(this).hasClass('abp_active')) {
            let tabsTarget = $(this).data('price-target');
            let parent = $(this).closest('.abp_product_price');
            parent.height(parent.height());
            parent.find('[data-price-target].abp_active').removeClass('abp_active');
            $(this).addClass('abp_active');
            if(tabsTarget==='all'){
                parent.find('[data-price-tab]').slideDown(350);
                parent.height('auto');
            }else {
                parent.find('[data-price-tab]').slideUp(350).promise().done(function () {
                    parent.find('[data-price-tab="' + tabsTarget + '"]').slideDown(350).promise().done(function () {
                        parent.height('auto');
                    });
                });
            }

        }
    });
}(jQuery));

(function ($) {
    "use strict";

    $.fn.abp_product_slider = function () {
        return this.each(function () {
            var $slider = $(this);
            var $stage = $slider.find('[data-slider]');
            var $items = $slider.find('.slider_item');
            var $thumbs = $slider.find('.thumb_item');
            var $counter = $slider.find('.slide_counter .current_index');
            var $fill = $slider.find('.progress_fill');
            var total = $items.length;
            var current = 0;
            var timer = null;
            var resizeTimer = null;
            var heights = [];

            if (total < 1) {
                return;
            }

            function measure() {
                var width = $slider.width();
                heights = [];
                $items.each(function (index) {
                    var img = this.querySelector('img');
                    if (img && img.naturalWidth > 0 && img.naturalHeight > 0) {
                        heights[index] = Math.round(width * (img.naturalHeight / img.naturalWidth));
                    } else {
                        heights[index] = $stage.height() || 0;
                    }
                });
            }

            function set_height(index, instant) {
                if (heights[index] > 0) {
                    if (instant) {
                        $stage.css('transition', 'none');
                        $stage.height(heights[index]);
                        setTimeout(function () {
                            $stage.css('transition', '');
                        }, 60);
                    } else {
                        $stage.height(heights[index]);
                    }
                }
            }

            function restart() {
                if (timer) {
                    clearTimeout(timer);
                    timer = null;
                }
                if (total < 2) {
                    return;
                }
                $fill.css({ 'transition': 'none', 'width': '0%' });
                setTimeout(function () {
                    $fill.css({ 'transition': 'width 4.5s linear', 'width': '100%' });
                }, 60);
                timer = setTimeout(next, 4600);
            }

            function go_to(index, instant) {
                index = (index + total) % total;
                $items.eq(current).addClass('active_slide').removeClass('active');
                $items.eq(index).addClass('active');
                $thumbs.removeClass('active').eq(index).addClass('active');
                $counter.text(index + 1);
                current = index;
                set_height(index, !!instant);
                restart();
            }

            function next() {
                go_to(current + 1);
            }

            function prev() {
                go_to(current - 1);
            }

            $slider.find('.prev_item').on('click', prev);
            $slider.find('.next_item').on('click', next);
            $thumbs.on('click', function () {
                go_to(parseInt($(this).attr('data-slide-index'), 10) || 0);
            });
            $slider.on('mouseenter', function () {
                if (timer) {
                    clearTimeout(timer);
                    timer = null;
                }
            }).on('mouseleave', function () {
                restart();
            });

            $items.find('img').one('load', function () {
                measure();
                set_height(current, true);
                $slider.find('.slider_loading').addClass('hidden');
            });
            $slider.find('.slider_item img').first().on('error', function () {
                $slider.find('.slider_loading').addClass('hidden');
            });
            $items.find('img').each(function () {
                if (this.complete) {
                    measure();
                    set_height(current, true);
                }
            });
            setTimeout(function () {
                $slider.find('.slider_loading').addClass('hidden');
            }, 2500);

            $(window).on('resize', function () {
                if (resizeTimer) {
                    clearTimeout(resizeTimer);
                }
                resizeTimer = setTimeout(function () {
                    measure();
                    set_height(current, true);
                }, 200);
            });

            measure();
            set_height(0, true);
            restart();
        });
    };

    $(function () {
        $('.abp_product_slider .abp_slider').each(function () {
            $(this).abp_product_slider();
        });
    });
}(jQuery));


