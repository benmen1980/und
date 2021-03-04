<?php


define('MY_PLUGIN_ROOT_FRONT', MY_PLUGIN_ROOT . '/front-end');
$current_customer = get_user_meta(get_current_user_id(), 'user_customer', true);
require_once MY_PLUGIN_ROOT_FRONT . '/include/functions/functions.php';

function get_ordering_style($current_customer)
{
	global $wpdb;
	$current_customer = empty($current_customer) ? 1 : $current_customer;
	$ordering_style = $wpdb->get_row("SELECT t.slug FROM {$wpdb->prefix}postmeta pm INNER JOIN {$wpdb->prefix}terms t ON t.term_id = pm.meta_value WHERE pm.post_id = $current_customer and pm.meta_key = 'ordering_style'");

	if (isset($ordering_style->slug))
		return $ordering_style->slug;

	return false;
}
function get_customer_type($current_customer)
{
	$type = get_post_meta($current_customer, 'customer_type', true);
	return $type;
}

add_action('init', 'redirect_non_logged_users_to_login_page');
function redirect_non_logged_users_to_login_page()
{
	if (!is_user_logged_in() && wp_login_url() != site_url('/' . $GLOBALS['pagenow'])) {
		wp_redirect(wp_login_url(home_url()));
		exit;
	}
}

add_action('woocommerce_before_checkout_form', 'can_user_checkout_check');
function can_user_checkout_check()
{
	if (check_proceed_to_checkout()) {
		wp_safe_redirect(wc_get_page_permalink('cart'));
		exit;
	}
}

/*add shipping fee on cart and checkout page, if subtotal is less than minimum order value */


//add_action( 'woocommerce_cart_calculate_fees', 'bbloomer_add_checkout_fee',20,1 );
//remove fee and transform fee nto new iten in product
//because woocommerce can not include fee in coupon if total is 0
function bbloomer_add_checkout_fee($cart) {
   // Edit "Fee" and "5" below to control Label and Amount
	
    $user_id = get_current_user_id();
	$user = get_userdata($user_id);

	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);

	$user_budget_left = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
	
	$budgets_in_campaign = get_post_meta($campaign_id, 'budget', true);
	$unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
	if ($unidress_budget > 0) {
		$budget_in_kit = $unidress_budget;
	} else {
		$budget_in_kit = $budgets_in_campaign[$kit_id] ? $budgets_in_campaign[$kit_id] : 0;
	}
	//$min_order_value = get_post_meta($campaign_id, 'min_order_value', true) ?: 0;
	$min_order_charge = get_post_meta($campaign_id, 'min_order_charge', true) ?: 0;
	$subtotal = WC()->cart->get_subtotal(true);
	$shipping_price = get_post_meta($campaign_id, 'shipping_price', true) ?: 0;

	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);
	if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
		$tax =  WC()->cart->get_subtotal_tax();
	}else {
		$tax = 0;
	}

	//  $amount = ($subtotal + $tax + $shipping_price);
	// if($amount < ((int)$budget_in_kit - (int)$user_budget_left)){

	// }
	if($min_order_charge > 0 && $subtotal < $min_order_charge ){
		
		$cart->add_fee('Shipping Price', $shipping_price,true,'standard');
	}
}

//hide quantity to product item : shipping price
add_filter( 'woocommerce_quantity_input_args', 'hide_quantity_input_field', 20, 2 );
function hide_quantity_input_field( $args, $product ) {
	global $wpdb;
	$ship_price = $wpdb->get_results( "SELECT p.ID from $wpdb->posts as p where p.post_title LIKE 'דמי משלוח' ", ARRAY_A);
	$ship_price = $ship_price[0]['ID'];
    // Here set your product IDs in the array
    $product_ids = array($ship_price);

    // Handling product variation
    $the_id = $product->is_type('variation') ? $product->get_parent_id() : $product->get_id();

    // Only on cart page for a specific product category
    if( is_cart() && in_array( $the_id, $product_ids ) ){
        $input_value = $args['input_value'];
        $args['min_value'] = $args['max_value'] = $input_value;
    }
    return $args;
}

//add remove button to product item : shiping
add_filter('woocommerce_cart_item_remove_link', 'customized_cart_item_remove_link', 20, 2 );
function customized_cart_item_remove_link( $button_link, $cart_item_key ){

	global $wpdb;
	$ship_price = $wpdb->get_results( "SELECT p.ID from $wpdb->posts as p where p.post_title LIKE 'דמי משלוח' ", ARRAY_A);
	$ship_price = $ship_price[0]['ID'];
	// Get the current cart item
	$cart_item = WC()->cart->get_cart()[$cart_item_key];
    // If the targeted product is in cart we remove the button link
    if ($cart_item['data']->get_id() == $ship_price) 
        $button_link = '';

    return $button_link;
}




function custom_cart_totals_order_total_html( $amount_total ){
    //$value = '<strong>' . WC()->cart->get_total() . '</strong> ';

	$user_id = get_current_user_id();
	$user = get_userdata($user_id);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
	$user_budget_left = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
	
	$budgets_in_campaign = get_post_meta($campaign_id, 'budget', true);
	$unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
	if ($unidress_budget > 0) {
		$budget_in_kit = $unidress_budget;
	} else {
		$budget_in_kit = $budgets_in_campaign[$kit_id] ? $budgets_in_campaign[$kit_id] : 0;
	}
	$amount_total = (int)WC()->cart->get_subtotal();
	$private_purchase_amount = get_post_meta($campaign_id, 'private_purchase_amount',  true);
	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);

	if(($user_budget_left) > $budget_in_kit){
		//that means that we already used all the budget
		$final_total = $amount_total;
		if( WC()->cart->fee_total){
			$fee = (int)WC()->cart->fee_total;
			$final_total+= $fee;
		}
		if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
			$tax =  WC()->cart->get_subtotal_tax();
			$final_total+= $tax;
		}
	}
	else{
		if($private_purchase_amount){
			$final_total = $amount_total;
			if( WC()->cart->fee_total){
				$fee = (int)WC()->cart->fee_total;
				$final_total+= $fee;
			}
			if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
				$tax =  WC()->cart->get_subtotal_tax();
				$final_total+= $tax;
			}
			if($final_total > ((int)$budget_in_kit - (int)$user_budget_left)){
				$final_total = $final_total - ((int)$budget_in_kit - (int)$user_budget_left);
				
			}
			else{
				$final_total = 0;
			}
		   
		}
		else{
			$final_total = 0;
		} 
		
	}

	$amount_total = wc_price($final_total) ;


    return $amount_total;
}

//add_filter( 'woocommerce_cart_totals_order_total_html', 'custom_cart_totals_order_total_html', 20, 1 );






    

/**
 * Output the variable product add to cart area.
 * show only assign variation
 */
function woocommerce_variable_add_to_cart()
{
	global $product;

	// Enqueue variation scripts.
	wp_enqueue_script('wc-add-to-cart-variation');


	// Get Available variations?
	$get_variations = count($product->get_children()) <= apply_filters('woocommerce_ajax_variation_threshold', 200, $product);

	$available_variations = $get_variations ? $product->get_available_variations() : false;
	$attributes           = $product->get_variation_attributes();
	$selected_attributes  = $product->get_default_attributes();

	$user_id            = get_current_user_id();
	$current_customer   = get_user_meta($user_id, 'user_customer', true);
	$campaign_id        = get_post_meta($current_customer, 'active_campaign', true);
	$product_option     = get_post_meta($campaign_id, 'product_option', true);
	$product_id         = $product->get_id();

	$customer_type      = get_post_meta($current_customer, 'customer_type', true);
	if ($customer_type == 'project') {
		$kit_id = 0;
	} else {
		$kit_id = get_user_meta($user_id, 'user_kit', true);
	}
	// pr($product_option[$kit_id][$product_id]);
	// pr($available_variations);
	// pr($attributes);
	// pr($product_option[$kit_id][$product_id]);
	//if (count($attributes) !== 2) {

	// pr($product_option[$kit_id][$product_id]['pa_color']);
	if (isset($product_option[$kit_id][$product_id]['pa_color'])) {
		// pr(count($available_variations));
		foreach ($available_variations as $index => $variation) {
			if (!in_array($variation['attributes']['attribute_pa_color'], $product_option[$kit_id][$product_id]['pa_color'])) {
				unset($available_variations[$index]);
			}
		}


		// pr(count($available_variations));
	} elseif (isset($product_option[$kit_id][$product_id]['variation'])) {
		foreach ($available_variations as $index => $variation) {
			if (!in_array($variation['variation_id'], $product_option[$kit_id][$product_id]['variation'])) {
				unset($available_variations[$index]);
			}
		}
	}
	// pr($available_variations);
	sort($available_variations);
	//} else {
	// pr($product_option[$kit_id][$product_id]);
	// pr($attributes);
	// foreach ($attributes as $at_key => $at_val) {
	// 	foreach ($at_val as $mk => $mval) {
	// 		if (!in_array($mval, $product_option[$kit_id][$product_id]['variation'][$at_key])) {
	// 			pr($mval);
	// 			unset($attributes[$mk]);
	// 		}
	// 	}
	// }
	//}

	// Load the template.
	wc_get_template(
		'single-product/add-to-cart/variable.php',
		array(
			'available_variations' => $available_variations,
			'attributes'           => $attributes,
			'selected_attributes'  => $selected_attributes,
			'current_customer'     => $current_customer,
		)
	);
}

add_action('woocommerce_product_query', 'unidress_product_query');
function unidress_product_query($q)
{
	// echo get_unidress_list_product();
	$q->set('post__in', (array)get_unidress_list_product());
	$q->set('orderby', 'post__in');
}

//Customer information
add_action('storefront_content_top', 'woocommerce_show_info', 10);
function woocommerce_show_info()
{
	if (!is_admin() && is_user_logged_in() && (is_shop() || is_product_taxonomy())) {

		//user data
		$data['user_id']                    = get_current_user_id();
		$userdata                           = get_userdata($data['user_id']);
		$name                               = $userdata->first_name . ' ' . $userdata->last_name;
		$username                           = $userdata->user_nicename;

		$data['kit_id']                     = get_user_meta($data['user_id'], 'user_kit', true);
		$branch_id                          = get_user_meta($data['user_id'], 'user_branch', true);

		$customer_branch_name               = $branch_id ? get_the_title($branch_id) : '-';
		$user_department                    = get_user_meta($data['user_id'], 'user_department', true);

		$data['customer_id']                = get_user_meta($data['user_id'], 'user_customer', true);
		$data['user_limits']                = get_user_meta($data['user_id'], 'user_limits', true);

		$data['campaign_id']                = get_post_meta($data['customer_id'], 'active_campaign', true);

		$data['product_option']             = get_post_meta($data['campaign_id'], 'product_option', true);
		$data['customer_type']              = get_post_meta($data['customer_id'], 'customer_type', true);
		$data['customer_ordering_style']    = get_ordering_style($data['customer_id']);

		?>
		<div id="user-data-container">
			<div class="user-data-header hidden-xs">unidress</div>
			<table class="table-container">
				<tbody>
					<tr>
						<td>
							<table class="user-info">
								<thead>
									<tr>
										<th colspan="2"><?php echo esc_attr__('User\'s Personal Details', 'unidress') ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="data-item"><?php echo esc_attr__('Name', 'unidress') ?></td>
										<td class="data-item"><?php echo $name ?></td>
									</tr>
									<tr>
										<td class="data-item"><?php echo esc_attr__('Username', 'unidress') ?></td>
										<td class="data-item"><?php echo $username ?></td>
									</tr>
									<tr>
										<td class="data-item"><?php echo esc_attr__('Branch', 'unidress') ?></td>
										<td class="data-item"><?php echo $customer_branch_name ?></td>
									</tr>
									<tr>
										<td class="data-item"><?php echo esc_attr__('Department', 'unidress') ?></td>
										<td class="data-item"><?php echo $user_department ?></td>
									</tr>
								</tbody>
							</table>
						</td>
						<?php do_action('additional_customer_information', $data) ?>
					</tr>
				</tbody>
			</table>
		</div>
	<?php

}
}


/**
 * Cart validation
 *
 * Assign group limit check
 * Required products check
 */

