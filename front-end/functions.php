<?php

$current_customer = get_user_meta(get_current_user_id(), 'user_customer', true);

function get_ordering_style($current_customer) {
    global $wpdb;

    $ordering_style = $wpdb->get_row( 'SELECT t.slug FROM wp_postmeta pm INNER JOIN wp_terms t ON t.term_id = pm.meta_value WHERE pm.post_id = "' . $current_customer . '" and pm.meta_key = "ordering_style"');

    if (isset($ordering_style->slug))
        return $ordering_style->slug;

    return false;
}
function get_customer_type($current_customer) {
    $type = get_post_meta($current_customer, 'customer_type', true);
    return $type;
}

add_action( 'init', 'redirect_non_logged_users_to_login_page' );
function redirect_non_logged_users_to_login_page() {
	if ( !is_user_logged_in() && wp_login_url() != site_url('/' . $GLOBALS['pagenow'])) {
		wp_redirect( wp_login_url( home_url() ) );
		exit;
	}
}

add_action( 'woocommerce_before_checkout_form', 'can_user_checkout_check' );
function can_user_checkout_check() {
	if ( check_proceed_to_checkout()) {
		wp_safe_redirect( wc_get_page_permalink( 'cart' ) );
		exit;
    }
}

/**
 * Output the variable product add to cart area.
 * show only assign variation
 */
