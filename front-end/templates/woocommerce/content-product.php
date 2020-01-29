<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

add_action( 'woocommerce_single_variation_closed_list', 'woocommerce_single_variation', 10 );
add_action( 'woocommerce_single_variation_closed_list', 'woocommerce_single_custom_variation_add_to_cart_button', 20 );


if ($product->get_type() == 'variable') {
    $get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
    if ($product->get_available_variations() && !empty($product->get_available_variations())){
        wp_enqueue_script( 'wc-add-to-cart-variation' );

        $available_variations = $get_variations ? $product->get_available_variations() : false;
        $attributes           = $product->get_variation_attributes();
        $selected_attributes  = $product->get_default_attributes();

	    $user_id = get_current_user_id();
	    $customer_id = get_user_meta($user_id, 'user_customer', true);
	    $campaign_id = get_post_meta($customer_id, 'active_campaign', true);
	    $kit_id = get_user_meta($user_id, 'user_kit', true);
        $product_option = get_post_meta($campaign_id, 'product_option', true);
	    $product_id = $product->get_id();
        $current_customer = get_user_meta(get_current_user_id(), 'user_customer', true);

        if (isset($product_option[$kit_id][$product_id]['variation'])){
            foreach ($available_variations as $index=>$variation) {
                if (!in_array($variation['variation_id'], $product_option[$kit_id][$product_id]['variation'])){
                    unset($available_variations[$index]);
                }
            }
        }

	    sort($available_variations);
	    $attribute_keys  = array_keys( $attributes );
        $variations_json = wp_json_encode( $available_variations );
        $variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
        ?>

<?php if ( get_ordering_style($current_customer) != 'closed_list' ) { ?>

<li <?php wc_product_class( '', $product ); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item' );
	/**
	 * Hook: woocommerce_before_shop_loop_item_title.
	 *
	 * @hooked woocommerce_show_product_loop_sale_flash - 10
	 * @hooked woocommerce_template_loop_product_thumbnail - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item_title' );
	/**
	 * Hook: woocommerce_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */
	do_action( 'woocommerce_shop_loop_item_title' );
	/**
	 * Hook: woocommerce_after_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_rating - 5
	 * @hooked woocommerce_template_loop_price - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item_title' );
	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>
<?php } else { ?>
        
    <li <?php wc_product_class(); ?>>
        <div id="variations_form_wrapper">
            <form class="variations_form cart closed_list" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
                <?php do_action( 'woocommerce_before_variations_form' ); ?>
                <div class="product-list-item">
       
                  
                    <div class="product-description">
                    <?php woocommerce_template_loop_product_thumbnail(); ?>

                        <?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
                            <p class="stock out-of-stock"><?php esc_html_e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?></p>
                        <?php else : ?>
                            <div class="variations" cellspacing="0">

                                <div class="product-description" style="margin-bottom: 20px;">
                                    <?php do_action( 'woocommerce_shop_loop_item_title' ); ?>
                                </div>
                                <?php foreach ( $attributes as $attribute_name => $options ) : ?>
                                    <div class="select-wrapper">
                                        <h3 class="label"><label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></h3>
                                        <div class="value">
                                            <?php
                                            wc_dropdown_variation_attribute_options( array(
                                                'options'   => $options,
                                                'attribute' => $attribute_name,
                                                'product'   => $product,
                                            ) );
                                            echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) ) : '';
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <div class="single_variation_wrap"></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="product-add">
		            <?php
		            do_action( 'woocommerce_before_single_variation' );
		            do_action( 'woocommerce_single_variation_closed_list' );
		            do_action( 'woocommerce_after_single_variation' );
		            do_action( 'woocommerce_after_variations_form' );
		            ?>
                </div>
            </form>
        </div>
    </li>

<?php
    }}
}

if ($product->get_type() == 'simple') {

    ?>


<?php if ( get_ordering_style($current_customer) != 'closed_list' ) { ?>

<li <?php wc_product_class( '', $product ); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item' );
	/**
	 * Hook: woocommerce_before_shop_loop_item_title.
	 *
	 * @hooked woocommerce_show_product_loop_sale_flash - 10
	 * @hooked woocommerce_template_loop_product_thumbnail - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item_title' );
	/**
	 * Hook: woocommerce_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */
	do_action( 'woocommerce_shop_loop_item_title' );
	/**
	 * Hook: woocommerce_after_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_rating - 5
	 * @hooked woocommerce_template_loop_price - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item_title' );
	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>
<?php } else { ?>

    <li <?php wc_product_class(); ?>>
        <form class="cart closed_list" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>

            <?php do_action( 'woocommerce_before_variations_form' ); ?>
            <div class="product-list-item">

                <div class="product-description">
                    <?php do_action( 'woocommerce_shop_loop_item_title' ); ?>
                </div>

                <div class="product-image">
                    <?php woocommerce_template_loop_product_thumbnail(); ?>
                </div>
            </div>
            <div class="product-add">
	            <?php
	            do_action( 'woocommerce_before_add_to_cart_quantity' );

	            woocommerce_quantity_input( array(
		            'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
		            'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
		            'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
	            ) );

	            do_action( 'woocommerce_after_add_to_cart_quantity' );
	            ?>

                <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

            </div>
        </form>
    </li>

<?php }}