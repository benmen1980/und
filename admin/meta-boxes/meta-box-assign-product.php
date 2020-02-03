<?php
add_action( 'admin_menu', 'add_table_product_to_project' );
add_action( 'admin_menu', 'add_table_product_to_campaign' );

function add_table_product_to_project() {
    add_meta_box('add-product-to-project', __( 'Product to project', 'unidress' ), 'table_product_to_project', 'project', 'normal');
}
function add_table_product_to_campaign() {
    add_meta_box('add-product-to-campaign', __( 'Kit on Campaign', 'unidress' ), 'table_product_to_campaign', 'campaign', 'normal');
}

// function unid_assign_product_unserialize($meta) {
    
// }

function table_product_to_project() {
	global $post;
    wp_nonce_field( basename( __FILE__ ), 'table_product_to_project_nonce' );
    wp_enqueue_script( 'product-assign-js', plugins_url( '/unidress/admin/js/product-assign-project.js'), array( 'jquery' ) );

    $post_meta  = get_post_meta($post->ID,'', true);
    foreach ($post_meta as $key=>$meta) {
        if (is_serialized($meta[0]))
            $post_meta[$key][0] = unserialize((string)$meta[0]);
    }
    

    // if (isset($post_meta['kits'][0])) {
    //     $kits = $post_meta['kits'][0];
    // } else {
    //     $kits = '';
    // }


    echo '<section id="section-0" class="assign-products-meta-box">';
        $current_customer = isset($post_meta['project_customer'][0]) ? $post_meta['project_customer'][0]:  false;
        $current_customer_campaign = get_post_meta($current_customer, 'active_campaign', true);
        if ($post->ID == $current_customer_campaign) : ?>
        <div class="active-project">
            <div class="inline-info">
                <label><?php echo esc_attr__( 'Active Project', 'unidress' )?></label>
                <input type="checkbox" disabled="disabled" checked="checked" >
            </div>
        </div>
        <?php else: ?>
        <div class="active-project">
            <div class="inline-info not-active">
                <label><?php echo esc_attr__( 'Not active Project', 'unidress' )?></label>
                <input type="checkbox" disabled="disabled" >
            </div>
        </div>
        <?php endif;
    render_product_to_project($post_meta);
    echo ' </section>';

}
function table_product_to_campaign() {
    global $post;
    wp_nonce_field( basename( __FILE__ ), 'table_product_to_campaign_nonce' );
    wp_enqueue_script( 'product-assign-campaign-js', plugins_url( '/unidress/admin/js/product-assign-campaign.js'), array( 'jquery' ) );

    $post_meta  = get_post_meta($post->ID,'', true);
    foreach ($post_meta as $key=>$meta) {
		if (is_serialized($meta[0]))
			$post_meta[$key][0] = unserialize((string)$meta[0]);
    }


    if (isset($post_meta['kits'][0])) {
        $kits = $post_meta['kits'][0];
    } else {
        $kits = '';
    }

    ?>
    <input type="hidden" id="kits" name="kits" value='<?php echo $kits ?>'>

    <div id="choose_kit">
        <h3 class="choose_kit_title"><?php echo __( 'Select Kit', 'unidress' ) ?></h3>
        <select><?php echo __( 'Choose', 'unidress' ) ?></select>
        <a id="add-kit" class="button"><?php echo __( 'Add', 'unidress' ) ?></a>
    </div>

    <?php

    if ($kits){

        $vowels = array("[", "]", "\"", "\\");
        $kits_array            = explode(",", str_replace($vowels, "", $kits));

	    foreach ($kits_array as $key => $kit) {
		    ?> <section id="section-<?php echo $key ?>"  class="assign-products-meta-box" data-kit='<?php echo $kit ?>'><?php
	        $select_copy = get_select_with_copy_kit ($post->ID, $kit);
            render_kit_info($kit, $post_meta, $select_copy);
		    render_product_to_campaign($kit, $post_meta);
		    ?> </section><?php
	    }
    }
}
function get_select_with_copy_kit ($post_id, $current_kit) {
	$kits  = get_post_meta($post_id,'kits', true);
	$vowels = array("[", "]", "\"", "\\");
	$kits_array            = $kits ? explode(",", str_replace($vowels, "", $kits)) : array();

    $output = '';
    $output .= '<select class="select-kit">';
    $output .= '<option value="">' . esc_attr__( "Select kit to copy", "unidress" ) . '</option>';

    foreach ($kits_array as $key => $kit) {
        if ($kit == $current_kit)
            continue;

        $kit_title = get_post_field( 'post_title', $kit );
	    $output .= '<option value="' . $kit . '">' . $kit_title . '</option>';
    }

    $output .= '</select>';
    $output .= '<a class="button copy-kit">' . __( "Save and Copy from", "unidress" ) . '</a>';

    return $output;
}
//render table
function render_kit_info($kit, $post_meta='', $select_copy =''){
	global $post;
    $kit_title = get_post_field( 'post_title', $kit );
    ?>
    <div class="kit-name">
        <h1 class="kit-title"><?php echo $kit_title ?></h1>
        <div class="kit-buttons">
            <div class="settings-duplicate">
	            <?php echo $select_copy ?>
            </div>
            <a class="button delete-kit"><?php echo __( 'Delete Kit', 'unidress' ) ?></a>
        </div>
    </div>
    <div class="kit-info">
        <div class="kit-one-order-toggle inline-info">
            <label for="one-order-toggle"><?php echo __( 'Only one order per user', 'unidress' ) ?></label>
	        <?php if (isset($post_meta['one_order_toggle'][0]) && isset($post_meta['one_order_toggle'][0][$kit])) : ?>

                <input id="one-order-toggle" type="checkbox" name="one_order_toggle[<?php echo $kit ?>]" <?php echo(($post_meta['one_order_toggle'][0][$kit] =='on') ? 'checked="checked"' : '' )?>>

	        <?php else: ?>

                <input id="one-order-toggle" type="checkbox" name="one_order_toggle[<?php echo $kit ?>]">

	        <?php endif; ?>
        </div>
        <div class="kit-budget">
            <label><?php echo __( 'Budget', 'unidress' ) ?></label>
            <input type="number" name="budget[<?php echo $kit ?>]" step="any" value="<?php echo $post_meta['budget'][0][$kit] ?>">
        </div>
        <?php
        $current_customer = $post_meta['campaign_customer'][0];
        $current_customer_campaign = get_post_meta($current_customer, 'active_campaign', true);
        if ($current_customer_campaign && $post->ID == $current_customer_campaign) : ?>
            <div class="kit-active-campaign inline-info">
                <label><?php echo esc_attr__( 'Active Campaign', 'unidress' )?></label>
                <input type="checkbox" disabled="disabled" checked="checked" >
            </div>
        <?php else: ?>
            <div class="kit-active-campaign inline-info not-active">
                <label><?php echo esc_attr__( 'Not active Campaign', 'unidress' )?></label>
                <input type="checkbox" disabled="disabled" >
            </div>
        <?php endif; ?>
    </div>
    <div class="kit-options kit-groups">
        <h3 class="kit-title"><?php echo esc_attr__( 'Assigning Groups', 'unidress' )?></h3>
        <table data-option="groups">
            <thead>
            <tr>
                <th class="column-group-name"><?php echo esc_attr__( 'Group Name', 'unidress' )?></th>
                <th class="column-group-amount"><?php echo esc_attr__( 'The number in the group', 'unidress' )?></th>
                <th class="column-button"></th>
            </tr>
            </thead>
            <tbody>
            <?php

            if ($kit != 0 && isset($post_meta['groups'][0]) && isset($post_meta['groups'][0][$kit])) : ?>
                <?php
                $assigning_groups = $post_meta['groups'][0][$kit];
                ?>
                <?php foreach ($assigning_groups as $group_id => $group): ?>
                    <tr>
                        <td class="column-group-name padding-left-10"><?php echo $group['name']; ?><input type="hidden" data-group-id="<?php echo $group_id; ?>" name="groups[<?php echo $kit; ?>][<?php echo $group_id; ?>][name]"  value="<?php echo $group['name']; ?>"></td>
                        <td class="column-group-amount"><input type="number" name="groups[<?php echo $kit; ?>][<?php echo $group_id; ?>][amount]" placeholder="<?php echo esc_attr__( 'amount', 'unidress' )?>" value="<?php echo $group['amount'] ?>"></td>
                        <td class="column-button"><a class="btn-remove-option btn-simple"><?php echo esc_attr__('del', 'unidress'); ?></a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td class="column-group-name"><input type="text"></td>
                <td class="column-group-amount"><input type="number"></td>
                <td class="column-button"><a class="btn-add-option btn-simple"><?php echo esc_attr__('add', 'unidress'); ?></a></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="kit-options required-products">
        <h3 class="kit-title"><?php echo esc_attr__( 'Required Products', 'unidress' )?></h3>
        <table data-option="required_products">
            <thead>
            <tr>
                <th class="column-group-name"><?php echo esc_attr__( 'Required Products', 'unidress' )?></th>
                <th class="column-group-amount"><?php echo esc_attr__( 'Minimum number', 'unidress' )?></th>
                <th class="column-button"></th>
            </tr>
            </thead>
            <tbody>
            <?php

            if ($kit != 0 && isset($post_meta['required_products'][0]) && isset($post_meta['required_products'][0][$kit])) : ?>
                <?php $required_products = $post_meta['required_products'][0][$kit]; ?>
                <?php foreach ($required_products as $product_id => $product): ?>
                    <tr>
                        <td class="column-group-name padding-left-10"><?php echo $product['name']; ?><input type="hidden" data-group-id="<?php echo $product_id; ?>" name="required_products[<?php echo $kit; ?>][<?php echo $product_id; ?>][name]"  value="<?php echo $product['name']; ?>"></td>
                        <td class="column-group-amount"><input type="number" name="required_products[<?php echo $kit; ?>][<?php echo $product_id; ?>][amount]" placeholder="<?php echo esc_attr__( 'amount', 'unidress' )?>" value="<?php echo $product['amount'] ?>"></td>
                        <td class="column-button"><a class="btn-remove-option btn-simple"><?php echo esc_attr__('del', 'unidress'); ?></a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <td class="column-group-name"><input type="text"></td>
                <td class="column-group-amount"><input type="number"></td>
                <td class="column-button"><a class="btn-add-option btn-simple"><?php echo esc_attr__('add', 'unidress'); ?></a></td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php

}
function render_product_to_project( $post_meta='') {

    if (isset($post_meta['add_product_to_project']) && $post_meta['add_product_to_project'][0]) {
        $already_assign_product = json_decode ($post_meta['add_product_to_project'][0]);
    } else {
        $already_assign_product = array();
    }
    ?>
    <div class="section-product">
        <div class="product-wrapper table-wrapper">
            <div class="filter-bar">
                <?php echo render_products_category_filter(); ?>
                <?php echo render_products_type_filter(); ?>
                <?php echo render_products_stock_status_filter(); ?>
                <a name="filter_action" table-assign="false" class="product-filter btn-product btn-simple"><?php echo __( 'Filter', 'unidress' ) ?></a>
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php echo __( 'Search', 'unidress' ) ?>:</label>
                    <input type="search" class="search-input" name="s" value="">
                    <a class="button button-search" table-assign="false"><?php echo __( 'Search', 'unidress' ) ?></a>
                </p>
            </div>
            <table id="product-all" class="product-table product-all">
                <thead>
                <tr>
                    <td class="column-image"><span class="wc-image tips" title="<?php echo __( 'Image', 'unidress' ) ?>"><?php echo __( 'Image', 'unidress' ) ?></span></td>
                    <td class="column-name"><?php echo esc_attr__( 'Name', 'unidress' )?></td>
                    <td class="column-sku"><?php echo esc_attr__( 'SKU', 'unidress' )?></td>
                    <td class="column-price"><?php echo esc_attr__( 'Price', 'unidress' )?></td>
                    <td class="column-button" scope="col"><a class="btn-add-all-product btn-simple"><?php echo __( 'Add all', 'unidress' ) ?></a></td>
                </tr>
                </thead>
                <tbody class="choices-list">
                <?php echo get_product_to_campaign(array(
                    'numberposts' => -1,
                    'category'    => 0,
                    'orderby'     => 'date',
                    'order'       => 'DESC',
                    'post_type'   => 'product',
                    'suppress_filters' => true,
                ), false, $already_assign_product, 0);
                ?>
                </tbody>
            </table>
        </div>
        <div class="product-assign-wrapper table-wrapper">
            <h3><?php echo __( 'Assign product', 'unidress' ) ?></h3>
            <input type="hidden" class="add_product"         name="add_product_to_project" value='<?php echo (isset($post_meta['add_product_to_project'][0])) ? $post_meta['add_product_to_project'][0] : '';?>'>
            <div id="filter-assign-product" class="filter-bar">
                <?php echo render_products_category_filter(); ?>
                <?php echo render_products_type_filter(); ?>
                <?php echo render_products_stock_status_filter(); ?>
                <a name="filter_action" table-assign="true" class="product-filter btn-product btn-simple"><?php echo __( 'Filter', 'unidress' ) ?></a>
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php echo __( 'Search', 'unidress' ) ?>:</label>
                    <input type="search" class="search-input" name="s" value="">
                    <a class="button button-search" table-assign="true"><?php echo __( 'Search', 'unidress' ) ?></a>
                </p>
            </div>
            <table class="product-table product-assign">
                <thead>
                <tr>
                    <td class="column-image"><span class="wc-image tips" title="<?php echo __( 'Image', 'unidress' ) ?>"><?php echo __( 'Image', 'unidress' ) ?></span></td>
                    <td class="column-name"><?php echo esc_attr__( 'Name', 'unidress' )?></td>
                    <td class="column-graphics"><?php echo esc_attr__( 'Graphics', 'unidress' )?></td>
                    <td class="column-sku"><?php echo esc_attr__( 'SKU', 'unidress' )?></td>
                    <td class="column-sku"><?php echo esc_attr__( 'Warehouse', 'unidress' )?></td>
                    <td class="column-price"><?php echo esc_attr__( 'Price', 'unidress' )?></td>
                    <td class="column-button"><a class="btn-remove-all-product btn-simple"><?php echo esc_attr__( 'Remove all', 'unidress' )?></a></td>
                    <td class="column-button"><?php echo esc_attr__( 'Variation', 'unidress' )?></a></td>

                </tr>
                </thead>
                <tbody class="choices-list">
                <?php
                echo get_product_to_campaign(array(
                    'numberposts' => -1,
                    'orderby'     => 'post__in ',
                    'order'       => 'ASC',
                    'post_type'   => 'product',
                    'post__in' => $already_assign_product
                ), true, $already_assign_product, 0, $post_meta );
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php

}
function render_product_to_campaign($kit, $post_meta='') {

    $already_assign_product = json_decode (($post_meta['add_product_to_campaign'][0])[$kit]);

    ?>
    <div class="section-product">
        <div class="product-wrapper table-wrapper">
            <h3><?php echo __( 'All product', 'unidress' ) ?></h3>
            <div class="filter-bar">
                <?php echo render_products_category_filter(); ?>
                <?php echo render_products_type_filter(); ?>
                <?php echo render_products_stock_status_filter(); ?>
                <a name="filter_action" table-assign="false" class="product-filter btn-product btn-simple"><?php echo __( 'Filter', 'unidress' ) ?></a>
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php echo __( 'Search', 'unidress' ) ?>:</label>
                    <input type="search" class="search-input" name="s" value="">
                    <a class="button button-search" table-assign="false"><?php echo __( 'Search', 'unidress' ) ?></a>
                </p>
            </div>
            <table id="product-all" class="product-table product-all">
                <thead>
                <tr>
                    <td class="column-image"><span class="wc-image tips" title="<?php echo __( 'Image', 'unidress' ) ?>"><?php echo __( 'Image', 'unidress' ) ?></span></td>
                    <td class="column-name"><?php echo esc_attr__( 'Name', 'unidress' )?></td>
                    <td class="column-sku"><?php echo esc_attr__( 'SKU', 'unidress' )?></td>
                    <td class="column-price"><?php echo esc_attr__( 'Price', 'unidress' )?></td>
                    <td class="column-button" scope="col"><a class="btn-add-all-product btn-simple"><?php echo __( 'Add all', 'unidress' ) ?></a></td>
                </tr>
                </thead>
                <tbody class="choices-list">
                <?php echo get_product_to_campaign(array(
                    'numberposts' => -1,
                    'category'    => 0,
                    'orderby'     => 'date',
                    'order'       => 'DESC',
                    'post_type'   => 'product',
                    'suppress_filters' => true,
                ), false, $already_assign_product);
                ?>
                </tbody>
            </table>
        </div>
        <div class="product-assign-wrapper table-wrapper">
            <h3><?php echo __( 'Assign product', 'unidress' ) ?></h3>
            <input type="hidden" class="add_product"         name="add_product_to_campaign[<?php echo $kit ?>]" value='<?php echo (isset($post_meta['add_product_to_campaign'][0][$kit])) ? $post_meta['add_product_to_campaign'][0][$kit] : '';?>'>
            <div id="filter-assign-product" class="filter-bar">
                <?php echo render_products_category_filter(); ?>
                <?php echo render_products_type_filter(); ?>
                <?php echo render_products_stock_status_filter(); ?>
                <a name="filter_action" table-assign="true" class="product-filter btn-product btn-simple"><?php echo __( 'Filter', 'unidress' ) ?></a>
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php echo __( 'Search', 'unidress' ) ?>:</label>
                    <input type="search" class="search-input" name="s" value="">
                    <a class="button button-search" table-assign="true"><?php echo __( 'Search', 'unidress' ) ?></a>
                </p>
            </div>
            <table class="product-table product-assign">
                <thead>
                <tr>
                    <td class="column-image"><span class="wc-image tips" title="<?php echo __( 'Image', 'unidress' ) ?>"><?php echo __( 'Image', 'unidress' ) ?></span></td>
                    <td class="column-name"><?php echo esc_attr__( 'Name', 'unidress' )?></td>
                    <td class="column-sku"><?php echo esc_attr__( 'SKU', 'unidress' )?></td>
                    <td class="column-price"><?php echo esc_attr__( 'Warehouse', 'unidress' )?></td>
                    <td class="column-option"><?php echo esc_attr__( 'Assignment Group', 'unidress' )?></td>
                    <td class="column-option"><?php echo esc_attr__( 'Required Products', 'unidress' )?></td>
                    <td class="column-price"><?php echo esc_attr__( 'Price', 'unidress' )?></td>
                    <td class="column-button"><a class="btn-remove-all-product btn-simple"><?php echo esc_attr__( 'Remove all', 'unidress' )?></a></td>
                    <td class="column-button"><?php echo esc_attr__( 'Variation', 'unidress' )?></td>
                </tr>
                </thead>
                <tbody class="choices-list">
                <?php
                echo get_product_to_campaign(array(
                    'numberposts' => -1,
                    'orderby'     => 'post__in ',
                    'order'       => 'ASC',
                    'post_type'   => 'product',
                    'post__in' => $already_assign_product
                ), true, $already_assign_product, $kit, $post_meta); ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php

}
function get_product_to_campaign ($arg, $assign = false, $already_assign_product = array(), $kit = 0, $post_meta = false  ) {
	if ((!$already_assign_product) and ($assign)) {
        return;
    }
    $output ='';
    $posts = get_posts( $arg );
    if (!$assign) {

        foreach( $posts as $post ){

            $product_meta       = get_post_meta($post->ID, '',true);
            $data['id']         = $post->ID;
            $data['title']      = render_name_column($post);
            $data['sku']        = (isset($product_meta['_sku'][0])) ? $product_meta['_sku'][0] : '-';
            $data['price']      = (isset($product_meta['_price'][0])) ? $product_meta['_price'][0] : '0';
            $data['image']      = render_thumb_column($post->ID);
            $data['variation']  = wc_get_product( $post->ID )->get_type();
            $data['added']      = in_array($post->ID, $already_assign_product);

            $output .= add_t_body_row($data, $assign);
        }

    } else {
        $assigning_groups   = false;
	    $required_products  = false;
        $product_option     = false;

        if (isset( $post_meta['project_graphics'][0] ) && !empty($post_meta['project_graphics'][0])) {
	        $project_graphics = $post_meta['project_graphics'][0];
	        $graphics = get_posts( array(
		        'include'     => $project_graphics,
		        'post_type'   => 'graphic',
		        'suppress_filters' => true,
	        ) );
            $assigning_graphics = array();
	        foreach ( $graphics as $index => $graphic ) {
                $assigning_graphics[$graphic->ID] = $graphic->post_title;
            }

        }

        if (isset( $post_meta['groups'][0] ))
	        $assigning_groups = $post_meta['groups'][0];

        if (isset( $post_meta['required_products'][0] ))
	        $required_products = $post_meta['required_products'][0];

        if (isset( $post_meta['product_option'][0] ))
            $product_option = $post_meta['product_option'][0];


        foreach( $already_assign_product as $post_id ){

            $product                        = wc_get_product( $post_id );
            $product_meta                   = get_post_meta($post_id, '',true);
            $data['id']                     = $post_id;
            $data['title']                  = render_name_column($post_id);
	        $data['image']                  = render_thumb_column($post_id);
	        $data['sku']                    = (isset($product_meta['_sku'][0])) ? $product_meta['_sku'][0] : '-';
            $data['price']                  = (isset($product_meta['_price'][0])) ? $product_meta['_price'][0] : '0';
	        $data['kit']                    = $kit;
	        $data['assigning_graphics']     = isset($assigning_graphics) ? $assigning_graphics : "";
	        $data['groups']                 = $assigning_groups[$kit];
	        $data['required_products']      = $required_products[$kit];
            $data['product_option']         = isset($product_option[$kit][$post_id]) ? $product_option[$kit][$post_id] : "";
            $data['variation']              = $product ? $product->get_type() : $product;

            $output .= add_t_body_row($data, $assign);
        }
    }

    wp_reset_postdata();

    return $output;
}

