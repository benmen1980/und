<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @package 	WooCommerce/Templates
 * @version     3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="woocommerce-order">

	<?php if ( $order ) : ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php _e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'unidress' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e( 'Pay', 'unidress' ) ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php _e( 'My account', 'unidress' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>
            <?php
			$order_id           = $order->get_order_number();
			$user_id            = get_current_user_id();
			$customer_id        = get_user_meta($user_id, 'user_customer', true);
			$branch_id          = get_user_meta($user_id, 'user_branch', true);
			$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
			$shipping_allow     = get_post_meta($campaign_id, 'shipping_allow', true);
			
			$unidress_shipping = get_post_meta($order_id, 'unidress_shipping', true);
			$unidress_shipping_title = get_the_title($unidress_shipping);

			if ($unidress_shipping == $branch_id) {
				if ($shipping_allow) {
					$checked = ' checked="checked"';
					$unidress_shipping_title .= ' - ' . get_post_meta($branch_id, 'branch_address', true);
				}
            }

			?>
			<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'woocommerce' ), $order ); ?></p>

			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

				<li class="woocommerce-order-overview__order order">
					<?php _e( 'Order number:', 'unidress' ); ?>
					<strong><?php echo $order_id; ?></strong>
				</li>

				<li class="woocommerce-order-overview__date date">
					<?php _e( 'Date:', 'unidress' ); ?>
					<strong><?php echo wc_format_datetime( $order->get_date_created() ); ?></strong>
				</li>

				<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
					<li class="woocommerce-order-overview__email email">
						<?php _e( 'Email:', 'unidress' ); ?>
						<strong><?php echo $order->get_billing_email(); ?></strong>
					</li>
				<?php endif; ?>

                <?php
                if ( !get_ordering_style($customer_id)=='closed_list'  ) : ?>
                    <li class="woocommerce-order-overview__total total">
                        <?php _e( 'Total:', 'unidress' ); ?>
                        <strong><?php echo $order->get_formatted_order_total(); ?></strong>
                    </li>
                <?php endif; ?>

				<?php if ( $order->get_payment_method_title() ) : ?>
                    <li class="woocommerce-order-overview__payment-method method">
						<?php _e( 'Payment method:', 'unidress' ); ?>
                        <strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
                    </li>
				<?php endif; ?>

				<?php
                if ($unidress_shipping) : ?>
                    <li class="woocommerce-order-overview__unidress-shipping">
						<?php _e( 'Shipping to:', 'unidress' ); ?>
                        <strong><?php echo wp_kses_post($unidress_shipping_title); ?></strong>
                    </li>
				<?php endif; ?>

			</ul>

		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'woocommerce' ), null ); ?></p>

	<?php endif; ?>

</div>