add_filter('woocommerce_update_cart_action_cart_updated', 'unidress_update_cart_validation', 10, 1);
function unidress_update_cart_validation($passed_validation)
{
	update_user_meta(1, '$passed_validation', $passed_validation);

	$old_cart = WC()->session->get('cart', null);
	WC()->cart->calculate_totals();

	//user data
	$user_id = get_current_user_id();
	$user = get_userdata($user_id);
	$user_roles = $user->roles[0];


	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$user_limits        = get_user_meta($user_id, 'user_limits', true);
	$one_order_value    = get_user_meta($user_id, 'one_order_value', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);

	//active campaign data
	$budget_in_campaign     = get_post_meta($campaign_id, 'budget', true);
	$groups_in_campaign     = get_post_meta($campaign_id, 'groups', true);
	$required_products      = get_post_meta($campaign_id, 'required_products', true);
	$product_in_campaign    = get_post_meta($campaign_id, 'product_option', true);
	$one_order_toggle       = get_post_meta($campaign_id, 'one_order_toggle', true);

	$customer_type             = get_post_meta($customer_id, 'customer_type', true);
	$customer_ordering_style   = get_ordering_style($customer_id);

	if (empty($campaign_id) || empty($kit_id)) {
		return;
	}


	//kit data
	$groups_in_kit       = $groups_in_campaign[$kit_id]   ?: array();
	$required_in_kit     = $required_products[$kit_id]    ?: array();
	$product_in_kit      = $product_in_campaign[$kit_id]  ?: array();
	$unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
	if ($unidress_budget > 0) {
		$budget_in_kit = $unidress_budget;
	} else {
		$budget_in_kit = $budget_in_campaign[$kit_id] ? $budget_in_campaign[$kit_id] : 0;
	}
	//$budget_in_kit       = $budget_in_campaign[$kit_id]   ?: 0;

	$get_cart = WC()->cart->get_cart();

	$product_in_cart = array();
	foreach ($get_cart as $product) {
		if (!isset($product_in_cart[$product['product_id']]))
			$product_in_cart[$product['product_id']] = 0;
		$product_in_cart[$product['product_id']] += $product['quantity'];
	}

	if (!is_array($user_limits))
		$user_limits = array();
	update_user_meta(1, '$passed_validation2', $passed_validation);

	global $wpdb;
	$ship_price = $wpdb->get_results( "SELECT p.ID from $wpdb->posts as p where p.post_title LIKE 'דמי משלוח' ", ARRAY_A);
	$ship_price = $ship_price[0]['ID'];
	// Product list check in cart
	if ($product_in_cart && $product_in_kit) {
		foreach ($product_in_cart as $product_id => $product_option) {
			if (!array_key_exists($product_id, $product_in_kit) && ($product_id != $ship_price)) {
				$passed_validation = false;
				$product = wc_get_product($product_id);
				$product_title = $product->get_title();
				wc_add_notice(__('Product "' . $product_title . '" not available for sale', 'unidress'), 'error');
			}
		}
	}
	update_user_meta(1, '$passed_validation3', $passed_validation);

	// budget limits check
	if ($customer_ordering_style == 'standard' && $customer_type == 'campaign') {

        $user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
        $user_budget_left   = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
		$private_purchase_amount = get_post_meta($campaign_id, 'private_purchase_amount',  true);
		$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);
		
		$subtotal = WC()->cart->get_subtotal(true);
		if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
			$tax =  WC()->cart->get_subtotal_tax();
		}else {
			$tax = 0;
		}

		$ordertotal = $subtotal + $tax;

		$amount = $subtotal + $tax;
		$total = WC()->cart->get_totals('total')['total'];
		
		if(!empty($private_purchase_amount) && $private_purchase_amount > 0) {
			$private_amt = $private_purchase_amount;
		}
		else{
			$private_amt = 0;
		}
		$new_budget_in_kit = (float)($budget_in_kit + $private_amt);
        $new_budget_limits = (isset($user_budget_limits[$campaign_id][$kit_id]) ? (int)$user_budget_limits[$campaign_id][$kit_id] : 0) + (int)$amount;
       // $balance = $budget_in_kit - (int)$user_budget_left - $total + $private_purchase_amount;
		//change 04/03/21
		$balance = $budget_in_kit - (int)$user_budget_left - $ordertotal + $private_purchase_amount;
		if ($user_roles != 'hr_manager') {
			// echo 'new_budget_in_kit :'.$new_budget_in_kit;
            // echo 'new_budget_ limit :'.$new_budget_limits; die;
		//change 04/03/21    elicheva 
			if ( $balance < 0) {
				$passed_validation = false;
				wc_add_notice(__('The total amount of the purchase exceeds the balance of your budget', 'unidress'), 'error');
			}
		}
	}
	update_user_meta(1, '$passed_validation4', $passed_validation);

	// Required products check
	if ($customer_type == 'campaign' && $required_in_kit) {
		$required_products_in_cart = array();
		foreach ($product_in_kit as $product_id => $product_options) {
			if (!isset($required_products_in_cart[$product_options['required_products']]))
				$required_products_in_cart[$product_options['required_products']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$required_products_in_cart[$product_options['required_products']] += $product_quantity;
		}

		if ($required_products_in_cart)

			foreach ($required_in_kit as $group_id => $group) {
				$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
				$required_cart = isset($required_products_in_cart[$group_id]) ? (int)$required_products_in_cart[$group_id] : 0;
				$balance = $group['amount'] - $user_limit - $required_cart;

				// if ($balance > 0) {
				// $passed_validation = false;
				// wc_add_notice(  __( 'Need More Essential Products', 'unidress' ), 'error' );
				// break;
				// }
			}
	}
	update_user_meta(1, '$passed_validation5', $passed_validation);

	// Assign group limit check
	if ($customer_type == 'campaign' && $groups_in_kit) {

		$group_in_cart = array();
		foreach ($product_in_kit as $product_id => $product_options) {
			if (!isset($group_in_cart[$product_options['groups']]))
				$group_in_cart[$product_options['groups']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$group_in_cart[$product_options['groups']] += $product_quantity;
		}

		if ($group_in_cart)
			foreach ($groups_in_kit as $group_id => $group) {
				$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
				$group_cart = isset($group_in_cart[$group_id]) ? (int)$group_in_cart[$group_id] : 0;

				$balance = $group['amount'] - $user_limit - $group_cart;

				if ($balance < 0) {
					wc_add_notice(__('You have exceeded the limit by product group', 'unidress'), 'error');
					$passed_validation = false;
					break;
				}
			}
	}
	update_user_meta(1, '$passed_validation6', $passed_validation);

	// Only one order per user
	if ($one_order_toggle[$kit_id] == 'on' && isset($one_order_value[$campaign_id][$kit_id]) && $one_order_value[$campaign_id][$kit_id]) {
		$passed_validation = false;
		wc_add_notice(__('You already buy something', 'unidress'), 'error');
	}

	update_user_meta(1, '$passed_validation7', $passed_validation);

	
	if (!$passed_validation) {
		foreach ($old_cart as $cart_item_key => $values) {

			$quantity = $values['quantity'];
			WC()->cart->set_quantity($cart_item_key, $quantity, false);
		}
	}
	return $passed_validation;
	
}


// Use price from assigning table
add_filter('woocommerce_product_get_price', 'unidress_product_get_price', 15, 2);
// add_filter('woocommerce_get_price_html', 'unidress_product_get_price', 20, 2);
function unidress_product_get_price($price, $product)
{
	if (!is_user_logged_in())
		return $price;

	$product_id         = $product->get_id();
	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
	$budget_by_point 	= get_post_meta($active_campaign, 'budget_by_points',  true);
	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);

	$product_option     = get_post_meta($active_campaign, 'product_option', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);

	
	if ($customer_type == "project") {
		$kit_id      = 0;
	} else {
		$kit_id      = get_user_meta($user_id, 'user_kit', true);
	}

	// if($price_list_include_vat == 1){
	// 	$taxrate = WC_Tax::_get_tax_rate(1);
	// 	$rate = $taxrate['tax_rate'];
		
	// }

	if (isset($product_option[$kit_id][$product_id]['price']) && $product_option[$kit_id][$product_id]['price'] != 0 && $budget_by_point != 1) {
		$pr_price = $product_option[$kit_id][$product_id]['price'];
		
			return $product_option[$kit_id][$product_id]['price'];
		
		
	} 
	


	// if campaign is budget by points send points. 
	if ($budget_by_point == 1 && $product_option[$kit_id][$product_id]['points'] != '') {
		$pr_point = $product_option[$kit_id][$product_id]['points'];
		

			return $product_option[$kit_id][$product_id]['points'];
		
	}

	
	return $price;
}

// if product has price from assigning table, product is purchasable
// add_filter('woocommerce_is_purchasable', 'unidress_product_is_purchasable', 2 , 10);
// function unidress_product_is_purchasable( $purchasable, $product) {
// }


add_filter('woocommerce_variable_price_html', 'woocommerce_show_variation_price', 1,2);
function woocommerce_show_variation_price($price, $variable)
{
     wc_delete_product_transients($variable->get_id());

	$product_id   = $variable->get_id();
	$product = wc_get_product( $product_id );
	$max_price = $product->get_variation_price( 'max', true );
    $min_price = $product->get_variation_price( 'min', true );
	$productprice = get_post_meta($product_id,'_price',true);
	// $get_cart = WC()->cart->get_cart();
	// pr($get_cart);
	if (!is_user_logged_in() || !isset($product_id))
		return $price;

	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
	$budget_by_point 	= get_post_meta($active_campaign, 'budget_by_points',  true);
	$product_option     = get_post_meta($active_campaign, 'product_option', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);
	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);

	
	if ($customer_type == "project") {
		$kit_id      = 0;
	} else {
		$kit_id      = get_user_meta($user_id, 'user_kit', true);
	}
	$rate = '';
	// if($price_list_include_vat == 1){
	// 	$taxrate = WC_Tax::_get_tax_rate(1);
	// 	$rate = $taxrate['tax_rate'];
		
	// }
	
	if (isset($product_option[$kit_id][$product_id]['price']) && $product_option[$kit_id][$product_id]['price'] != 0 && $budget_by_point != 1) {
		$pr_price = $product_option[$kit_id][$product_id]['price'];
	
			return wc_price($product_option[$kit_id][$product_id]['price']);
	
	 }
	
	// if campaign is budget by points send points. 
	if ($budget_by_point == 1 && $product_option[$kit_id][$product_id]['points'] != '') {
		$pr_point = $product_option[$kit_id][$product_id]['points'];
		
			return wc_price($product_option[$kit_id][$product_id]['points']);
		
	}
	

	return $price;
}


/**
 * Change a currency symbol
 */
add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);

function change_existing_currency_symbol($currency_symbol, $currency)
{

	if (!is_admin() && is_user_logged_in()) {
		$user_id            = get_current_user_id();
		$customer_id        = get_user_meta($user_id, 'user_customer', true);
		$active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
		$budget_by_point 	= get_post_meta($active_campaign, 'budget_by_points',  true);

		if ($budget_by_point == 1) {
			$currency_symbol =  esc_attr__('Pts', 'unidress');
		}
	}


	return $currency_symbol;
}


add_filter('woocommerce_product_variation_get_price', 'unidress_product_variation_get_price', 20, 2);

function unidress_product_variation_get_price($price, $product)
{
	if (!is_user_logged_in())
		return $price;

	$product_id         = $product->get_parent_id();
	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
	$budget_by_point 	= get_post_meta($active_campaign, 'budget_by_points',  true);
	$product_option     = get_post_meta($active_campaign, 'product_option', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);
	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);

	if ($customer_type == "project") {
		$kit_id      = 0;
	} else {
		$kit_id      = get_user_meta($user_id, 'user_kit', true);
	}
	$rate = '';
	// if($price_list_include_vat == 1){
	// 	$taxrate = WC_Tax::_get_tax_rate(1);
	// 	$rate = $taxrate['tax_rate'];
		
	// }


	
	if (isset($product_option[$kit_id][$product_id]['price']) && $product_option[$kit_id][$product_id]['price'] != 0 && $budget_by_point != 1) {
		$pr_price = $product_option[$kit_id][$product_id]['price'];
	
			return $product_option[$kit_id][$product_id]['price'];
	 }

	// if campaign is budget by points send points. 
	if ($budget_by_point == 1 && $product_option[$kit_id][$product_id]['points'] != '') {
		$pr_point = $product_option[$kit_id][$product_id]['points'];

		
			return $product_option[$kit_id][$product_id]['points'];
		
	}


	return $price;
}

/* UN2-T37 show price including vat 
*define the woocommerce_get_price_html callback 
*/
function filter_woocommerce_get_price_html( $price, $instance ) { 
    // make filter magic happen here...
    //echo $instance->price;
    $user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
	$budget_by_point 	= get_post_meta($active_campaign, 'budget_by_points',  true);
	$product_option     = get_post_meta($active_campaign, 'product_option', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);
	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);
 
    $check = 'incl' === get_option( 'woocommerce_tax_display_shop' ) || ($price_list_include_vat == 1)?
		wc_get_price_including_tax(
			$instance,
			array(
				//'qty'   => $qty,
				'price' => $instance->price,
			)
		) :
		wc_get_price_excluding_tax(
			$instance,
			array(
				//'qty'   => $qty,
				'price' => $instance->price,
			)
		); 
     return wc_price($check); 
}; 
add_filter( 'woocommerce_get_price_html', 'filter_woocommerce_get_price_html', 10, 2 ); 


/* UN2-T37 show price including vat 
*define the woocommerce_cart_product_price callback 
*/
function filter_woocommerce_cart_product_price( $wc_price, $product ) { 
	 $user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
	$budget_by_point 	= get_post_meta($active_campaign, 'budget_by_points',  true);
	$product_option     = get_post_meta($active_campaign, 'product_option', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);
	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);
 	//woocommerce_tax_display_cart
 	$product_price = 'incl' === get_option( 'woocommerce_tax_display_cart' ) || ($price_list_include_vat == 1) ?
 		wc_get_price_including_tax( $product )
		:
		wc_get_price_excluding_tax( $product );
    
    return wc_price($product_price); 
}; 
         
