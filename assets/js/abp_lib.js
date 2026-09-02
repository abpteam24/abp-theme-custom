
(function ($) {
    "use strict";
    $(document).on('click', 'div.abp_area .abp_price_section [data-price-target]', function () {
        if (!$(this).hasClass('abp_active')) {
            let tabsTarget = $(this).data('price-target');
            let parent = $(this).closest('.abp_price_section');
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