function add_t_body_row($data, $assign = false ) {
    $row = '<tr data-id="' . $data['id'] . '">';
    $row .=     '<td class="column-image">' . $data['image'] .    '</td>';
    if ($assign) {
        $row .=     '<td class="column-name"><div class="column-name-title">'  . $data['title'] . '</div>' . unidress_load_variations($data) . '</td>';
    } else {
        $row .=     '<td class="column-name">'  . $data['title'] . '</td>';
    }
	if (isset($data['kit']) && $data['kit'] == 0 && $assign) {
		$row .=     '<td class="column-option">'   . get_assign_graphics_button($data)   .    '</td>';
	}

    $row .=     '<td class="column-sku">'   . $data['sku']   .    '</td>';



    if (!$assign) {


	    $row .= '<td class="column-price"><input type="number" step="any" value="' . $data['price'] . '"></td>';

        $row .= '<td class="column-button">';
	    if (!$data['added'])
            $row .= '<a class="btn-add-product btn-simple" data-id="' . $data['id'] . '">' . __( 'Add', 'unidress' ) . '</a>';
	    $row .= '</td>';
    } else {

        // TEST
        $row .= ' <td><div class="acf-field"><div class="acf-input"><input class="js-assign-product-warehouse" type="text" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][warehouse]" placeholder=" " value="' . ( (isset($data['product_option']['warehouse']) && $data['product_option']['warehouse'] != '0') ? $data['product_option']['warehouse'] : "") . '"></div></div></td>';
        // TEST

        // CHANGE
        if (isset($data['kit']) && $data['kit'] !=''  && $data['kit'] !='0' && $assign) {
            $row .=     '<td class="column-option">' . get_assign_group_select($data)   .    '</td>';
            $row .=     '<td class="column-option">' . get_required_products_select($data)   .    '</td>';
        }
        // CHANGE

	    $row .= '<td class="column-price"><input type="number" step="any" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][price]" placeholder="' . $data['price'] . '" value="' . ( (isset($data['product_option']['price']) && $data['product_option']['price'] != '0') ? $data['product_option']['price'] : "") . '"></td>';
	    $row .= '<td class="column-button"><a class="btn-remove-product btn-simple" data-id="' . $data['id'] . '">Remove</a></td>';

        


        if ($data['variation'] == 'variable') {
            $row .= '<td class="column-button"><a class="show-product-variation btn-simple">' . esc_attr__( 'Variation', 'unidress' ) . '</a></td>';
        } else {
            $row .= '<td class="column-button"></td>';
        }
    }
    $row .= '</tr>';
    return $row;
}
function get_assign_group_select($data) {
    $output = '<select data-option="groups" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][groups]">';
    $output .= '<option value="">' . esc_attr__( 'Not assign', 'unidress' ) . '</option>';

    if (isset($data['groups']) && !$data['groups']==''){

        foreach ($data['groups'] as $group_ID=>$group) {

            if ( isset($data['product_option']['groups']) && ($data['product_option']['groups'] == $group_ID )) {
                $output .= '<option selected="selected" value="' . $group_ID . '">' . $group['name'] . '</option>';
            } else {
                $output .= '<option value="' . $group_ID . '">' . $group['name'] . '</option>';
            }
        }
    }
    $output .= '</select>';

    return $output;
}
function get_required_products_select($data) {
    $output = '<select data-option="required_products" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][required_products]">';
    $output .= '<option value="">' . esc_attr__( 'Not assign', 'unidress' ) . '</option>';

    if (isset($data['required_products']) && !$data['required_products']==''){

        foreach ($data['required_products'] as $group_ID=>$group) {

            if ( isset($data['product_option']['required_products']) && ($data['product_option']['required_products'] == $group_ID )) {
                $output .= '<option selected="selected" value="' . $group_ID . '">' . $group['name'] . '</option>';
            } else {
                $output .= '<option value="' . $group_ID . '">' . $group['name'] . '</option>';
            }
        }
    }
    $output .= '</select>';

    return $output;
}
function get_assign_graphics_button($data) {
	$output = '<a class="show-product-graphics btn-simple">' . __( 'Graphics', 'unidress' ) .'</a>';
	$output .= '<table class="graphic-list striped"><tbody>';
	if (isset($data['assigning_graphics']) && !$data['assigning_graphics']==''){
		foreach ($data['assigning_graphics'] as $graphic_id=>$name) {
			$output .= '<tr data-option="graphic-' . $graphic_id .'" class="variation-row">';
			if ( isset($data['product_option']['graphics']) && in_array($graphic_id, $data['product_option']['graphics'] )) {
				$output .= '<td><input type="checkbox" class="input-checkbox" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][graphics][]" value="' . $graphic_id . '" checked></td>';
			} else {
				$output .= '<td><input type="checkbox" class="input-checkbox" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][graphics][]" value="' . $graphic_id . '" ></td>';
			}
            $output .= '<td>' . $name . '</td>';
            $output .= '</tr>';
		}
	}

	$output .= '</tbody></table>';

	return $output;
}

