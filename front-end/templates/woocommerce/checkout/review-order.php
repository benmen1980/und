<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_customer = get_user_meta(get_current_user_id(), 'user_customer', true);

?>
<table class="shop_table woocommerce-checkout-review-order-table">
	<thead>
		<tr>
			<?php if ( get_ordering_style($current_customer) != 'closed_list' ) : ?>
                <th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
                <th class="product-total"><?php _e( 'Quantity', 'unidress' ); ?></th>
                <th class="product-total"><?php _e( 'Total', 'woocommerce' ); ?></th>
			<?php else: ?>
                <th colspan="2" class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
            <?php endif; ?>
		</tr>
	</thead>
	<tbody>
		<?php
			do_action( 'woocommerce_review_order_before_cart_contents' );

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					?>
					<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<?php if ( get_ordering_style($current_customer) != 'closed_list' ) : ?>

                            <td class="product-name">
								<?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;'; ?>
								<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                            </td>
                            <td class="product-quantity">
	                            <?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); ?>
                            </td>
                            <td class="product-total">
								<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                            </td>

						<?php else : ?>

                            <td colspan="2" class="product-name">
								<?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;'; ?>
								<?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); ?>
								<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                            </td>

                        <?php endif; ?>

					</tr>
					<?php
				}
			}

			do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</tbody>
	<tfoot>
        <?php
        $user_id = get_current_user_id();
        $kit_id             = get_user_meta($user_id, 'user_kit', true);
        $customer_id        = get_user_meta($user_id, 'user_customer', true);
        $campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
        $budgets_in_campaign = implode(get_post_meta($campaign_id, 'budget', true));
        $user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
        $user_budget_left   = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
        $subtotal =WC()->cart->get_cart_subtotal();
        $total = WC()->cart->get_cart_total();
        //$amount_total = (int)WC()->cart->cart_contents_total;
        $amount_total = (int)WC()->cart->get_subtotal();
        $private_purchase_amount = get_post_meta($campaign_id, 'private_purchase_amount',  true);


        ?>
        <?php if ( get_ordering_style($current_customer) != 'closed_list' ) : ?>

            <tr class="cart-subtotal">
                <th colspan="2" ><?php _e( 'Subtotal', 'woocommerce' ); ?></th>
                <td><?php wc_cart_totals_subtotal_html(); ?></td>
            </tr>

        <?php endif; ?>

        <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) :?>
            
            <?php do_action( 'woocommerce_review_order_before_shipping' ); ?>

            <?php wc_cart_totals_shipping_html(); ?>

            <?php do_action( 'woocommerce_review_order_after_shipping' ); ?>

        <?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th colspan="2" ><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>
        <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th colspan="2" ><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
                <?php if(true):?>
                    <td><?php  echo wc_price($coupon->amount);
					//wc_cart_totals_coupon_html( $coupon ); ?>
                    </td>
                <?php endif;?>
			</tr>
		<?php endforeach; ?>
		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
						<th colspan="2" ><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th colspan="2" ><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

	<?php if ( get_ordering_style($current_customer) != 'closed_list' ) : ?>
        <tr class="order-total">
                <th colspan="2"><?php _e( 'Total', 'woocommerce' ); ?></th>
                <td>
                    <?php
                    //echo $total;
                     wc_cart_totals_order_total_html(); 
                    ?>
                </td>
            </tr>

	<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	</tfoot>
</table>