/*
* UN2- T37 show price including VAT
*/
add_filter( 'woocommerce_cart_product_price', 'filter_woocommerce_cart_product_price', 10, 2 ); 
function filter_woocommerce_cart_product_subtotal( $product_subtotal, $product, $quantity, $instance ) { 
	 $user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
	$budget_by_point 	= get_post_meta($active_campaign, 'budget_by_points',  true);
	$product_option     = get_post_meta($active_campaign, 'product_option', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);
	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);
    if ( $product->is_taxable() ) {

		if ( 'incl' === get_option( 'woocommerce_tax_display_cart' ) || ($price_list_include_vat == 1) ) {
			$row_price        = wc_get_price_including_tax( $product, array( 'qty' => $quantity ) );
			$product_subtotal = wc_price( $row_price );

			if ( ! wc_prices_include_tax() && $instance->get_subtotal_tax() > 0 ) {
				$product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
			}
		} else {
			$row_price        = wc_get_price_excluding_tax( $product, array( 'qty' => $quantity ) );
			$product_subtotal = wc_price( $row_price );

			if ( wc_prices_include_tax() && $instance->get_subtotal_tax() > 0 ) {
				$product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
			}
		}
	} else {
		$price = $product->get_price();
		$row_price        = $price * $quantity;
		$product_subtotal = wc_price( $row_price );
	}
    return $product_subtotal; 
}; 
         
// add the filter 
add_filter( 'woocommerce_cart_product_subtotal', 'filter_woocommerce_cart_product_subtotal', 10, 4 ); 


add_action('woocommerce_product_meta_end', 'add_graphic_option_in_product', 20);
function add_graphic_option_in_product()
{

	global $product;
	//user data
	$user_id            = get_current_user_id();
	$product_id         = $product->get_id();;
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$project_id         = get_post_meta($customer_id, 'active_campaign', true);
	$product_option     = get_post_meta($project_id, 'product_option', true);
	$product_graphics   = isset($product_option[0][$product_id]['graphics']) ? $product_option[0][$product_id]['graphics'] : "";
	$project_graphics   = get_post_meta($project_id, 'project_graphics', true);

	if (empty($product_graphics) ||  empty($project_graphics))
		return;
	$graphics = get_posts(array(
		'include'     => $project_graphics,
		'post_type'   => 'graphic',
		'suppress_filters' => true,
	));
	$assigning_graphics = array();
	foreach ($graphics as $index => $graphic) {
		if (in_array($graphic->ID, $product_graphics))
			$assigning_graphics[$graphic->ID] = $graphic->post_title;
	}
	echo '<div class="unidress-options-graphic">';
	echo '<legend>' . __('Graphics', 'unidress') . '</legend>';
	foreach ($assigning_graphics as $graphic_id => $name) {
		echo '<div class="unidress-graphic">
            <input id="graphic' . $graphic_id . '" type="checkbox" class="input-radio" name="unidress-graphic[]" value="' . $graphic_id . '" checked="checked">
            <label for="graphic' . $graphic_id . '">' . $name . '</label>
        </div>';
	}

	echo '</div>';
}

// matrix
if (get_ordering_style($current_customer) == 'matrix') {
	add_action('woocommerce_single_product_summary', 'get_product_type',  5);
	function get_product_type()
	{
		global $post;
		if (function_exists('get_product')) {
			$product = wc_get_product($post->ID);

			if ($product->is_type('variable')) {
				$attributes = $product->get_variation_attributes();
				if (count(get_array_product_attribute($product, $attributes)) == 2) {
					remove_action('woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30);
					add_action('woocommerce_after_single_product_summary', 'woocommerce_variable_add_to_cart', 5);
				}
				remove_action('woocommerce_after_single_product_summary', 'storefront_single_product_pagination', 30);
			}
		}
	}
}

// Closed list. Change layout
if (get_ordering_style($current_customer) == 'closed_list') {

	// hide price
	add_action('wc_price', 'closed_list_hide_price', 4, 10);
	//add_action('woocommerce_cart_subtotal', 'closed_list_hide_price', 4, 10);
	//add_action('woocommerce_cart_hash', 'closed_list_hide_price', 4, 10);
	function closed_list_hide_price($cart_subtotal, $compound)
	{
		$cart_subtotal = '';
		$compound = '';
	}

	remove_action('woocommerce_before_shop_loop', 'wc_setup_loop');
	add_action('woocommerce_before_shop_loop', 'closed_list_setup_loop');
	function closed_list_setup_loop($args = array())
	{
		$default_args = array(
			'loop'         => 0,
			'columns'      => 3,
			'name'         => '',
			'is_shortcode' => false,
			'is_paginated' => true,
			'is_search'    => false,
			'is_filtered'  => false,
			'total'        => 0,
			'total_pages'  => 0,
			'per_page'     => 0,
			'current_page' => 1,
		);

		// If this is a main WC query, use global args as defaults.
		if ($GLOBALS['wp_query']->get('wc_query')) {
			$default_args = array_merge($default_args, array(
				'is_search'    => $GLOBALS['wp_query']->is_search(),
				'is_filtered'  => is_filtered(),
				'total'        => $GLOBALS['wp_query']->found_posts,
				'total_pages'  => $GLOBALS['wp_query']->max_num_pages,
				'per_page'     => $GLOBALS['wp_query']->get('posts_per_page'),
				'current_page' => max(1, $GLOBALS['wp_query']->get('paged', 1)),
			));
		}

		// Merge any existing values.
		if (isset($GLOBALS['woocommerce_loop'])) {
			$default_args = array_merge($default_args, $GLOBALS['woocommerce_loop']);
		}

		$GLOBALS['woocommerce_loop'] = wp_parse_args($args, $default_args);
	}
}

if (get_customer_type($current_customer) == 'campaign') {

	// Show assign group limits
	//add_action( 'additional_customer_information', 'woocommerce_show_assign_groups_in_shop', 30 );
	function woocommerce_show_assign_groups_in_shop($data)
	{
		if (!is_admin()) {

			//active campaign data
			$groups_in_campaign  = get_post_meta($data['campaign_id'], 'groups', true);
			$product_in_campaign = $data['product_option'];

			if (empty($data['campaign_id']) || empty($data['kit_id']) || !isset($groups_in_campaign[$data['kit_id']])) {
				return;
			}

			//kit data
			$groups_in_kit  = $groups_in_campaign[$data['kit_id']];
			$product_in_kit = $product_in_campaign[$data['kit_id']];

			$get_cart = WC()->cart->get_cart();

			$product_in_cart = array();
			foreach ($get_cart as $product) {
				if (!isset($product_in_cart[$product['product_id']])) {
					$product_in_cart[$product['product_id']] = 0;
				}
				$product_in_cart[$product['product_id']] += $product['quantity'];
			}

			$group_in_cart = array();
			foreach ($product_in_kit as $product_id => $product_options) {
				if (!isset($group_in_cart[$product_options['groups']])) {
					$group_in_cart[$product_options['groups']] = 0;
				}

				$product_quantity                            = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
				$group_in_cart[$product_options['groups']] += $product_quantity;
			}

			if (!is_array($data['user_limits'])) {
				$data['user_limits'] = array();
			}

			?>

			<table class="user-group-limit">
				<thead>
					<tr>
						<th></th>
						<th><?php echo esc_attr__('Limit', 'unidress') ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($groups_in_kit as $group_id => $group) : ?>
						<tr>
							<td><?php echo $group['name'] ?>:</td>
							<td class="data-item center"><?php echo $group['amount'] ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

		<?php

	}
}
}
// Show budget on cart
if ((get_ordering_style($current_customer) == 'standard') && (get_customer_type($current_customer) == 'campaign')) {
	// Update users limits
	add_action('woocommerce_checkout_update_order_meta', 'update_budget_to_user');
	function update_budget_to_user()
	{
		global $wpdb;
		$user_id        = get_current_user_id();
		$customer_id    = get_user_meta($user_id, 'user_customer', true);
		$kit_id         = get_user_meta($user_id, 'user_kit', true);
		$campaign_id    = get_post_meta($customer_id, 'active_campaign', true);

		$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
		$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);

		$total = WC()->cart->get_totals('total')['total'];
		$subtotal = WC()->cart->get_subtotal(true);
		if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
			$tax =  WC()->cart->get_subtotal_tax();
		}else {
			$tax = 0;
		}
		$addfee = get_user_meta( $user_id, 'additional_shipping_fee', true );
		if($subtotal == 0) {
			
			$finaltotal = $subtotal ;
		}else{
			
			$finaltotal = $subtotal + $addfee;
		}
		$amount = $finaltotal + $tax;
		//Clear another campaign budget limit
		$new_budget_limits = array();
		$new_budget_limits[$campaign_id][$kit_id] = (isset($user_budget_limits[$campaign_id][$kit_id]) ? (int)$user_budget_limits[$campaign_id][$kit_id] : 0) + (int)$amount;
		update_user_meta($user_id, 'user_budget_limits', $new_budget_limits);
	}
}

