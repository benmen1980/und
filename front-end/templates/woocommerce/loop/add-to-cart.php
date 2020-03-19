<?php
/**
 * Loop Add to Cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/add-to-cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if (!defined('ABSPATH')) {
	exit;
}

global $product;
$pType = $product->get_type();

// customize to show add to cart based on product type. 
if (is_user_logged_in() && $pType == 'simple') {
	// show custom checkout form if simple product for custom sample field 
	do_action('woocommerce_' . $product->get_type() . '_add_to_cart');
} else {
	echo apply_filters(
		'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
		sprintf(
			'<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
			esc_url($product->add_to_cart_url()),
			esc_attr(isset($args['quantity']) ? $args['quantity'] : 1),
			esc_attr(isset($args['class']) ? $args['class'] : 'button'),
			isset($args['attributes']) ? wc_implode_html_attributes($args['attributes']) : '',
			esc_html($product->add_to_cart_text())
		),
		$product,
		$args
	);
}
