<?php
/**
 * Proceed to checkout button
 *
 * Contains the markup for the proceed to checkout button on the cart.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/proceed-to-checkout-button.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 2.4.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$current_customer = get_user_meta(get_current_user_id(), 'user_customer', true);

if (get_customer_type($current_customer) == "campaign") {
    wp_enqueue_script('View_Campaign_Status-js', plugins_url('/unidress/front-end/js/View_Campaign_Status.js'), array('jquery'));
    wp_enqueue_script('bootstrap-modal-js', plugins_url('/unidress/front-end/js/bootstrap.modal.min.js'), array('jquery'));
    wp_enqueue_style('bootstrap-modal-css', plugins_url('/unidress/front-end/css/bootstrap.modal.min.css'));

    $user_id                = get_current_user_id();
    $customer_id            = get_user_meta($user_id, 'user_customer', true);
    $kit_id                 = get_user_meta($user_id, 'user_kit', true);
    $campaign_id            = get_post_meta($customer_id, 'active_campaign', true);
    $one_order_toggle       = get_post_meta($campaign_id, 'one_order_toggle', true);

    $user_budget_limits     = get_user_meta($user_id, 'user_budget_limits', true);
    $user_budget_left       = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;

    $budget_in_campaign    = get_post_meta($campaign_id, 'budget', true);
    $unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
    if ($unidress_budget > 0) {
        $budget_in_kit = $unidress_budget;
    } else {
        $budget_in_kit = $budget_in_campaign[$kit_id] ?: 0;
    }

    $total                  = WC()->cart->get_totals('total')['total'];
    $budget_total           = $budget_in_kit - (int)$user_budget_left - $total;

    if ($one_order_toggle[$kit_id] == 'on' && $budget_total > 0) : ?>
        <a class="checkout-button button alt wc-forward" data-toggle="modal" data-target="#order-warning">
            <?php esc_html_e('Proceed to checkout', 'unidress'); ?>
        </a>
        <!-- Modal -->
        <div class="modal fade" id="order-warning" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <?php if ($budget_total > 0) { ?>
                            <span class="message-warning-1"><?php echo __('Dear employee, only one order can be placed. Please make sure you have selected all the items you want.', 'unidress'); ?></span>
                        <?php } ?>
                        <span class="message-warning-2"><?php echo sprintf(__('You have <span class="remaining-budget">%s</span>%s left in your budget.', 'unidress'), $budget_total, get_woocommerce_currency_symbol()); ?></span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php esc_html_e('Cancel', 'unidress'); ?></button>
                        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="checkout-modal-button button alt wc-forward">
                            <?php esc_html_e('Confirm', 'unidress'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="checkout-button button alt wc-forward">
            <?php esc_html_e('Proceed to checkout', 'unidress'); ?>
        </a>
    <?php endif;
} else {
    ?>
    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="checkout-button button alt wc-forward">
        <?php esc_html_e('Proceed to checkout', 'unidress'); ?>
    </a>
<?php
}
