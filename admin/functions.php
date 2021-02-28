<?php
/**
 * Functions.php
 *
 * @package  Theme_Customisations
 * @author   WooThemes
 * @since    1.0.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
global $translatePluginsAdmin;
$translatePluginsAdmin = array(
	'type' => array(
		'customers' => array(
			'no_active_campaign' => __('no active campaign', 'unidress'),
			'no_active_project' => __('no active project', 'unidress'),
		),
	),
);

define('MY_PLUGIN_ROOT_ADMIN', MY_PLUGIN_ROOT . '/admin');

require_once MY_PLUGIN_ROOT_ADMIN . '/include/functions/functions.php';

//требуем указать заголовок и отрывок end

add_action('admin_head', 'unidress_style_script');
function unidress_style_script() {
	wp_enqueue_style('style-admin', plugins_url('/style-admin.css', __FILE__));
	wp_enqueue_script('main-js', plugins_url('/js/main.js', __FILE__), array('jquery'));
	wp_enqueue_script('dynamic-select-js', plugins_url('/js/dynamic-select.js', __FILE__), array('jquery'));
}

//user's field
//add_filter('manage_users_columns', 'show_users_column');
//add_filter('manage_users_custom_column', 'show_users_column_content', 10, 3);

//new post-type
add_action('init', 'create_customers_post_type');
add_action('manage_customers_posts_columns', 'customers_title_columns');
add_action('manage_customers_posts_custom_column', 'customers_value_columns', 5, 2);

add_action('init', 'create_branch_post_type');
add_action('manage_branch_posts_columns', 'branch_title_columns');
add_action('manage_branch_posts_custom_column', 'branch_value_columns', 5, 2);

add_action('init', 'create_kits_post_type');
add_action('manage_kits_posts_columns', 'kits_title_columns');
add_action('manage_kits_posts_custom_column', 'kits_value_columns', 5, 2);

add_action('init', 'create_project_post_type');
add_action('manage_project_posts_columns', 'project_title_columns');
add_action('manage_project_posts_custom_column', 'project_value_columns', 5, 2);

add_action('init', 'create_campaign_post_type');
add_action('manage_campaign_posts_columns', 'campaign_title_columns');
add_action('manage_campaign_posts_custom_column', 'campaign_value_columns', 5, 2);

add_action('init', 'create_graphic_post_type');
add_action('manage_graphic_posts_columns', 'graphic_title_columns');
add_action('manage_graphic_posts_custom_column', 'graphic_value_columns', 5, 2);

add_action('init', 'create_shop_post_type');

function show_users_column($columns) {
	unset($columns['posts']);
	$columns['user_customer'] = __('Customer Name', 'unidress');
	$columns['user_branch'] = __('Branch', 'unidress');
	$columns['user_department'] = __('Department', 'unidress');
	$columns['user_kit'] = __('Kit', 'unidress');
	return $columns;
}
function show_users_column_content($output = '', $column_name, $user_id) {
	if ($column_name === 'user_customer') {
		$column_value = get_field("user_customer", 'user_' . $user_id);
		$column_value = get_the_title($column_value);
		return $column_value;
	}
	if ($column_name === 'user_branch') {
		$column_value = get_user_meta($user_id, 'user_branch', true);
		$column_value = get_the_title($column_value);
		if (!$column_value) {
			$column_value = '';
		}

		return $column_value;
	}
	if ($column_name === 'user_department') {
		$column_value = get_user_meta($user_id, 'user_department', true);
		return $column_value;
	}
	if ($column_name === 'user_kit') {
		$column_value = get_user_meta($user_id, 'user_kit', true);
		$column_value = get_the_title($column_value);
		if (!$column_value) {
			$column_value = '';
		}

		return $column_value;
	}
	return $output;
}

function create_customers_post_type() {
	$args = array(
		'labels' => array(
			'name' => __('Customers', 'unidress'),
			'singular_name' => __('Customer', 'unidress'),
			'add_new' => __('Add New Customer', 'unidress'),
			'add_new_item' => __('Add New Customer', 'unidress'),
			'edit_item' => __('Edit Customer', 'unidress'),
		),
		'public' => true,
	);
	register_post_type('customers', $args);
}
function customers_title_columns($columns) {
	unset($columns['date']);

	$columns['customer_name'] = __('Name', 'unidress');
	$columns['customer_type'] = __('Customer Type', 'unidress');
	$columns['customer_number'] = __('Priority Customer Number', 'unidress');
	$columns['customer_price'] = __('Customer Price List', 'unidress');
	return $columns;
}
function customers_value_columns($column_name, $post_id) {

	if ($column_name === 'customer_name') {
		echo get_the_title($post_id);
	}
	if ($column_name === 'customer_type') {
		echo get_field("customer_type", $post_id);
	}
	if ($column_name === 'customer_number') {
		$number = get_field("priority_customer_number", $post_id);
		echo '<input type="text" value=' . $number . '><a class="save-field" data-field="priority_customer_number" href="#">' . __('Save', 'unidress') . '</a>';
	}
	if ($column_name === 'customer_price') {

		$field = get_field_object('customer_price_list');

		if ($field['choices']) {

			$selected_price_list = get_field("customer_price_list", $post_id);

			echo '<select class="price-list" data-field="customer_price_list">';

			foreach ($field['choices'] as $k => $v) {
				if ($v == $selected_price_list) {
					echo '<option selected value="' . $k . '">' . $v . '</option>';
				} else {
					echo '<option value="' . $k . '">' . $v . '</option>';
				}
			}
			echo '</select>';
		}

	}
}

function create_branch_post_type() {
	$args = array(
		'labels' => array(
			'name' => __('Branch', 'unidress'),
			'singular_name' => __('Branch', 'unidress'),
			'add_new' => __('Add New Branch', 'unidress'),
			'add_new_item' => __('Add New Branch', 'unidress'),
			'edit_item' => __('Edit Branch', 'unidress'),
		),
		'public' => true,
	);
	register_post_type('branch', $args);
}
function branch_title_columns($columns) {
	unset($columns['date']);
	$columns['branch_customer'] = __('Customer Name', 'unidress');
	$columns['branch_number'] = __('Priority Branch Number', 'unidress');
	return $columns;
}
function branch_value_columns($column_name, $post_id) {
	if ($column_name === 'branch_customer') {
		$branch_customer = get_field("branch_customer", $post_id);

		if ($branch_customer) {
			echo get_the_title($branch_customer);
		}

	}
	if ($column_name === 'branch_number') {
		$number = get_field("branch_priority_number", $post_id);
		echo '<input type="text" value=' . $number . '><a class="save-field" data-field="branch_priority_number" href="#">save</a>';
	}
}

function create_kits_post_type() {
	$args = array(
		'labels' => array(
			'name' => __('Kits', 'unidress'),
			'singular_name' => __('Kit', 'unidress'),
			'add_new' => __('Add New Kit', 'unidress'),
			'add_new_item' => __('Add New Kit', 'unidress'),
			'edit_item' => __('Edit Kit', 'unidress'),
		),
		'public' => true,
	);
	register_post_type('kits', $args);
}
function kits_title_columns($columns) {
	unset($columns['date']);
	$columns['customer_name'] = __('Customer Name', 'unidress');
	return $columns;
}
function kits_value_columns($column_name) {
	if ($column_name === 'customer_name') {
		$customer_object = get_field("kit_customer");
		if (isset($customer_object->post_title)) {
			echo $customer_object->post_title;
		} else {
			echo '-';
		}
	}
}

function create_project_post_type() {
	$args = array(
		'labels' => array(
			'name' => __('Projects', 'unidress'),
			'singular_name' => __('Project', 'unidress'),
			'add_new' => __('Add New Project', 'unidress'),
			'add_new_item' => __('Add New Project', 'unidress'),
			'edit_item' => __('Edit Project', 'unidress'),
		),
		'public' => true,
	);
	register_post_type('project', $args);
}
function project_title_columns($columns) {
	unset($columns['date']);
	$columns['project_customer'] = __('Customer', 'unidress');
	return $columns;
}
function project_value_columns($column_name, $post_id) {
	if (($column_name === 'project_customer') && (get_field("project_customer"))) {
		$column_value = get_field("project_customer", $post_id);
		echo $column_value->post_title;
	}
}

function create_campaign_post_type() {
	$args = array(
		'labels' => array(
			'name' => __('Campaign', 'unidress'),
			'singular_name' => __('Campaign', 'unidress'),
			'add_new' => __('Add New Campaign', 'unidress'),
			'add_new_item' => __('Add New Campaign', 'unidress'),
			'edit_item' => __('Edit Campaign', 'unidress'),
		),
		'public' => true,
	);
	register_post_type('campaign', $args);
}
function campaign_title_columns($columns) {
	unset($columns['date']);
	$columns['campaign_customer'] = __('Customer', 'unidress');
	return $columns;
}
function campaign_value_columns($column_name, $post_id) {
	if (($column_name === 'campaign_customer') && (get_field("campaign_customer"))) {
		$column_value = get_field("campaign_customer", $post_id);
		echo $column_value->post_title;
	}
}

function create_graphic_post_type() {
	$args = array(
		'labels' => array(
			'name' => __('Graphics', 'unidress'),
			'singular_name' => __('Graphic', 'unidress'),
			'add_new' => __('Add New Graphic', 'unidress'),
			'add_new_item' => __('Add New Graphic', 'unidress'),
			'edit_item' => __('Edit Graphic', 'unidress'),
		),
		'public' => true,
		'supports' => array('title'),
	);
	register_post_type('graphic', $args);
}
function graphic_title_columns($columns) {
	unset($columns['date']);
	$columns['customer'] = __('Customer', 'unidress');
	$columns['embroidery'] = __('Embroidery type', 'unidress');
	$columns['colors'] = __('Colors in logo', 'unidress');
	$columns['location'] = __('Print location', 'unidress');
	$columns['image'] = __('Image', 'unidress');
	return $columns;
}
function graphic_value_columns($column_name, $post_id) {
	if ($column_name === 'customer') {
		echo get_the_title(get_field("graphic_customer"));
	}
	//to do: May be add taxonomy term?
	if ($column_name === 'embroidery') {
		if (get_field("graphic_embroidery") == 0) {
			echo __('No embroidery', 'unidress');
		} else {
			echo get_term(get_field("graphic_embroidery"))->name;
		}
	}
	//to do: May be add taxonomy term?
	if ($column_name === 'colors') {
		if (get_field("graphic_colors") == 0) {
			echo __('No color', 'unidress');
		} else {
			echo get_term(get_field("graphic_colors"))->name;
		}
	}
	if ($column_name === 'location') {

		if (get_field("graphic_location") == 0) {
			echo __('No graphic', 'unidress');
		} else {
			echo get_term(get_field("graphic_location"))->name;
		}
	}
	if ($column_name === 'image') {
		echo '<img style="max-width: 50px" src="' . get_field("graphic_image") . '">';
	}
}

function create_shop_post_type() {
	$args = array(
		'labels' => array(
			'name' => __('Shops', 'unidress'),
			'singular_name' => __('Shops', 'unidress'),
			'add_new' => __('Add New Shop', 'unidress'),
			'add_new_item' => __('Add New Shop', 'unidress'),
			'edit_item' => __('Edit Shop', 'unidress'),
		),
		'public' => true,
		'menu_position' => 60,
		'show_in_menu' => 'unidress_options',
	);
	register_post_type('shop', $args);
}
// Show custom post type in one column
// New publish/save button
add_action('admin_menu', 'change_publish_box');
function narrow_publish_box($post, $args = array()) {
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object($post_type);
	$can_publish = current_user_can($post_type_object->cap->publish_posts);
	?>
    <div class="submitbox" id="submitpost">
    <div id="publishing-action2">
        <span class="spinner"></span>
        <?php
if (!in_array($post->post_status, array('publish', 'future', 'private')) || 0 == $post->ID) {
		if ($can_publish):
			if (!empty($post->post_date_gmt) && time() < strtotime($post->post_date_gmt . ' +0000')):
			?>
				                    <input name="original_publish" type="hidden" id="original_publish"
				                           value="<?php echo esc_attr_x('Schedule', 'post action/button label'); ?>"/>
				                    <?php submit_button(_x('Schedule', 'post action/button label'), 'primary large', 'publish', false);?>
				                <?php else: ?>
                    <input name="original_publish" type="hidden" id="original_publish"
                           value="<?php esc_attr_e('Save');?>"/>
                    <?php submit_button(__('Publish'), 'primary large', 'publish', false);?>
                <?php
endif;
		else:
		?>
                <input name="original_publish" type="hidden" id="original_publish"
                       value="<?php esc_attr_e('Save');?>"/>
                <?php submit_button(__('Submit for Review'), 'primary large', 'publish', false);?>
            <?php
endif;
	} else {
		?>
            <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Save');?>"/>
            <input name="save" type="submit" class="button button-primary button-large" id="publish"
                   value="<?php esc_attr_e('Save');?>"/>
            <?php
}
	?>
    </div>
    <div id="delete-action2">
        <?php
if (current_user_can('delete_post', $post->ID)) {
		if (!EMPTY_TRASH_DAYS) {
			$delete_text = __('Delete Permanently');
		} else {
			$delete_text = __('Move to Trash');
		}
		?>
            <a class="submitdelete deletion"
               href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a>
            <?php
}
	?>
    </div>
    <div class="clear"></div>
    <?php
}
function change_publish_box() {

	$screens = array(
		'customers',
		'branch',
		'kits',
		'project',
		'campaign',
		'ordering-campaign',
		'ordering-project',
		'shop',
		'graphic',
	);

	remove_meta_box('submitdiv', $screens, 'normal');
	add_meta_box('save_button', __('Publish'), 'narrow_publish_box', $screens, 'normal', 'low');
}
add_filter("screen_layout_columns", 'show_post_in_one_column', 10, 3);
function show_post_in_one_column($empty_columns, $screen_id) {

	$screens = array(
		'customers',
		'branch',
		'kits',
		'project',
		'campaign',
		'ordering-campaign',
		'ordering-project',
		'shop',
		'graphic',
	);

	if (!in_array($screen_id, $screens)) {
		return;
	}

	add_screen_option(
		'layout_columns',
		array(
			'max' => 2,
			'default' => 1,
		)
	);
}

// Add new taxonomy.
// Ordering Style for Campaign Customers
// Ordering Style for Project Customers
add_action('init', 'create_ordering_campaign_taxonomies');
function create_ordering_campaign_taxonomies() {

	register_taxonomy('ordering_style_campaign', 'customers', array(
		'hierarchical' => false,
		'labels' => array(
			'name' => _x('Ordering Style for Campaign Customers', 'taxonomy general name', 'unidress'),
			'singular_name' => _x('Ordering Style', 'taxonomy singular name', 'unidress'),
			'search_items' => __('Search Ordering Style', 'unidress'),
			'all_items' => __('All Ordering Style', 'unidress'),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Ordering Style - Campaign Customers', 'unidress'),
			'update_item' => __('Update Ordering Style - Campaign Customers', 'unidress'),
			'add_new_item' => __('Add New Ordering Style - Campaign Customers', 'unidress'),
			'menu_name' => __('Ordering Style - Campaign Customers', 'unidress'),
		),
		'public' => false,
		'show_in_nav_menus' => false,
		'show_ui' => true,
		'show_in_menu' => false,
		'meta_box_cb' => false,
	));

	register_taxonomy('ordering_style_project', 'customers', array(
		'hierarchical' => false,
		'labels' => array(
			'name' => _x('Ordering Style for Project Customers', 'taxonomy general name', 'unidress'),
			'singular_name' => _x('Ordering Style', 'taxonomy singular name', 'unidress'),
			'search_items' => __('Search Ordering Style', 'unidress'),
			'all_items' => __('All Ordering Style', 'unidress'),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Ordering Style - Project Customers', 'unidress'),
			'update_item' => __('Update Ordering Style - Project Customers', 'unidress'),
			'add_new_item' => __('Add New Ordering Style - Project Customers', 'unidress'),
			'menu_name' => __('Ordering Style - Project Customers', 'unidress'),
		),
		'public' => false,
		'show_in_nav_menus' => false,
		'show_ui' => true,
		'show_in_menu' => false,
		'meta_box_cb' => false,
	));

}

// Add Option menu to admin-menu (magic)
add_action('admin_menu', 'register_unidress_options');
function register_unidress_options() {
	$menu_title = '';
	$taxname = '';
	if (isset($_GET['taxonomy'])) {

		switch ($_GET['taxonomy']) {
		case 'ordering_style_campaign':
			$taxname = $_GET['taxonomy'];
			$menu_title = 'Options';
			break;

		case "ordering_style_project":
			$taxname = $_GET['taxonomy'];
			$menu_title = 'Options';
			break;

		case "options":
			$taxname = $_GET['taxonomy'];
			$menu_title = 'Products';
			break;
		}
	}

	$taxonomies = isset($_GET['taxonomy']) && $_GET['taxonomy'] === $taxname;

	if ($taxonomies) {
		add_filter('parent_file', '__return_false');
	}

	add_menu_page(__('Options', 'unidress'), __('Options', 'unidress'), 'edit_others_posts', 'unidress_options', 'unidress_options_function', 'dashicons-admin-generic', '50.2');
	add_submenu_page('unidress_options', __('Ordering Style - Campaign Customers', 'unidress'), __('Ordering Style - Campaign Customers', 'unidress'), 'manage_options', "edit-tags.php?taxonomy=ordering_style_campaign", null);
	add_submenu_page('unidress_options', __('Ordering Style - Project Customers', 'unidress'), __('Ordering Style - Project Customers', 'unidress'), 'manage_options', "edit-tags.php?taxonomy=ordering_style_project", null);

	$menu_item = &$GLOBALS['menu'][key(wp_list_filter($GLOBALS['menu'], [$menu_title]))];

	foreach ($menu_item as &$val) {

		if (false !== strpos($val, 'menu-top')) {
			$val = 'menu-top' . ($taxonomies ? ' current activated-submenu' : '');
		}

	}

}

// add submenu for graphic post menu
add_action('admin_menu', 'register_products_options');
function register_products_options() {
	add_submenu_page('edit.php?post_type=graphic', __('Graphics Properties', 'unidress'), __('Graphics Properties', 'unidress'), 'manage_product_terms', 'product_option', 'product_option_page');
}
function product_option_page() {
	include_once dirname(__FILE__) . '/product_option.php';
	product_options_output();
}

// new Option tab on product page
add_filter('woocommerce_product_data_tabs', 'add_tab_product_menu', 50);
function add_tab_product_menu($array) {

	$array['option'] = array(
		'label' => 'Option',
		'target' => 'product_options',
		'class' => array(),
		'priority' => 80,
	);
	return $array;
}
// Create taxonomy for graphics properties
add_action('init', 'create_graphics_properties_taxonomies');
function create_graphics_properties_taxonomies() {
	$options = get_posts(array(
		'post_type' => 'product_options',
		'suppress_filters' => false,
	));
	foreach ($options as $option) {
		register_taxonomy($option->post_name, 'product_options', array(
			'hierarchical' => false,
			'labels' => array(
				'name' => _x($option->post_title, 'taxonomy general name'),
				'singular_name' => _x($option->post_title, 'taxonomy singular name'),
				'search_items' => __('Search', 'unidress') . $option->post_title,
				'all_items' => __('All Options', 'unidress') . $option->post_title,
				'parent_item' => null,
				'parent_item_colon' => null,
				'edit_item' => __('Edit', 'unidress') . ' ' . $option->post_title,
				'update_item' => __('Update', 'unidress') . ' ' . $option->post_title,
				'add_new_item' => __('Add New', 'unidress') . ' ' . $option->post_title,
				'menu_name' => $option->post_title,
			),
			'public' => true,
			'show_in_nav_menus' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'meta_box_cb' => false,
			'_builtin' => true,
		));
	}
}
add_action('woocommerce_product_data_panels', 'show_product_options');
function show_product_options() {
	include 'views/html-product-data-options.php';
}

// show managing values
add_action('admin_head', 'hide_slug_field_for_product_option');
function hide_slug_field_for_product_option() {
	if (get_current_screen()->post_type === 'graphic') {
		echo '<style>
                .nosubsub:before {
                    content: "Managing values";
                    display: block;
                    padding-top: 25px;
                    font-size: 32px;
                    line-height: 32px;
                    text-align: center;
                }
              </style>';
	}
}

/* Change logo in wp-admin log in*/
function wph_login_logo() {
	echo "
    <style>
    body.login #login h1 a {
        background: url('" . plugins_url('/unidress/unidress-logo.png') . "') no-repeat scroll center top transparent;
        background-position: center;
        height: 77px;
        width: 320px;
    }
    </style>
    ";
};
add_action('login_head', 'wph_login_logo');
function wph_login_link() {
	return get_home_url();
}
add_filter('login_headerurl', 'wph_login_link');
function wph_login_title() {
	return 'Unidress';
}
add_filter('login_headertext', 'wph_login_title');

