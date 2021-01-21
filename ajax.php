<?php
/**
 * There all ajax, $_POST, $_GET
 */


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

//Admin
if (wp_doing_ajax()) {

    add_action('wp_ajax_add_product_option', 'add_product_option');
    function add_product_option()
    {
        ob_start();

        check_ajax_referer('add-attribute', 'security');

        if (!current_user_can('edit_products')) {
            wp_die(-1);
        }
        $i             = absint($_POST['i']);
        $taxonomies  = get_posts(array(
            'post_type'        => 'product_options',
            'suppress_filters' => false,
        ));
        $terms = array();

        foreach ($taxonomies as $tax) {

            $product_options_slug = $tax->post_name;

            $terms_obj = get_terms($product_options_slug, array(
                'hide_empty' => false,
            ));
            foreach ($terms_obj as $term) {
                $terms[$term->term_id] = $term->name;
            }
            $product_options[$product_options_slug]['taxonomy_id'] = $tax->ID;
            $product_options[$product_options_slug]['label'] = $tax->post_title;
            if (isset($terms_obj[0])) {
                $product_options[$product_options_slug]['terms'] = $terms;
            }
        }
        $metabox_class = array();

        if ($_POST['taxonomy']) {
            $taxonomy = $_POST['taxonomy'];
            $options[$taxonomy] = array();
            $terms = $product_options[$taxonomy];
            if ($options) {
                $metabox_class[] = 'taxonomy';
                $metabox_class[] = $_POST['taxonomy'];
            }
        }

        include 'admin/views/html-product-options.php';

        wp_die();
    }

    add_action('wp_ajax_save_product_options', 'save_product_options');

    /**
     * Save attributes via ajax.
     */
    function save_product_options()
    {
        check_ajax_referer('save-attributes', 'security');

        if (!current_user_can('edit_products')) {
            wp_die(-1);
        }

        parse_str($_POST['data'], $data);
        $options = array();
        foreach ($data['options_values'] as $key => $value) {
            if ($data['options_id'][$key]) {
                $options[$data['options'][$key]]['terms'] = $value;
            } else {
                $terms = array_map('trim', explode('|', $value));
                $options[$data['options'][$key]]['terms'] = $terms;
            }
            $options[$data['options'][$key]]['taxonomy_id'] = $data['options_id'][$key];
            $options[$data['options'][$key]]['label'] = $data['options_label'][$key];
            if (isset($data['attribute_visibility'][$key])) {
                $options[$data['options'][$key]]['visibility'] = $data['attribute_visibility'][$key];
            }
        }

        update_post_meta($_POST['post_id'], '_product_options', $options);
        wp_die();
    }
    add_action('wp_ajax_add_new_options', 'add_new_options');

    function add_new_options()
    {

        if (current_user_can('manage_product_terms')) {
            $taxonomy = esc_attr($_POST['taxonomy']);
            $term     = wc_clean($_POST['term']);
            if (taxonomy_exists($taxonomy)) {

                $result = wp_insert_term($term, $taxonomy);

                if (is_wp_error($result)) {
                    wp_send_json(
                        array(
                            'error' => $result->get_error_message(),
                        )
                    );
                } else {
                    $term = get_term_by('id', $result['term_id'], $taxonomy);
                    wp_send_json(
                        array(
                            'term_id' => $term->term_id,
                            'name'    => $term->name,
                            'slug'    => $term->slug,
                        )
                    );
                }
            }
        }
        wp_die(-1);
    }

    //Save priority number on post_type page
    add_action('wp_ajax_update_acf_field', 'save_priority');
    function save_priority()
    {
        update_field($_POST['field_name'], $_POST['field_value'], $_POST['id']);
        die;
    }

    // update select data
    add_action('wp_ajax_get_post_type_for_select', 'get_post_type_for_select');
    function get_post_type_for_select()
    {
        global $wpdb;

        if (isset($_POST['post_id'])) {
            $selectedoption = get_post_meta($_POST['post_id'], $_POST['meta_key'], true);
            $query = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_type = '" . $_POST["post_type"] . "'");
        }

        $option = array();
        foreach ($query as $post) {
            $option[$post->ID] = $post->post_title;
        }
        $output = array();
        foreach ($option as $value => $label) {
            if ($value == $selectedoption) {
                $output[] = array(
                    'value' => $value,
                    'label' => $label,
                    'selected' => 'selected'
                );
            } else {
                $output[] = array(
                    'value' => $value,
                    'label' => $label,
                );
            }
        }

        echo json_encode($output);
        die;
    }
    add_action('wp_ajax_get_data_for_graphic_field', 'get_data_for_graphic_field');
    function get_data_for_graphic_field()
    {
        $customer_id = $_POST['customer_id'];

        if (!isset($_POST['customer_id']))
            return;

        $graphics = get_posts(array(
            'numberposts' => -1,
            'include'     => array(),
            'meta_key'    => 'graphic_customer',
            'meta_value'  => $customer_id,
            'post_type'   => 'graphic',
            'suppress_filters' => true,
        ));

        $graphics_list = '';
        foreach ($graphics as $graphic) {
            $graphic_image = get_post_meta($graphic->ID, 'graphic_image', true);
            $graphics_list .= '<li><span class="acf-rel-item" data-id="' . $graphic->ID . '"><div class="thumbnail"><img src="' . $graphic_image . '" alt=""></div>' . (empty($graphic->post_title) ? 'graphic-' . $graphic->ID : $graphic->post_title) . '</span></li>';
        }

        die;
    }
    add_action('wp_ajax_get_data_for_select', 'get_data_for_select');
    function get_data_for_select()
    {

        global $wpdb;
        if (isset($_POST['post_id'])) {
            $selectedoption = '';
            $table_name = $wpdb->prefix . 'posts';
            $query = $wpdb->get_results("SELECT p.ID, p.post_title FROM $table_name p INNER JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id WHERE pm.meta_value = '" . $_POST['affecting_value'] . "' AND pm.meta_key = '" . $_POST['depending_key'] . "'");
        }

        $option = array();
        foreach ($query as $post) {
            $option[$post->ID] = $post->post_title;
        }
        $output = array();
        foreach ($option as $value => $label) {
            if ($label == $selectedoption) {
                $output[] = array(
                    'value' => $value,
                    'label' => $label,
                    'selected' => 'selected'
                );
            } else {
                $output[] = array(
                    'value' => $value,
                    'label' => $label,
                );
            }
        }

        echo json_encode($output);
        die;
    }

    // update select data
    add_action('wp_ajax_load_branch_for_current_customer', 'ajax_load_branch_for_current_customer');
    add_action('wp_ajax_load_PCN_for_current_customer', 'ajax_load_PCN_for_current_customer');
    add_action('wp_ajax_load_kit_for_current_customer', 'ajax_load_kit_for_current_customer');
    add_action('wp_ajax_fill_select_for_current_customer', 'ajax_fill_select_for_current_customer');
    add_action('wp_ajax_fill_selects_for_current_customer', 'ajax_fill_selects_for_current_customer');

    function ajax_load_branch_for_current_customer()
    {

        global $wpdb;

        if (isset($_POST['customer'])) {
            $table_name = $wpdb->prefix . 'posts';
            $query = $wpdb->get_results("SELECT c.ID, c.post_title FROM $table_name c INNER JOIN {$wpdb->prefix}postmeta a ON c.ID = a.post_id WHERE a.meta_value = '" . $_POST['customer'] . "' AND a.meta_key = 'branch_customer'");
        }
        $choices = array();

        foreach ($query as $post) {
            $choices[$post->ID] = $post->post_title;
        }

        $brunches = array();
        if ($choices) {
            foreach ($choices as $value => $label) {
                $brunches[] = array('value' => $value, 'label' => $label);
            }
            echo json_encode($brunches);
        }
        die;
    }
    function ajax_load_PCN_for_current_customer()
    {
        if (!isset($_POST['customer']))
            return;

        $pcn = get_post_meta($_POST['customer'], 'priority_customer_number', true);
        echo $pcn;

        die;
    }
    function ajax_load_kit_for_current_customer()
    {
        global $wpdb;
        if (isset($_POST['customer'])) {
            $table_name = $wpdb->prefix . 'posts';
            $query = $wpdb->get_results("SELECT c.ID, c.post_title FROM $table_name c INNER JOIN {$wpdb->prefix}postmeta a ON c.ID = a.post_id WHERE a.meta_value = '" . $_POST['customer'] . "' AND a.meta_key = 'kit_customer'");
        }

        $choices = array();
        foreach ($query as $post) {
            $choices[$post->ID] = $post->post_title;
        }

        if ($choices) {
            $kits = array();
            foreach ($choices as $value => $label) {
                $kits[] = array('value' => $value, 'label' => $label);
            }
            echo json_encode($kits);
        }
        die;
    }
    function ajax_fill_select_for_current_customer()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'postmeta';
        if (isset($_POST['user_id'])) {
            $kit_selected = get_user_meta($_POST['user_id'], $_POST['user_data'], true);
            $kits = $wpdb->get_results("SELECT b.ID, b.post_title FROM $table_name a INNER JOIN {$wpdb->prefix}posts b on a.post_id = b.ID WHERE a.meta_value = '" . $_POST['customer'] . "' and a.meta_key = '" . $_POST['data_name'] . "'");
        } else {
            $kit_selected = get_post_meta($_POST['meta_id'], $_POST['user_data']);
            $kits = $wpdb->get_results("SELECT b.ID, b.post_title FROM $table_name a INNER JOIN {$wpdb->prefix}posts b on a.post_id = b.ID WHERE a.meta_value = '" . $_POST['customer'] . "' and a.meta_key = '" . $_POST['data_name'] . "'");
        }

        foreach ($kits as $post) {
            $choices[$post->ID] = $post->post_title;
        }

        if ($choices) {
            foreach ($choices as $value => $label) {

                if ($value == $kit_selected) {
                    $options[] = array(
                        'value' => $value,
                        'label' => $label,
                        'selected' => 'selected'
                    );
                } else {
                    $options[] = array(
                        'value' => $value,
                        'label' => $label,
                    );
                }
            }
            echo json_encode($options);
        }
        die;
    }
    function ajax_fill_selects_for_current_customer()
    {

        global $wpdb;
        $table_name = $wpdb->prefix . 'postmeta';
        $query_posts_meta = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE 'post_id' = " . $_POST['post_id']);
        $kits = $wpdb->get_results("SELECT b.ID, b.post_title FROM $table_name a INNER JOIN {$wpdb->prefix}posts b on a.post_id = b.ID WHERE a.meta_value = '" . $_POST['customer'] . "' and a.meta_key = '" . $_POST['data_name'] . "'");

        foreach ($query_posts_meta as $post_meta) {
            $posts_meta[$post_meta->meta_key] = $post_meta->meta_value;
        }

        $kits_option = array();
        foreach ($kits as $post) {
            $kits_option[$post->ID] = $post->post_title;
        }

        $count_selectors = $posts_meta['table_assign_kit_to_campaign'];

        $kit_selected_array = array();
        for ($i = 0; $i < $count_selectors; $i++) {
            $kit_selected_array[$i] =  $posts_meta['table_assign_kit_to_campaign_' . $i . '_choose_kit'];
        }

        $options = array();
        for ($i = 0; $i < $count_selectors; $i++) {
            foreach ($kits_option as $value => $label) {
                if ($value == $kit_selected_array[$i]) {
                    $options[$i][] = array(
                        'value' => $value,
                        'label' => $label,
                        'selected' => 'selected'
                    );
                } else {
                    $options[$i][] = array(
                        'value' => $value,
                        'label' => $label,
                    );
                }
            }
        }
        echo json_encode($options);

        die;
    }

    // Update shipping variation when change customer in project/campaign
    add_action('wp_ajax_update_shipping_option', 'update_shipping_option');
    function update_shipping_option()
    {
        $customer_id = $_POST['customer'];
        $campaign_id = $_POST['campaign'];

        $output = render_unidress_shipping_option($customer_id, $campaign_id);
        echo $output;
        die;
    }

    //Add new kit
    add_action('wp_ajax_add_kit', 'add_kit');
    function add_kit()
    {
        if ($_POST['newDepartmentId']) {
            echo '<section id="section-' . $_POST['key'] . '"  class="assign-products-meta-box" data-kit=' . (int)$_POST['newDepartmentId'] . '>';
            $select_copy = get_select_with_copy_kit($_POST['post_id'], $_POST['newDepartmentId']);
            echo render_kit_info((int)$_POST['newDepartmentId'], $post_meta = '', $select_copy);
            echo render_product_to_campaign((int)$_POST['newDepartmentId']);
            echo '</section>';
        }
        die;
    }

    //Copy kit
    add_action('wp_ajax_copy_kit', 'copy_kit');
    function copy_kit()
    {
        $data = array();
        parse_str($_POST['form'], $data);
        $post_id = $data['post_ID'];

        if (isset($_POST['donor']) && $_POST['donor'] && isset($_POST['recipient']) && $_POST['recipient']) {
            $donor = $_POST['donor'];
            $recipient = $_POST['recipient'];

            if (isset($data['product_option'][$recipient]))
                unset($data['product_option'][$recipient]);
            if (isset($data['add_product_to_campaign'][$recipient]))
                unset($data['add_product_to_campaign'][$recipient]);
            if (isset($data['groups'][$recipient]))
                unset($data['groups'][$recipient]);
            if (isset($data['budget'][$recipient]))
                unset($data['budget'][$recipient]);

            $data['budget'][$recipient]                         = $data['budget'][$donor];
            $data['product_option'][$recipient]                 = $data['product_option'][$donor];
            $data['add_product_to_campaign'][$recipient]        = $data['add_product_to_campaign'][$donor];

            $new_group_id = array();
            if (isset($data['groups'][$donor]) && $data['groups'][$donor]) {
                foreach ($data['groups'][$donor] as $key => $group) {
                    $new_group_id[$key] = rand();
                    $data['groups'][$recipient][$new_group_id[$key]]             = $data['groups'][$donor][$key];
                    unset($data['groups'][$recipient][$key]);
                }

                foreach ($data['product_option'][$donor] as $product_id => $product_option) {
                    if (isset($data['groups'][$donor]) && $data['groups'][$donor] && isset($data['product_option'][$donor][$product_id]['groups']) && isset($new_group_id[$data['product_option'][$donor][$product_id]['groups']]))
                        $data['product_option'][$recipient][$product_id]['groups'] = $new_group_id[$data['product_option'][$donor][$product_id]['groups']];
                }
            }
        }

        save_table_product_to_campaign($post_id, $data);

        die;
    }

    //Add new row
    add_action('wp_ajax_add_new_row', 'add_new_row');
    function add_new_row()
    {
        if ($_POST['product_id']) {
            $post_id = $_POST['product_id'];
            $budget_by_point = get_post_meta($_POST['campaign'], 'budget_by_points',  true);
            $data = array();

            if (isset($_POST['graphic_id'])) {
                $project_graphics = $_POST['graphic_id'];

                $graphics = get_posts(array(
                    'include'     => $project_graphics,
                    'post_type'   => 'graphic',
                    'suppress_filters' => true,
                ));

                $assigning_graphics = array();
                foreach ($graphics as $index => $graphic) {
                    $assigning_graphics[$graphic->ID] = $graphic->post_title;
                }
            }

            if (isset($_POST['campaign'])) {
                $data['campaign'] = $_POST['campaign'];
            }
            if (isset($_POST['kit'])) {
                $data['kit'] = $_POST['kit'];
            }
            if (isset($_POST['assigning_id'])) {
                foreach ($_POST['assigning_id'] as $key => $assigning_id) {
                    $data['groups'][$assigning_id]['name'] = $_POST['assigning_names'][$key];
                }
            }
            if (isset($_POST['required_id'])) {
                foreach ($_POST['required_id'] as $key => $required_id) {
                    $data['required_products'][$required_id]['name'] = $_POST['required_names'][$key];
                }
            }

            $product_meta                  = get_post_meta($post_id, '', true);

            $data['id']                     = $post_id;
            $data['title']                  = render_name_column($post_id);

            $data['sku']                    = (isset($product_meta['_sku'][0])) ? $product_meta['_sku'][0] : '-';
            if ($budget_by_point == 1) {
                $data['nipl_points']        = (isset($product_meta['points'][0])) ? $product_meta['points'][0] : '0';
            }

            $data['image']                  = render_thumb_column($post_id);
            $data['assigning_graphics']     = isset($assigning_graphics) ? $assigning_graphics : "";
            $data['product_option']         = '';
            $data['variation']              = wc_get_product($post_id)->get_type();
            $output = add_t_body_row($data, true);
            echo $output;
        }

        die;
    }
    //filter
    add_action('wp_ajax_filter_product', 'get_data_for_filter_product');
    function get_data_for_filter_product()
    {
        global $wpdb, $post;
        $posts_category = array();
        $posts_product_type = array();
        $posts_stock_status = array();
        $already_assign_product = $_POST['assign'];
        $filtered = false;
        //convert to bool
        $table_assign = true;

        if ($_POST['table-assign'] == "false") {
            $table_assign = false;
        }

        $vowels = array("[", "]", "\"", "\\");
        $already_assign_product_array      = explode(",", str_replace($vowels, "", $already_assign_product));

        if ($_POST['category']) {
            $table_name = $wpdb->prefix . 'term_relationships';
            $has_category = $wpdb->get_results("SELECT a.ID FROM $table_name t INNER JOIN {$wpdb->prefix}posts a ON t.object_id = a.ID WHERE t.term_taxonomy_id = '" . $_POST['category'] . "' AND a.post_status = 'publish'");
            foreach ($has_category as $post) {
                $posts_category[] = $post->ID;
            }
            $filtered = true;
        }

        if ($_POST['product_type']) {
            $table_name = $wpdb->prefix . 'terms';
            $has_product_type = $wpdb->get_results("SELECT a.ID FROM $table_name te INNER JOIN  {$wpdb->prefix}posts AS a INNER JOIN  {$wpdb->prefix}term_taxonomy AS tt INNER JOIN  {$wpdb->prefix}term_relationships AS tr ON te.term_id = tt.term_id and tt.term_taxonomy_id = tr.term_taxonomy_id AND tr.object_id = a.ID WHERE te.name = '" . $_POST['product_type'] . "' AND a.post_status = 'publish'");
            foreach ($has_product_type as $post) {
                $posts_product_type[] = $post->ID;
            }
            $filtered = true;
        }

        if ($_POST['stock_status']) {
            $has_stock_status = $wpdb->get_results("SELECT a.ID FROM  {$wpdb->prefix}postmeta m INNER JOIN {$wpdb->prefix}posts a ON m.post_id = a.ID WHERE m.meta_key = '_stock_status' AND a.post_status = 'publish' AND m.meta_value = '" . $_POST['stock_status'] . "'");
            foreach ($has_stock_status as $post) {
                $posts_stock_status[] = $post->ID;
            }
            $filtered = true;
        }

        $result = array_unique(array_merge($posts_category, $posts_product_type, $posts_stock_status));
        if ($table_assign)
            $result = array_intersect($already_assign_product_array, $result);
        if ($_POST['category'])
            $result = array_intersect($result, $posts_category);
        if ($_POST['product_type'])
            $result = array_intersect($result, $posts_product_type);
        if ($_POST['stock_status'])
            $result = array_intersect($result, $posts_stock_status);

        if ($filtered) {
            if (!$result) {
                die;
                return;
            }
        } else {
            if ($table_assign) {
                $result = $already_assign_product_array;
            }
        }

        echo json_encode(array_values($result));

        die;
    }

    //search
    add_action('wp_ajax_search_product', 'get_data_for_search_product');
    function get_data_for_search_product()
    {
        global $wpdb;
        $searchText = $_POST['searchText'];
        $vowels = array("[", "]", "\"", "\\");
        $already_assign_product_array = explode(",", str_replace($vowels, "", $_POST['assign']));
        $already_assign_product_array = array_unique($already_assign_product_array);
        $table_assign = true;
        $posts_search = array();

        if ($_POST['table-assign'] == "false") {
            $table_assign = false;
        }

        if ($table_assign) {
            $query = $wpdb->get_results("SELECT * FROM  {$wpdb->prefix}posts WHERE 
                post_type = 'product' 
                AND post_status = 'publish'
                AND post_title LIKE '"% $searchText %"' 
                AND ID IN (" . implode(',', array_map('intval', $already_assign_product_array)) . ") 
                UNION (SELECT p.* FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as pm on p.ID = pm.post_id 
                WHERE p.post_type = 'product' 
                AND p.post_status = 'publish' AND pm.meta_key = '_sku' 
                AND pm.meta_value LIKE '"%  $searchText %"' 
                and ID IN (" . implode(',', array_map('intval', $already_assign_product_array)) . "))");
        } else {
            $query = $wpdb->get_results("SELECT * FROM  {$wpdb->prefix}posts WHERE 
            post_type = 'product' 
            AND post_status = 'publish' 
            AND post_title LIKE '"% $searchText %"' 
            UNION (SELECT p.* FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as pm on p.ID = pm.post_id 
            WHERE p.post_type = 'product' 
            AND p.post_status = 'publish' AND pm.meta_key = '_sku' 
            AND pm.meta_value LIKE '"% $searchText %"')");
        }

        foreach ($query as $post) {
            $posts_search[] = $post->ID;
        }

        echo json_encode($posts_search);

        die;
    }

    //search
    add_action('wp_ajax_get_option_row', 'get_option_row');
    function get_option_row()
    {
        $random_id  = $_POST['random_id'] ?: '';
        $group      = $_POST['group'] ?: '';
        $kit        = $_POST['kit'] ?: '';
        $amount     = $_POST['amount'] ?: '';
        $option     = $_POST['option'] ?: '';

        if ($group) {
            $data           = '';
            $data           .= '<tr>';
            $group          = esc_attr(stripslashes($group));
            $name           = $option . '[' . $kit . '][' . $random_id . '][name]';
            $data           .= "<td class='column-group-name padding-left-10'>$group<input type='hidden' data-group-id='$random_id' name='$name' value=' $group'></td>";
            $kit            =  $option . '[' . $kit . '][' . $random_id . '][amount]';
            $data           .= "<td class='column-group-amount'><input type='number' name='$kit' placeholder='amount' value='$amount'></td>";
            $del_text       =  __('del', 'unidress');
            $data           .= "<td class='column-button'><a class='btn-remove-option btn-simple'>$del_text</a></td>";
            $data           .= '</tr>';
            $output['row']  = "$data";
        } else {
            $output['alert'] = __('Group name can\'t be empty', 'unidress');
        }

        echo json_encode($output);
        die;
    }
}

//Front-end
if (wp_doing_ajax()) {

    // update Budget for customers with type "Campaign"
    add_action('wp_ajax_update_unidress_budget', 'update_unidress_budget');
    function update_unidress_budget()
    {
        global $wpdb;
    	//user data
        $user_id            = get_current_user_id();
        $customer_id        = get_user_meta($user_id, 'user_customer', true);
        $kit_id             = get_user_meta($user_id, 'user_kit', true);
        $user_limits        = get_user_meta($user_id, 'user_limits', true);
        $campaign_id        = get_post_meta($customer_id, 'active_campaign', true);

        //active campaign data
        $groups_in_campaign     = get_post_meta($campaign_id, 'groups', true);
        $required_products      = get_post_meta($campaign_id, 'required_products', true);
        $product_in_campaign    = get_post_meta($campaign_id, 'product_option', true);
        $customer_type          = get_post_meta($customer_id, 'customer_type', true);
        $customer_ordering_style   = get_ordering_style($customer_id);
        $price_list_include_vat = get_post_meta($customer_id, 'price_list_include_vat',  true);
        if (empty($campaign_id) || empty($kit_id)) {
            return;
        }

        $get_cart = WC()->cart->get_cart();

        $product_in_cart = array();
        foreach ($get_cart as $product) {
            if (!isset($product_in_cart[$product['product_id']]))
                $product_in_cart[$product['product_id']] = 0;
            $product_in_cart[$product['product_id']] += $product['quantity'];
        }

        if (!is_array($user_limits))
            $user_limits = array();
        // user-budget
        if ($customer_type == 'campaign' && $customer_ordering_style == 'standard') {

            $customer_id        = get_user_meta($user_id, 'user_customer', true);
            $campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
            $user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
            $user_budget_left   = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
            if (empty($campaign_id) || empty($kit_id)) {
                return;
            }
            //campaign data
            $budgets_in_campaign    = get_post_meta($campaign_id, 'budget', true);
            $unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
            if ($unidress_budget > 0) {
                $budget_in_kit = $unidress_budget;
            } else {
                $budget_in_kit = $budgets_in_campaign[$kit_id] ? $budgets_in_campaign[$kit_id] : 0;
            }

            $total = WC()->cart->get_totals('total')['total'];
            $subtotal = WC()->cart->get_subtotal(true);
            if('incl' === get_option('woocommerce_tax_display_shop') || $price_list_include_vat == 1) {
                $tax =  WC()->cart->get_subtotal_tax();
            }else {
                $tax = 0;
            }
            
            $usercoupon = get_user_meta($user_id,'last_used_coupon',true);
            $coupon_results = $wpdb->get_results( "SELECT p.ID,p.post_title,p.post_author,p.post_status from $wpdb->posts as p where p.post_title LIKE '%{$usercoupon}%' and p.post_author = {$user_id} AND p.post_status = 'publish' ",ARRAY_A);
			$additionalfee = get_post_meta( $coupon_results[0]['ID'], 'coupon_amount' ,true);
                //echo 'additional fee'; print_r($additionalfee);
            if($subtotal == 0) {
				$finaltotal = $subtotal;
			}else{
				$finaltotal = $additionalfee;
			}
            $amount = $finaltotal + $tax ;

            // echo 'budgets_in_campaign ='.$budgets_in_campaign.'<br>';
            // echo 'unidress_budget = '.$unidress_budget.'<br>'; 
            // echo 'budget in kit ='.$budget_in_kit.'<br>';
            // echo 'budget left = '.$user_budget_left.'<br>'; 
            // echo 'bubget limit';
            // var_dump($user_budget_limits);
            
            // echo 'amount ='.$amount.'<br>';
            // echo 'total = '.$total.'<br>';
            // echo 'subtotal = '.$subtotal.'<br>';

            //$budget_total = (int)$budget_in_kit - (int)$user_budget_left - (int)$amount;
            // change 21/01/2021
            $budget_total = (float)($budget_in_kit - (int)$user_budget_left - ($subtotal + $tax));
            echo $budget_total;
        }
        die();
    }

    // Depricated
    // update Required Products for customers with type "Campaign"
    //add_action('wp_ajax_update_unidress_status', 'update_unidress_status');
    function update_unidress_status()
    {

        //user data
        $user_id            = get_current_user_id();
        $customer_id        = get_user_meta($user_id, 'user_customer', true);
        $kit_id             = get_user_meta($user_id, 'user_kit', true);
        $user_limits        = get_user_meta($user_id, 'user_limits', true);
        $campaign_id        = get_post_meta($customer_id, 'active_campaign', true);

        //active campaign data
        $groups_in_campaign     = get_post_meta($campaign_id, 'groups', true);
        $required_products      = get_post_meta($campaign_id, 'required_products', true);
        $product_in_campaign    = get_post_meta($campaign_id, 'product_option', true);
        $customer_type          = get_post_meta($customer_id, 'customer_type', true);
        $customer_ordering_style   = get_ordering_style($customer_id);

        if (empty($campaign_id) || empty($kit_id)) {
            return;
        }

        //kit data
        $groups_in_kit   = $groups_in_campaign[$kit_id]   ?: array();
        $required_in_kit = $required_products[$kit_id]    ?: array();
        $product_in_kit  = $product_in_campaign[$kit_id]  ?: array();

        $get_cart = WC()->cart->get_cart();

        $product_in_cart = array();
        foreach ($get_cart as $product) {
            if (!isset($product_in_cart[$product['product_id']]))
                $product_in_cart[$product['product_id']] = 0;
            $product_in_cart[$product['product_id']] += $product['quantity'];
        }

        if (!is_array($user_limits))
            $user_limits = array();

        $table_footer = '</tbody>';
        $table_footer .= '</table>';


        // user-budget
        $output = '';
        if ($customer_type == 'campaign' && $customer_ordering_style == 'standard') {

            $table_header  = '<table class="user-budget">';
            $table_header .= '<tbody>';

            $customer_id        = get_user_meta($user_id, 'user_customer', true);
            $campaign_id        = get_post_meta($customer_id, 'active_campaign', true);
            $user_budget_limits = get_user_meta($user_id, 'user_budget_limits', true);
            $user_budget_left   = isset($user_budget_limits[$campaign_id][$kit_id]) ? $user_budget_limits[$campaign_id][$kit_id] : 0;
            if (empty($campaign_id) || empty($kit_id)) {
                return;
            }
            //campaign data
            $budgets_in_campaign    = get_post_meta($campaign_id, 'budget', true);
            $unidress_budget = get_user_meta($user_id, 'unidress_budget', true) ? get_user_meta($user_id, 'unidress_budget', true) : 0;
            if ($unidress_budget > 0) {
                $budget_in_kit = $unidress_budget;
            } else {
                $budget_in_kit = $budgets_in_campaign[$kit_id] ?: 0;
            }

            $total = WC()->cart->get_totals('total')['total'];

            $output .= '<tr>';
            $output .= '<td class="data-item">' . esc_attr__('Budget', 'unidress') . ':</td>';
            $output .= '<td class="data-item">' . $budget_in_kit . '</td>';
            $output .= '</tr>';

            $output .= '<tr>';
            $output .= '<td class="data-item">' . esc_attr__('Remaining', 'unidress') . ':</td>';
            $output .= '<td class="data-item">' . ((int)$budget_in_kit - (int)$user_budget_left - (int)$total) . '</td>';
            $output .= '</tr>';

            echo $table_header . $output . $table_footer;
        }

        //class="user-required"
        $output = '';
        if ($customer_type == 'campaign' && $required_in_kit) {

            $required_products_in_cart = array();

            foreach ($product_in_kit as $product_id => $product_options) {

                if (empty($product_options['required_products']))
                    continue;

                if (!isset($required_products_in_cart[$product_options['required_products']]))
                    $required_products_in_cart[$product_options['required_products']] = 0;

                $product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
                $required_products_in_cart[$product_options['required_products']] += $product_quantity;
            }

            $table_header  = '<table class="user-required">';
            $table_header .= '<thead>';
            $table_header .= '<tr>';
            $table_header .= '<th></th>';
            $table_header .= '<th>' . __('Required', 'unidress') . '</th>';
            $table_header .= '<th>' . __('Amount', 'unidress') . '</th>';
            $table_header .= '</tr>';
            $table_header .= '</thead>';
            $table_header .= '<tbody>';

            foreach ($required_in_kit as $group_id => $group) {
                $output .= '<tr>';
                $output .= '<td>' . $group['name'] . ':</td>';
                $output .= '<td class="center">' . $group['amount'] . '</td>';

                if (isset($required_products_in_cart[$group_id])) {

                    $user_limit = isset($user_limits[$group_id]) ? (int)$user_limits[$group_id] : 0;
                    $output .= '<td class="center">' . ($user_limit + $required_products_in_cart[$group_id]) . '</td>';
                } else {

                    $output .= '<td class="center">0</td>';
                }

                $output .= '</tr>';
            }

            echo $table_header . $output . $table_footer;
        }

        // user-group-limit
        $output = '';
        if ($customer_type == 'campaign' && $groups_in_kit) {

            $group_in_cart = array();
            foreach ($product_in_kit as $product_id => $product_options) {

                if (empty($product_options['groups']))
                    continue;

                if (!isset($group_in_cart[$product_options['groups']]))
                    $group_in_cart[$product_options['groups']] = 0;

                $product_quantity = isset($product_in_cart[$product_id]) ? $product_in_cart[$product_id] : 0;
                $group_in_cart[$product_options['groups']] += $product_quantity;
            }

            if ($group_in_cart) {

                $table_header  = '<table class="user-group-limit">';
                $table_header .= '<thead>';
                $table_header .= '<tr>';
                $table_header .= '<th></th>';
                $table_header .= '<th>' . __('Limit', 'unidress') . '</th>';
                $table_header .= '</tr>';
                $table_header .= '</thead>';
                $table_header .= '<tbody>';

                foreach ($groups_in_kit as $group_id => $group) {
                    $output .= '<tr>';
                    $output .= '<td>' . $group['name'] . ':</td>';
                    $output .= '<td class="center">' . $group['amount'] . '</td>';
                    $output .= '</tr>';
                }

                echo $table_header . $output . $table_footer;
            }
        }
        die();
    }

    add_action('wp_ajax_is_active_campaign', 'is_active_campaign');
    function is_active_campaign()
    {
        $campaign = $_POST['campaign'];
        $customer = $_POST['customer'];
        $current_customer_campaign = get_post_meta($customer, 'active_campaign', true);
        $output = '';
        if ($campaign == $current_customer_campaign) {
            $output .= '<div class="kit-active-campaign inline-info">';
            $output .= '    <label>Active Campaign</label>';
            $output .= '    <input type="checkbox" disabled="disabled" checked="checked" >';
            $output .= '</div>';
        } else {
            $output .= '<div class="kit-active-campaign inline-info not-active">';
            $output .= '    <label>Not active Campaign</label>';
            $output .= '    <input type="checkbox" disabled="disabled" >';
            $output .= '</div>';
        }

        echo $output;
        die;
    }
}
