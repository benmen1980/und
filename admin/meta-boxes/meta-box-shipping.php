<?php

add_action( 'admin_menu', 'add_shipping_option');
add_action( 'save_post', 'save_shipping_option');

function add_shipping_option() {
	add_meta_box('campaign_shipping_option', __( 'Shipping', 'unidress' ), 'unidress_shipping_option', array('project', 'campaign'), 'normal');
}

function unidress_shipping_option($post) {
	wp_enqueue_script( 'shipping-js', plugins_url( '/unidress/admin/js/meta-box-shipping.js'), array( 'jquery' ) );

	$post_type = $post->post_type;

	$customer_id = '';
	if ($post_type == 'project') {
		$customer_id = get_post_meta($post->ID, 'project_customer', true);
	} elseif ($post_type == 'campaign') {
		$customer_id = get_post_meta($post->ID, 'campaign_customer', true);
	}
	echo render_unidress_shops_shipping_option($customer_id, $post->ID);
	echo render_shipping_to_user_brunch($post->ID);
	echo render_customer_branches_address_shipping_option($customer_id, $post->ID);

	wp_nonce_field( basename( __FILE__ ), 'project_shipping_option' );
}

function render_shipping_to_user_brunch($campaign_id) {
	get_post_meta($campaign_id, 'shipping_allow', true) ? $checked = 'checked="checked"' : $checked='';
	$min_order_value = get_post_meta($campaign_id, 'min_order_value', true) ?: 0;
	$shipping_price = get_post_meta($campaign_id, 'shipping_price', true) ?: 0;

	$output = '<fieldset>
				<table>
					<thead>
						<tr>
							<th><h4 class="shipping-option-label">' . esc_html__( 'Shipping to user\'s brunch', 'unidress' ) .'</h4></th>
							<th><h4 class="shipping-option-label">' . esc_html__( 'Minimum order value', 'unidress' ) .'</h4></th>
							<th><h4 class="shipping-option-label">' . esc_html__( 'Shipping Price', 'unidress' ) .'</h4></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<ul class="acf-checkbox-list acf-bl">
									<li>
										<label>
											<input type="checkbox" name="shipping_allow" ' .$checked. '>
											'.__( 'Allow shipping to user\'s brunch', 'unidress' ).'
										</label>
									</li>
								</ul>
							</td>
							<td><input type="number" name="min_order_value" value="'.$min_order_value.'"></td>
							<td><input type="number" name="shipping_price" value="'.$shipping_price.'"></td>
						</tr>
					</tbody>
				</table>
			</fieldset>';
	return $output;

}
function render_unidress_shops_shipping_option($customer_id = '', $campaign_id) {
	$output = '<fieldset class="unidress-shops-shipping">';

	$shops_checked = get_post_meta($campaign_id, 'shops', true);

	$shops = get_posts( array(
		'numberposts' => -1,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'post_type'   => 'shop',
		'suppress_filters' => true,
	) );

	$output .= '<h4 class="shipping-option-label">' . esc_html__( 'Unidress shops', 'unidress' ) .'</h4>';

	$output .= '<label>
					<input name="shops[]" class="shipping-all-select" type="checkbox">
					'.esc_html__( 'Select all', 'unidress' ).'
				</label>';

	if ($shops) {
		$output .= '<ul class="acf-checkbox-list acf-bl">';

		foreach( $shops as $shop ){
			$checked = '';
			if (is_array($shops_checked) && in_array($shop->ID, $shops_checked)) {
				$checked = 'checked="checked"';
			}
			setup_postdata($shop);

			$output .= '	<li>';
			$output .= '		<label>';
			$output .= '			<input type="checkbox" class="shipping-select" name="shops[]" value="' . $shop->ID . '" ' .$checked. '>';
			$output .=				$shop->post_title;
			$output .= '		</label>';
			$output .= '	</li>';
		}
		$output .= '</ul>';

		wp_reset_postdata();

	}
	$output .= '</fieldset>';

	wp_reset_postdata();

	return $output;
}
function render_customer_branches_address_shipping_option($customer_id = '', $campaign_id) {
	$output = '';

	if ($customer_id) {

		$shops_branch = get_posts( array(
			'numberposts' => -1,
			'category'    => 0,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'include'     => array(),
			'exclude'     => array(),
			'meta_key'    => 'branch_customer',
			'meta_value'  => $customer_id,
			'post_type'   => 'branch',
			'suppress_filters' => true,
		) );

		if (!$shops_branch)
			return;

		$shops_checked = get_post_meta($campaign_id, 'shops', true);

		$output = '<fieldset class="unidress-shops-shipping">';
		$output .= '<h4 class="shipping-option-label">' . esc_html__( 'Branches addresses', 'unidress' ) .'</h4>';
		$output .= '		<label>';
		$output .= '			<input name="shops[]" class="shipping-all-select" type="checkbox" >';
		$output .=				esc_html__( 'Select all', 'unidress' );
		$output .= '		</label>';
		$output .= '<ul class="acf-checkbox-list acf-bl">';
		foreach( $shops_branch as $shop ){
			$checked = '';
			if (is_array($shops_checked) && in_array($shop->ID, $shops_checked)) {
				$checked = 'checked="checked"';
			}
			setup_postdata($shop);

			$output .= '	<li>';
			$output .= '		<label>';
			$output .= '			<input type="checkbox" name="shops[]" class="shipping-select" value="' . $shop->ID . '" ' .$checked. '>';
			$output .=				$shop->post_title;
			$output .= '		</label>';
			$output .= '	</li>';
			
		}

		$output .= '</ul>';
		$output .= '</fieldset>';

		wp_reset_postdata();

	}

	return $output;

}