// add filter branch and kits post_types
add_action('restrict_manage_posts', 'add_event_table_filters');
function add_event_table_filters($post_type) {

	wp_enqueue_style('select2', plugins_url('/js/select2/css/select2.min.css', __FILE__));
	wp_enqueue_script('select2-js', plugins_url('/js/select2/js/select2.min.js', __FILE__), array('jquery'));

	if ($post_type == 'branch') {
		echo unidress_dropdown_filter_posts($post_type, 'customers', 'branch_customer');
	}
	if ($post_type == 'kits') {
		echo unidress_dropdown_filter_posts($post_type, 'customers', 'kit_customer');
	}
}

// dropdown with selected post type on front-end
function unidress_dropdown_filter_posts($post_type, $filter_post_type = false, $meta_key = false) {
	$array_search = array();
	if ($meta_key) {

		$args = array(
			'meta_key' => $meta_key,
			'numberposts' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
			'post_type' => $post_type,
			'suppress_filters' => true,
		);

		$posts = get_posts($args);
		foreach ($posts as $post) {
			$array_search[] = get_post_meta($post->ID, $meta_key, true);
		}
	}

	$filter_posts = get_posts(array(
		'numberposts' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
		'post_type' => $filter_post_type,
		'include' => $array_search,
		'suppress_filters' => true,
	));

	$output = '<select class="select2-transform" name="' . $filter_post_type . '">';
	$output .= '<option value="-1"> - ' . esc_attr__($filter_post_type, 'unidress') . ' - </option>';

	foreach ($filter_posts as $filter_post) {
		setup_postdata($post);
		$output .= '<option value="' . $filter_post->ID . '" ' . selected($filter_post->ID, @$_GET[$filter_post_type], 0) . '>' . $filter_post->post_title . '</option>';
	}

	$output .= '</select>';

	wp_reset_postdata();

	return $output;

}
function unidress_dropdown_filter_users($filter_post_type = false, $meta_key = false) {
	$array_search = array();
	$empty_posts = false;

	if ($meta_key) {

		$users = get_users();

		foreach ($users as $user) {
			$meta_value = get_user_meta($user->ID, $meta_key, true);
			if ($meta_value) {
				$array_search[] = $meta_value;
			} else {
				$empty_posts = true;
			}
		}
	}

	$output = '<select class="select2-transform" name="' . $filter_post_type . '">';
	$output .= '<option value="-1"> - ' . esc_attr__($filter_post_type, 'unidress') . ' - </option>';

	if ($empty_posts) {
		$output .= '<option value="0"' . selected(0, @$_GET[$filter_post_type], 0) . '> - ' . esc_attr__('empty', 'unidress') . ' - </option>';
	}

	if ($array_search) {

		$posts = get_posts(array(
			'numberposts' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
			'post_type' => $filter_post_type,
			'include' => $array_search,
			'suppress_filters' => true,
		));

		foreach ($posts as $post) {
			setup_postdata($post);
			$output .= '<option value="' . $post->ID . '" ' . selected($post->ID, @$_GET[$filter_post_type], 0) . '>' . $post->post_title . '</option>';
		}
	}

	$output .= '</select>';

	wp_reset_postdata();

	return $output;

}

