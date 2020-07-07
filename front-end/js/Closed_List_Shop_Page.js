jQuery(document).ready(function(jQuery){

	if ( typeof wc_add_to_cart_params === 'undefined' ) {
		return false;
	}
    
    jQuery(".add-closed-list").on('click', function (e) {
        if (jQuery(this).hasClass('disabled'))
            return;
        e.preventDefault();

        var listComplete = true;

        let form = jQuery(this).parents('form');
        let select = jQuery(form).find('select');

        jQuery(select).find('option').each(function () {
            if(this.selected && jQuery(this).val() ==  '' )
            listComplete = false;
        });

        if(!listComplete) {

        } else {

            let product_id      = Number( jQuery(form).find('input[name="product_id"]').eq(0).val() );
            let variation_id    = Number( jQuery(form).find('input[name="variation_id"]').eq(0).val() );
            let quantity        = Number( jQuery(form).find('.quantity input').eq(0).val() );
            let result = new Array(product_id, variation_id, quantity);

            var button = jQuery(this);

            jQuery.ajax({
                type: "POST",
                url: '/wp-admin/admin-ajax.php',
                data: {
                    action: 'add_item_to_cart',
                    products: result
                },
                beforeSend: function (){
                    jQuery(button).addClass('disabled relative blockUI');
                },
                success: function (res) {
                    jQuery(button).removeClass('disabled relative blockUI');
                    jQuery('ul#site-header-cart ').html(res);
                },
            });

        }

    })


});

    var AddCustomToCartHandler = function() {
        jQuery( document.body )
            .on( 'click', '.add_to_cart_button2', this.onAddToCart )
            .on( 'click', '.remove_from_cart_button', this.onRemoveFromCart )
            .on( 'added_to_cart', this.updateButton )
            .on( 'added_to_cart', this.updateCartPage )
            .on( 'added_to_cart removed_from_cart', this.updateFragments );
    };

    /**
     * Handle the add to cart event.
     */
    AddCustomToCartHandler.prototype.onAddToCart = function( e ) {

        if ( typeof wc_add_to_cart_params === 'undefined' ) {
            return false;
        }

        var jQuerythisbutton = jQuery( this );

        if ( jQuerythisbutton.is( '.ajax_add_to_cart2' ) ) {
            if ( ! jQuerythisbutton.attr( 'data-product_id' ) ) {
                return true;
            }
            if ( jQuerythisbutton.hasClass( 'disabled' ) ) {
                return true;
            }


            e.preventDefault();

            jQuerythisbutton.removeClass( 'added' );
            jQuerythisbutton.addClass( 'loading' );

            var data = {};

            // jQuery.each( jQuerythisbutton.data(), function( key, value ) {
            //     data[ key ] = value;
            // });
            data['quantity'] = jQuerythisbutton.parent().find('input[name="quantity"]').val();
            data['product_id'] = jQuerythisbutton.parent().find('input[name="product_id"]').val();
            var variation_id = jQuerythisbutton.parent().find('input[name="variation_id"]').val();
            if (variation_id && variation_id != 0 )
                data['product_id'] = variation_id;
            // Trigger event.
            jQuery( document.body ).trigger( 'adding_to_cart', [ jQuerythisbutton, data ] );

            // Ajax action.
            jQuery.post( wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'add_to_cart' ), data, function( response ) {

                if ( ! response ) {
                    return;
                }

                if ( response.error && response.product_url ) {
                    window.location = response.product_url;
                    return;
                }

                // Redirect to cart option
                if ( wc_add_to_cart_params.cart_redirect_after_add === 'yes' ) {
                    window.location = wc_add_to_cart_params.cart_url;
                    return;
                }

                // Trigger event so themes can refresh other areas.
                jQuery( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, jQuerythisbutton ] );
            });
        }
    };

    /**
     * Update fragments after remove from cart event in mini-cart.
     */
    AddCustomToCartHandler.prototype.onRemoveFromCart = function( e ) {
        var $thisbutton = $( this ),
        $row        = $thisbutton.closest( '.woocommerce-mini-cart-item' );

        e.preventDefault();

        $row.block({
            message: null,
            overlayCSS: {
                opacity: 0.6
            }
        });

        e.data.addToCartHandler.addRequest({
            type: 'POST',
            url: wc_add_to_cart_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'remove_from_cart' ),
            data: {
                cart_item_key : $thisbutton.data( 'cart_item_key' )
            },
            success: function( response ) {
                if ( ! response || ! response.fragments ) {
                    window.location = $thisbutton.attr( 'href' );
                    return;
                }

                $( document.body ).trigger( 'removed_from_cart', [ response.fragments, response.cart_hash, $thisbutton ] );
            },
            error: function() {
                window.location = $thisbutton.attr( 'href' );
                return;
            },
            dataType: 'json'
        });
    };

    /**
     * Update cart page elements after add to cart events.
     */
    AddCustomToCartHandler.prototype.updateButton = function( e, fragments, cart_hash, jQuerybutton ) {
        jQuerybutton = typeof jQuerybutton === 'undefined' ? false : jQuerybutton;

        if ( jQuerybutton ) {
            jQuerybutton.removeClass( 'loading' );
            jQuerybutton.addClass( 'added' );

            // View cart text.
            if ( ! wc_add_to_cart_params.is_cart && jQuerybutton.parent().find( '.added_to_cart' ).length === 0 ) {
                jQuerybutton.after( ' <a href="' + wc_add_to_cart_params.cart_url + '" class="added_to_cart wc-forward" title="' +
                    wc_add_to_cart_params.i18n_view_cart + '">' + wc_add_to_cart_params.i18n_view_cart + '</a>' );
            }

            jQuery( document.body ).trigger( 'wc_cart_button_updated', [ jQuerybutton ] );
        }
    };

    /**
     * Update cart page elements after add to cart events.
     */
    AddCustomToCartHandler.prototype.updateCartPage = function() {

    };


    /**
     * Init AddCustomToCartHandler.
     */
    new AddCustomToCartHandler();