// Check user limits and budget before proceed checkout
add_action('woocommerce_checkout_process', 'check_group_limit');
function check_group_limit()
{
	//user data
	$user_id = get_current_user_id();
	$user = get_userdata($user_id);
	$user_roles = $user->roles[0];

	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$user_limits        = get_user_meta($user_id, 'user_limits', true);
	$one_order_value    = get_user_meta($user_id, 'one_order_value', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);

	//active campaign data
	$budget_in_campaign     = get_post_meta($campaign_id, 'budget', true);
	$groups_in_campaign     = get_post_meta($campaign_id, 'groups', true);
	$required_products      = get_post_meta($campaign_id, 'required_products', true);
	$product_in_campaign    = get_post_meta($campaign_id, 'product_option', true);
	$one_order_toggle       = get_post_meta($campaign_id, 'one_order_toggle', true);

	$customer_type             = get_post_meta($customer_id, 'customer_type', true);
	$customer_ordering_style   = get_ordering_style($customer_id);

	if (empty($campaign_id) || empty($kit_id)) {
		return;
	}

	//kit data
	$groups_in_kit       = $groups_in_campaign[$kit_id]  ?: array();
	$required_in_kit     = $required_products[$kit_id]   ?: array();
	$product_in_kit      = $product_in_campaign[$kit_id] ?: array();
	$unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
	if ($unidress_budget > 0) {
		$budget_in_kit = $unidress_budget;
	} else {
		$budget_in_kit = $budget_in_campaign[$kit_id] ? $budget_in_campaign[$kit_id] : 0;
	}
	//$budget_in_kit       = $budget_in_campaign[$kit_id]   ?: 0;

	$get_cart = WC()->cart->get_cart();

	$product_in_cart = array();
	foreach ($get_cart as $product) {
		if (!isset($product_in_cart[$product['product_id']]))
			$product_in_cart[$product['product_id']] = 0;
		$product_in_cart[$product['product_id']] += $product['quantity'];
	}

	if (!is_array($user_limits))
		$user_limits = array();

	// budget limits check
	if ($customer_type == 'campaign' && $customer_ordering_style == 'standard') {

        $user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
        $user_budget_left   = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
		$private_purchase_amount 	= get_post_meta($campaign_id, 'private_purchase_amount',  true);

		$total = WC()->cart->get_totals('total')['total'];

		if(!empty($private_purchase_amount) && $private_purchase_amount > 0) {
			$private_amt = $private_purchase_amount;
		}
		else{
			$private_amt = 0;
		}
        $new_budget_limits = (isset($user_budget_limits[$campaign_id][$kit_id]) ? (int)$user_budget_limits[$campaign_id][$kit_id] : 0) + (int)$total + $private_amt;

        $subtotal = WC()->cart->get_subtotal(true);
		if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
			$tax =  WC()->cart->get_subtotal_tax();
		}else {
			$tax = 0;
		}
		$ordertotal = $subtotal + $tax;
		//change 04/03/21
		$balance = $budget_in_kit - (int)$user_budget_left - $ordertotal + $private_purchase_amount;
        //$balance = $budget_in_kit - (int)$user_budget_left - $total + $private_purchase_amount;

		if ($user_roles != 'hr_manager') {
            //if ($budget_in_kit < $new_budget_limits) {
            //change 21/01 - elicheva- condition was not right - i don't understand what is new budget limit
            if ($balance < 0) 
                throw new Exception(__('The total amount of the purchase exceeds the balance of your budget', 'unidress'));
		}
	}

	// Required products check
	if ($customer_type == 'campaign' && $required_in_kit) {
		$required_products_in_cart = array();
		foreach ($product_in_kit as $product_id => $product_options) {
			if (!isset($required_products_in_cart[$product_options['required_products']]))
				$required_products_in_cart[$product_options['required_products']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$required_products_in_cart[$product_options['required_products']] += $product_quantity;
		}

		if ($required_products_in_cart)
			foreach ($required_in_kit as $group_id => $group) {
				$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
				$required_cart = isset($required_products_in_cart[$group_id]) ? (int)$required_products_in_cart[$group_id] : 0;

				$balance = $group['amount'] - $user_limit - $required_cart;

				if ($user_roles != 'hr_manager') {
					if ($balance > 0) {
						throw new Exception(sprintf(__('Need More Essential Products. Go to <a href="%s">Cart</a> or <a href="%s">Shop</a>', 'unidress'), esc_url(wc_get_page_permalink('cart')), esc_url(wc_get_page_permalink('shop'))));
						break;
					}
				}
			}
	}

	// Assign group limit check
	if ($customer_type == 'campaign' &&  $groups_in_kit) {

		$group_in_cart = array();
		foreach ($product_in_kit as $product_id => $product_options) {
			if (!isset($group_in_cart[$product_options['groups']]))
				$group_in_cart[$product_options['groups']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$group_in_cart[$product_options['groups']] += $product_quantity;
		}

		if ($group_in_cart)
			foreach ($groups_in_kit as $group_id => $group) {
				$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
				$group_cart = isset($group_in_cart[$group_id]) ? (int)$group_in_cart[$group_id] : 0;

				$balance = $group['amount'] - $user_limit - $group_cart;

				if ($balance < 0) {
					throw new Exception(__('You have exceeded the limit by product group', 'unidress'));
					break;
				}
			}
	}

	// You already buy something check
	if ($one_order_toggle[$kit_id] == 'on' && isset($one_order_value[$campaign_id][$kit_id]) && $one_order_value[$campaign_id][$kit_id]) {
		throw new Exception(__('You already buy something', 'unidress'));
	}

	global $wpdb;
	$ship_price = $wpdb->get_results( "SELECT p.ID from $wpdb->posts as p where p.post_title LIKE 'דמי משלוח' ", ARRAY_A);
	$ship_price = $ship_price[0]['ID'];
	// Product list check in cart
	if ($product_in_cart && $product_in_kit) {
		foreach ($product_in_cart as $product_id => $product_option) {
			if (!array_key_exists($product_id, $product_in_kit) && ($product_id != $ship_price)) {
				$product = wc_get_product($product_id);
				$product_title = $product->get_title();
				throw new Exception(__('Product "' . $product_title . '" not available for sale', 'unidress'));
			}
		}
	}
}

/**
 * ver 2
 * Update user limits
 * Campaign users
 */
add_action('woocommerce_checkout_update_order_meta', 'update_user_limits', 20, 2);
function update_user_limits($order_id, $data)
{

	//user data
	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$user_limits        = get_user_meta($user_id, 'user_limits', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);

	$customer_type      = get_post_meta($customer_id, 'customer_type', true);

	if ($customer_type != 'campaign')
		return;

	//active campaign data
	$groups_in_campaign     = get_post_meta($campaign_id, 'groups', true);
	$required_products      = get_post_meta($campaign_id, 'required_products', true);
	$product_in_campaign    = get_post_meta($campaign_id, 'product_option', true);
	$one_order_toggle       = get_post_meta($campaign_id, 'one_order_toggle', true);

	if (empty($campaign_id) || empty($kit_id)) {
		return;
	}
	if (!isset($product_in_campaign[$kit_id]))
		return;

	$product_in_kit      = $product_in_campaign[$kit_id];

	$get_cart = WC()->cart->get_cart();

	$product_in_cart = array();
	foreach ($get_cart as $product) {
		if (!isset($product_in_cart[$product['product_id']]))
			$product_in_cart[$product['product_id']] = 0;
		$product_in_cart[$product['product_id']] += $product['quantity'];
	}

	if (!is_array($required_products) || !isset($required_products[$kit_id])) {
		$required_products[$kit_id] = array();
	}

	if (!is_array($groups_in_campaign) || !isset($groups_in_campaign[$kit_id])) {
		$groups_in_campaign[$kit_id] = array();
	}

	$new_user_limit_raw = array_keys($required_products[$kit_id]);
	$new_user_limit_raw = array_merge($new_user_limit_raw, array_keys($groups_in_campaign[$kit_id]));

	$new_user_limit = array();
	foreach ($new_user_limit_raw as $key) {
		$new_user_limit[$key] = 0;
	}

	// Required products check
	if ($customer_type == 'campaign') {

		foreach ($product_in_cart as $product_id => $quantity) {

			if (isset($product_in_kit[$product_id]['required_products']) && $product_in_kit[$product_id]['required_products']) {
				$option_id = $product_in_kit[$product_id]['required_products'];
				$new_user_limit[$option_id] += $quantity;
			}
		}
	}

	// Assign group limit check
	if ($customer_type == 'campaign') {

		foreach ($product_in_cart as $product_id => $quantity) {

			if (isset($product_in_kit[$product_id]['groups']) && $product_in_kit[$product_id]['groups']) {
				$option_id = $product_in_kit[$product_id]['groups'];
				$new_user_limit[$option_id] += $quantity;
			}
		}
	}

	// add in $new_user_limit old limit value
	if (is_array($user_limits))
		foreach ($new_user_limit as $option_limit_id => $option_limit_value) {
			if (isset($user_limits[$option_limit_id]))
				$new_user_limit[$option_limit_id] += $user_limits[$option_limit_id];
		}

	update_user_meta(get_current_user_id(), 'user_limits', $new_user_limit);

	//   user can only one order
	if ($one_order_toggle[$kit_id] == 'on') {
		$one_order_value[$campaign_id][$kit_id] = $order_id;
		update_user_meta(get_current_user_id(), 'one_order_value', $one_order_value);
	}
}

//nisl remove tax from cart and checkout
// Remove the Tax Line item from the cart.
function wc_remove_cart_tax_totals( $tax_totals, $instance ) {

	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
	$budget_by_point 	= get_post_meta($active_campaign, 'budget_by_points',  true);
	$product_option     = get_post_meta($active_campaign, 'product_option', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);
	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);

	if ($customer_type == "project") {
		$kit_id      = 0;
	} else {
		$kit_id      = get_user_meta($user_id, 'user_kit', true);
	}
	$rate = '';
	if($price_list_include_vat != 1 && 'excl' === get_option('woocommerce_tax_display_shop')){

		$tax_totals = array();
	}

	return $tax_totals;
}
//add_filter( 'woocommerce_cart_tax_totals', 'wc_remove_cart_tax_totals', 10, 2 );

// Show the cart total excluding tax.
function wc_exclude_tax_cart_total( $total, $instance ) {
	global $wpdb;
	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
	$budget_by_point 	= get_post_meta($active_campaign, 'budget_by_points',  true);
	$product_option     = get_post_meta($active_campaign, 'product_option', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);
	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);
	$min_order_charge = get_post_meta($active_campaign, 'min_order_charge', true) ?: 0;
	if ($customer_type == "project") {
		$kit_id      = 0;
	} else {
		$kit_id      = get_user_meta($user_id, 'user_kit', true);
	}
	$rate = '';

	if($price_list_include_vat != 1 && 'excl' === get_option('woocommerce_tax_display_shop')){

		$total = round( WC()->cart->cart_contents_total + WC()->cart->shipping_total + WC()->cart->fee_total, WC()->cart->dp );

	}
	$subtotal = WC()->cart->get_subtotal(true);
	$addfee = get_user_meta($user_id,'additional_shipping_fee',true);
	if($min_order_charge > 0 && $subtotal < $min_order_charge) {
		$total = round($total - ($subtotal +  WC()->cart->fee_total));
	}else{
		$total = round($total - $subtotal);
	}

	return $total;
}
//add_filter( 'woocommerce_calculated_total', 'wc_exclude_tax_cart_total', 10, 2 );

//add_filter( 'woocommerce_subscriptions_calculated_total', 'wc_exclude_tax_cart_total', 10, 2 );


function get_unidress_list_product()
{
	//user data
	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$one_order_value    = get_user_meta($user_id, 'one_order_value', true);
	$one_order_toggle   = get_post_meta($campaign_id, 'one_order_toggle', true);

	$product_option 	= get_post_meta($campaign_id, 'product_option', true);
	$product_option_order 	= [];

	// pr($product_option);
	// pr($kit_id);
	// pr($campaign_id);
	if (empty($campaign_id) || empty($kit_id)) {
		return 0;
	}
	//active campaign data
	if ($customer_type == "campaign") {
		$product_in_campaign    = get_post_meta($campaign_id, 'add_product_to_campaign', true);


		if (isset($product_in_campaign[$kit_id]))
			$product_list           = json_decode($product_in_campaign[$kit_id]);
	} else {
		$product_list           = get_post_meta($campaign_id, 'add_product_to_project', true);
		$vowels = array("[", "]", "\"", "\\");
		$product_list           = explode(",", str_replace($vowels, "", $product_list));
	}

	if (!isset($product_list) || !$product_list) {
		$product_list = 0;
	}
	// You already buy something check
	// commented for  UN2-T22
	// if ($one_order_toggle && isset($one_order_value[$campaign_id][$kit_id]) && $one_order_value[$campaign_id][$kit_id]) {
	// 	$product_list = 0;
	// }

	// DISPLAY ORDER UN1-T130
	$countPost = 1000;
	foreach ($product_list as $key => $value) {
		// var_dump($value);
		if ($product_option[$kit_id][$value]['order'] == '') {
			$product_option_order[] = $countPost++;
		} else {
			$product_option_order[] = $product_option[$kit_id][$value]['order'];
		}

		// var_dump($product_option[$kit_id][$value]['order']);
	}
	// var_dump($product_option_order);
	$product_list = array_combine($product_option_order, $product_list);
	ksort($product_list);
	// var_dump($product_list);
	return $product_list;
}

//add tab to product page
add_filter('woocommerce_product_tabs', 'product_option_tab');
function product_option_tab($tabs)
{
	global $post;
	$show = false;

	$options = get_post_meta($post->ID, '_product_options', true);
	if ($options) {
		foreach ($options as $taxonomy => $option) {
			if (isset($option['visibility'])) {
				$option = get_post($option['taxonomy_id']);
				if ($option) {
					$show = true;
					break;
				}
			}
		}
	}
	if ($show) {
		$tabs['test_tab'] = array(
			'title' 	=> 'Options',
			'priority' 	=> 50,
			'callback' 	=> 'product_option_tab_content'
		);
	}

	return $tabs;
}
function product_option_tab_content($tabs)
{
	global $post;
	$options = get_post_meta($post->ID, '_product_options', true);
	?>
	<table class="shop_attributes">
		<?php foreach ($options as $taxonomy => $option) : ?>

			<?php if (isset($option['visibility'])) : ?>
				<?php if (isset($option['taxonomy_id'])) : ?>
					<?php $label = wc_attribute_label(get_the_title($option['taxonomy_id']));
					if ($label) : ?>
						<tr>
							<th><?php echo $label; ?></th>
							<td><?php
								$values = array();
								$options_values = get_terms(array(
									'hide_empty' => false,
									'include' => $option['terms'],
								));
								foreach ($options_values as $value) {
									$values[] = $value->name;
								}
								echo apply_filters('woocommerce_attribute', wpautop(wptexturize(implode(', ', $values))), $taxonomy, $values);
								?>
							</td>
						</tr>
					<?php endif; ?>

				<?php else : ?>
					<tr>
						<th><?php echo wc_attribute_label($option['label']); ?></th>
						<td><?php echo apply_filters('woocommerce_attribute', wpautop(wptexturize(implode(', ', $option['terms']))), $option['label'], $option['terms']); ?></td>
					</tr>
				<?php endif; ?>

			<?php endif; ?>

		<?php endforeach; ?>
	</table>

<?php

}