//render filters fields
function render_products_type_filter() {
    $current_product_type = isset( $_REQUEST['product_type'] ) ? wc_clean( wp_unslash( $_REQUEST['product_type'] ) ) : false; // WPCS: input var ok, sanitization ok.
    $output               = '<select name="product_type" id="dropdown_product_type"><option value="">' . __( 'Filter by product type', 'unidress' ) . '</option>';

    foreach ( wc_get_product_types() as $value => $label ) {
        $output .= '<option value="' . esc_attr( $value ) . '" ';
        $output .= selected( $value, $current_product_type, false );
        $output .= '>' . esc_html( $label ) . '</option>';

        if ( 'simple' === $value ) {

            $output .= '<option value="downloadable" ';
            $output .= selected( 'downloadable', $current_product_type, false );
            $output .= '> ' . ( is_rtl() ? '&larr;' : '&rarr;' ) . ' ' . __( 'Downloadable', 'unidress' ) . '</option>';

            $output .= '<option value="virtual" ';
            $output .= selected( 'virtual', $current_product_type, false );
            $output .= '> ' . ( is_rtl() ? '&larr;' : '&rarr;' ) . ' ' . __( 'Virtual', 'unidress' ) . '</option>';
        }
    }

    $output .= '</select>';
    return $output; // WPCS: XSS ok.
}
function render_products_category_filter() {
    $categories = get_categories( array(
            'taxonomy'           => 'product_cat',
        )
    );
    $output               = '<select name="category"><option value="">' . esc_html__( 'Filter by category', 'unidress' ) . '</option>';

    foreach( $categories as $cat ){
        $output .= '<option value="' . esc_attr( $cat->term_id ) . '">' . esc_html( $cat->name ) . '</option>';
    }
    $output .= '</select>';
    return $output;
}
function render_products_stock_status_filter() {
    $current_stock_status = isset( $_REQUEST['stock_status'] ) ? wc_clean( wp_unslash( $_REQUEST['stock_status'] ) ) : false; // WPCS: input var ok, sanitization ok.
    $stock_statuses       = wc_get_product_stock_status_options();
    $output               = '<select name="stock_status"><option value="">' . esc_html__( 'Filter by stock status', 'unidress' ) . '</option>';

    foreach ( $stock_statuses as $status => $label ) {
        $output .= '<option ' . selected( $status, $current_stock_status, false ) . ' value="' . esc_attr( $status ) . '">' . esc_html( $label ) . '</option>';
    }

    $output .= '</select>';
    return $output;
}

