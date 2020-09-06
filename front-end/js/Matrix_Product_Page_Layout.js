jQuery(document).ready(function($){
    let products = jQuery('#variation-table').attr('data-product');
    products = JSON.parse(products);

    jQuery('#variation-table').find('td[attributes]').each(function (index, elem) {
        let product_variation = JSON.parse($(this).attr('attributes'));
        $(products).each(function (indexProduct, product) {
            if (JSON.stringify(product['attributes']) == JSON.stringify(product_variation) ) {
                let input = '<input type="number" variationId="' + product['variation_id'] + '" value="0">';
                $(elem).html(input);
                return false;
            }
        })
    });

    jQuery('#add_to_cart_matrix_product').on('click', function () {
        let products = [];
        let quantity = [];
        jQuery('#variation-table input[variationid]').each(function (index, elem) {
            let variation_id = $(elem).attr('variationid');
            let variation_value = $(elem).val();
            products.push(variation_id);
            quantity.push(variation_value);
        });

        var result = products.reduce(function(acc, el, index) {
            acc[el] = quantity[index];
            return acc;
        }, {});

        jQuery.ajax({
            type: "POST",
            url: '/wp-admin/admin-ajax.php',
            data: {
                action: 'add_item_from_cart',
                products: result
            },
            success: function (res) {
                window.location.href = '/cart/';
            }
        });

    });



});