//UN1-T11: Site Logo Defined by Customer
//UN1-T24: Customer Name below the customer's logo
function storefront_site_branding()
{
	?>
	<div class="site-branding">
		<?php
		if (function_exists('the_custom_logo') && has_custom_logo()) {
			$logo = get_custom_logo();
			$html = is_home() ? '<h1 class="logo">' . $logo . '</h1>' : $logo;
		} else {
			$tag = is_home() ? 'h1' : 'div';
			$html = '<' . esc_attr($tag) . ' class="beta site-title"><a href="' . esc_url(home_url('/')) . '" rel="home">' . esc_html(get_bloginfo('name')) . '</a></' . esc_attr($tag) . '>';

			if ('' !== get_bloginfo('description')) {
				$html .= '<p class="site-description">' . esc_html(get_bloginfo('description', 'display')) . '</p>';
			}

			if (is_user_logged_in()) {
				$current_customer = get_user_meta(get_current_user_id(), 'user_customer', true);
				$customer_name = get_the_title($current_customer);
				if ($current_customer) {
					$html .= '<div>' . $customer_name . '</div>';
				}
			}
		}
		echo $html; // WPCS: XSS ok.
		?>
	</div>
	<div class="hidden-xs">
		<?php get_budget_banner(); ?>
	</div>
<?php
}
function get_budget_banner()
{
	global $wpdb;
	$user_id = get_current_user_id();
	$user = get_userdata($user_id);
	$user_roles = $user->roles[0];
	$current_customer = get_user_meta($user_id, 'user_customer', true);

	if ((get_ordering_style($current_customer) == 'standard') && (get_customer_type($current_customer) == 'campaign')) {
		if (!is_admin()) {

			$kit_id = get_user_meta($user_id, 'user_kit', true);
			$customer_id = get_user_meta($user_id, 'user_customer', true);
			$campaign_id = get_post_meta($customer_id, 'active_campaign', true);
			$budget_by_point = get_post_meta($campaign_id, 'budget_by_points',  true);

			$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);
			$private_purchase_amount 	= get_post_meta($campaign_id, 'private_purchase_amount',  true);

			$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
			$user_budget_left = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
			if (empty($campaign_id) || empty($kit_id)) {
				return;
			}
			
			//campaign data
			$budgets_in_campaign = get_post_meta($campaign_id, 'budget', true);
			$unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
			if ($unidress_budget > 0) {
				$budget_in_kit = $unidress_budget;
			} else {
				$budget_in_kit = $budgets_in_campaign[$kit_id] ? $budgets_in_campaign[$kit_id] : 0;
			}
			//pr($user_id);
			//$budget_in_kit = $budgets_in_campaign[$kit_id] ? $budgets_in_campaign[$kit_id] : 0;
			$total = WC()->cart->get_totals('total')['total'];
			$subtotal = WC()->cart->get_subtotal(true);
			if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
				$tax =  WC()->cart->get_subtotal_tax();
			}else {
				$tax = 0;
			}
			$min_order_charge = get_post_meta($campaign_id, 'min_order_charge', true) ?: 0;

			$addfee = get_user_meta( $user_id, 'additional_shipping_fee', true );
			
			if($min_order_charge > 0 && $subtotal < $min_order_charge && $subtotal != 0) {
				
                $finaltotal = $subtotal + WC()->cart->fee_total;
               
			}else{
				
				$finaltotal = $subtotal;
			}
			if ($user_roles != 'hr_manager') {
				?>
				<div class="user-budget-bar"><?php echo esc_attr__('Budget Balance', 'unidress') ?>: <span class="remaining-budget"><?php echo (float)($budget_in_kit - (int)$user_budget_left - ($finaltotal + $tax)  ); ?></span><span class="woocommerce-Price-currencySymbol"> <?php echo get_woocommerce_currency_symbol() ?> </span></div>
			<?php
		}
	}
}
}

add_filter('get_custom_logo',  'custom_logo_url', 0);
function custom_logo_url($html)
{
	$html = '';
	$current_id = get_current_user_id();
	$current_customer = get_user_meta($current_id, 'user_customer', true);
	$user_id = get_current_user_id();
	$kit_id = get_user_meta($user_id, 'user_kit', true);
	$image = get_field('kit_logo', $kit_id);
	$customer_name = get_the_title($current_customer);
	$customer_logo = get_post_meta($current_customer, 'customers_logo', true);

	if (is_user_logged_in()) {
		if ($image) {
			$url = $image['url'];
			// T 167- remove customer name below logo 27/01/2021
			//$html .= '<center><img src="' . $url . '" />' . $customer_name . '</center>';
			$html .= '<center><img src="' . $url . '" /></center>';
		} else {
			if ($customer_logo) {
				$url = network_site_url();
				$html = sprintf(
					'<a href="%1$s" class="custom-logo-link" rel="home" itemprop="url">%2$s</a>',
					esc_url($url),
					wp_get_attachment_image($customer_logo, 'full', false, array(
						'class' => 'custom-logo',
					))
				);
			}
		}
	}
	return $html;
}

function get_array_product_attribute($product, $attributes)
{

	$index = 0;
	$terms_name_array = array();

	foreach ($attributes as $attribute_name => $options) {

		if (taxonomy_exists($attribute_name)) {
			$terms = wc_get_product_terms($product->get_id(), $attribute_name, array(
				'fields' => 'all',
			));
			foreach ($terms as $key => $term) {
				if (in_array($term->slug, $options, true)) {
					$terms_name_array[$index][$term->slug] = $term->name;
				}
			}
		} else {
			$options = $attributes[$attribute_name];
			foreach ($options as $key => $term) {
				$terms_name_array[$index][$term] = $term;
			}
		}

		$index++;
	}

	return $terms_name_array;
}

/**
 * Output the custom add to cart button for variations.
 */
function woocommerce_single_custom_variation_add_to_cart_button()
{
	wc_get_template('single-product/add-to-cart/custom-variation-add-to-cart-button.php');
}


add_action('storefront_homepage',  'storefront_homepage_header2', 10);
add_action('storefront_homepage',  'storefront_page_content2', 20);

/**
 * Display the page header without the featured image
 */
function storefront_homepage_header2()
{ }

/**
 * Display the post content
 */
function storefront_page_content2()
{

	$user_id            = get_current_user_id();
	$current_customer   = get_user_meta($user_id, 'user_customer', true);
	$campaign_id        = get_post_meta($current_customer, 'active_campaign', true);

	setup_postdata($campaign_id);

	?>
	<div class="entry-content">
		<?php the_content(); ?>
		<?php
		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . __('Pages:', 'storefront'),
				'after'  => '</div>',
			)
		);
		?>
		<div class="button-go-shop-wrapper">
			<a class="button button-go-shop" href="<?php echo get_permalink(wc_get_page_id('shop'));  ?>"> <?php echo __('Go to Marketplace', 'unidress') ?> </a>
		</div>
	</div><!-- .entry-content -->

	<?php

	wp_reset_postdata();
}

// disable billing information in checkout
// add shipping rule
add_filter('woocommerce_cart_needs_shipping', function ($field) {
	return 0;
}, 1, 10);

$user_id            = get_current_user_id();
$customer_id        = get_user_meta($user_id, 'user_customer', true);
$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
$shipping_allow     = get_post_meta($campaign_id, 'shipping_allow', true);
$shipping_private_address     = get_post_meta($campaign_id, 'shipping_private_address', true);
if ($shipping_private_address == "") {
	add_filter('woocommerce_checkout_fields', function ($field) {
		global $woocommerce;

		$user_id                = get_current_user_id();
		$customer_id            = get_user_meta($user_id,     'user_customer', true);
		$campaign_id            = get_post_meta($customer_id, 'active_campaign', true);
		$shops_checked          = get_post_meta($campaign_id, 'shops', true);
		$required               = $shops_checked ? true : false;
		$user_first_name        = get_user_meta($user_id, 'first_name', true);
		$user_last_name         = get_user_meta($user_id, 'last_name', true);
		$user_billing_email     = get_user_meta($user_id, 'billing_email', true);
		$user_billing_phone     = get_user_meta($user_id, 'billing_phone', true);

		$billing_clear_first_last = get_post_meta($campaign_id, 'billing_clear_first_last', true) ? 'on' : 'off';
		$billing_clear_email = get_post_meta($campaign_id, 'billing_clear_email', true) ? 'on' : 'off';
		$billing_clear_phone = get_post_meta($campaign_id, 'billing_clear_phone', true) ? 'on' : 'off';

		if (isset($field['billing']['billing_country']))
			unset($field['billing']['billing_country']);
		unset($field['billing']['billing_address_1']);
		unset($field['billing']['billing_address_2']);
		unset($field['billing']['billing_city']);
		unset($field['billing']['billing_state']);
		unset($field['billing']['billing_postcode']);
		unset($field['billing']['billing_company']);
		$field['unidress_shipping'] = array(
			'unidress_shipping' => array(
				'class'       => array('notes'),
				'required'    => true,
				'label'       => __('Choose Shipping', 'unidress'),
			),
		);

		if ($billing_clear_first_last == 'on') {
			$field['billing']['billing_first_name']['default'] = NULL;
			$field['billing']['billing_last_name']['default'] = NULL;
			$woocommerce->session->customer['first_name'] = '';
			$woocommerce->session->customer['last_name'] = '';
		} else {
			$field['billing']['billing_first_name']['custom_attributes']['readonly'] = 'readonly';
			$field['billing']['billing_last_name']['custom_attributes']['readonly'] = 'readonly';
			$field['billing']['billing_first_name']['default'] = $user_first_name;
			$field['billing']['billing_last_name']['default'] = $user_last_name;
		}

		if ($billing_clear_email == 'on') {
			$field['billing']['billing_email']['default'] = NULL;
			$field['billing']['billing_email']['required'] = true;
			$woocommerce->session->customer['email'] = '';
		} else {
			$field['billing']['billing_email']['default'] = $user_billing_email;
			$field['billing']['billing_email']['required'] = false;
		}

		if ($billing_clear_phone == 'on') {
			$field['billing']['billing_phone']['default'] = NULL;
			$woocommerce->session->customer['phone'] = '';
		} else {
			$field['billing']['billing_phone']['default'] = $user_billing_phone;
		}

		return $field;
	}, 4, 10);
}

//remove company and country field for private shipping address
function custom_override_checkout_fields( $fields ) {
	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
	$shipping_allow     = get_post_meta($campaign_id, 'shipping_allow', true);
	$shipping_private_address     = get_post_meta($campaign_id, 'shipping_private_address', true);
	if($shipping_private_address == "on"){
		unset($fields['billing']['billing_country']);
		unset($fields['billing']['billing_company']);
	}
	return $fields;
}
	
add_filter('woocommerce_checkout_fields','custom_override_checkout_fields');




