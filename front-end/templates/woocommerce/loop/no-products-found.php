<?php
/**
 * Displayed when no products are found matching the current query
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/no-products-found.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
$user_id            = get_current_user_id();
$customer_id        = get_user_meta($user_id, 'user_customer', true);
$kit_id             = get_user_meta($user_id, 'user_kit', true);
$one_order_value    = get_user_meta($user_id, 'one_order_value', true);

$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
$one_order_toggle   = get_post_meta($campaign_id, 'one_order_toggle', true);

// You already buy something check
if ($one_order_toggle[$kit_id] == 'on' && isset($one_order_value[$campaign_id][$kit_id]) && $one_order_value[$campaign_id][$kit_id]) {
	echo '<p class="woocommerce-info">' . __("Dear employee, An order has already been placed on the system. No further booking can be made.", "unidress") . '</p>';
} else {
	echo '<p class="woocommerce-info">' . __("No products were found matching your selection.", "unidress") . '</p>';
}