function unidress_dropdown_filter_users_by_meta($filter_post_type = false, $meta_key = false) {
	$array_filter = array();
	$empty_posts = false;

	if ($meta_key) {

		$users = get_users();

		foreach ($users as $user) {

			$user_meta = get_user_meta($user->ID, $meta_key, true);

			if ($user_meta) {
				$array_filter[$user->ID] = $user_meta;
			} else {
				$empty_posts = true;
			}
		}

		$array_filter = array_unique($array_filter);

		$output = '<select class="select2-transform" name="' . $meta_key . '">';
		$output .= '<option value="-1"> - ' . esc_attr__($filter_post_type, 'unidress') . ' - </option>';

		if ($empty_posts) {
			$output .= '<option value="0"' . selected(0, @$_GET[$meta_key], 0) . '> - ' . esc_attr__('empty', 'unidress') . ' - </option>';
		}

		foreach ($array_filter as $user_id => $meta_value) {
			$output .= '<option value="' . $user_id . '" ' . selected($user_id, @$_GET[$meta_key], 0) . '>' . $meta_value . '</option>';
		}

		$output .= '</select>';

		return $output;
	}

}
// dropdown with selected post type on back-end
add_filter('request', 'unidress_filter_for_custom_posts');
function unidress_filter_for_custom_posts($vars) {

	global $pagenow;
	global $post_type;

	if (!empty($pagenow) && $pagenow == 'edit.php' && $post_type == 'branch') {
		if (!empty($_GET['customers'])) {

			if (intval($_GET['customers']) >= 0) {
				$vars['meta_query'] = array(
					"relation" => "AND",
					array(
						"key" => "branch_customer",
						"value" => intval($_GET['customers']),
						"compare" => "=",
					),
				);
			} elseif (intval($_GET['customers']) == 0) {
				$vars['meta_query'] = array(
					"relation" => "AND",
					array(
						"key" => "branch_customer",
						"value" => intval($_GET['customers']),
						"compare" => "NOT EXISTS",
					),
				);
			}
			unset($vars['customers']);
			unset($vars['name']);
		}
	}
	if (!empty($pagenow) && $pagenow == 'edit.php' && $post_type == 'kits') {
		if (!empty($_GET['customers'])) {

			if (intval($_GET['customers']) >= 0) {
				$vars['meta_query'] = array(
					"relation" => "AND",
					array(
						"key" => "kit_customer",
						"value" => intval($_GET['customers']),
						"compare" => "=",
					),
				);
			} elseif (intval($_GET['customers']) == 0) {
				$vars['meta_query'] = array(
					"relation" => "AND",
					array(
						"key" => "kit_customer",
						"value" => intval($_GET['customers']),
						"compare" => "NOT EXISTS",
					),
				);
			}
			unset($vars['customers']);
			unset($vars['name']);
		}
	}

	return $vars;

}