// CHANGE EMAIL UN1-T130
add_action('woocommerce_after_checkout_form', function () {
	?><script type="text/javascript">
		document.getElementById('billing_email').value = '';
	</script><?php
		});

		add_action('woocommerce_checkout_order_processed', 'add_unidress_shipping_to_order', 10, 4);
		function add_unidress_shipping_to_order($order_id, $posted_data, $order)
		{
			update_post_meta($order_id, 'unidress_shipping', $posted_data['unidress_shipping']);
		}

		// UN1-T62: Hide Suggestion
		add_action('init', 'remove_action_unidress');
		function remove_action_unidress()
		{
			// Hide from Products Page
			remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

			// Hide from Welcome Page:
			remove_action('homepage', 'storefront_product_categories', 20);
			remove_action('homepage', 'storefront_recent_products', 30);
			remove_action('homepage', 'storefront_featured_products', 40);
			remove_action('homepage', 'storefront_popular_products', 50);
			//remove_action( 'homepage', 'storefront_on_sale_products', 60 );
			remove_action('homepage', 'storefront_best_selling_products', 70);
		}

		// Cancel add to cart if the limit check failed
		add_action('woocommerce_add_to_cart_validation', 'unidress_add_to_cart_validation', 20, 3);
		function unidress_add_to_cart_validation($output, $add_product_id, $add_quantity)
		{


			$user_id = get_current_user_id();
			$user = get_userdata($user_id);
			$user_roles = $user->roles[0];

			$customer_id        = get_user_meta($user_id, 'user_customer', true);
			$kit_id             = get_user_meta($user_id, 'user_kit', true);
			$user_limits        = get_user_meta($user_id, 'user_limits', true);
			$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);

			//active campaign data
			$groups_in_campaign     = get_post_meta($campaign_id, 'groups', true);
			$product_in_campaign    = get_post_meta($campaign_id, 'product_option', true);

			$customer_type             = get_post_meta($customer_id, 'customer_type', true);
			$customer_ordering_style   = get_ordering_style($customer_id);

			if (empty($campaign_id) || empty($kit_id)) {
				return;
			}
			// pr($campaign_id);
			// pr($groups_in_campaign);
			// die;

			//kit data
			$groups_in_kit       = $groups_in_campaign[$kit_id]       ?: array();
			$product_in_kit      = $product_in_campaign[$kit_id]      ?: array();

			$get_cart = WC()->cart->get_cart();

			$product_in_cart = array();
			foreach ($get_cart as $product) {
				if (!isset($product_in_cart[$product['product_id']]))
					$product_in_cart[$product['product_id']] = 0;
				$product_in_cart[$product['product_id']] += $product['quantity'];
			}

			if (!is_array($user_limits))
				$user_limits = array();



			// Assign group limit check
			if ($customer_type == 'campaign') {
				$group_in_cart = array();
				foreach ($product_in_kit as $product_id => $product_options) {
					if (!isset($group_in_cart[$product_options['groups']]))
						$group_in_cart[$product_options['groups']] = 0;

					$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
					$group_in_cart[$product_options['groups']] += $product_quantity;

					if ($product_id == $add_product_id)
						$group_in_cart[$product_options['groups']] += $add_quantity;
				}

				if ($group_in_cart)
					foreach ($groups_in_kit as $group_id => $group) {
						$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
						$group_cart = isset($group_in_cart[$group_id]) ? (int)$group_in_cart[$group_id] : 0;

						$balance = $group['amount'] - $user_limit - $group_cart;

						if ($balance < 0) {
							wc_add_notice(__('You are not allowed to order more', 'unidress') . ' "' . $groups_in_kit[$group_id]['name'] . '"', 'error');
							$output = false;
							wp_redirect(site_url().'/shop/');
							exit;
							//break;
						}
					}

				// Budget limit check in cart
				if ($customer_ordering_style == 'standard' && $customer_type == 'campaign') {
					$budgets_in_campaign = get_post_meta($campaign_id, 'budget', true);
					$budget_by_point 	= get_post_meta($campaign_id, 'budget_by_points',  true);
					$private_purchase_amount 	= get_post_meta($campaign_id, 'private_purchase_amount',  true);
					$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);
					
					//$budget_in_kit = $budgets_in_campaign[$kit_id] ?: 0;
					$unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
					if ($unidress_budget > 0) {
						$budget_in_kit = $unidress_budget;
					} else {
						$budget_in_kit = $budgets_in_campaign[$kit_id] ? $budgets_in_campaign[$kit_id] : 0;
					}

					$user_budget_limits         = get_user_meta($user_id, 'user_budget_limits', true);
					$user_budget_left           = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;

					$price_filed = ($budget_by_point == 1) ? 'points' : 'price';

					$product_price_added        = (isset($product_in_kit[$add_product_id][$price_filed]) &&  $product_in_kit[$add_product_id][$price_filed]) ? $product_in_kit[$add_product_id][$price_filed] : get_post_meta($add_product_id, '_price', true);
					$vat_rate = $price_list_include_vat == 1 ? 1.17 : 1.0;
					$product_price_added_total  = $vat_rate * $product_price_added * $add_quantity;
					$total = WC()->cart->get_totals('total')['total'];

					$subtotal = WC()->cart->get_subtotal(true);
					if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
						$tax =  WC()->cart->get_subtotal_tax();
					}else {
						$tax = 0;
					}
					global $wpdb;
					$usercoupon = get_user_meta($user_id,'last_used_coupon',true);
					//echo $usercoupon;
					//$coupon_results = $wpdb->get_results( "SELECT p.ID,p.post_title,p.post_author,p.post_status from $wpdb->posts as p where p.post_title LIKE '%{$usercoupon}%' and p.post_author = {$user_id} AND p.post_status = 'publish' ",ARRAY_A);
					//$additionalfee = get_post_meta( $coupon_results[0]['ID'], 'coupon_amount' ,true);
					
					$ordertotal = $subtotal + $tax + $product_price_added_total;

					if(!empty($private_purchase_amount) && $private_purchase_amount > 0 ) {
                        
                        // elicheva - 20/01 the balance was not right,
                        //i don't understand how they calculate the balance
                        $balance = $budget_in_kit - (int)$user_budget_left - $ordertotal + $private_purchase_amount ;

						/*$halfbalance = $budget_in_kit - (int)$user_budget_left + $private_purchase_amount;
						
						if($ordertotal > $halfbalance) {
							$show_error = 1;
						}else{
							$show_error = 0;
						}*/
					}else {
                        //$balance = $budget_in_kit - (int)$user_budget_left - $ordertotal - $product_price_added_total;
                        // elicheva - 20/01 the balance was not right,
                        //i don't understand how they calculate the balnce
                        //$balance = $budget_in_kit - (int)$user_budget_left - $subtotal - $product_price_added_total;
						$balance = $budget_in_kit - (int)$user_budget_left - $ordertotal;
						/*if($ordertotal > $balance  && (empty($private_purchase_amount) || $private_purchase_amount == 0)) {
							$show_error = 1;
						}else{
							$show_error = 0;
						}*/
						

					}
					//pr($balance);
					if ($user_roles != 'hr_manager') {
						//if ($show_error == 1) {
						if($balance < 0) {
                            //echo 'enter balance negative';
							wc_add_notice(__('You are exceeding your budget, this product cannot be added to cart.', 'unidress'), 'error');
							$output = false;
						}
					}
				}
			}

			return $output;
		}

		// Redirect if the limit check failed
		function check_proceed_to_checkout()
		{

			$output = false;

			$user_id = get_current_user_id();
			$user = get_userdata($user_id);
			$user_roles = $user->roles[0];

			$customer_id        = get_user_meta($user_id, 'user_customer', true);
			$kit_id             = get_user_meta($user_id, 'user_kit', true);
			$user_limits        = get_user_meta($user_id, 'user_limits', true);
			$one_order_value    = get_user_meta($user_id, 'one_order_value', true);
			$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);

			//active campaign data
			$groups_in_campaign     = get_post_meta($campaign_id, 'groups', true);
			$required_products      = get_post_meta($campaign_id, 'required_products', true);
			$product_in_campaign    = get_post_meta($campaign_id, 'product_option', true);
			$one_order_toggle       = get_post_meta($campaign_id, 'one_order_toggle', true);

			$customer_type             = get_post_meta($customer_id, 'customer_type', true);
			$customer_ordering_style   = get_ordering_style($customer_id);

			if (empty($campaign_id) || empty($kit_id)) {
				return;
			}

			//kit data
			$groups_in_kit       = $groups_in_campaign[$kit_id]       ?: array();
			$required_in_kit     = $required_products[$kit_id]        ?: array();
			$product_in_kit      = $product_in_campaign[$kit_id]      ?: array();

			$get_cart = WC()->cart->get_cart();

			$product_in_cart = array();
			foreach ($get_cart as $product) {
				if (!isset($product_in_cart[$product['product_id']]))
					$product_in_cart[$product['product_id']] = 0;
				$product_in_cart[$product['product_id']] += $product['quantity'];
			}

			if (!is_array($user_limits))
				$user_limits = array();

			// Required products check
			if ($customer_type == 'campaign' && $required_in_kit) {
				$required_products_in_cart = array();
				foreach ($product_in_kit as $product_id => $product_options) {
					if (!isset($required_products_in_cart[$product_options['required_products']]))
						$required_products_in_cart[$product_options['required_products']] = 0;

					$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
					$required_products_in_cart[$product_options['required_products']] += $product_quantity;
				}

				if ($required_products_in_cart)
					foreach ($required_in_kit as $group_id => $group) {
						$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
						$required_cart = isset($required_products_in_cart[$group_id]) ? (int)$required_products_in_cart[$group_id] : 0;

						$balance = $group['amount'] - $user_limit - $required_cart;
						if ($user_roles != 'hr_manager') {
							if ($balance > 0) {
								$output = true;
								wc_add_notice(sprintf(__('Need More Essential Products. Go to <a href="%s">Cart</a> or <a href="%s">Shop</a>', 'unidress'), esc_url(wc_get_page_permalink('cart')), esc_url(wc_get_page_permalink('shop'))), 'error');
								break;
							}
						}
					}
			}

			// Assign group limit check
			if ($customer_type == 'campaign' && $groups_in_kit) {
				$group_in_cart = array();
				foreach ($product_in_kit as $product_id => $product_options) {
					if (!isset($group_in_cart[$product_options['groups']]))
						$group_in_cart[$product_options['groups']] = 0;

					$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
					$group_in_cart[$product_options['groups']] += $product_quantity;
				}

				if ($group_in_cart)
					foreach ($groups_in_kit as $group_id => $group) {
						$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
						$group_cart = isset($group_in_cart[$group_id]) ? (int)$group_in_cart[$group_id] : 0;

						$balance = $group['amount'] - $user_limit - $group_cart;

						if ($balance < 0) {
							wc_add_notice(__('You have exceeded the limit by product group', 'unidress'), 'error');
							$output = true;
							break;
						}
					}
			}

			// You already buy something check
			if ($one_order_toggle[$kit_id] == 'on' && isset($one_order_value[$campaign_id][$kit_id]) && $one_order_value[$campaign_id][$kit_id]) {
				$output = true;
				wc_add_notice(__('You already buy something', 'unidress'), 'error');
			}
			global $wpdb;
			$ship_price = $wpdb->get_results( "SELECT p.ID from $wpdb->posts as p where p.post_title LIKE 'דמי משלוח' ", ARRAY_A);
			$ship_price = $ship_price[0]['ID'];
			// Product list check in cart
			if ($product_in_cart && $product_in_kit) {
				foreach ($product_in_cart as $product_id => $product_option) {
					if (!array_key_exists($product_id, $product_in_kit) && ($product_id != $ship_price)) {
						$product = wc_get_product($product_id);
						$product_title = $product->get_title();
						wc_add_notice(__('Product "' . $product_title . '" not available for sale', 'unidress'), 'error');
						$output = true;
					}
				}
			}

			// Budget limit check in cart
			if ($customer_ordering_style == 'standard' && $customer_type == 'campaign') {

				$budgets_in_campaign    = get_post_meta($campaign_id, 'budget', true);
				$unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
				if ($unidress_budget > 0) {
					$budget_in_kit = $unidress_budget;
				} else {
					$budget_in_kit = $budgets_in_campaign[$kit_id] ? $budgets_in_campaign[$kit_id] : 0;
				}
				//$budget_in_kit          = $budgets_in_campaign[$kit_id] ?: 0;


				$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
				$user_budget_left   = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
				if (empty($campaign_id) || empty($kit_id)) {
					return;
				}
				$private_purchase_amount 	= get_post_meta($campaign_id, 'private_purchase_amount',  true);
				$subtotal = WC()->cart->get_subtotal(true);
                //$total = WC()->cart->get_totals('total')['total'];
                $total = WC()->cart->get_cart_total();
				//change 04/03/21 add tax to subtotal 
				if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
					$tax =  WC()->cart->get_subtotal_tax();
				}else {
					$tax = 0;
				}
				$ordertotal = $subtotal + $tax;
                //$balance = $budget_in_kit - (int)$user_budget_left - $total + $private_purchase_amount;
                //change 21/01 - balance was not right
                $balance = $budget_in_kit - (int)$user_budget_left - $ordertotal + $private_purchase_amount ;
        

				if(!empty($private_purchase_amount) && $private_purchase_amount > 0) {
					$private_amt = $private_purchase_amount;
				}
				else{
					$private_amt = 0;
				}
                $unidress_budget =  $budget_in_kit + $private_amt;

                $new_budget_limits = (isset($user_budget_limits[$campaign_id][$kit_id]) ? (int)$user_budget_limits[$campaign_id][$kit_id] : 0) + (int)$total + $private_amt;
                
				if ($user_roles != 'hr_manager') {
					//if ($budget_in_kit < $new_budget_limits) {
                    //change 21/01 - elicheva- condition was not right - i don't understand what is new budget limit
					if ($balance < 0) {
						wc_add_notice(__('The total amount of the purchase exceeds the balance of your budget', 'unidress'), 'error');
						$output = true;
					}
				}
			}

			// UN2-T10 : Shipping price per campaign
			$min_order_value = get_post_meta($campaign_id, 'min_order_value_nisl', true) ?: 0;
			$min_order_charge = get_post_meta($campaign_id, 'min_order_charge', true) ?: 0;
			$shipping_price = get_post_meta($campaign_id, 'shipping_price', true) ?: 0;

			if ($min_order_value > 0) {
				if ($subtotal < $min_order_value) {
					wc_add_notice(wp_sprintf(__("You can not complete the order if the total price is less than %d", "unidress"), $min_order_value), 'error');
					$output = true;
				}
				
			}

			return $output;
		}
		add_action('login_footer', function () {
			?>
	<script type="text/javascript">
		document.addEventListener('DOMContentLoaded', function() {
			let p = document.createElement('p');
			p.setAttribute('id', 'backtoblog');
			p.innerHTML = '<a href="https://www.unidress.co.il/"> <?php echo __('&larr; To Unidress Main Site', 'unidress') ?> </a>';
			document.getElementById('backtoblog').replaceWith(p);
		});
	</script>
<?php
});

function storefront_credit()
{
	?>
	<div class="site-info">
		<?php echo esc_html(apply_filters('storefront_copyright_text', $content = '&copy; ' . get_bloginfo('name') . ' ' . date('Y'))); ?>
		<?php if (apply_filters('storefront_credit_link', true)) { ?>
			<br />
			<?php
			if (apply_filters('storefront_privacy_policy_link', true) && function_exists('the_privacy_policy_link')) {
				the_privacy_policy_link('', '<span role="separator" aria-hidden="true"></span>');
			}
			?>
		<?php } ?>
	</div><!-- .site-info -->
<?php
}

add_action('storefront_header', 'unidress_new_search_container', 60);
function unidress_new_search_container()
{
	do_action('unidress_new_search_container');
}

add_action('template_redirect', 'unidress_header_template');
function unidress_header_template()
{
	//add unidress logo
	add_action('storefront_header', function () {
        $custom_logo_id = get_theme_mod( 'custom_logo' );
		$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
		echo '<div class="unidress-logo"><img src="'.$image[0].'" alt=""></div>';
	}, 40);

	//move search bat to cart
	remove_action('storefront_header', 'storefront_product_search', 40);
	remove_action('storefront_header', 'storefront_header_cart', 60);
	add_action('unidress_new_search_container', function () {
		echo '<div class="unidress-menu-element">';
	}, 10);
	add_action('unidress_new_search_container', 'storefront_product_search', 20);
	add_action('unidress_new_search_container', 'storefront_header_cart', 30);
	add_action('unidress_new_search_container', function () {
		echo '</div>';
	}, 40);

	add_action('storefront_before_content', function () {
		echo '<div class="user-budget-bar-wrapper visible-xs mb-10">';
		get_budget_banner();
		echo '</div>';
	}, 70);
}

// redirect to shop page after add_to_cart
add_filter('woocommerce_cart_redirect_after_error', 'unidress_shopping_redirect_to_shop_error', 30, 2);
add_filter('woocommerce_add_to_cart_redirect', 'unidress_shopping_redirect_to_shop', 30, 2);
function unidress_shopping_redirect_to_shop_error($url, $adding_to_cart)
{
	$url = wc_get_page_permalink('shop');
	return $url;
}
function unidress_shopping_redirect_to_shop($url, $adding_to_cart)
{
	if (is_product())
		$url = wc_get_page_permalink('shop');
	return $url;
}

