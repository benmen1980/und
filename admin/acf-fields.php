<?php
// Define path and URL to the ACF plugin.

$acf_url = plugins_url('/admin/include/ACF/', dirname(__FILE__) );

define('MY_ACF_PATH', __DIR__ . '/include/ACF/');
define('MY_ACF_URL', $acf_url);

include_once( MY_ACF_PATH . 'acf.php' );

// Customize the url setting to fix incorrect asset URLs.
add_filter('acf/settings/url', 'my_acf_settings_url');
function my_acf_settings_url( $url ) {
	return MY_ACF_URL;
}

// (Optional) Hide the ACF admin menu item.
add_filter('acf/settings/show_admin', 'my_acf_settings_show_admin');
function my_acf_settings_show_admin( $show_admin ) {
	return false;
}

function custom_acf_settings_localization($localization){
	return true;
}
add_filter('acf/settings/l10n', 'custom_acf_settings_localization');

function custom_acf_settings_textdomain($domain){
	return 'unidress';
}
add_filter('acf/settings/l10n_textdomain', 'custom_acf_settings_textdomain');

if( function_exists('acf_add_local_field_group') ):
	//Branch fields
    acf_add_local_field_group(array(
        'key' => 'group_5c86c5c2c124f',
        'title' => __('Branch fields', 'unidress'),
        'fields' => array(
            array(
                'key' => 'field_5c86c63c89ddc',
                'label' => __('Priority Branch Number', 'unidress'),
                'name' => 'branch_priority_number',
                'type' => 'text',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array(
	                'width' => '50',
	                'class' => 'unidress-input-width',
	                'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_5c86c64f89ddd',
                'label' => __('Customer', 'unidress'),
                'name' => 'branch_customer',
                'type' => 'post_object',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'post_type' => array(
                    0 => 'customers',
                ),
                'taxonomy' => '',
                'allow_null' => 0,
                'multiple' => 0,
                'return_format' => 'id',
                'ui' => 1,
            ),
            array(
                'key' => 'field_5c86c68689dde',
                'label' => __('Branch Address', 'unidress'),
                'name' => 'branch_address',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_5cd1312f90268',
                'label' => __('Contact Name', 'unidress'),
                'name' => 'contact_name',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_5cd1315090269',
                'label' => __('Contact Phone Number', 'unidress'),
                'name' => 'contact_phone_number',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'branch',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
	//Campaign fields
    acf_add_local_field_group(array(
        'key' => 'group_5c8778a997626',
        'title' => __('Campaign fields', 'unidress'),
        'fields' => array(
            array(
                'key' => 'field_5c8778d42c013',
                'label' => __('Customer', 'unidress'),
                'name' => 'campaign_customer',
                'type' => 'post_object',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'post_type' => array(
                    0 => 'customers',
                ),
                'taxonomy' => '',
                'allow_null' => 0,
                'multiple' => 0,
                'return_format' => 'object',
                'ui' => 1,
            ),
            array(
                'key' => 'field_5cd6fd8d7a4bb',
                'label' => __('Campaign Number', 'unidress'),
                'name' => 'campaign_number',
                'type' => 'number',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'min' => '',
                'max' => '',
                'step' => '',
            ),
	        array(
		        'key' => 'field_5d638cc8adc32',
		        'label' => __('Order Due Date', 'unidress'),
		        'name' => 'order_due_date',
		        'type' => 'date_picker',
		        'instructions' => '',
		        'required' => 0,
		        'conditional_logic' => 0,
		        'wrapper' => array(
			        'width' => '',
			        'class' => 'unidress-input-width',
			        'id' => '',
		        ),
		        'display_format' => 'd/m/Y',
		        'return_format' => 'u',
		        'first_day' => 1,
	        ),
	        array(
		        'key' => 'field_5d8b76a94780f',
		        'label' => __('Enable order notes', 'unidress'),
		        'name' => 'enable_order_notes',
		        'type' => 'true_false',
		        'instructions' => '',
		        'required' => 0,
		        'conditional_logic' => 0,
		        'wrapper' => array(
			        'width' => '',
			        'class' => '',
			        'id' => '',
		        ),
		        'message' => __('Show order notes in checkout page', 'unidress'),
		        'default_value' => 0,
		        'ui' => 0,
		        'ui_on_text' => '',
		        'ui_off_text' => '',
	        ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'campaign',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
	//Customers fields
    acf_add_local_field_group(array(
        'key' => 'group_5c866abc0f284',
        'title' => __('Customers fields', 'unidress'),
        'fields' => array(
            array(
                'key' => 'field_5c866ae41e4de',
                'label' => __('Priority Customer Number', 'unidress'),
                'name' => 'priority_customer_number',
                'type' => 'number',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'min' => '',
                'max' => '',
                'step' => '',
            ),
            array(
                'key' => 'field_5c866af11e4df',
                'label' => __('Customer Type', 'unidress'),
                'name' => 'customer_type',
                'type' => 'select',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'choices' => array(
                    'project' => 'Project customer',
                    'campaign' => 'Campaign customer',
                ),
                'default_value' => array(
                    // 'test' => 'Project customer',
                ),
                'allow_null' => true,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'label',
                'ajax' => 0,
                'placeholder' => '',
            ),
            array(
                'key' => 'field_5c9200c20756a',
                'label' => __('Ordering Style for Campaign Customers', 'unidress'),
                'name' => 'ordering_style',
                'type' => 'taxonomy',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_5c866af11e4df',
                            'operator' => '==',
                            'value' => 'campaign',
                        ),
                    ),
                ),
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'taxonomy' => 'ordering_style_campaign',
                'field_type' => 'select',
                'allow_null' => 0,
                'add_term' => 0,
                'save_terms' => 0,
                'load_terms' => 0,
                'return_format' => 'object',
                'multiple' => 0,
            ),
            array(
                'key' => 'field_5caaf8a774b12',
                'label' => __('Ordering Style for Project Customers', 'unidress'),
                'name' => 'ordering_style',
                'type' => 'taxonomy',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_5c866af11e4df',
                            'operator' => '==',
                            'value' => 'project',
                        ),
                    ),
                ),
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'taxonomy' => 'ordering_style_project',
                'field_type' => 'select',
                'allow_null' => 0,
                'add_term' => 0,
                'save_terms' => 0,
                'load_terms' => 0,
                'return_format' => 'id',
                'multiple' => 0,
            ),
            array(
                'key' => 'field_5c866c331e4e0',
                'label' => __('Customer Price List', 'unidress'),
                'name' => 'customer_price_list',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'choices' => array(
                    'stores' => 'Stores',
                    'jwg' => 'JWG',
                ),
                'default_value' => array(
                ),
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'label',
                'ajax' => 0,
                'placeholder' => '',
            ),
	        array(
		        'key' => 'field_5ced1313470ba',
		        'label' => __('Active Campaign', 'unidress'),
		        'name' => 'active_campaign',
		        'type' => 'post_object',
		        'instructions' => '',
		        'required' => 1,
		        'conditional_logic' => array(
			        array(
				        array(
					        'field' => 'field_5c866af11e4df',
					        'operator' => '==contains',
					        'value' => 'campaign',
				        ),
			        ),
		        ),
		        'wrapper' => array(
			        'width' => '',
			        'class' => 'unidress-input-width',
			        'id' => '',
		        ),
		        'post_type' => array(
			        0 => 'campaign',
		        ),
		        'taxonomy' => '',
		        'allow_null' => 1,
		        'multiple' => 0,
		        'return_format' => 'object',
		        'ui' => 1,
                'placeholder' => 'no active project',
	        ),
	        array(
		        'key' => 'field_5ced1313470b2',
		        'label' => __('Active Project', 'unidress'),
		        'name' => 'active_campaign',
		        'type' => 'post_object',
		        'instructions' => '',
		        'required' => 1,
		        'conditional_logic' => array(
			        array(
				        array(
					        'field' => 'field_5c866af11e4df',
					        'operator' => '==contains',
					        'value' => 'project',
				        ),
			        ),
		        ),
		        'wrapper' => array(
			        'width' => '',
			        'class' => 'unidress-input-width',
			        'id' => '',
		        ),
		        'post_type' => array(
			        0 => 'project',
		        ),
		        'taxonomy' => '',
		        'allow_null' => 1,
		        'multiple' => 0,
		        'return_format' => 'object',
		        'ui' => 1,
                'placeholder' => 'no active project',
	        ),
            array(
                //'key' => 'field_5c91f9674933c',
                'label' => __('Customer\'s Logo', 'unidress'),
                'name' => 'customers_logo',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'customers',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
	//Kits fields
    acf_add_local_field_group(array(
        'key' => 'group_5c86cac40b152',
        'title' => __('Kits fields', 'unidress'),
        'fields' => array(
            array(
                'key' => 'field_5c86cafeab356',
                'label' => __('Kit number', 'unidress'),
                'name' => 'kit_number',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
	                'width' => '50',
	                'class' => 'unidress-input-width',
	                'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_5c86cb06ab357',
                'label' => __('Customer', 'unidress'),
                'name' => 'kit_customer',
                'type' => 'post_object',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'post_type' => array(
                    0 => 'customers',
                ),
                'taxonomy' => '',
                'allow_null' => 0,
                'multiple' => 0,
                'return_format' => 'object',
                'ui' => 1,
            ),
            array(
                'key' => 'field_5c86cb06ab35799',
                'label' => __('Kit Logo', 'unidress'),
                'name' => 'kit_logo',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'kits',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
	//Product option tag
    acf_add_local_field_group(array(
        'key' => 'group_5cbedf489f13d',
        'title' => __('Product option tag', 'unidress'),
        'fields' => array(
            array(
                'key' => 'field_5cbee05235abf',
                'label' => __('Priority SKU', 'unidress'),
                'name' => 'options_priority_sku',
                'type' => 'number',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'min' => '',
                'max' => '',
                'step' => 1,
            ),
            array(
                'key' => 'field_5cbee14035ac0',
                'label' => __('Image', 'unidress'),
                'name' => 'options_image',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'url',
                'preview_size' => 'thumbnail',
                'library' => 'all',
                'min_width' => '',
                'min_height' => '',
                'min_size' => '',
                'max_width' => '',
                'max_height' => '',
                'max_size' => '',
                'mime_types' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'taxonomy',
                    'operator' => '==',
                    'value' => 'all',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'left',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
    //Project fields
    acf_add_local_field_group(array(
        'key' => 'group_5c8768334b61b',
        'title' => __('Project fields', 'unidress'),
        'fields' => array(
            array(
                'key' => 'field_5c87686374a66',
                'label' => __('Customer', 'unidress'),
                'name' => 'project_customer',
                'type' => 'post_object',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'post_type' => array(
                    0 => 'customers',
                ),
                'taxonomy' => '',
                'allow_null' => 0,
                'multiple' => 0,
                'return_format' => 'object',
                'ui' => 1,
            ),
            array(
                'key' => 'field_5cd6fe3e20e34',
                'label' => __('Project Number', 'unidress'),
                'name' => 'project_number',
                'type' => 'number',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => 'unidress-input-width',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'min' => '',
                'max' => '',
                'step' => 1,
            ),
	        array(
		        'key' => 'field_5d638cc8adc32',
		        'label' => __('Order Due Date', 'unidress'),
		        'name' => 'order_due_date',
		        'type' => 'date_picker',
		        'instructions' => '',
		        'required' => 0,
		        'conditional_logic' => 0,
		        'wrapper' => array(
			        'width' => '',
			        'class' => 'unidress-input-width',
			        'id' => '',
		        ),
		        'display_format' => 'd/m/Y',
		        'return_format' => 'u',
		        'first_day' => 1,
	        ),
	        array(
		        'key' => 'field_5d0214dd8f9ed',
		        'label' => __('Graphics in Project', 'unidress'),
		        'name' => 'project_graphics',
		        'type' => 'relationship',
		        'instructions' => '',
		        'required' => 0,
		        'conditional_logic' => 0,
		        'wrapper' => array(
			        'width' => '',
			        'class' => 'unidress-input-width-wide',
			        'id' => '',
		        ),
		        'post_type' => array(
			        0 => 'graphic',
		        ),
		        'taxonomy' => '',
		        'filters' => array(
			        0 => 'search',
		        ),
		        'elements' => array(
			        0 => 'featured_image',
		        ),
		        'min' => '',
		        'max' => '',
		        'return_format' => 'object',
	        ),
	        array(
		        'key' => 'field_5d8b76a94780f',
		        'label' => __('Enable order notes', 'unidress'),
		        'name' => 'enable_order_notes',
		        'type' => 'true_false',
		        'instructions' => '',
		        'required' => 0,
		        'conditional_logic' => 0,
		        'wrapper' => array(
			        'width' => '',
			        'class' => '',
			        'id' => '',
		        ),
		        'message' => __('Show order notes in checkout page', 'unidress'),
		        'default_value' => 0,
		        'ui' => 0,
		        'ui_on_text' => '',
		        'ui_off_text' => '',
	        ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'project',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
	//Shops field
    acf_add_local_field_group(array(
        'key' => 'group_5c9b2ca8a0704',
        'title' => __('Shops field', 'unidress'),
        'fields' => array(
            array(
                'key' => 'field_5c9b2cb30918c',
                'label' => __('Address', 'unidress'),
                'name' => 'address',
                'type' => 'text',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'shop',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
	//Users fields
    acf_add_local_field_group(array(
        'key' => 'group_5c87598d02405',
        'title' => __('Users fields', 'unidress'),
        'fields' => array(
            array(
                'key' => 'field_5c875d16244f5',
                'label' => __('Customer', 'unidress'),
                'name' => 'user_customer',
                'type' => 'post_object',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => 'unidress-input-width',
                    'id' => 'user_customer',
                ),
                'post_type' => array(
                    0 => 'customers',
                ),
                'taxonomy' => '',
                'allow_null' => 1,
                'multiple' => 0,
                'return_format' => 'object',
                'ui' => 1,
            ),
            array(
                'key' => 'field_5c875d48244f6',
                'label' => __('Branch', 'unidress'),
                'name' => 'user_branch',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => 'unidress-input-width',
                    'id' => 'user_brunch',
                ),
                'choices' => array(
                ),
                'default_value' => array(
                ),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'ajax' => 0,
                'placeholder' => '',
            ),
            array(
                'key' => 'field_5c875d63244f7',
                'label' => __('Kit', 'unidress'),
                'name' => 'user_kit',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => 'unidress-input-width',
                    'id' => 'user_kit',
                ),
                'choices' => array(
                ),
                'default_value' => array(
                ),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'ajax' => 0,
                'placeholder' => '',
            ),
	        array(
		        'key' => 'field_5d70f752e575a',
		        'label' => __('Department', 'unidress'),
		        'name' => 'user_department',
		        'type' => 'text',
		        'instructions' => '',
		        'required' => 0,
		        'conditional_logic' => 0,
		        'wrapper' => array(
			        'width' => '50',
			        'class' => 'unidress-input-width',
			        'id' => '',
		        ),
		        'default_value' => '',
		        'placeholder' => '',
		        'prepend' => '',
		        'append' => '',
		        'maxlength' => '',
	        ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'user_form',
                    'operator' => '==',
                    'value' => 'all',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
	//Graphic field
	acf_add_local_field_group(array(
		'key' => 'group_5cfba1f26094d',
		'title' => __('Graphic field', 'unidress'),
		'fields' => array(
			array(
				'key' => 'field_5cfba20a621e3',
				'label' => __('Customer', 'unidress'),
				'name' => 'graphic_customer',
				'type' => 'post_object',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'unidress-input-width',
					'id' => '',
				),
				'post_type' => array(
					0 => 'customers',
				),
				'taxonomy' => '',
				'allow_null' => 0,
				'multiple' => 0,
				'return_format' => 'object',
				'ui' => 1,
			),
			array(
				'key' => 'field_5cfba52205fa3',
				'label' => __('Embroidery type', 'unidress'),
				'name' => 'graphic_embroidery',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'unidress-input-width',
					'id' => '',
				),
				'choices' => array(
				),
				'default_value' => array(
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'field_5cfba57a05fa4',
				'label' => __('Colors in logo', 'unidress'),
				'name' => 'graphic_colors',
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'unidress-input-width',
					'id' => '',
				),
				'choices' => array(
				),
				'default_value' => array(
				),
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'field_5cfba57a05fa7',
				'label' => __('Print location', 'unidress'),
				'name' => 'graphic_location',
				'type' => 'taxonomy',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => 'unidress-input-width',
					'id' => '',
				),
				'taxonomy' => 'und_location',
				'field_type' => 'select',
				'allow_null' => 0,
				'add_term' => 1,
				'save_terms' => 0,
				'load_terms' => 0,
				'return_format' => 'id',
				'multiple' => 0,
			),
			array(
				'key' => 'field_5cfc0fd117d9b',
				'label' => __('Print location description', 'unidress'),
				'name' => 'graphic_location_description',
				'type' => 'textarea',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => '',
				'new_lines' => '',
			),
			array(
				'key' => 'field_5cfc11e5f09e5',
				'label' => __('Image', 'unidress'),
				'name' => 'graphic_image',
				'type' => 'image',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'return_format' => 'url',
				'preview_size' => 'thumbnail',
				'library' => 'all',
				'min_width' => '',
				'min_height' => '',
				'min_size' => '',
				'max_width' => '',
				'max_height' => '',
				'max_size' => '',
				'mime_types' => '',
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'graphic',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'acf_after_title',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
	));

endif;

// ACF filters and query's
// only show Customers with customer_type = project
add_filter('acf/fields/post_object/query/name=project_customer', 'get_customers_for_project', 10, 3);
function get_customers_for_project( $args ) {
	$args['meta_value'] = 'project';
	return $args;
}
// only show Customers with customer_type = campaign
add_filter('acf/fields/post_object/query/name=campaign_customer', 'get_customers_for_campaign', 10, 3);
function get_customers_for_campaign( $args ) {
	$args['meta_value'] = 'campaign';
	return $args;
}
//Show Priority SKU inputs for option products (taxonomy)
add_filter('acf/prepare_field/name=options_priority_sku', 'get_options_priority_sku_taxonomies', 10, 4);
function get_options_priority_sku_taxonomies( $field   ) {
	$current_taxonomy = $_GET['taxonomy'];
	$allowed_taxonomy = get_object_taxonomies('product_options' );
	if (!in_array($current_taxonomy, $allowed_taxonomy)) {
		if( $field ) {
			$field = false;
		}
	}
	return $field;
}
//Show image inputs for option products (taxonomy)
add_filter('acf/prepare_field/name=options_image', 'get_options_image_taxonomies', 10, 4);
function get_options_image_taxonomies( $field  ) {
	$current_taxonomy = $_GET['taxonomy'];
	$allowed_taxonomy = get_object_taxonomies('product_options' );
	if (!in_array($current_taxonomy, $allowed_taxonomy)) {
		if( $field ) {
			$field = false;
		}
	}
	return $field;
}

// only show Customers with customer_type == campaign
add_filter('acf/fields/post_object/query/name=active_campaign', 'get_campaigns_for_customer', 10, 3);
function get_campaigns_for_customer( $args, $field, $post_id ) {
	$args['meta_value'] = $post_id;
	return $args;
}

// only show "customer graphic" == "project graphic"
add_filter('acf/fields/relationship/query/name=project_graphics', 'get_graphic_for_project_customer', 10, 3);
function get_graphic_for_project_customer( $args, $field, $post_id ) {
	$selected_customer = get_post_meta($post_id, 'project_customer', true );
	$args['meta_value'] = $selected_customer;
	return $args;
}

function fill_graphic_embroidery( $field ) {
	$terms = get_terms( [
		'taxonomy' => 'und_embroidery',
		'hide_empty' => false,
	] );

	$field['choices'][0] = 'No embroidery';

	foreach ( $terms as $term ) {
		$field['choices'][$term->term_id] = $term->name;
	}
	$field['default_value'] = 0;

	return $field;

}
add_filter('acf/load_field/name=graphic_embroidery', 'fill_graphic_embroidery');
function fill_graphic_colors( $field ) {
	$terms = get_terms( [
		'taxonomy' => 'und_colors',
		'hide_empty' => false,
	] );

	$field['choices'][0] = 'No color';

	foreach ( $terms as $term ) {
		$field['choices'][$term->term_id] = $term->name;
	}
	$field['default_value'] = 0;

	return $field;

}
add_filter('acf/load_field/name=graphic_colors', 'fill_graphic_colors');