//render column value
function render_name_column($post_id) {
    $title     = _draft_or_post_title( $post_id );
    return $title;
}
function render_thumb_column($product_id) {
    $meta = get_post_meta($product_id,'_thumbnail_id',true);
    return wc_get_gallery_image_html( $meta, true ); // WPCS: XSS ok.
}
/*noway*/
function unidress_load_variations($data) {

    $product_id     = $data['id'];
    $kit_id  = ($data['kit']!='') ? $data['kit'] : '0';
    $product_option = $data['product_option'];
    $product_object = wc_get_product( $product_id );
    $variations     = wc_get_products(
        array(
            'status'  => array( 'private', 'publish' ),
            'type'    => 'variation',
            'parent'  => $product_id,
            'limit'   => -1,
            'orderby' => array(
                'menu_order' => 'ASC',
                'ID'         => 'DESC',
            ),
            'return'  => 'objects',
        )
    );

	$header = '<div style="display: none" class="variation-list">';

    $output = '';
    $array_vari = array();
    $array_vari2 = array();
	$array_vari_slug = array();
	$navigation = '';

	if ( $variations ) {

		if (count( $product_object->get_attributes() ) !== 2 ) {

			foreach ( $variations as $variation_object ) {

				$variation_id   = $variation_object->get_id();

				$checked = '';
				if (isset($product_option['variation'])) {
					if (in_array ($variation_id, $product_option['variation'] ))
						$checked = 'checked';
				}

				$product = wc_get_product( $variation_id );

				$output .= '<div class="variation-row">';
				$output .= '<span><input data-variation="' . $variation_id . '" type="checkbox" name="product_option[' . $kit_id . '][' . $product_id . '][variation][]" value="' . esc_html( $variation_id ) . '" ' . $checked . '></span>';
				$output .= '<span>#' . esc_html( $variation_id ) . '</span>';

				foreach ( $product_object->get_attributes( 'edit' ) as $attribute ) {
					if ( ! $attribute->get_variation() ) {
						continue;
					}
					$output .= '<span>' . $product->get_attribute( sanitize_title( $attribute->get_name() ) ) . '</span>';
				}

				$output .= '</div>';

			}

		} else {

			foreach ( $variations as $variation_object ) {

				$variation_id   = $variation_object->get_id();

				$product = wc_get_product( $variation_id );

                foreach ( $product->get_attributes('edit') as $key => $attribute ) {
                    $array_vari_slug[$attribute] = $key;
                    $array_vari[$product->get_ID()][] = $attribute;
                }

			}

			if (is_array($array_vari)) {
				$checked = '';

				foreach ( $array_vari as $key2=> $attribute2) {
					$array_vari2[$attribute2[0]][$attribute2[1]] = $key2;
				}

				$i = 0;
				$navigation .= '<ul class="navigation-tabs">';
				foreach ( $array_vari2 as $key2 => $attribute2) {
					$product = wc_get_product( current($attribute2 ));
					$attr = $product->get_attribute( sanitize_title( $array_vari_slug[$key2] ) );

					$navigation .= '<li class="button-tab' . ($i==0 ? ' active' : '') .'" data-tab="tab_' . $i .'"><a><input class="tab_checked" data-checked="tab_' . $i .'" type="checkbox">' . $attr .'</a></li>';
					$i++;
				}
				$navigation .= '</ul>';
				$navigation .= '<div class="tab-content navigation-tabContent">';
				$i = 0;

				foreach ( $array_vari2 as $key2=> $attribute2) {

					$navigation .= '<div data-tab="tab_' . $i .'" class="tab-pane' . ($i==0 ? ' show-tab' : '') . ' tab_' . $i .'" >';

					foreach ($attribute2 as $attribute22 => $id){
						$product = wc_get_product( $id );
						$attr2 = $product->get_attribute( sanitize_title( $array_vari_slug[$attribute22] ) );

						if (isset($product_option['variation'])) {
							in_array ($id, $product_option['variation'] ) ? $checked = 'checked' : $checked = '';
						}
						$navigation .= '<div class="variation-row">';
						$navigation .= '<span><input data-variation="' . $id . '" type="checkbox" name="product_option[' . $kit_id . '][' . $product_id . '][variation][]" value="' . esc_html( $id ) . '" ' . $checked . '></span>';
						$navigation .= '<span>' . esc_html($attr2) . '</span>';
						$navigation .= '</div>';
					}
					$navigation .= '</div>';
					$i++;

				}
				$navigation .= '</div>';
			}

		}

	}
    $output .= '</div>';
    return $header.$navigation.$output;
}

