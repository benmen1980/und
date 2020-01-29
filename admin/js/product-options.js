jQuery(document).ready(function($){
    // Initial order.
    var woocommerce_options_items = $( '.product_attributes' ).find( '.woocommerce_options' ).get();

    woocommerce_options_items.sort( function( a, b ) {
        var compA = parseInt( $( a ).attr( 'rel' ), 10 );
        var compB = parseInt( $( b ).attr( 'rel' ), 10 );
        return ( compA < compB ) ? -1 : ( compA > compB ) ? 1 : 0;
    });
    $( woocommerce_options_items ).each( function( index, el ) {
        $( '.product_options' ).append( el );
    });

    function attribute_row_indexes() {
        $( '.product_options .woocommerce_options' ).each( function( index, el ) {
            $( '.attribute_position', el ).val( parseInt( $( el ).index( '.product_options .woocommerce_options' ), 10 ) );
        });
    }

    $( '.product_options .woocommerce_options' ).each( function( index, el ) {
        if ( $( el ).css( 'display' ) !== 'none' && $( el ).is( '.taxonomy' ) ) {
            $( 'select.attribute_options' ).find( 'option[value="' + $( el ).data( 'taxonomy' ) + '"]' ).attr( 'disabled', 'disabled' );
        }
    });

    // Add rows.
    $( 'button.add_option' ).on( 'click', function() {
        var size         = $( '.product_options .woocommerce_option' ).length;
        var attribute    = $( 'select.attribute_options' ).val();
        var $wrapper     = $( this ).closest( '#product_options' );
        var $attributes  = $wrapper.find( '.product_options' );
        var product_type = $( 'select#product-type' ).val();
        var data         = {
            action:   'add_product_option',
            taxonomy: attribute,
            i:        size,
            security: woocommerce_admin_meta_boxes.add_attribute_nonce
        };

        $wrapper.block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

        $.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {

            $attributes.append( response );

            if ( 'variable' !== product_type ) {
                $attributes.find( '.enable_variation' ).hide();
            }

            $( document.body ).trigger( 'wc-enhanced-select-init' );

            attribute_row_indexes();

            $attributes.find( '.woocommerce_options' ).last().find( 'h3' ).click();

            $wrapper.unblock();

            $( document.body ).trigger( 'woocommerce_added_attribute' );
        });

        if ( attribute ) {
            $( 'select.attribute_options' ).find( 'option[value="' + attribute + '"]' ).attr( 'disabled','disabled' );
            $( 'select.attribute_options' ).val( '' );
        }

        return false;
    });

    $( '.product_options' ).on( 'blur', 'input.option_name', function() {
        $( this ).closest( '.woocommerce_option' ).find( 'strong.option_name' ).text( $( this ).val() );
        $( this ).closest( '.woocommerce_option' ).find( '.options_label' ).val( $( this ).val() );
    });

    $( '.product_options' ).on( 'click', 'button.select_all_options', function() {
        $( this ).closest( 'td' ).find( 'select option' ).attr( 'selected', 'selected' );
        $( this ).closest( 'td' ).find( 'select' ).change();
        return false;
    });

    $( '.product_options' ).on( 'click', 'button.select_no_options', function() {
        $( this ).closest( 'td' ).find( 'select option' ).removeAttr( 'selected' );
        $( this ).closest( 'td' ).find( 'select' ).change();
        return false;
    });

    $( '.product_options' ).on( 'click', '.remove_row', function() {
        if ( window.confirm( woocommerce_admin_meta_boxes.remove_attribute ) ) {
            var $parent = $( this ).parent().parent();

            if ( $parent.is( '.taxonomy' ) ) {
                $parent.find( 'select, input[type=text]' ).val( '' );
                $parent.html('');
                $( 'select.attribute_options' ).find( 'option[value="' + $parent.data( 'taxonomy' ) + '"]' ).removeAttr( 'disabled' );
            } else {
                $parent.find( 'select, input[type=text]' ).val( '' );
                $parent.html('');
                attribute_row_indexes();
            }
        }
        return false;
    });

    // Attribute ordering.
    $( '.product_options' ).sortable({
        items: '.woocommerce_options',
        cursor: 'move',
        axis: 'y',
        handle: 'h3',
        scrollSensitivity: 40,
        forcePlaceholderSize: true,
        helper: 'clone',
        opacity: 0.65,
        placeholder: 'wc-metabox-sortable-placeholder',
        start: function( event, ui ) {
            ui.item.css( 'background-color', '#f6f6f6' );
        },
        stop: function( event, ui ) {
            ui.item.removeAttr( 'style' );
            attribute_row_indexes();
        }
    });
        // Save attributes and update variations.
    $( '.save_options' ).on( 'click', function() {

        $( '.product_options' ).block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
        var original_data = $( '.product_options' ).find( 'input, select, textarea' );
        var data = {
            post_id     : woocommerce_admin_meta_boxes.post_id,
            product_type: $( '#product-type' ).val(),
            data        : original_data.serialize(),
            action      : 'save_product_options',
            security    : woocommerce_admin_meta_boxes.save_attributes_nonce
        };
        $.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {

            $( '.product_options' ).unblock();

        });
    });
});

$( '.product_options' ).on( 'click', 'button.add_new_options', function() {

    $( '.product_options' ).block({
        message: null,
        overlayCSS: {
            background: '#fff',
            opacity: 0.6
        }
    });

    var $wrapper           = $( this ).closest( '.woocommerce_option' );
    var attribute          = $wrapper.data( 'taxonomy' );
    var new_attribute_name = window.prompt( woocommerce_admin_meta_boxes.new_attribute_prompt );

    if ( new_attribute_name ) {

        var data = {
            action:   'add_new_options',
            taxonomy: attribute,
            term:     new_attribute_name,
            security: woocommerce_admin_meta_boxes.add_attribute_nonce
        };
        $.post( woocommerce_admin_meta_boxes.ajax_url, data, function( response ) {

            if ( response.error ) {
                // Error.
                window.alert( response.error );
            } else if ( response.slug ) {
                // Success.
                $wrapper.find( 'select.options_values' ).append( '<option value="' + response.term_id + '" selected="selected">' + response.name + '</option>' );
                $wrapper.find( 'select.options_values' ).change();
            }

            $( '.product_options' ).unblock();
        });

    } else {
        $( '.product_options' ).unblock();
    }

    return false;
});
