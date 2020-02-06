<?php

// var_dump(get_user_meta(get_current_user_id(),'', true));
// die;

add_action('woocommerce_thankyou', 'unid_change_password_after_checkout', 10, 1);
function unid_change_password_after_checkout( $order_id ) {
    $order = wc_get_order($order_id);
	$user_id = wp_update_user([ 
		'ID'       => get_current_user_id(), 
		'user_email' => $order->data['billing']['email'],
	]);
	// if ( is_wp_error( $user_id ) ) {
	// }
	// else {
	// 	// Все ОК!
	// 	echo "Ока";
	// 	// die;
	// }
}