//save meta-box
add_action('save_post', 'save_table_product_to_project');

function save_table_product_to_project( $post_id ) {

    

    if ( !isset( $_POST['table_product_to_project_nonce'] )
        || !wp_verify_nonce( $_POST['table_product_to_project_nonce'], basename( __FILE__ ) ) )
        return $post_id;

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        return $post_id;

    if ( !current_user_can( 'edit_post', $post_id ) )
        return $post_id;

    update_post_meta( $post_id, 'add_product_to_project'      , $_POST['add_product_to_project']);
	update_post_meta( $post_id, 'product_option'              , $_POST['product_option']);
	wc_delete_product_transients($post_id);

}

add_action('save_post', 'save_table_product_to_campaign');
function save_table_product_to_campaign( $post_id, $data = '' ) {

    if ( isset($_POST['action']) && ($_POST['action'] != 'editpost') && ($data != '') )
	    $_POST = $data;

    if ( !isset( $_POST['table_product_to_campaign_nonce'] ) || !wp_verify_nonce( $_POST['table_product_to_campaign_nonce'], basename( __FILE__ ) ) )
	    return $post_id;

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        return $post_id;

    if ( !current_user_can( 'edit_post', $post_id ) )
        return $post_id;

    if ($_POST['kits']) {
        // Product options for kit

        $groups             = isset($_POST['groups']) ? $_POST['groups'] : null;
        $required_products  = isset($_POST['required_products']) ? $_POST['required_products'] : null;

        update_post_meta( $post_id, 'kits',       $_POST['kits']);
        update_post_meta( $post_id, 'groups',  $groups);
        update_post_meta( $post_id, 'required_products', $required_products);

	    isset($_POST['one_order_toggle'])           ? update_post_meta( $post_id, 'one_order_toggle'             , $_POST['one_order_toggle']) : update_post_meta( $post_id, 'one_order_toggle', null);
	    isset($_POST['budget'])                     ? update_post_meta( $post_id, 'budget'                       , $_POST['budget']) : '';
	    isset($_POST['print-type'])                 ? update_post_meta( $post_id, 'print-type'                   , $_POST['print-type']) : '';
	    isset($_POST['print-location'])             ? update_post_meta( $post_id, 'print-location'               , $_POST['print-location']) : '';
	    isset($_POST['closed-list'])                ? update_post_meta( $post_id, 'closed-list'                  , $_POST['closed-list']) : '';
	    isset($_POST['add_product_to_campaign'])    ? update_post_meta( $post_id, 'add_product_to_campaign'      , $_POST['add_product_to_campaign']) : '';

	    isset($_POST['product_option'])             ? update_post_meta( $post_id, 'product_option'               , $_POST['product_option']) : '';

    } else {
        delete_post_meta( $post_id, 'kits' );
        delete_post_meta( $post_id, 'one_order_toggle' );
        delete_post_meta( $post_id, 'budget' );
        delete_post_meta( $post_id, 'print-location' );
        delete_post_meta( $post_id, 'closed-list' );
        delete_post_meta( $post_id, 'add_product_to_campaign' );
        delete_post_meta( $post_id, 'product_option' );
        delete_post_meta( $post_id, 'groups' );
        delete_post_meta( $post_id, 'required_products' );
    }

}