function save_shipping_option( $post_id ) {

	if ( !isset( $_POST['project_shipping_option'] ) || !wp_verify_nonce( $_POST['project_shipping_option'], basename( __FILE__ ) ) )
		return $post_id;

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		return $post_id;

	if ( !current_user_can( 'edit_post', $post_id ) )
		return $post_id;

	$shops = isset($_POST['shops']) ? $_POST['shops'] : '';
	$shipping_allow = isset($_POST['shipping_allow']) ? $_POST['shipping_allow'] : '';
	$min_order_value = isset($_POST['min_order_value']) ? $_POST['min_order_value'] : 0;
	$shipping_price = isset($_POST['shipping_price']) ? $_POST['shipping_price'] : 0;
	update_post_meta($post_id, 'shops', $shops);
	update_post_meta($post_id, 'shipping_allow', $shipping_allow);
	update_post_meta($post_id, 'min_order_value', $min_order_value);
	update_post_meta($post_id, 'shipping_price', $shipping_price);
}

//Show shipping in checkout
add_action( 'unidress_shipping_select', 'show_shipping_option_in_checkout');
function show_shipping_option_in_checkout() {

	if (!is_user_logged_in())
		return;

	$user_id        = get_current_user_id();
	$customer_id    = get_user_meta($user_id, 'user_customer', true);
	$campaign_id    = get_post_meta($customer_id, 'active_campaign', true);
	$shops_checked  = get_post_meta($campaign_id, 'shops', true);
	$shipping_allow = get_post_meta($campaign_id, 'shipping_allow', true);

	if (empty($customer_id) || empty($campaign_id) || empty($shops_checked)) {
		return;
	}

	$shops = get_posts( array(
		'numberposts' => -1,
		'include' => $shops_checked,
		'orderby' => 'date',
		'order' => 'DESC',
		'post_type' => 'shop',
		'suppress_filters' => true,
	) );
	$output = '';

	$output .= '<table>';
	$output .= '<tbody>';

	if (!empty($shops)) {

		$output .= '<tr class="order-shipping">';
		$output .= '<th>'. esc_html__( 'Shipping to Unidress Shop', 'unidress' ) .'</th>';
		$output .= '<td>';

		foreach( $shops as $shop ){

			$output .= '<ul class="cart-shipping-list">';
			$output .= '	<li>';
			$output .= '		<label>';
			$output .= '			<input type="radio" name="unidress_shipping" value="' . $shop->ID . '">';
			$output .=				$shop->post_title;
			$output .= '		</label>';
			$output .= '	</li>';
			$output .= '</ul>';

		}

		$output .= '</td>';
	}


	if ($shipping_allow) {

		$user_branch = get_user_meta($user_id, 'user_branch', true);
		$shop = get_post($user_branch);

		$output .= '<tr class="order-shipping">';
		$output .= '<th>'. esc_html__( 'Shipping to your Branch', 'unidress' ) .'</th>';
		$output .= '<td>';

		$title = $shop->post_title . ' - ' . get_post_meta($user_branch, 'branch_address', true);

		$output .= '<ul class="cart-shipping-list">';
		$output .= '	<li>';
		$output .= '		<label>';
		$output .= '			<input type="radio" name="unidress_shipping" value="' . $user_branch . '" checked="checked">';
		$output .=				$title;
		$output .= '		</label>';
		$output .= '	</li>';
		$output .= '</ul>';

		$output .= '</td>';
		$output .= '</tr>';

	} else {

		$shops_branch = get_posts( array(
			'numberposts' => -1,
			'orderby'     => 'date',
			'order'       => 'DESC',
			'include'     => $shops_checked,
			'post_type'   => 'branch',
			'suppress_filters' => true,
		) );

		if (!empty($shops_branch)) {
			$output .= '<tr class="order-shipping">';
			$output .= '<th>'. esc_html__( 'Shipping to your Branch', 'unidress' ) .'</th>';
			$output .= '<td>';
			$output .= '<ul class="cart-shipping-list">';

			foreach( $shops_branch as $key => $shop ){
				$output .= '	<li>';
				$output .= '		<label>';
				$output .= '			<input type="radio" name="unidress_shipping" value="' . $shop->ID . '">';
				$output .=				$shop->post_title;
				$output .= '		</label>';
				$output .= '	</li>';
			}

			$output .= '</ul>';
			$output .= '</td>';
			$output .= '</tr>';
		}

	}

	$output .= '</tbody>';
	$output .= '</table>';

	echo $output;
}