function woocommerce_variable_add_to_cart() {
	global $product;

	// Enqueue variation scripts.
	wp_enqueue_script( 'wc-add-to-cart-variation' );

	// Get Available variations?
	$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

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

	if (isset($product_option[$kit_id][$product_id]['variation'])){
		foreach ($available_variations as $index=>$variation) {
			if (!in_array($variation['variation_id'], $product_option[$kit_id][$product_id]['variation'])){
				unset($available_variations[$index]);
			}
		}
	}
	sort($available_variations);
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

add_action( 'woocommerce_product_query', 'unidress_product_query' );
function unidress_product_query( $q ){

	$q->set( 'post__in', (array) get_unidress_list_product() );

}

//Customer information
add_action( 'storefront_content_top', 'woocommerce_show_info', 10);
function woocommerce_show_info() {
	if ( !is_admin() && is_user_logged_in() && ( is_shop() || is_product_taxonomy() ) ) {

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
                                <th colspan="2"><?php echo esc_attr__( 'User\'s Personal Details', 'unidress' )?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="data-item"><?php echo esc_attr__( 'Name', 'unidress' )?></td>
                                <td class="data-item"><?php echo $name?></td>
                            </tr>
                            <tr>
                                <td class="data-item"><?php echo esc_attr__( 'Username', 'unidress' )?></td>
                                <td class="data-item"><?php echo $username?></td>
                            </tr>
                            <tr>
                                <td class="data-item"><?php echo esc_attr__( 'Branch', 'unidress' )?></td>
                                <td class="data-item"><?php echo $customer_branch_name?></td>
                            </tr>
                            <tr>
                                <td class="data-item"><?php echo esc_attr__( 'Department', 'unidress' )?></td>
                                <td class="data-item"><?php echo $user_department?></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
	                <?php do_action('additional_customer_information', $data)?>
                </tr>
                </tbody>
            </table>
        </div>
		<?php

	}

}

// for kit
add_action( 'additional_customer_information', 'unidress_required_products', 20);
function unidress_required_products($data) {
	if ( !is_admin() ) {
		wp_enqueue_script( 'View_Campaign_Status-js', plugins_url( '/unidress/front-end/js/View_Campaign_Status.js' ), array( 'jquery' ) );
		if ($data['customer_type'] == 'campaign') {

			$product_in_campaign   = $data['product_option'];
			$required_products     = get_post_meta($data['campaign_id'], 'required_products', true);

            if ( empty($data['campaign_id']) || empty($data['kit_id']) || !isset($required_products[$data['kit_id']]) ) {
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
            foreach ($product_in_kit as $product_id=>$product_options) {
                if (!isset($group_in_cart[$product_options['required_products']]))
                    $group_in_cart[$product_options['required_products']] = 0;

                $product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
                $group_in_cart[$product_options['required_products']] += $product_quantity;
            }

			if (!is_array($data['user_limits']))
				$data['user_limits'] = array();

			$rows = 4;
			$count_group = ceil(count($required_in_kit)/$rows);
			$index = 0;

    		?>
            <td>
                <div class="user-required">
                    <div class="grid">
                        <div data-grid-position="header-required" class="th"><?php echo esc_attr__( 'Required Products', 'unidress' )?></div>

                        <?php foreach ($required_in_kit as $group_id=>$group) : ?>
                        <div data-grid-position="<?php echo ++$index; ?>" class="data-item-row">
                            <div class="data-item"><?php echo $group['name'] ?></div>
                            <div class="data-item"><?php echo $group['amount']?></div>
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
            for ($h=0; $h < $count_group; $h++) {
	            $grid_areas .= ' header-required';
            }

			$grid_areas .= '"';

			for ($j=1; $j <= $rows; $j++) {

	            $grid_areas .= '"';

	            for ($i = 0; $j+ $i*$rows <= $index; $i++) {
		            $grid_areas .= ' grid-';
		            $grid_areas .= $j+ $i*$rows;
	            }

	            $grid_areas .= '" ';

            }
            ?>
            <style>
                @media (min-width: 768px) {

                    .grid {
                        grid-template-areas: <?php echo $grid_areas ?>
                    }
                    .grid > div[data-grid-position="header-required"] {
                        grid-area: header-required;
                    }
                    <?php for ($in = 1; $in <= $index; $in++) : ?>
                    .grid > div[data-grid-position="<?php echo $in; ?>"] {
                    <?php if ( ($in + (int)($in-1)/$rows) % 2 === 0) : ?>
                        background-color: #f2f2f2;
                    <?php endif; ?>

                        grid-area: grid-<?php echo $in; ?>;
                    }
                    <?php endfor; ?>

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

/**
 * Cart validation
 *
 * Assign group limit check
 * Required products check
 */

add_filter('woocommerce_update_cart_action_cart_updated', 'unidress_update_cart_validation', 1 , 10);
function unidress_update_cart_validation($passed_validation) {
update_user_meta(1, '$passed_validation', $passed_validation );

	$old_cart = WC()->session->get( 'cart', null );
	WC()->cart->calculate_totals();

	//user data
	$user_id            = get_current_user_id();
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

	if ( empty($campaign_id) || empty($kit_id) ) {
		return;
	}


	//kit data
	$groups_in_kit       = $groups_in_campaign[$kit_id]   ?: array();
	$required_in_kit     = $required_products[$kit_id]    ?: array();
	$product_in_kit      = $product_in_campaign[$kit_id]  ?: array();
	$budget_in_kit       = $budget_in_campaign[$kit_id]   ?: 0;

	$get_cart = WC()->cart->get_cart();

	$product_in_cart = array();
	foreach ($get_cart as $product) {
		if (!isset($product_in_cart[$product['product_id']]))
			$product_in_cart[$product['product_id']] = 0;
		$product_in_cart[$product['product_id']] += $product['quantity'];
	}

	if (!is_array($user_limits))
		$user_limits = array();
	update_user_meta(1, '$passed_validation2', $passed_validation );

	// Product list check in cart
	if ($product_in_cart && $product_in_kit) {
		foreach ($product_in_cart as $product_id => $product_option) {
			if ( !array_key_exists($product_id, $product_in_kit ) ) {
				$passed_validation = false;
				$product = wc_get_product( $product_id );
				$product_title = $product->get_title();
				wc_add_notice(  __( 'Product "' . $product_title . '" not available for sale', 'unidress' ), 'error' );
            }
		}
	}
	update_user_meta(1, '$passed_validation3', $passed_validation );

	// budget limits check
	if ( $customer_ordering_style == 'standard' && $customer_type == 'campaign' ) {

		$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);

		$total = WC()->cart->get_totals( 'total' )['total'];

		$new_budget_limits = (isset($user_budget_limits[$campaign_id][$kit_id]) ? (int)$user_budget_limits[$campaign_id][$kit_id] : 0) + (int)$total;

		if ($budget_in_kit <= $new_budget_limits) {
			$passed_validation = false;
			wc_add_notice( __( 'The total amount of the purchase exceeds the balance of your budget', 'unidress' ), 'error' );
		}

	}
	update_user_meta(1, '$passed_validation4', $passed_validation );

	// Required products check
	if ($customer_type == 'campaign' && $required_in_kit) {
		$required_products_in_cart = array();
		foreach ($product_in_kit as $product_id=>$product_options) {
			if (!isset($required_products_in_cart[$product_options['required_products']]))
				$required_products_in_cart[$product_options['required_products']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$required_products_in_cart[$product_options['required_products']] += $product_quantity;
		}

		if ($required_products_in_cart)

            foreach ($required_in_kit as $group_id=>$group) {
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
	update_user_meta(1, '$passed_validation5', $passed_validation );

	// Assign group limit check
	if ($customer_type == 'campaign' && $groups_in_kit) {

		$group_in_cart = array();
		foreach ($product_in_kit as $product_id=>$product_options) {
			if (!isset($group_in_cart[$product_options['groups']]))
				$group_in_cart[$product_options['groups']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$group_in_cart[$product_options['groups']] += $product_quantity;
		}

        if ($group_in_cart)
            foreach ($groups_in_kit as $group_id=>$group) {
	            $user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
	            $group_cart = isset($group_in_cart[$group_id]) ? (int)$group_in_cart[$group_id] : 0;

	            $balance = $group['amount'] - $user_limit - $group_cart;

	            if ($balance < 0){
	                wc_add_notice(  __( 'You have exceeded the limit by product group', 'unidress' ), 'error' );
	                $passed_validation = false;
	                break;
                }
            }
		}
	update_user_meta(1, '$passed_validation6', $passed_validation );

	// Only one order per user
	if ($one_order_toggle && isset($one_order_value[$campaign_id][$kit_id]) && $one_order_value[$campaign_id][$kit_id] ) {
		$passed_validation = false;
		wc_add_notice( __( 'You already buy something', 'unidress' ), 'error' );
	}

	update_user_meta(1, '$passed_validation7', $passed_validation );

	////////////
    if (!$passed_validation) {
	    foreach ( $old_cart as $cart_item_key => $values ) {

		    $quantity = $values['quantity'];
            WC()->cart->set_quantity( $cart_item_key, $quantity, false );

	    }

    }

	return $passed_validation;
}


// Use price from assigning table
add_filter('woocommerce_product_get_price', 'unidress_product_get_price', 2 , 10);
function unidress_product_get_price( $price, $product) {
    if (!is_user_logged_in())
        return $price;

    $product_id         = $product->get_id();
    $user_id            = get_current_user_id();
    $customer_id        = get_user_meta($user_id, 'user_customer', true);
    $active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
    $product_option     = get_post_meta($active_campaign, 'product_option', true);
    $customer_type      = get_post_meta($customer_id, 'customer_type', true);

    if ($customer_type == "project") {
        $kit_id      = 0;
    } else {
        $kit_id      = get_user_meta($user_id, 'user_kit', true);
    }

    if (isset($product_option[$kit_id][$product_id]['price']) && $product_option[$kit_id][$product_id]['price'] != 0) {
        return $product_option[$kit_id][$product_id]['price'];
    }

    return $price;
}

// if product has price from assigning table, product is purchasable
// add_filter('woocommerce_is_purchasable', 'unidress_product_is_purchasable', 2 , 10);
// function unidress_product_is_purchasable( $purchasable, $product) {
// }

add_filter('woocommerce_variable_price_html', 'woocommerce_show_variation_price', 2 , 10);
function woocommerce_show_variation_price( $price , $variable ) {
    $product_id         = $variable->get_id();

    if (!is_user_logged_in() || !isset($product_id))
        return $price;

    $user_id            = get_current_user_id();
    $customer_id        = get_user_meta($user_id, 'user_customer', true);
    $active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
    $product_option     = get_post_meta($active_campaign, 'product_option', true);
    $customer_type      = get_post_meta($customer_id, 'customer_type', true);

    if ($customer_type == "project") {
        $kit_id      = 0;
    } else {
        $kit_id      = get_user_meta($user_id, 'user_kit', true);
    }

    if (isset($product_option[$kit_id][$product_id]['price']) && $product_option[$kit_id][$product_id]['price'] != 0) {

        return wc_price($product_option[$kit_id][$product_id]['price']);
    }

    return $price;
}
add_filter('woocommerce_product_variation_get_price', 'unidress_product_variation_get_price', 2 , 10);
function unidress_product_variation_get_price( $price, $product) {
    if (!is_user_logged_in())
        return $price;

    $product_id         = $product->get_parent_id();
    $user_id            = get_current_user_id();
    $customer_id        = get_user_meta($user_id, 'user_customer', true);
    $active_campaign    = get_post_meta($customer_id, 'active_campaign', true);
    $product_option     = get_post_meta($active_campaign, 'product_option', true);
    $customer_type      = get_post_meta($customer_id, 'customer_type', true);

    if ($customer_type == "project") {
        $kit_id      = 0;
    } else {
        $kit_id      = get_user_meta($user_id, 'user_kit', true);
    }

    if (isset($product_option[$kit_id][$product_id]['price']) && $product_option[$kit_id][$product_id]['price'] != 0) {

        return $product_option[$kit_id][$product_id]['price'];
    }

    return $price;
}

add_action( 'woocommerce_product_meta_end', 'add_graphic_option_in_product', 20 );
function add_graphic_option_in_product(){

	global $product;
	//user data
	$user_id            = get_current_user_id();
	$product_id         = $product->get_id();;
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$project_id         = get_post_meta($customer_id, 'active_campaign', true);
	$product_option     = get_post_meta($project_id, 'product_option', true);
	$product_graphics   = isset($product_option[0][$product_id]['graphics']) ? $product_option[0][$product_id]['graphics'] : "";
	$project_graphics   = get_post_meta($project_id, 'project_graphics', true);

	if ( empty($product_graphics) ||  empty($project_graphics) )
		return;

	$graphics = get_posts( array(
		'include'     => $project_graphics,
		'post_type'   => 'graphic',
		'suppress_filters' => true,
	) );
	$assigning_graphics = array();
	foreach ( $graphics as $index => $graphic ) {
		if (in_array($graphic->ID, $product_graphics))
			$assigning_graphics[$graphic->ID] = $graphic->post_title;
	}
	echo '<div class="unidress-options-graphic">';
	echo '<legend>' . __( 'Graphics', 'unidress' ) .'</legend>';
	foreach ($assigning_graphics as $graphic_id=>$name) {
		echo '<div class="unidress-graphic">
            <input id="graphic' . $graphic_id .'" type="checkbox" class="input-radio" name="unidress-graphic[]" value="' . $graphic_id .'" checked="checked">
            <label for="graphic' . $graphic_id .'">' . $name .'</label>
        </div>';
	}

	echo '</div>';
}

// matrix
if ( get_ordering_style($current_customer)=='matrix' ){
    add_action( 'woocommerce_single_product_summary', 'get_product_type',  5 );
    function get_product_type() {
        global $post;
        if( function_exists('get_product') ){
            $product = wc_get_product( $post->ID );

            if( $product->is_type( 'variable' ) ){
                $attributes = $product->get_variation_attributes();
                if (count(get_array_product_attribute ($product, $attributes)) == 2 ) {
                    remove_action('woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30);
                    add_action('woocommerce_after_single_product_summary', 'woocommerce_variable_add_to_cart', 5);
                }
                remove_action('woocommerce_after_single_product_summary', 'storefront_single_product_pagination', 30);
            }
        }
    }
}

// Closed list. Change layout
if ( get_ordering_style($current_customer)=='closed_list' ) {

    // hide price
    add_action('wc_price', 'closed_list_hide_price', 4, 10);
    //add_action('woocommerce_cart_subtotal', 'closed_list_hide_price', 4, 10);
    //add_action('woocommerce_cart_hash', 'closed_list_hide_price', 4, 10);
    function closed_list_hide_price($cart_subtotal, $compound) {
        $cart_subtotal = '';
        $compound = '';
    }

    remove_action('woocommerce_before_shop_loop', 'wc_setup_loop');
    add_action('woocommerce_before_shop_loop', 'closed_list_setup_loop');
    function closed_list_setup_loop( $args = array()  ) {

        $default_args = array(
            'loop'         => 0,
            'columns'      => 3,
            'name'         => '',
            'is_shortcode' => false,
            'is_paginated' => false,
            'is_search'    => false,
            'is_filtered'  => false,
            'total'        => 0,
            'total_pages'  => 0,
            'per_page'     => 0,
            'current_page' => 1,
        );

        // If this is a main WC query, use global args as defaults.
        if ( $GLOBALS['wp_query']->get( 'wc_query' ) ) {
            $default_args = array_merge( $default_args, array(
                'is_search'    => $GLOBALS['wp_query']->is_search(),
                'is_filtered'  => is_filtered(),
                'total'        => $GLOBALS['wp_query']->found_posts,
                'total_pages'  => $GLOBALS['wp_query']->max_num_pages,
                'per_page'     => $GLOBALS['wp_query']->get( 'posts_per_page' ),
                'current_page' => max( 1, $GLOBALS['wp_query']->get( 'paged', 1 ) ),
            ) );
        }

        // Merge any existing values.
        if ( isset( $GLOBALS['woocommerce_loop'] ) ) {
            $default_args = array_merge( $default_args, $GLOBALS['woocommerce_loop'] );
        }

        $GLOBALS['woocommerce_loop'] = wp_parse_args( $args, $default_args );
    }


}

if (get_customer_type($current_customer) == 'campaign' )  {

    // Show assign group limits
	//add_action( 'additional_customer_information', 'woocommerce_show_assign_groups_in_shop', 30 );
	function woocommerce_show_assign_groups_in_shop( $data ) {
		if ( ! is_admin() ) {

			//active campaign data
			$groups_in_campaign  = get_post_meta( $data['campaign_id'], 'groups', true );
			$product_in_campaign = $data['product_option'];

			if ( empty( $data['campaign_id'] ) || empty( $data['kit_id'] ) || ! isset( $groups_in_campaign[ $data['kit_id'] ] ) ) {
				return;
			}

			//kit data
			$groups_in_kit  = $groups_in_campaign[ $data['kit_id'] ];
			$product_in_kit = $product_in_campaign[ $data['kit_id'] ];

			$get_cart = WC()->cart->get_cart();

			$product_in_cart = array();
			foreach ( $get_cart as $product ) {
				if ( ! isset( $product_in_cart[ $product['product_id'] ] ) ) {
					$product_in_cart[ $product['product_id'] ] = 0;
				}
				$product_in_cart[ $product['product_id'] ] += $product['quantity'];
			}

			$group_in_cart = array();
			foreach ( $product_in_kit as $product_id => $product_options ) {
				if ( ! isset( $group_in_cart[ $product_options['groups'] ] ) ) {
					$group_in_cart[ $product_options['groups'] ] = 0;
				}

				$product_quantity                            = isset( $product_in_cart[ $product_id ] ) ? $product_in_cart[ $product_id ] : 0;
				$group_in_cart[ $product_options['groups'] ] += $product_quantity;
			}

			if ( ! is_array( $data['user_limits'] ) ) {
				$data['user_limits'] = array();
			}

			?>

            <table class="user-group-limit">
                <thead>
                <tr>
                    <th></th>
                    <th><?php echo esc_attr__( 'Limit', 'unidress' ) ?></th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ( $groups_in_kit as $group_id => $group ) : ?>
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
if ( ( get_ordering_style($current_customer)=='standard' ) && ( get_customer_type($current_customer) == 'campaign' ) ) {
    // Update users limits
    add_action( 'woocommerce_checkout_update_order_meta', 'update_budget_to_user' );
    function update_budget_to_user() {
        $user_id        = get_current_user_id();
        $customer_id    = get_user_meta($user_id, 'user_customer', true);
        $kit_id         = get_user_meta($user_id, 'user_kit', true);
        $campaign_id    = get_post_meta($customer_id, 'active_campaign', true);

        $user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);

        $total = WC()->cart->get_totals( 'total' )['total'];

        //Clear another campaign budget limit
        $new_budget_limits = array();
        $new_budget_limits[$campaign_id][$kit_id] = (isset($user_budget_limits[$campaign_id][$kit_id]) ? (int)$user_budget_limits[$campaign_id][$kit_id] : 0) + (int)$total;
        update_user_meta($user_id,'user_budget_limits', $new_budget_limits);
    }

}

// Check user limits and budget before proceed checkout
add_action('woocommerce_checkout_process', 'check_group_limit');
function check_group_limit() {

	//user data
	$user_id            = get_current_user_id();
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

	if ( empty($campaign_id) || empty($kit_id) ) {
		return;
	}

	//kit data
	$groups_in_kit       = $groups_in_campaign[$kit_id]   ?: array();
	$required_in_kit     = $required_products[$kit_id]    ?: array();
	$product_in_kit      = $product_in_campaign[$kit_id]  ?: array();
	$budget_in_kit       = $budget_in_campaign[$kit_id]   ?: 0;

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
	if ($customer_type == 'campaign' && $customer_ordering_style == 'standard' ) {

		$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);

		$total = WC()->cart->get_totals( 'total' )['total'];

		$new_budget_limits = (isset($user_budget_limits[$campaign_id][$kit_id]) ? (int)$user_budget_limits[$campaign_id][$kit_id] : 0) + (int)$total;

		if ($budget_in_kit <= $new_budget_limits)
			throw new Exception( __( 'The total amount of the purchase exceeds the balance of your budget', 'unidress' ) );

	}

	// Required products check
	if ($customer_type == 'campaign' && $required_in_kit) {
		$required_products_in_cart = array();
		foreach ($product_in_kit as $product_id=>$product_options) {
			if (!isset($required_products_in_cart[$product_options['required_products']]))
				$required_products_in_cart[$product_options['required_products']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$required_products_in_cart[$product_options['required_products']] += $product_quantity;
		}

		if ($required_products_in_cart)
			foreach ($required_in_kit as $group_id=>$group) {
				$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
				$required_cart = isset($required_products_in_cart[$group_id]) ? (int)$required_products_in_cart[$group_id] : 0;

				$balance = $group['amount'] - $user_limit - $required_cart;

				if ($balance > 0){
					throw new Exception(sprintf( __( 'Need More Essential Products. Go to <a href="%s">Cart</a> or <a href="%s">Shop</a>' , 'unidress' ), esc_url( wc_get_page_permalink( 'cart' ) ), esc_url( wc_get_page_permalink( 'shop' ) ) ) );
					break;
				}
			}
	}

	// Assign group limit check
	if ($customer_type == 'campaign' &&  $groups_in_kit) {

		$group_in_cart = array();
		foreach ($product_in_kit as $product_id=>$product_options) {
			if (!isset($group_in_cart[$product_options['groups']]))
				$group_in_cart[$product_options['groups']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$group_in_cart[$product_options['groups']] += $product_quantity;
		}

		if ($group_in_cart)
			foreach ($groups_in_kit as $group_id=>$group) {
				$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
				$group_cart = isset($group_in_cart[$group_id]) ? (int)$group_in_cart[$group_id] : 0;

				$balance = $group['amount'] - $user_limit - $group_cart;

				if ($balance < 0){
					throw new Exception( __( 'You have exceeded the limit by product group', 'unidress' ) );
					break;
				}
			}
	}

	// You already buy something check
	if ($one_order_toggle && isset($one_order_value[$campaign_id][$kit_id]) && $one_order_value[$campaign_id][$kit_id] ) {
		throw new Exception( __( 'You already buy something', 'unidress' ) );
	}

	// Product list check in cart
	if ($product_in_cart && $product_in_kit) {
		foreach ($product_in_cart as $product_id => $product_option) {
			if ( !array_key_exists($product_id, $product_in_kit ) ) {
				$product = wc_get_product( $product_id );
				$product_title = $product->get_title();
				throw new Exception( __( 'Product "' . $product_title . '" not available for sale', 'unidress' ) );
			}
		}
	}
}

/**
 * ver 2
 * Update user limits
 * Campaign users
 */
add_action( 'woocommerce_checkout_update_order_meta', 'update_user_limits', 20, 2 );
function update_user_limits($order_id, $data) {

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

	if ( empty($campaign_id) || empty($kit_id) ) {
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
	$new_user_limit_raw = array_merge( $new_user_limit_raw, array_keys($groups_in_campaign[$kit_id]));

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

	update_user_meta(get_current_user_id(),'user_limits', $new_user_limit);

	//   user can only one order
	if ($one_order_toggle) {
		$one_order_value[$campaign_id][$kit_id] = $order_id;
		update_user_meta(get_current_user_id(),'one_order_value', $one_order_value);
	}
}

function get_unidress_list_product() {
	//user data
	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$customer_type      = get_post_meta($customer_id, 'customer_type', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$one_order_value    = get_user_meta($user_id, 'one_order_value', true);
	$one_order_toggle   = get_post_meta($campaign_id, 'one_order_toggle', true);

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
	if ($one_order_toggle && isset($one_order_value[$campaign_id][$kit_id]) && $one_order_value[$campaign_id][$kit_id] ) {
		$product_list = 0;
	}

    return $product_list;
}

//add tab to product page
add_filter( 'woocommerce_product_tabs', 'product_option_tab' );
function product_option_tab( $tabs ) {
    global $post;
    $show = false;

    $options = get_post_meta($post->ID, '_product_options', true);
    if ($options){
        foreach ( $options as $taxonomy => $option ) {
            if ( isset($option['visibility'] ) ) {
                $option = get_post( $option['taxonomy_id'] );
                if ($option) {
                    $show = true;
                    break;
                }
            }
        }
    }
    if ($show){
        $tabs['test_tab'] = array(
            'title' 	=> 'Options',
            'priority' 	=> 50,
            'callback' 	=> 'product_option_tab_content'
        );
    }

    return $tabs;
}
function product_option_tab_content( $tabs ) {
    global $post;
	$options = get_post_meta($post->ID, '_product_options', true);
    ?>
    <table class="shop_attributes">
        <?php foreach ( $options as $taxonomy => $option ) : ?>

            <?php if ( isset($option['visibility'] ) ) : ?>
                <?php if ( isset($option['taxonomy_id'] ) ) : ?>
                    <?php $label = wc_attribute_label( get_the_title($option['taxonomy_id']) );
                    if ($label) : ?>
                        <tr>
                            <th><?php echo $label; ?></th>
                            <td><?php
                                $values = array();
                                $options_values = get_terms( array(
                                    'hide_empty' => false,
                                    'include' => $option['terms'],
                                ));
                                foreach ($options_values as $value){
                                    $values[] = $value->name;
                                }
                                echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $taxonomy, $values );
                                ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                <?php else: ?>
                    <tr>
                        <th><?php echo wc_attribute_label( $option['label'] ); ?></th>
                        <td><?php echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $option['terms'] ) ) ), $option['label'], $option['terms'] ); ?></td>
                    </tr>
                <?php endif; ?>

            <?php endif; ?>

        <?php endforeach; ?>
    </table>

    <?php

}

//UN1-T11: Site Logo Defined by Customer
//UN1-T24: Customer Name below the customer's logo
function storefront_site_branding() {
    ?>
    <div class="site-branding">
        <?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
            $logo = get_custom_logo();
            $html = is_home() ? '<h1 class="logo">' . $logo . '</h1>' : $logo;
        } else {
            $tag = is_home() ? 'h1' : 'div';

            $html = '<' . esc_attr( $tag ) . ' class="beta site-title"><a href="' . esc_url( home_url( '/' ) ) . '" rel="home">' . esc_html( get_bloginfo( 'name' ) ) . '</a></' . esc_attr( $tag ) . '>';

            if ( '' !== get_bloginfo( 'description' ) ) {
                $html .= '<p class="site-description">' . esc_html( get_bloginfo( 'description', 'display' ) ) . '</p>';
            }

            if (is_user_logged_in()){
                $current_customer = get_user_meta(get_current_user_id(), 'user_customer', true);
                $customer_name = get_the_title($current_customer);
                if ($current_customer) {
                    $html.= '<div>' . $customer_name .'</div>';
                }
            }
        }

        echo $html; // WPCS: XSS ok. ?>
    </div>
    <div class="hidden-xs">
        <?php get_budget_banner(); ?>
    </div>
	<?php
}
function get_budget_banner() {

	$user_id            = get_current_user_id();
	$current_customer = get_user_meta($user_id, 'user_customer', true);

	if ( ( get_ordering_style($current_customer)=='standard' ) && ( get_customer_type($current_customer) == 'campaign' ) ) {
		if ( !is_admin() ) {

			$kit_id             = get_user_meta($user_id, 'user_kit', true);
			$customer_id        = get_user_meta($user_id, 'user_customer', true);
			$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
			$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
			$user_budget_left   = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
			if (empty($campaign_id) || empty($kit_id)) {
				return;
			}
			//campaign data
			$budgets_in_campaign     = get_post_meta($campaign_id, 'budget', true);
			$budget_in_kit    = $budgets_in_campaign[$kit_id] ?: 0;

			$total = WC()->cart->get_totals( 'total' )['total'];
			?>
            <div class="user-budget-bar"><?php echo esc_attr__( 'Budget Balance', 'unidress' )?>: <span class="remaining-budget"><?php echo $budget_in_kit - (int)$user_budget_left - $total ?></span><span class="woocommerce-Price-currencySymbol"> <?php echo get_woocommerce_currency_symbol() ?> </span></div>
			<?php
		}

	}
}

add_filter( 'get_custom_logo',  'custom_logo_url', 0);
function custom_logo_url ( $html ) {
    $current_id = get_current_user_id();
    $current_customer = get_user_meta($current_id, 'user_customer', true);
    $user_id = get_current_user_id();
    $kit_id = get_user_meta($user_id, 'user_kit', true);
    $image = get_field('kit_logo', $kit_id);
    $customer_name = get_the_title($current_customer);
    $customer_logo = get_post_meta($current_customer, 'customers_logo', true);
 
    if (is_user_logged_in()){
        if ($image) {
            $url = $image['url'];
            $html.= '<center><img src="'. $url.'" />'.$customer_name.'</center>';
        }else{
            if ($customer_logo) {
                $url = network_site_url();
                $html = sprintf( '<a href="%1$s" class="custom-logo-link" rel="home" itemprop="url">%2$s</a>',
                    esc_url( $url  ),
                    wp_get_attachment_image( $customer_logo, 'full', false, array(
                        'class'    => 'custom-logo',
                    ) )
                );
            }
        }
    }
    return $html;
}

function get_array_product_attribute ($product, $attributes){

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
function woocommerce_single_custom_variation_add_to_cart_button() {
	wc_get_template( 'single-product/add-to-cart/custom-variation-add-to-cart-button.php' );
}


add_action( 'storefront_homepage',  'storefront_homepage_header2', 10);
add_action( 'storefront_homepage',  'storefront_page_content2', 20);

/**
 * Display the page header without the featured image
 */
function storefront_homepage_header2() {
}

/**
 * Display the post content
 */
function storefront_page_content2() {

    $user_id            = get_current_user_id();
    $current_customer   = get_user_meta($user_id, 'user_customer', true);
    $campaign_id        = get_post_meta($current_customer, 'active_campaign', true);

    setup_postdata( $campaign_id );

    ?>
    <div class="entry-content">
        <?php the_content(); ?>
        <?php
        wp_link_pages(
            array(
                'before' => '<div class="page-links">' . __( 'Pages:', 'storefront' ),
                'after'  => '</div>',
            )
        );
        ?>
        <div class="button-go-shop-wrapper">
            <a class="button button-go-shop" href="<?php echo get_permalink( wc_get_page_id( 'shop' ) );  ?>"> <?php echo __('Go to Marketplace', 'unidress') ?> </a>
        </div>
    </div><!-- .entry-content -->

    <?php

    wp_reset_postdata();

}

// disable billing information in checkout
// add shipping rule
add_filter( 'woocommerce_cart_needs_shipping', function( $field ){
	return 0;
}, 1, 10);
add_filter( 'woocommerce_checkout_fields', function( $field ){

	$user_id                = get_current_user_id();
	$customer_id            = get_user_meta($user_id,     'user_customer', true);
	$campaign_id            = get_post_meta($customer_id, 'active_campaign', true);
	$shops_checked          = get_post_meta($campaign_id, 'shops', true);
	$required               = $shops_checked ? true : false;
	$user_first_name        = get_user_meta($user_id, 'first_name', true);
	$user_last_name         = get_user_meta($user_id, 'last_name', true);
	$user_billing_email     = get_user_meta($user_id, 'billing_email', true);
	$user_billing_phone     = get_user_meta($user_id, 'billing_phone', true);

	if (isset($field['billing']['billing_country']))
	    unset($field['billing']['billing_country']);
	unset($field['billing']['billing_address_1']);
	unset($field['billing']['billing_address_2']);
	unset($field['billing']['billing_city']);
	unset($field['billing']['billing_state']);
	unset($field['billing']['billing_postcode']);
	unset($field['billing']['billing_postcode']);
	$field['unidress_shipping'] = array(
		'unidress_shipping' => array(
			'class'       => array( 'notes' ),
			'required'    => $required,
			'label'       => __( 'Choose Shipping', 'unidress' ),
		),
	);

	$field['billing']['billing_first_name']['custom_attributes']['readonly'] = 'readonly';
	$field['billing']['billing_last_name']['custom_attributes']['readonly']= 'readonly';

	$field['billing']['billing_first_name']['default'] = $user_first_name;
	$field['billing']['billing_last_name']['default'] = $user_last_name;
	$field['billing']['billing_email']['default'] = $user_billing_email;
	$field['billing']['billing_phone']['default'] = $user_billing_phone;

	$field['billing']['billing_email']['required'] = false;



	return $field;
}, 4, 10);

// CHANGE EMAIL UN1-T130
add_action('woocommerce_after_checkout_form', function () {
	?><script type="text/javascript">document.getElementById('billing_email').value = '';</script><?php
});

add_action('woocommerce_checkout_order_processed', 'add_unidress_shipping_to_order', 10, 4);
function add_unidress_shipping_to_order($order_id, $posted_data, $order) {
    update_post_meta($order_id, 'unidress_shipping', $posted_data['unidress_shipping']);
}

// UN1-T62: Hide Suggestion
add_action( 'init', 'remove_action_unidress' );
function remove_action_unidress() {
	// Hide from Products Page
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

	// Hide from Welcome Page:
	remove_action( 'homepage', 'storefront_product_categories', 20 );
	remove_action( 'homepage', 'storefront_recent_products', 30 );
	remove_action( 'homepage', 'storefront_featured_products', 40 );
	remove_action( 'homepage', 'storefront_popular_products', 50 );
	//remove_action( 'homepage', 'storefront_on_sale_products', 60 );
	remove_action( 'homepage', 'storefront_best_selling_products', 70 );
}

// Cancel add to cart if the limit check failed
add_action('woocommerce_add_to_cart_validation' , 'unidress_add_to_cart_validation', 20, 3);
function unidress_add_to_cart_validation($output, $add_product_id, $add_quantity) {

	$user_id            = get_current_user_id();
	$customer_id        = get_user_meta($user_id, 'user_customer', true);
	$kit_id             = get_user_meta($user_id, 'user_kit', true);
	$user_limits        = get_user_meta($user_id, 'user_limits', true);
	$campaign_id        = get_post_meta($customer_id, 'active_campaign', true);

	//active campaign data
	$groups_in_campaign     = get_post_meta($campaign_id, 'groups', true);
	$product_in_campaign    = get_post_meta($campaign_id, 'product_option', true);

	$customer_type             = get_post_meta($customer_id, 'customer_type', true);
	$customer_ordering_style   = get_ordering_style($customer_id);

	if ( empty($campaign_id) || empty($kit_id) ) {
		return;
	}

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
	if ($customer_type == 'campaign' && $groups_in_kit) {
		$group_in_cart = array();
		foreach ($product_in_kit as $product_id=>$product_options) {
			if (!isset($group_in_cart[$product_options['groups']]))
				$group_in_cart[$product_options['groups']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$group_in_cart[$product_options['groups']] += $product_quantity;

			if ($product_id == $add_product_id)
				$group_in_cart[$product_options['groups']] += $add_quantity;

		}

		if ($group_in_cart)
			foreach ($groups_in_kit as $group_id=>$group) {
				$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
				$group_cart = isset($group_in_cart[$group_id]) ? (int)$group_in_cart[$group_id] : 0;

				$balance = $group['amount'] - $user_limit - $group_cart;

				if ($balance < 0){
					wc_add_notice( __( 'You are not allowed to order more', 'unidress' ) . ' "' . $groups_in_kit[$group_id]['name'] . '"', 'error' );
					$output = false;
					break;
				}
			}

		// Budget limit check in cart
		if ( $customer_ordering_style == 'standard' && $customer_type == 'campaign' ) {

			$budgets_in_campaign        = get_post_meta($campaign_id, 'budget', true);
			$budget_in_kit              = $budgets_in_campaign[$kit_id] ?: 0;

			$user_budget_limits         = get_user_meta($user_id, 'user_budget_limits', true);
			$user_budget_left           = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;

			$product_price_added        = ( isset($product_in_kit[$add_product_id]['price']) &&  $product_in_kit[$add_product_id]['price'] ) ? $product_in_kit[$add_product_id]['price'] : get_post_meta($add_product_id, '_price', true);
			$product_price_added_total  = $product_price_added * $add_quantity;
			$total = WC()->cart->get_totals( 'total' )['total'];

			$balance = $budget_in_kit - (int)$user_budget_left - $total - $product_price_added_total;

			if ($balance < 0) {
				wc_add_notice( __( 'You are exceeding your budget, this product cannot be added to cart.', 'unidress' ), 'error' );
				$output = false;
			}
		}
	}

	return $output;
}

// Redirect if the limit check failed
function check_proceed_to_checkout() {

	$output = false;

	$user_id            = get_current_user_id();
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

	if ( empty($campaign_id) || empty($kit_id) ) {
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
		foreach ($product_in_kit as $product_id=>$product_options) {
			if (!isset($required_products_in_cart[$product_options['required_products']]))
				$required_products_in_cart[$product_options['required_products']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$required_products_in_cart[$product_options['required_products']] += $product_quantity;
		}

		if ($required_products_in_cart)
			foreach ($required_in_kit as $group_id=>$group) {
				$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
				$required_cart = isset($required_products_in_cart[$group_id]) ? (int)$required_products_in_cart[$group_id] : 0;

				$balance = $group['amount'] - $user_limit - $required_cart;

				if ($balance > 0){
					$output = true;
					wc_add_notice( sprintf( __( 'Need More Essential Products. Go to <a href="%s">Cart</a> or <a href="%s">Shop</a>' , 'unidress' ), esc_url( wc_get_page_permalink( 'cart' ) ), esc_url( wc_get_page_permalink( 'shop' ) ) ), 'error' );
					break;
				}
			}
	}

	// Assign group limit check
	if ($customer_type == 'campaign' && $groups_in_kit) {
		$group_in_cart = array();
		foreach ($product_in_kit as $product_id=>$product_options) {
			if (!isset($group_in_cart[$product_options['groups']]))
				$group_in_cart[$product_options['groups']] = 0;

			$product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
			$group_in_cart[$product_options['groups']] += $product_quantity;
		}

		if ($group_in_cart)
			foreach ($groups_in_kit as $group_id=>$group) {
				$user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
				$group_cart = isset($group_in_cart[$group_id]) ? (int)$group_in_cart[$group_id] : 0;

				$balance = $group['amount'] - $user_limit - $group_cart;

				if ($balance < 0){
					wc_add_notice( __( 'You have exceeded the limit by product group', 'unidress' ), 'error' );
					$output = true;
					break;
				}
			}
	}

	// You already buy something check
	if ($one_order_toggle && isset($one_order_value[$campaign_id][$kit_id]) && $one_order_value[$campaign_id][$kit_id] ) {
		$output = true;
		wc_add_notice( __( 'You already buy something', 'unidress' ), 'error' );
	}

	// Product list check in cart
	if ($product_in_cart && $product_in_kit) {
		foreach ($product_in_cart as $product_id => $product_option) {
			if ( !array_key_exists($product_id, $product_in_kit ) ) {
				$product = wc_get_product( $product_id );
				$product_title = $product->get_title();
				wc_add_notice(  __( 'Product "' . $product_title . '" not available for sale', 'unidress' ), 'error' );
				$output = true;
			}
		}
	}

	// Budget limit check in cart
	if ( $customer_ordering_style == 'standard' && $customer_type == 'campaign' ) {

		$budgets_in_campaign    = get_post_meta($campaign_id, 'budget', true);
		$budget_in_kit          = $budgets_in_campaign[$kit_id] ?: 0;

		$user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
		$user_budget_left   = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
		if (empty($campaign_id) || empty($kit_id)) {
			return;
		}

		$total = WC()->cart->get_totals( 'total' )['total'];
		$balance = $budget_in_kit - (int)$user_budget_left - $total;

		if ($balance < 0) {
			wc_add_notice( __( 'The total amount of the purchase exceeds the balance of your budget', 'unidress' ), 'error' );
			$output = true;
		}

	}

	return $output;

}
add_action('login_footer', function (){
	?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function(){
            let p = document.createElement('p');
            p.setAttribute('id', 'backtoblog');
            p.innerHTML = '<a href="https://www.unidress.co.il/"> <?php echo __('&larr; To Unidress Main Site', 'unidress') ?> </a>';
            document.getElementById('backtoblog').replaceWith(p);
        });
    </script>
<?php
});

function storefront_credit() {
	?>
    <div class="site-info">
		<?php echo esc_html( apply_filters( 'storefront_copyright_text', $content = '&copy; ' . get_bloginfo( 'name' ) . ' ' . date( 'Y' ) ) ); ?>
		<?php if ( apply_filters( 'storefront_credit_link', true ) ) { ?>
            <br />
			<?php
			if ( apply_filters( 'storefront_privacy_policy_link', true ) && function_exists( 'the_privacy_policy_link' ) ) {
				the_privacy_policy_link( '', '<span role="separator" aria-hidden="true"></span>' );
			}
			?>
		<?php } ?>
    </div><!-- .site-info -->
	<?php
}

add_action( 'storefront_header', 'unidress_new_search_container', 60 );
function unidress_new_search_container () {
    do_action('unidress_new_search_container');
}

add_action( 'template_redirect', 'unidress_header_template' );
function unidress_header_template () {
    //add unidress logo
	add_action( 'storefront_header', function (){
		echo '<div class="unidress-logo"><img src="' . plugins_url( '/unidress/unidress-logo.png') . '"></div>';
	}, 40 );

	//move search bat to cart
	remove_action( 'storefront_header', 'storefront_product_search', 40 );
	remove_action( 'storefront_header', 'storefront_header_cart', 60 );
	add_action( 'unidress_new_search_container', function (){
	    echo '<div class="unidress-menu-element">';
    }, 10 );
	add_action( 'unidress_new_search_container', 'storefront_product_search', 20 );
	add_action( 'unidress_new_search_container', 'storefront_header_cart', 30 );
	add_action( 'unidress_new_search_container', function (){
		echo '</div>';
	}, 40 );

	add_action( 'storefront_before_content', function (){
		echo '<div class="user-budget-bar-wrapper visible-xs mb-10">';
		get_budget_banner();
		echo '</div>';

	}, 70 );
}

// redirect to shop page after add_to_cart
add_filter( 'woocommerce_cart_redirect_after_error', 'unidress_shopping_redirect_to_shop_error', 30 , 2);
add_filter( 'woocommerce_add_to_cart_redirect', 'unidress_shopping_redirect_to_shop', 30 , 2);
function unidress_shopping_redirect_to_shop_error ($url, $adding_to_cart) {
    $url = wc_get_page_permalink( 'shop' );
	return $url;
}
function unidress_shopping_redirect_to_shop ($url, $adding_to_cart) {
    if (is_product())
	    $url = wc_get_page_permalink( 'shop' );
	return $url;
}

// enable Order notes
add_filter( 'woocommerce_enable_order_notes_field', 'unidress_enable_order_notes_field', 10 , 2);
function unidress_enable_order_notes_field ($enable) {
	$user_id                = get_current_user_id();
	$customer_id            = get_user_meta($user_id, 'user_customer', true);
	$campaign_id            = get_post_meta($customer_id, 'active_campaign', true);
	$enable_order_notes     = get_post_meta($campaign_id, 'enable_order_notes', true);

	$enable = $enable_order_notes ? true : false;

	return $enable;
}

/**
 * Display the page header
 */
function storefront_page_header() {
	if ( is_front_page() && is_page_template( 'template-fullwidth.php' ) ) {
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
	$budget_in_kit    = $budgets_in_campaign[$kit_id] ?: 0;

	$total = WC()->cart->get_totals( 'total' )['total'];
	$budget = $budget_in_kit - (int)$user_budget_left - $total;
	?>
    <header class="entry-header">
		<?php
		storefront_post_thumbnail( 'full' );
		the_title( '<h1 class="entry-title">', '</h1>' );
		if (is_cart()) {
			// You already buy something check
			if ($one_order_toggle) {
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
add_filter( 'woocommerce_account_menu_items', 'unidress_edit_account_menu_items', 10 , 2);
function unidress_edit_account_menu_items ($items, $endpoints) {
    $disable_items = array (
	    'dashboard'   ,
	    'downloads'   ,
	    'edit-address'
    );
    foreach ($disable_items as $item) {
        if (isset($items[$item])) unset($items[$item]);
    }
	return $items;
}

//UN1-T102: Fix the mobile menu
add_action( 'storefront_header', 'unidress_remove_handheld_footer_bar', 10 );
function unidress_remove_handheld_footer_bar () {
	remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );
}
add_action( 'storefront_before_header', 'unidress_add_header_bar', 10 );
function unidress_add_header_bar () {
    ?>
    <div class="unidress-mobile-header">
        <div class="unidress-mobile-header-bar">
            <div class="menu-burger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="menu-logo">
                <img src="<?php echo plugins_url( '/unidress/unidress-logo-white.png')?>">
            </div>
            <div class="my-account">
               <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) ?>"></a>
            </div>
            <div class="search">
                <a></a>
            </div>
            <div class="cart">
                <a class="footer-cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'storefront' ); ?>">
                    <span class="count"><?php echo wp_kses_data( WC()->cart->get_cart_contents_count() ); ?></span>
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



