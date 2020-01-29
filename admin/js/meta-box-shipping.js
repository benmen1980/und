jQuery(document).ready(function($){
    checkAllInputs(jQuery('.unidress-shops-shipping input.shipping-select').eq(0));

    /* Update ajax for campaign customer */
    jQuery('.inside').on('change', '#acf-field_5c8778d42c013', function(){
        let customer = jQuery(this).val();
        let campaign = jQuery('#post_ID').val();

        let data = {
            'action'            : 'update_shipping_option',
            'customer'          :  customer,
            'campaign'          :  campaign,
        };

        jQuery.post(ajaxurl, data, function(responsive) {
            jQuery('#campaign_shipping_option fieldset').html(responsive);
        });

    });

    /* Update ajax for project customer */
    jQuery('.inside').on('change', '#acf-field_5c87686374a66', function(){
        let customer = jQuery(this).val();
        let campaign = jQuery('#post_ID').val();

        let data = {
            'action'            : 'update_shipping_option',
            'customer'          :  customer,
            'campaign'          :  campaign,
        };

        jQuery.post(ajaxurl, data, function(responsive) {
            jQuery('#campaign_shipping_option fieldset').html(responsive);
        });

    });

    // #46: UN1-T47: Shipping to All Unidress Shops
    jQuery('.inside').on('change', '.shipping-all-select', function() {
        let val = jQuery(this).prop("checked");
        jQuery(this).closest('fieldset').find('ul .shipping-select').each(function () {
            jQuery(this).prop("checked", val);
        })
    });
    jQuery('.inside').on('change', '.shipping-select', function() {
        checkAllInputs(jQuery(this));
    });

});

function checkAllInputs(inputs) {
    var allChecked = true;
    jQuery(inputs).closest('ul').find('.shipping-select').each(function () {

        if (!jQuery(this).prop("checked")) {
            allChecked = false;
            return false;
        }

    });
    jQuery(inputs).closest('fieldset').find('.shipping-all-select').prop("checked", allChecked)
}