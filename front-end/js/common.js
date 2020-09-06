jQuery(function ($) {

    $('.unidress-mobile-header').on('click', '.search', function () {
        $('.unidress-mobile-header').find('.site-search').toggle()
    });

    $('.unidress-mobile-header').on('click', '.menu-burger', function () {
        $('.unidress-mobile-header').find('.handheld-navigation').toggle()
    });

    jQuery(document).on('click', '.single_add_to_cart_button', function (e) {
        var passed = true;
        if (jQuery(this).closest('form').find('.nipl_simple_option_wrp').length > 0) {
            var custom_sels = jQuery(this).closest('form').find('.nipl_simple_option_wrp select').val();
            if (custom_sels === '') {
                passed = false;
                jQuery(this).closest('form').find('.nipl_simple_option_wrp select').css('border', '1px solid red');
            }
        }

        if (passed) {
            jQuery(this).closest('form').find('.nipl_simple_option_wrp select').css('border', '1px solid rgb(169, 169, 169)');
        } else {
            e.preventDefault();
        }

    })

});