// enable Order notes
add_filter('woocommerce_enable_order_notes_field', 'unidress_enable_order_notes_field', 10, 2);
function unidress_enable_order_notes_field($enable)
{
	if(!is_admin()){
	$user_id                = get_current_user_id();
	$customer_id            = get_user_meta($user_id, 'user_customer', true);
	$campaign_id            = get_post_meta($customer_id, 'active_campaign', true);
	$enable_order_notes     = get_post_meta($campaign_id, 'enable_order_notes', true);

	$enable = $enable_order_notes ? true : false;
	}
	return $enable;
}

/**
 * Display the page header
 */
function storefront_page_header()
{
	if (is_front_page() && is_page_template('template-fullwidth.php')) {
		return;
	}

	$user_id            = get_current_user_id();
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
	$one_order_toggle   = get_post_meta($campaign_id, 'one_order_toggle', true);
	$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
	$user_budget_left   = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;

	$budgets_in_campaign     = get_post_meta($campaign_id, 'budget', true);
	$unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
	if ($unidress_budget > 0) {
		$budget_in_kit = $unidress_budget;
	} else {
		$budget_in_kit = $budgets_in_campaign[$kit_id] ? $budgets_in_campaign[$kit_id] : 0;
	}
	//$budget_in_kit    = $budgets_in_campaign[$kit_id] ?: 0;

	$total = WC()->cart->get_totals('total')['total'];
	$budget = $budget_in_kit - (int)$user_budget_left - $total;
	?>
	<header class="entry-header">
		<?php
		storefront_post_thumbnail('full');
		the_title('<h1 class="entry-title">', '</h1>');
		if (is_cart()) {
			// You already buy something check
			if ($one_order_toggle[$kit_id] == 'on') {
				if ($budget > 0) {
					echo '<p class="unidress-entry-message">' . __('Dear employee, only one order can be placed. Please make sure you have selected all the items you want.', 'unidress') . '</p>';
				}
			}
		}
		?>
	</header><!-- .entry-header -->
<?php
}

// UN1-T100: Hide pages in My Account and make read only
add_filter('woocommerce_account_menu_items', 'unidress_edit_account_menu_items', 10, 2);
function unidress_edit_account_menu_items($items, $endpoints)
{
	$disable_items = array(
		'dashboard',
		'downloads',
		'edit-address'
	);
	foreach ($disable_items as $item) {
		if (isset($items[$item])) unset($items[$item]);
	}
	return $items;
}

//UN1-T102: Fix the mobile menu
add_action('storefront_header', 'unidress_remove_handheld_footer_bar', 10);
function unidress_remove_handheld_footer_bar()
{
	remove_action('storefront_footer', 'storefront_handheld_footer_bar', 999);
}
add_action('storefront_before_header', 'unidress_add_header_bar', 10);
function unidress_add_header_bar()
{
	?>
	<div class="unidress-mobile-header">
		<div class="unidress-mobile-header-bar">
			<div class="menu-burger">
				<span></span>
				<span></span>
				<span></span>
			</div>
			<!-- <div class="menu-logo">
				<img src="<?//php echo plugins_url('/unidress/unidress-logo-white.png') ?>">
			</div> -->
			<?php
				$custom_logo_id = get_theme_mod( 'custom_logo' );
				$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
				echo '<div class="menu-logo"><img src="'.$image[0].'" alt=""></div>';
			?>
			<div class="my-account">
				<a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))) ?>"></a>
			</div>
			<div class="search">
				<a></a>
			</div>
			<div class="cart">
				<a class="footer-cart-contents" href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php esc_attr_e('View your shopping cart', 'storefront'); ?>">
					<span class="count"><?php echo wp_kses_data(WC()->cart->get_cart_contents_count()); ?></span>
				</a>
			</div>
		</div>
		<?php
		wp_nav_menu(
			array(
				'theme_location'  => 'handheld',
				'container_class' => 'handheld-navigation',
			)
		);
		storefront_product_search();
		?>
	</div>
<?php
}
add_action('woocommerce_after_checkout_billing_form', 'woocommerce_after_checkout_billing_form_function');
function woocommerce_after_checkout_billing_form_function()
{
	$user_id                = get_current_user_id();
	$customer_id            = get_user_meta($user_id,     'user_customer', true);
	$campaign_id            = get_post_meta($customer_id, 'active_campaign', true);

	$billing_clear_first_last = get_post_meta($campaign_id, 'billing_clear_first_last', true) ? 'on' : 'off';
	$billing_clear_email = get_post_meta($campaign_id, 'billing_clear_email', true) ? 'on' : 'off';
	$billing_clear_phone = get_post_meta($campaign_id, 'billing_clear_phone', true) ? 'on' : 'off';

	if ($billing_clear_first_last == 'on') {
		echo "<script>jQuery('#billing_first_name').val('');</script>";
		echo "<script>jQuery('#billing_last_name').val('');</script>";
	}

	if ($billing_clear_email == 'on') {
		echo "<script>jQuery('#billing_phone').val('');</script>";
	}

	if ($billing_clear_phone == 'on') {
		echo "<script>jQuery('#billing_email').val('');</script>";
	}
}


/**
 * Add custom field to add to cart only on loop not on single page. 
 */
function nipl_woocommerce_before_add_to_cart_button()
{
	if (!is_user_logged_in())
		return;

	global $product;
	$pType = $product->get_type();

	$product_id         = $product->get_id();
	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
	$budget_by_point 	= get_post_meta($active_campaign, 'budget_by_points',  true);

	$product_option     = get_post_meta($active_campaign, 'product_option', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);


	if ($customer_type == "project") {
		$kit_id      = 0;
	} else {
		$kit_id      = get_user_meta($user_id, 'user_kit', true);
	}

	$pSimpleField = $product_option[$kit_id][$product_id]['uni_simple_field'];
	if (($pType == 'simple' && $pSimpleField != '') || ($pType == 'variable' && $pSimpleField != '' && is_singular('product'))) {
		$simple_vals = array_map('trim', explode(',', $pSimpleField));
		$simple_options = "<option value=''>" . __('Choose an option', 'unidress') . "</option>";
		foreach ($simple_vals as $sv) {
			$simple_options .= "<option value='$sv'>$sv</option>";
		}
		echo "<span class='nipl_simple_option_wrp'><select required name='prd_simple_option'>$simple_options</select></span>";
	}
}

add_action('woocommerce_before_add_to_cart_button', 'nipl_woocommerce_before_add_to_cart_button', 10);


/**
 * Store custom meta in cart 
 */
function nipl_woocommerce_add_cart_item_data($cart_item_data, $product_id, $variation_id)
{

	if (isset($_POST['prd_simple_option'])) {
		$cart_item_data['prd_simple_option'] = sanitize_text_field($_POST['prd_simple_option']);
	}
	return $cart_item_data;
}
add_action('woocommerce_add_cart_item_data', 'nipl_woocommerce_add_cart_item_data',  10, 3);





/**
 * Show custom order data in cart page. 
 */
function nipl_woocommerce_get_item_data($item_data, $cart_item_data)
{
	if (isset($cart_item_data['prd_simple_option'])) {
		$item_data[] = array(
			'key'     => __('test', 'plugin-republic'),
			'value'   => wc_clean($cart_item_data['prd_simple_option'])
		);
	}
	return $item_data;
}
// add_filter('woocommerce_get_item_data', 'nipl_woocommerce_get_item_data', 10, 2);


/**
 * Add custom simple option to order meta. 
 */
function nipl_woocommerce_checkout_create_order_line_item($item, $cart_item_key, $values, $order)
{
	if (isset($values['prd_simple_option'])) {
		$item->add_meta_data(
			__('Simple Option', 'unidress'),
			$values['prd_simple_option'],
			true
		);
	}
}
add_action('woocommerce_checkout_create_order_line_item', 'nipl_woocommerce_checkout_create_order_line_item', 10, 4);

/**
 * product loop image 
 */
function nipl_woocommerce_product_get_image($image, $product, $size, $attr, $placeholder)
{
	if ($product->get_type() == 'variable') {
		$pid = $product->get_id();

		$user_id            = get_current_user_id();
		$customer_id        = get_user_meta($user_id, 'user_customer', true);
		$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
		$kit_id             = get_user_meta($user_id, 'user_kit', true);
		$product_option 	= get_post_meta($campaign_id, 'product_option', true);
		$thumbnail_id 		= get_post_meta($pid, '_thumbnail_id', true);
		$custom_img 		= $product_option[$kit_id][$pid]['camp_varible_img'];

		if ($custom_img != '' && ($custom_img != $thumbnail_id)) {
			$image = wp_get_attachment_image($custom_img, $size, false);
		}
	}
	return $image;
}
add_filter('woocommerce_product_get_image', 'nipl_woocommerce_product_get_image', 10, 6);


/**
 * Single product image 
 */
function nipl_woocommerce_single_product_image_thumbnail_html($html, $post_thumbnail_id)
{
	$pid = get_the_ID();
	$product_detail = wc_get_product($pid);
	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$product_option 	= get_post_meta($campaign_id, 'product_option', true);
	$thumbnail_id 		= get_post_meta($pid, '_thumbnail_id', true);
	$custom_img 		= $product_option[$kit_id][$pid]['camp_varible_img'];
	
	if ($custom_img != '' && ($thumbnail_id != $custom_img)) {
		 //$html = wp_get_attachment_image($custom_img, 'full', false, array('class' => 'nipl_grn_border'));

		if($product_detail->is_type('variable')) {
			//$html = wp_get_attachment_image($custom_img, 'full', false);
			$html = wc_get_gallery_image_html($custom_img, false);
			
		}else {

			$html = wc_get_gallery_image_html($custom_img, true);
		}
		remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );

	}
	return $html;
}
add_filter('woocommerce_single_product_image_thumbnail_html', 'nipl_woocommerce_single_product_image_thumbnail_html', 10, 2);



/**
 * Overwrite WC plugin function to show custom product thumbnail
 */
function woocommerce_get_product_thumbnail($size = 'shop_catalog', $deprecated1 = 0, $deprecated2 = 0)
{
	global $post;
	$image_size = apply_filters('single_product_archive_thumbnail_size', $size);

	if (has_post_thumbnail()) {
		$pid = get_the_ID();
		$user_id            = get_current_user_id();
		$customer_id        = get_user_meta($user_id, 'user_customer', true);
		$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
		$kit_id             = get_user_meta($user_id, 'user_kit', true);
		$thumbnail_id_main = $thumb_id 		= get_post_thumbnail_id();
		$product_option 	= get_post_meta($campaign_id, 'product_option', true);
		$custom_img 		= $product_option[$kit_id][$pid]['camp_varible_img'];

		if ($custom_img != '' && ($thumbnail_id_main != $custom_img)) {
			$thumbnail_id_main =  $custom_img;
		}


		$props = wc_get_product_attachment_props($thumbnail_id_main, $post);

		// show custom image if set. 
		if ($custom_img != '' && ($thumb_id != $custom_img)) {
			return	wp_get_attachment_image($custom_img, $image_size, false);
		} else {
			return get_the_post_thumbnail($post->ID, $image_size, array(
				'title'	 => $props['title'],
				'alt'    => $props['alt'],
			));
		}
	} elseif (wc_placeholder_img_src()) {
		return wc_placeholder_img($image_size);
	}
}