// dropdown with selected post type on back-end
add_action('pre_get_users', 'users_filter_handler');
function users_filter_handler($uquery) {
	if (!is_admin() || get_current_screen()->id !== 'users') {
		return;
	}

	$array_filter = array();

	if (isset($_GET['customers'])) {

		if ($_GET['customers'] > 0) {
			$array_filter[] = array(
				"key" => "user_customer",
				"value" => intval($_GET['customers']),
				"compare" => "=",
			);
		}

		if ($_GET['customers'] == 0) {
			$array_filter[] = array(
				"key" => "user_customer",
				"value" => intval($_GET['customers']),
				"compare" => "NOT EXISTS",
			);
		}

	}

	if (isset($_GET['branch'])) {

		if ($_GET['branch'] > 0) {
			$array_filter[] = array(
				"key" => "user_branch",
				"value" => intval($_GET['branch']),
				"compare" => "=",
			);
		}

		if ($_GET['branch'] == 0) {
			$array_filter[] = array(
				"key" => "user_branch",
				"value" => intval($_GET['branch']),
				"compare" => "NOT EXISTS",
			);
		}

	}

	if (isset($_GET['kits'])) {

		if ($_GET['kits'] > 0) {
			$array_filter[] = array(
				"key" => "user_kit",
				"value" => intval($_GET['kits']),
				"compare" => "=",
			);
		}

		if ($_GET['kits'] == 0) {
			$array_filter[] = array(
				"key" => "user_kit",
				"value" => intval($_GET['kits']),
				"compare" => "NOT EXISTS",
			);
		}

	}

	if (isset($_GET['user_department'])) {

		if ($_GET['user_department'] == 0) {

			$array_filter[] = array(
				"key" => "user_department",
				"compare" => "NOT EXISTS",
			);

		}
		if ($_GET['user_department'] > 0) {
			$value = get_user_meta($_GET['user_department'], 'user_department', true);
			$array_filter[] = array(
				"key" => "user_department",
				"value" => $value,
				"compare" => "=",
			);

		}

	}
	if ($array_filter) {
		$uquery->set('meta_query', $array_filter);
	}

	return $uquery;
}

