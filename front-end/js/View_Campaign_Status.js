console.log('asdasdasd');
jQuery(document).ready(function(jQuery){
    /* update budget on add new product */
    jQuery('.product').on('click', '.ajax_add_to_cart', function () {
        update_unidress_budget();
    });

    /* update limit on delete product */
    jQuery('#main').on('click', 'a.remove', function () {
        update_unidress_budget();
    });

    /* update budget on change numbers product */
    jQuery('.woocommerce').on('click', 'button', function () {
        update_unidress_budget();
    });

    /* update budget on change numbers product */
    jQuery('.woocommerce').on('keydown', function (e) {
        if(e.keyCode === 13) {
            update_unidress_budget();
        }
    });

});

function update_unidress_budget() {
    setTimeout(function() {
        jQuery.ajax({
            type: "POST",
            //url: '/wp-admin/admin-ajax.php',
            url: admin_url('admin-ajax.php'),
            data: {
                action: 'update_unidress_budget',
            },
            success: function (response) {
                jQuery('.remaining-budget').html(response);
                console.log(response);
            }
        });
    }, 1000);
}