// for kit
add_action('additional_customer_information', 'unidress_required_products', 20);
function unidress_required_products($data)
{
	if (!is_admin()) {
		wp_enqueue_script('View_Campaign_Status-js', plugins_url('/unidress/front-end/js/View_Campaign_Status.js'), array('jquery'));
		if ($data['customer_type'] == 'campaign') {

			$product_in_campaign   = $data['product_option'];
			$required_products     = get_post_meta($data['campaign_id'], 'required_products', true);

			if (empty($data['campaign_id']) || empty($data['kit_id']) || !isset($required_products[$data['kit_id']])) {
				return;
			}

			//kit data
			$required_in_kit  = $required_products[$data['kit_id']];
			$product_in_kit = $product_in_campaign[$data['kit_id']];
			$get_cart = WC()->cart->get_cart();

			$product_in_cart = array();
			foreach ($get_cart as $product) {
				if (!isset($product_in_cart[$product['product_id']]))
					$product_in_cart[$product['product_id']] = 0;
				$product_in_cart[$product['product_id']] += $product['quantity'];
			}
			$group_in_cart = array();
			foreach ($product_in_kit as $product_id => $product_options) {
				if (!isset($group_in_cart[$product_options['required_products']]))
					$group_in_cart[$product_options['required_products']] = 0;

				$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
				$group_in_cart[$product_options['required_products']] += $product_quantity;
			}

			if (!is_array($data['user_limits']))
				$data['user_limits'] = array();

			$rows = 4;
			$count_group = ceil(count($required_in_kit) / $rows);
			$index = 0;

			?>
			<td>
				<div class="user-required">
					<div class="grid">
						<div data-grid-position="header-required" class="th"><?php echo esc_attr__('Required Products', 'unidress') ?></div>

						<?php foreach ($required_in_kit as $group_id => $group) : ?>
							<div data-grid-position="<?php echo ++$index; ?>" class="data-item-row">
								<div class="data-item"><?php echo $group['name'] ?></div>
								<div class="data-item"><?php echo $group['amount'] ?></div>
							</div>
						<?php endforeach; ?>
						<?php while ($index % $rows != 0) { ?>
							<div data-grid-position="<?php echo ++$index; ?>" class="data-item-row display-none">
								<div class="data-item"></div>
								<div class="data-item"></div>
							</div>
						<?php } ?>
					</div>
				</div>
			</td>
			<?php
			$grid_areas = '"';
			for ($h = 0; $h < $count_group; $h++) {
				$grid_areas .= ' header-required';
			}

			$grid_areas .= '"';

			for ($j = 1; $j <= $rows; $j++) {

				$grid_areas .= '"';

				for ($i = 0; $j + $i * $rows <= $index; $i++) {
					$grid_areas .= ' grid-';
					$grid_areas .= $j + $i * $rows;
				}

				$grid_areas .= '" ';
			}
			?>
			<style>
				@media (min-width: 768px) {

					.grid {
						grid-template-areas: <?php echo $grid_areas ?>
					}

					.grid>div[data-grid-position="header-required"] {
						grid-area: header-required;
					}

					<?php for (
						$in = 1;
						$in <= $index;

						$in++
					) : ?>.grid>div[data-grid-position="<?php echo $in; ?>"] {
							<?php if (($in + (int)($in - 1) / $rows) % 2 === 0) : ?> background-color: #f2f2f2;
							<?php endif;
						?>grid-area: grid-<?php echo $in;
												?>;
						}

					<?php endfor;
				?>
				}
			</style>
		<?php
	}
}
}
// matrix
// Closed List - layout
// Show budget on cart
// Cart validation

//NIPL UN2-T39 coupon code
add_action('woocommerce_cart_coupon', 'discount_on_order', 10, 1);
//add_action('woocommerce_ajax_added_to_cart', 'discount_on_order',20);
add_action('woocommerce_update_cart_action_cart_updated', 'discount_on_order', 25, 1);

function discount_on_order($cart_updated) {
	global $woocommerce;
	global $order,$wpdb;
    
	$user_id = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$user_limits        = get_user_meta($user_id, 'user_limits', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
	$budgets_in_campaign = get_post_meta($campaign_id, 'budget', true);
	$budget_by_point 	= get_post_meta($campaign_id, 'budget_by_points',  true);
	$price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);
	$private_purchase_amount 	= get_post_meta($campaign_id, 'private_purchase_amount',  true);
	

	$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
	//pr($user_budget_limits);
	$user_budget_left = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
	if (empty($campaign_id) || empty($kit_id)) {
		return;
	}
	//campaign data
	$budgets_in_campaign = get_post_meta($campaign_id, 'budget', true);
	$unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
	if ($unidress_budget > 0) {
		$budget_in_kit = $unidress_budget;
	} else {
		$budget_in_kit = $budgets_in_campaign[$kit_id] ? $budgets_in_campaign[$kit_id] : 0;
	}
	
	$subtotal = WC()->cart->get_subtotal(true);
	if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
		$tax =  WC()->cart->get_subtotal_tax();
	}else {
		$tax = 0;
	}
	
	foreach(WC()->cart->get_fees() as $fee) :
		$additionalfee = (float)$fee->amount;
	endforeach;

	if(!empty($additionalfee)){
		update_user_meta( $user_id, 'additional_shipping_fee', $additionalfee);
		// if($additionalfee > ((int)$budget_in_kit - (int)$user_budget_left)){
		// 	$additionalfee -= ((int)$budget_in_kit - (int)$user_budget_left);
		// 	update_user_meta( $user_id, 'additional_shipping_fee', $additionalfee);
		// }
		// else{
		// 	update_user_meta( $user_id, 'additional_shipping_fee', 0);
		// }
		
	}else{
		update_user_meta( $user_id, 'additional_shipping_fee', '0' );
	}

	$amount = ($subtotal + $tax + $additionalfee );

	if(($user_budget_left) > $budget_in_kit){
		$discounted = 0;
	}
	else{
		if($private_purchase_amount){
			if($amount > ((int)$budget_in_kit - (int)$user_budget_left)){
				$discounted = (int)$budget_in_kit - (int)$user_budget_left;
			}
			else{
				$discounted = $amount;
			}
			
		}
		else{
			$discounted = $amount;
		}    
	}


	$displaybudget = (float)($budget_in_kit - (int)$user_budget_left );
	//$amount = WC()->cart->get_totals('total')['total']; // Amount
	$rdnm = mt_rand(1111,9999);
    $coupon_code = 'uni_budget_'.$rdnm; 
	$discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product
	$usercoupon = get_user_meta($user_id,'last_used_coupon',true);
	//echo $usercoupon; 
	$coupon_results = $wpdb->get_results( "SELECT p.ID,p.post_title,p.post_author,p.post_status from $wpdb->posts as p where p.post_title LIKE '%{$usercoupon}%' and p.post_author = {$user_id} AND p.post_status = 'publish' ", ARRAY_A);
	if(!empty($coupon_results)) {

		if(get_post_meta($coupon_results[0]['ID'],'usage_count',true) == '0') {
            update_post_meta( $coupon_results[0]['ID'], 'coupon_amount', $discounted );
 
			WC()->cart->apply_coupon( $usercoupon );
		}
		else {
			$coupon = array(
				'post_title' => $coupon_code,
				'post_content' => '',
				'post_author' => $user_id,
				'post_status' => 'publish',
				'post_type' => 'shop_coupon'
            );  
            
            $new_coupon_id = wp_insert_post( $coupon );

			// Add meta
			update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
			update_post_meta( $new_coupon_id, 'coupon_amount', $discounted );
			update_post_meta( $new_coupon_id, 'usage_limit', '1' );
			update_post_meta( $new_coupon_id, 'usage_limit_per_user', '1' );
			update_post_meta( $new_coupon_id, 'usage_count', '0' );
			update_user_meta($user_id,'last_used_coupon',$coupon_code);
			

			WC()->cart->apply_coupon( $coupon_code );
		}


	}
	//first time - user doesn't have any coupon 
	//create coupon and insert total tocoupon amount
	else{
		//echo 'coupon created';
		$coupon = array(
			'post_title' => $coupon_code,
			'post_content' => '',
			'post_author' => $user_id,
			'post_status' => 'publish',
			'post_type' => 'shop_coupon'
		);    

		$new_coupon_id = wp_insert_post( $coupon );

		// Add meta
		update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
		update_post_meta( $new_coupon_id, 'coupon_amount', $discounted );
		update_post_meta( $new_coupon_id, 'usage_limit', '1' );
		update_post_meta( $new_coupon_id, 'usage_limit_per_user', '1' );
		update_post_meta( $new_coupon_id, 'usage_count', '0' );
		update_user_meta($user_id,'last_used_coupon',$coupon_code);
		
		WC()->cart->apply_coupon( $coupon_code );
		
	}

	$new_value = $amount - $discounted;
	WC()->cart->set_total( $new_value );

	// Recalc our totals
	WC()->cart->calculate_totals();
	
	
	
}

add_action( 'woocommerce_add_to_cart', 'custom_add_to_cart', 25 ); 
add_action('woocommerce_calculate_totals', 'custom_add_to_cart', 999);
function custom_add_to_cart($cart_object ) {
	$new_post = array(
	'post_title'    => 'דמי משלוח' ,
	'post_status'   => 'publish',
	'post_author'   => 1,
	'post_type'     =>'product'
	);
	$user_id = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
	$min_order_charge = get_post_meta($campaign_id, 'min_order_charge', true) ?: 0;
	$shipping_price = get_post_meta($campaign_id, 'shipping_price', true) ?: 0;
	$subtotal = WC()->cart->get_subtotal(true);
	$total_w_shipping = $subtotal - $shipping_price;



	global $wpdb;
	$ship_price = $wpdb->get_results( "SELECT p.ID from $wpdb->posts as p where p.post_title LIKE 'דמי משלוח' ", ARRAY_A);
	 
	if(!empty($ship_price)) {
		$product_ID = $ship_price[0]['ID'];
		$product_cart_id = WC()->cart->generate_cart_id( $product_ID );
		$cart_item_key = WC()->cart->find_product_in_cart( $product_cart_id );
		if($min_order_charge > 0 && $subtotal < $min_order_charge ){
			update_post_meta($product_ID, '_regular_price', $shipping_price );
			update_post_meta($product_ID, '_price', $shipping_price );
			update_post_meta($product_ID, '_stock_status', 'instock' );
			if ( !$cart_item_key  && WC()->cart->cart_contents_count!=0) { 
				WC()->cart->add_to_cart( $product_ID, $quantity=1 );
			}
			elseif($cart_item_key && WC()->cart->cart_contents_count==1){
				WC()->cart->remove_cart_item( $cart_item_key );
			}
		}
		else{
			if ( $cart_item_key && (($total_w_shipping > $min_order_charge) || ($total_w_shipping == $min_order_charge)) ) { 
				 WC()->cart->remove_cart_item( $cart_item_key );
			}
		}

	}
	else{
		if($min_order_charge > 0 && $subtotal < $min_order_charge ){
			$product_ID = wp_insert_post( $new_post );
			add_post_meta($product_ID, '_regular_price', $shipping_price );
			add_post_meta($product_ID, '_price', $shipping_price );
			add_post_meta($product_ID, '_stock_status', 'instock' );
			WC()->cart->add_to_cart( $product_ID, $quantity=1 );
		}
	} 

	//return $cart_object;

}




// add class to shipping cost item
add_filter( 'woocommerce_mini_cart_item_class', 'additional_class_to_cart_item_classes', 10, 3 );
add_filter( 'woocommerce_cart_item_class', 'additional_class_to_cart_item_classes', 10, 3 );
add_filter( 'woocommerce_order_item_class', 'additional_class_to_cart_item_classes', 10, 3 );
function additional_class_to_cart_item_classes ( $class, $cart_item, $cart_item_key ) {
	global $wpdb;
	$ship_price = $wpdb->get_results( "SELECT p.ID from $wpdb->posts as p where p.post_title LIKE 'דמי משלוח' ", ARRAY_A);
	$product_ID = $ship_price[0]['ID'];
	$product_cart_id = WC()->cart->generate_cart_id( $product_ID );
	$cart_item_key1 = WC()->cart->find_product_in_cart( $product_cart_id );
    if ( $cart_item_key ==  $cart_item_key1) {
        $class .= ' shipping_cost_item';
    }

    return $class;
}






//T39
add_action( 'woocommerce_before_checkout_form', 'remove_checkout_coupon_form', 9 );
function remove_checkout_coupon_form(){
    remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
}

//UN2-T48 NIPL hide phone field on checkout page
add_filter( 'woocommerce_checkout_fields', 'unidress_unset_wc_phone_field',99);
function unidress_unset_wc_phone_field( $fields ) {
	$user_id = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$user_limits        = get_user_meta($user_id, 'user_limits', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
	$hide_phone_on_checkout = get_post_meta($customer_id, 'hide_phone_on_checkout',  true);

	if($hide_phone_on_checkout == 1) {
		$fields['billing']['billing_phone']['required'] = false;
		unset($fields['billing']['billing_phone']);
	}else{
		$fields['billing']['billing_phone']['required'] = true;
	}
	return $fields;
}


//  Redirect User Login to Homepage
//this one not working
add_filter('woocommerce_login_redirect', 'wc_login_redirect');
function wc_login_redirect( $redirect_to ) {
     $redirect_to = get_home_url();
     return $redirect_to;
}

add_action('login_form','my_added_login_field');
function my_added_login_field(){
    //Output your HTML
?>
	<input type="hidden" name="redirect" value="<?php echo get_home_url(); ?>" />
<?php
}


// remove error: "Coupon code already applied!"
// the error code of this message is 103

add_filter( 'woocommerce_coupon_error', 'wp_coupon_error', 10, 2 );

function wp_coupon_error( $err, $err_code ) {
	return ( '103' == $err_code ) ? '' : $err;
}


/**
 * @snippet       Enable Payment Gateway according to the type of campaign
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 3.8
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
  
add_filter( 'woocommerce_available_payment_gateways', 'bbloomer_paypal_enable_manager' );
  
function bbloomer_paypal_enable_manager( $available_gateways ) {

	$user_id = get_current_user_id();
	$user = get_userdata($user_id);
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
	$private_purchase_amount = get_post_meta($campaign_id, 'private_purchase_amount',  true);
	if(empty($private_purchase_amount)){
		if ( isset( $available_gateways['paypal'] ) ) {
			unset( $available_gateways['paypal'] );
		}
		if ( isset( $available_gateways['creditguard'] ) ) {
			unset( $available_gateways['creditguard'] );
		}
	}
	else{
		if ( WC()->cart->needs_payment() ){
			if ( isset( $available_gateways['cod'] ) ) {
				unset( $available_gateways['cod'] );
			} 
		}
		
	}
   
   return $available_gateways;
}