// add filters to users table
function add_users_table_filters($which) {
	wp_enqueue_style('select2', plugins_url('/js/select2/css/select2.min.css', __FILE__));
	wp_enqueue_script('select2-js', plugins_url('/js/select2/js/select2.min.js', __FILE__), array('jquery'));

	if ($which != 'top') {
		return;
	}
	// only top
	echo '<div class="alignleft filter-users">';

	echo unidress_dropdown_filter_users('customers', 'user_customer');
	echo unidress_dropdown_filter_users('branch', 'user_branch');
	echo unidress_dropdown_filter_users('kits', 'user_kit');
	echo unidress_dropdown_filter_users_by_meta('department', 'user_department');

	echo '<input type="submit" name="filter_action" id="post-query-submit" class="button" value="' . __('Filter', 'unidress') . '">';
	echo '<a href="' . site_url("/wp-admin/users.php") . '">' . __('Reset', 'unidress') . '</a>';
	echo '</div>';

}
//add_action('manage_users_extra_tablenav', 'add_users_table_filters');

// Clear One order limit when woocommerce cancelled order
add_action('woocommerce_order_status_changed', function ($order_id, $from, $to) {

	if ($to != 'cancelled') {
		return;
	}

	clear_one_order_limit($order_id);

}, 10, 4);
function clear_one_order_limit($order_id) {
	$user_id = get_post_meta($order_id, '_customer_user', true);
	$one_order_value = get_user_meta($user_id, 'one_order_value', true);
	if (is_array($one_order_value)) {
		$old_order = array_pop(array_pop($one_order_value));

		if ($old_order == $order_id) {
			update_user_meta($user_id, 'one_order_value', '');
		}

	}
}
add_action('woocommerce_admin_order_data_after_order_details', 'unidress_editable_order_meta_general');

function unidress_editable_order_meta_general($order) {
	?>

        <br class="clear" />
        <h4>Shop <a href="#" class="edit_address">Edit</a></h4>
        <?php
/*
	 * get all the meta data values we need
	 */
	$shopid = get_post_meta($order->id, 'unidress_shipping', true);
	$shopdetail = get_post($shopid);

	$user_id = $order->customer_id;
	?>
        <div class="address">
            <p><strong><?php echo $shopdetail->post_title; ?></strong></p>

        </div>
        <div class="edit_address">

            <?php //echo do_action('unidress_shipping_select');
	$customer_id = get_user_meta($user_id, 'user_customer', true);
	$campaign_id = get_post_meta($customer_id, 'active_campaign', true);

	$shops_checked = get_post_meta($campaign_id, 'shops', true);
	$shipping_allow = get_post_meta($campaign_id, 'shipping_allow', true);

	$shops = get_posts(array(
		'numberposts' => -1,
		'include' => $shops_checked,
		'orderby' => 'date',
		'order' => 'DESC',
		'post_type' => 'shop',
		'suppress_filters' => true,
	));
	$output = '';
	if (!empty($shops)) {
		$output .= '<div class="order-shipping">';
		$output .= '<label><strong>' . esc_html__('Shipping to Unidress Shop', 'unidress') . '</strong></label>';

		$output .= '<select class="cart-shipping-list" aria-hidden="true" name="unidress_shipping">';
		foreach ($shops as $shop) {

			$checked = ($shopdetail->ID == $shop->ID) ? 'selected="selected"' : '';
			/*$output .= '  <li>';
            $output .= '        <label>';*/
			$output .= '            <option  value="' . $shop->ID . '" ' . $checked . ' >';
			$output .= $shop->post_title;
			/*$output .= '      </label>';
            $output .= '    </li>';*/

		}
		$output .= '</select>';

		$output .= '</div>';
	}

	echo $output;
	?>

            </div>


<?php }

add_action('woocommerce_process_shop_order_meta', 'unidress_save_general_details');

function unidress_save_general_details($ord_id) {
	update_post_meta($ord_id, 'unidress_shipping', wc_clean($_POST['unidress_shipping']));

}
