<?php
add_action('admin_menu', 'add_table_product_to_project');
add_action('admin_menu', 'add_table_product_to_campaign');

function add_table_product_to_project()
{
    add_meta_box('add-product-to-project', __('Product to project', 'unidress'), 'table_product_to_project', 'project', 'normal');
}
function add_table_product_to_campaign()
{
    add_meta_box('add-product-to-campaign', __('Kit on Campaign', 'unidress'), 'table_product_to_campaign', 'campaign', 'normal');
}

function unid_unserialize_assign_product($post)
{
    $post_meta  = get_post_meta($post->ID, '', true);
    foreach ($post_meta as $key => $meta) {
        if (is_serialized($meta[0]))
            $post_meta[$key][0] = unserialize((string)$meta[0]);
    }
    return $post_meta;
}


function table_product_to_project()
{
    global $post;
    wp_nonce_field(basename(__FILE__), 'table_product_to_project_nonce');
    wp_enqueue_script('product-assign-js', plugins_url('/unidress/admin/js/product-assign-project.js'), array('jquery'));

    $post_meta = unid_unserialize_assign_product($post);


    echo '<section id="section-0" class="assign-products-meta-box">';
    $current_customer = isset($post_meta['project_customer'][0]) ? $post_meta['project_customer'][0] : false;
    $current_customer_campaign = get_post_meta($current_customer, 'active_campaign', true);
    if ($post->ID == $current_customer_campaign) : ?>
        <div class="active-project">
            <div class="inline-info">
                <label><?php echo esc_attr__('Active Project', 'unidress') ?></label>
                <input type="checkbox" disabled="disabled" checked="checked">
            </div>
        </div>
    <?php else : ?>
        <div class="active-project">
            <div class="inline-info not-active">
                <label><?php echo esc_attr__('Not active Project', 'unidress') ?></label>
                <input type="checkbox" disabled="disabled">
            </div>
        </div>
    <?php endif;
render_product_to_project($post_meta);
echo ' </section>';
}
function table_product_to_campaign()
{
    global $post;
    wp_nonce_field(basename(__FILE__), 'table_product_to_campaign_nonce');
    wp_enqueue_script('product-assign-campaign-js', plugins_url('/unidress/admin/js/product-assign-campaign.js'), array('jquery'));
    // The wp_localize_script allows us to output the ajax_url path for our script to use.
    //wp_localize_script('product-assign-campaign-js', 'ajax_obj', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ));

    $post_meta  = get_post_meta($post->ID, '', true);
    foreach ($post_meta as $key => $meta) {
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
        <h3 class="choose_kit_title"><?php echo __('Select Kit', 'unidress') ?></h3>
        <select><?php echo __('Choose', 'unidress') ?></select>
        <a id="add-kit" class="button"><?php echo __('Add', 'unidress') ?></a>
    </div>

    <?php

    if ($kits) {

        $vowels = array("[", "]", "\"", "\\");
        $kits_array            = explode(",", str_replace($vowels, "", $kits));

        foreach ($kits_array as $key => $kit) {
            ?>
            <section id="section-<?php echo $key ?>" class="assign-products-meta-box" data-kit='<?php echo $kit ?>'>
                <?php
                $select_copy = get_select_with_copy_kit($post->ID, $kit);
                render_kit_info($kit, $post_meta, $select_copy);
                render_product_to_campaign($kit, $post_meta);
                ?>
            </section>
        <?php
    }
}
}
function get_select_with_copy_kit($post_id, $current_kit)
{
    $kits  = get_post_meta($post_id, 'kits', true);
    $vowels = array("[", "]", "\"", "\\");
    $kits_array            = $kits ? explode(",", str_replace($vowels, "", $kits)) : array();

    $output = '';
    $output .= '<select class="select-kit">';
    $output .= '<option value="">' . esc_attr__("Select kit to copy", "unidress") . '</option>';

    foreach ($kits_array as $key => $kit) {
        if ($kit == $current_kit)
            continue;

        $kit_title = get_post_field('post_title', $kit);
        $output .= '<option value="' . $kit . '">' . $kit_title . '</option>';
    }

    $output .= '</select>';
    $output .= '<a class="button copy-kit">' . __("Save and Copy from", "unidress") . '</a>';

    return $output;
}
//render table
function render_kit_info($kit, $post_meta = '', $select_copy = '')
{
    global $post;
    $kit_title = get_post_field('post_title', $kit);
    ?>
    <div class="kit-name">
        <h1 class="kit-title"><?php echo $kit_title ?></h1>
        <div class="kit-buttons">
            <div class="settings-duplicate">
                <?php echo $select_copy ?>
            </div>
            <a class="button delete-kit"><?php echo __('Delete Kit', 'unidress') ?></a>
        </div>
    </div>
    <div class="kit-info">
        <div class="kit-one-order-toggle inline-info">
            <label for="one-order-toggle"><?php echo __('Only one order per user', 'unidress') ?></label>
            <?php if (isset($post_meta['one_order_toggle'][0]) && isset($post_meta['one_order_toggle'][0][$kit])) : ?>

                <input id="one-order-toggle" type="checkbox" name="one_order_toggle[<?php echo $kit ?>]" <?php echo (($post_meta['one_order_toggle'][0][$kit] == 'on') ? 'checked="checked"' : '') ?>>

            <?php else : ?>

                <input id="one-order-toggle" type="checkbox" name="one_order_toggle[<?php echo $kit ?>]">

            <?php endif; ?>
        </div>
        <div class="kit-budget">
            <label><?php echo __('Budget', 'unidress') ?></label>
            <input type="number" name="budget[<?php echo $kit ?>]" step="any" value="<?php echo $post_meta['budget'][0][$kit] ?>">
        </div>
        <?php
        $current_customer = $post_meta['campaign_customer'][0];
        $current_customer_campaign = get_post_meta($current_customer, 'active_campaign', true);
        if ($current_customer_campaign && $post->ID == $current_customer_campaign) : ?>
            <div class="kit-active-campaign inline-info">
                <label><?php echo esc_attr__('Active Campaign', 'unidress') ?></label>
                <input type="checkbox" disabled="disabled" checked="checked">
            </div>
        <?php else : ?>
            <div class="kit-active-campaign inline-info not-active">
                <label><?php echo esc_attr__('Not active Campaign', 'unidress') ?></label>
                <input type="checkbox" disabled="disabled">
            </div>
        <?php endif; ?>
    </div>
    <div class="kit-options kit-groups">
        <h3 class="kit-title"><?php echo esc_attr__('Assigning Groups', 'unidress') ?></h3>
        <table data-option="groups">
            <thead>
                <tr>
                    <th class="column-group-name"><?php echo esc_attr__('Group Name', 'unidress') ?></th>
                    <th class="column-group-amount"><?php echo esc_attr__('The number in the group', 'unidress') ?></th>
                    <th class="column-button"></th>
                </tr>
            </thead>
            <tbody>
                <?php

                if ($kit != 0 && isset($post_meta['groups'][0]) && isset($post_meta['groups'][0][$kit])) : ?>
                    <?php
                    $assigning_groups = $post_meta['groups'][0][$kit];
                    ?>
                    <?php foreach ($assigning_groups as $group_id => $group) : ?>
                        <tr>
                            <td class="column-group-name padding-left-10"><?php echo $group['name']; ?><input type="hidden" data-group-id="<?php echo $group_id; ?>" name="groups[<?php echo $kit; ?>][<?php echo $group_id; ?>][name]" value="<?php echo $group['name']; ?>"></td>
                            <td class="column-group-amount"><input type="number" name="groups[<?php echo $kit; ?>][<?php echo $group_id; ?>][amount]" placeholder="<?php echo esc_attr__('amount', 'unidress') ?>" value="<?php echo $group['amount'] ?>"></td>
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
        <h3 class="kit-title"><?php echo esc_attr__('Required Products', 'unidress') ?></h3>
        <table data-option="required_products">
            <thead>
                <tr>
                    <th class="column-group-name"><?php echo esc_attr__('Required Products', 'unidress') ?></th>
                    <th class="column-group-amount"><?php echo esc_attr__('Minimum number', 'unidress') ?></th>
                    <th class="column-button"></th>
                </tr>
            </thead>
            <tbody>
                <?php

                if ($kit != 0 && isset($post_meta['required_products'][0]) && isset($post_meta['required_products'][0][$kit])) : ?>
                    <?php $required_products = $post_meta['required_products'][0][$kit]; ?>
                    <?php foreach ($required_products as $product_id => $product) : ?>
                        <tr>
                            <td class="column-group-name padding-left-10"><?php echo $product['name']; ?><input type="hidden" data-group-id="<?php echo $product_id; ?>" name="required_products[<?php echo $kit; ?>][<?php echo $product_id; ?>][name]" value="<?php echo $product['name']; ?>"></td>
                            <td class="column-group-amount"><input type="number" name="required_products[<?php echo $kit; ?>][<?php echo $product_id; ?>][amount]" placeholder="<?php echo esc_attr__('amount', 'unidress') ?>" value="<?php echo $product['amount'] ?>"></td>
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
function render_product_to_project($post_meta = '')
{

    if (isset($post_meta['add_product_to_project']) && $post_meta['add_product_to_project'][0]) {
        $already_assign_product = json_decode($post_meta['add_product_to_project'][0]);
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
                <a name="filter_action" table-assign="false" class="product-filter btn-product btn-simple"><?php echo __('Filter', 'unidress') ?></a>
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php echo __('Search', 'unidress') ?>:</label>
                    <input type="search" class="search-input" name="s" value="">
                    <a class="button button-search" table-assign="false"><?php echo __('Search', 'unidress') ?></a>
                </p>
            </div>
            <table id="product-all" class="product-table product-all">
                <thead>
                    <tr>
                        <td class="column-image"><span class="wc-image tips" title="<?php echo __('Image', 'unidress') ?>"><?php echo __('Image', 'unidress') ?></span></td>
                        <td class="column-name"><?php echo esc_attr__('Name', 'unidress') ?></td>
                        <td class="column-sku"><?php echo esc_attr__('SKU', 'unidress') ?></td>
                        <td class="column-price"><?php echo esc_attr__('Price', 'unidress') ?></td>
                        <td class="column-button" scope="col"><a class="btn-add-all-product btn-simple"><?php echo __('Add all', 'unidress') ?></a></td>
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
        <div class="product-assign-wrapper table-wrapper js-product-assign-wrapper">
            <h3><?php echo __('Assign product', 'unidress') ?></h3>
            <input type="hidden" class="add_product" name="add_product_to_project" value='<?php echo (isset($post_meta['add_product_to_project'][0])) ? $post_meta['add_product_to_project'][0] : ''; ?>'>
            <div id="filter-assign-product" class="filter-bar">
                <?php echo render_products_category_filter(); ?>
                <?php echo render_products_type_filter(); ?>
                <?php echo render_products_stock_status_filter(); ?>
                <a name="filter_action" table-assign="true" class="product-filter btn-product btn-simple"><?php echo __('Filter', 'unidress') ?></a>
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php echo __('Search', 'unidress') ?>:</label>
                    <input type="search" class="search-input" name="s" value="">
                    <a class="button button-search" table-assign="true"><?php echo __('Search', 'unidress') ?></a>
                </p>
            </div>
            <?php require MY_PLUGIN_ROOT_ADMIN . '/parts/assign-product/pagination.php'; ?>
            <table class="product-table product-assign">
                <thead>
                    <tr>
                        <td class="column-image"><span class="wc-image tips" title="<?php echo __('Image', 'unidress') ?>"><?php echo __('Image', 'unidress') ?></span></td>
                        <td class="column-name"><?php echo esc_attr__('Name', 'unidress') ?></td>
                        <td class="column-graphics"><?php echo esc_attr__('Graphics', 'unidress') ?></td>
                        <td class="column-sku"><?php echo esc_attr__('SKU', 'unidress') ?></td>
                        <td class="column-sku"><?php echo esc_attr__('Warehouse', 'unidress') ?></td>
                        <td class="column-price"><?php echo esc_attr__('Price', 'unidress') ?></td>
                        <td class="column-button"><a class="btn-remove-all-product btn-simple"><?php echo esc_attr__('Remove all', 'unidress') ?></a></td>
                        <td class="column-button"><?php echo esc_attr__('Variation', 'unidress') ?></a></td>
                        <td class="column-button">
                            <div class="h-width-1 h-margin-auto"><?php echo esc_attr__('Display Order', 'unidress') ?></div>
                        </td>

                    </tr>
                </thead>
                <tbody class="choices-list js-choices-list-order">
                    <?php
                    // $pageda = $_GET['assign-paged'] ? $_GET['assign-paged'] : 1;
                    // $posts_per_page = 1; //get_option('posts_per_page');
                    // $post_offset = ($pageda - 1) * $posts_per_page;
                    // echo unid_get_per_page_assign_product();

                    echo get_product_to_campaign(array(
                        'numberposts'   =>  -1,
                        // 'offset'        =>  $post_offset,

                        'orderby'     => 'post__in',
                        'order'       => 'ASC',
                        'post_type'   => 'product',
                        'post__in' => $already_assign_product
                    ), true, $already_assign_product, 0, $post_meta);
                    ?>
                </tbody>
            </table>
            <?php require MY_PLUGIN_ROOT_ADMIN . '/parts/assign-product/pagination.php'; ?>
        </div>
    </div>

<?php



}


function render_product_to_campaign($kit, $post_meta = '')
{
    $camp_id = $_GET['post']; // campaign id

    $already_assign_product = json_decode(($post_meta['add_product_to_campaign'][0])[$kit]);

    ?>
    <div class="section-product">
        <div class="product-wrapper table-wrapper">
            <h3><?php echo __('All product', 'unidress') ?></h3>
            <div class="filter-bar">
                <?php echo render_products_category_filter(); ?>
                <?php echo render_products_type_filter(); ?>
                <?php echo render_products_stock_status_filter(); ?>
                <a name="filter_action" table-assign="false" class="product-filter btn-product btn-simple"><?php echo __('Filter', 'unidress') ?></a>
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php echo __('Search', 'unidress') ?>:</label>
                    <input type="search" class="search-input" name="s" value="">
                    <a class="button button-search" table-assign="false"><?php echo __('Search', 'unidress') ?></a>
                </p>
            </div>
            <table id="product-all" class="product-table product-all">
                <thead>
                    <tr>
                        <td class="column-image"><span class="wc-image tips" title="<?php echo __('Image', 'unidress') ?>"><?php echo __('Image', 'unidress') ?></span></td>
                        <td class="column-name"><?php echo esc_attr__('Name', 'unidress') ?></td>
                        <td class="column-sku"><?php echo esc_attr__('SKU', 'unidress') ?></td>
                        <td class="column-price"><?php echo esc_attr__('Price', 'unidress') ?></td>
                        <td class="column-button" scope="col"><a class="btn-add-all-product btn-simple"><?php echo __('Add all', 'unidress') ?></a></td>
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
        <div class="product-assign-wrapper table-wrapper js-product-assign-wrapper">
            <h3><?php echo __('Assign product', 'unidress') ?></h3>
            <input type="hidden" class="add_product" name="add_product_to_campaign[<?php echo $kit ?>]" value='<?php echo (isset($post_meta['add_product_to_campaign'][0][$kit])) ? $post_meta['add_product_to_campaign'][0][$kit] : ''; ?>'>
            <div id="filter-assign-product" class="filter-bar">
                <?php echo render_products_category_filter(); ?>
                <?php echo render_products_type_filter(); ?>
                <?php echo render_products_stock_status_filter(); ?>
                <a name="filter_action" table-assign="true" class="product-filter btn-product btn-simple"><?php echo __('Filter', 'unidress') ?></a>
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input"><?php echo __('Search', 'unidress') ?>:</label>
                    <input type="search" class="search-input" name="s" value="">
                    <a class="button button-search" table-assign="true"><?php echo __('Search', 'unidress') ?></a>
                </p>
            </div>
            <?php require MY_PLUGIN_ROOT_ADMIN . '/parts/assign-product/pagination.php'; ?>
            <table class="product-table product-assign">
                <thead>
                    <tr>
                        <td class="column-image"><span class="wc-image tips" title="<?php echo __('Image', 'unidress') ?>"><?php echo __('Image', 'unidress') ?></span></td>
                        <td class="column-name"><?php echo esc_attr__('Name', 'unidress') ?></td>
                        <td class="column-sku"><?php echo esc_attr__('SKU', 'unidress') ?></td>
                        <td class="column-price warehouse"><?php echo esc_attr__('Warehouse', 'unidress') ?></td>
                        <td class="column-price simple-option"><?php echo esc_attr__('Simple Options', 'unidress') ?></td>
                        <td class="column-option"><?php echo esc_attr__('Assignment Group', 'unidress') ?></td>
                        <td class="column-option"><?php echo esc_attr__('Required Products', 'unidress') ?></td>
                        <?php
                        $budget_by_point = get_post_meta($camp_id, 'budget_by_points',  true);

                        if ($budget_by_point == 1) {
                            ?>
                            <td class="column-points"><?php echo esc_attr__('Points', 'unidress') ?></td>
                        <?php
                    }
                    ?>
                        <td class="column-price"><?php echo esc_attr__('Price', 'unidress') ?></td>
                        <td class="column-button"><a class="btn-remove-all-product btn-simple"><?php echo esc_attr__('Remove all', 'unidress') ?></a></td>
                        <td class="column-button"><?php echo esc_attr__('Variation', 'unidress') ?></td>
                        <td class="column-button">
                            <div class="h-width-1 h-margin-auto"><?php echo esc_attr__('Display Order', 'unidress') ?></div>
                        </td>
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
            <?php require MY_PLUGIN_ROOT_ADMIN . '/parts/assign-product/pagination.php'; ?>
        </div>
    </div>
<?php

}
function get_product_to_campaign($arg, $assign = false, $already_assign_product = array(), $kit = 0, $post_meta = false)
{

    if ((!$already_assign_product) and ($assign)) {
        return;
    }
    $output = '';
    // pr($arg);
    // pr('--------');
    // pr($assign);
    // pr('--------');
    // pr($already_assign_product);
    // pr('--------');
    // pr($post_meta);
    // pr('--------');

    $posts = get_posts($arg);
    if (!$assign) {

        foreach ($posts as $post) {

            $product_meta       = get_post_meta($post->ID, '', true);
            $data['id']         = $post->ID;
            $data['title']      = render_name_column($post);
            $data['sku']        = (isset($product_meta['_sku'][0])) ? $product_meta['_sku'][0] : '-';
            if ($post_meta['budget_by_points'][0] == 1) {
                $data['nipl_points']            = '';
            }
            $data['price']      = (isset($product_meta['_price'][0])) ? $product_meta['_price'][0] : '0';
            $data['image']      = render_thumb_column($post->ID);
            $data['variation']  = wc_get_product($post->ID)->get_type();
            $data['added']      = in_array($post->ID, (is_array($already_assign_product)) ? $already_assign_product : array());

            $output .= add_t_body_row($data, $assign);
        }
    } else {
        $assigning_groups   = false;
        $required_products  = false;
        $product_option     = false;

        if (isset($post_meta['project_graphics'][0]) && !empty($post_meta['project_graphics'][0])) {
            $project_graphics = $post_meta['project_graphics'][0];
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

        if (isset($post_meta['groups'][0]))
            $assigning_groups = $post_meta['groups'][0];

        if (isset($post_meta['required_products'][0]))
            $required_products = $post_meta['required_products'][0];

        if (isset($post_meta['product_option'][0]))
            $product_option = $post_meta['product_option'][0];
        $count = 1;
        // $already_assign_product = array_merge($already_assign_product, $already_assign_product, $already_assign_product, $already_assign_product, $already_assign_product, $already_assign_product, $already_assign_product);
        // shuffle($already_assign_product);
        $already_assign_product = unid_ksort(array(
            'product_list' => $already_assign_product,
            'product_option' => $product_option,
            'kit' => $kit,
        ));

        // add kit first up so that in render_thumb_col we get kit id. 
        $data['kit']                    = $kit;

        foreach ($already_assign_product as $post_id) {
            $product                        = wc_get_product($post_id);
            $product_meta                   = get_post_meta($post_id, '', true);
            $data['id']                     = $post_id;
            $data['title']                  = render_name_column($post_id);
            $data['image']                  =  render_thumb_column_image($post_id, $data, $product_option);
            $data['sku']                    = (isset($product_meta['_sku'][0])) ? $product_meta['_sku'][0] : '-';
            if ($post_meta['budget_by_points'][0] == 1) {
                $data['nipl_points']            = '';
            }
            $data['price']                  = (isset($product_meta['_price'][0])) ? $product_meta['_price'][0] : '0';
            $data['kit']                    = $kit;
            $data['assigning_graphics']     = isset($assigning_graphics) ? $assigning_graphics : "";
            $data['groups']                 = $assigning_groups[$kit];
            $data['required_products']      = $required_products[$kit];
            $data['product_option']         = isset($product_option[$kit][$post_id]) ? $product_option[$kit][$post_id] : "";
            $data['variation']              = $product ? $product->get_type() : $product;

            $output .= add_t_body_row($data, $assign);
            $count++;
        }
    }

    wp_reset_postdata();

    return $output;
}

function add_t_body_row($data, $assign = false)
{
    // pr($data);

    $row = '<tr class="js-product-assign-tr ' . ((!$assign) ? '' : 'hidden') . '" data-id="' . $data['id'] . '">';
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
            $row .= '<a class="btn-add-product btn-simple" data-id="' . $data['id'] . '">' . __('Add', 'unidress') . '</a>';
        $row .= '</td>';
    } else {

        // TEST
        $row .= ' <td class="column-warehouse"><div class="acf-field"><div class="acf-input"><input class="js-assign-product-warehouse" type="text" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][warehouse]" placeholder=" " value="' . ((isset($data['product_option']['warehouse']) && $data['product_option']['warehouse'] != '0') ? $data['product_option']['warehouse'] : "") . '" maxlength="4"></div></div></td>';
        // TEST

        // simple field column
        $row .= ' <td class="column-simple-field"><input class="simple-col-field" type="text" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][uni_simple_field]" placeholder=" " value="' . ((isset($data['product_option']['uni_simple_field']) && $data['product_option']['uni_simple_field'] != '0') ? $data['product_option']['uni_simple_field'] : "") . '"  maxlength="60"></td>';

        // CHANGE
        if (isset($data['kit']) && $data['kit'] != ''  && $data['kit'] != '0' && $assign) {
            $row .=     '<td class="column-option">' . get_assign_group_select($data)   .    '</td>';
            $row .=     '<td class="column-option">' . get_required_products_select($data)   .    '</td>';
        }
        // CHANGE

        // points column
        $blank_points_val = ''; // this is to prevent points value loss, when campaign not budget by points. 
        if (isset($data['nipl_points'])) {
            $row .= '<td class="column-points"><input type="number" step="any" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][points]" placeholder="' . $data['product_option']['points'] . '" value="' . ((isset($data['product_option']['points']) && $data['product_option']['points'] != '0') ? $data['product_option']['points'] : "") . '"></td>';
        } else {
            // store in hidden field so it is not lost. 
            $blank_points_val = '<input type="hidden" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][points]" placeholder="' . ((isset($data['product_option']['points']) && $data['product_option']['points'] != '0') ? $data['product_option']['points'] : "") . '" value="' . ((isset($data['product_option']['points']) && $data['product_option']['points'] != '0') ? $data['product_option']['points'] : "") . '">';
        }

        $row .= '<td class="column-price"><input type="number" step="any" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][price]" placeholder="' . $data['price'] . '" value="' . ((isset($data['product_option']['price']) && $data['product_option']['price'] != '0') ? $data['product_option']['price'] : "") . '">' . $blank_points_val . '</td>';


        $row .= '<td class="column-button"><a class="btn-remove-product btn-simple" data-id="' . $data['id'] . '">Remove</a></td>';




        if ($data['variation'] == 'variable') {
            $row .= '<td class="column-button"><a class="show-product-variation btn-simple">' . esc_attr__('Variation', 'unidress') . '</a></td>';
        } else {
            $row .= '<td class="column-button"></td>';
        }
        $row .= '<td><input class="h-width-full" type="number" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][order]" value="' . ((isset($data['product_option']['order']) && $data['product_option']['order'] != '0') ? $data['product_option']['order'] : "") . '"></td>';
    }
    $row .= '</tr>';

    return $row;
}
function get_assign_group_select($data)
{
    $output = '<select data-option="groups" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][groups]">';
    $output .= '<option value="">' . esc_attr__('Not assign', 'unidress') . '</option>';

    if (isset($data['groups']) && !$data['groups'] == '') {

        foreach ($data['groups'] as $group_ID => $group) {

            if (isset($data['product_option']['groups']) && ($data['product_option']['groups'] == $group_ID)) {
                $output .= '<option selected="selected" value="' . $group_ID . '">' . $group['name'] . '</option>';
            } else {
                $output .= '<option value="' . $group_ID . '">' . $group['name'] . '</option>';
            }
        }
    }
    $output .= '</select>';

    return $output;
}
function get_required_products_select($data)
{
    $output = '<select data-option="required_products" name="product_option[' . $data["kit"] . '][' . $data['id'] . '][required_products]">';
    $output .= '<option value="">' . esc_attr__('Not assign', 'unidress') . '</option>';

    if (isset($data['required_products']) && !$data['required_products'] == '') {

        foreach ($data['required_products'] as $group_ID => $group) {

            if (isset($data['product_option']['required_products']) && ($data['product_option']['required_products'] == $group_ID)) {
                $output .= '<option selected="selected" value="' . $group_ID . '">' . $group['name'] . '</option>';
            } else {
                $output .= '<option value="' . $group_ID . '">' . $group['name'] . '</option>';
            }
        }
    }
    $output .= '</select>';

    return $output;
}
function get_assign_graphics_button($data)
{
    $output = '<a class="show-product-graphics btn-simple">' . __('Graphics', 'unidress') . '</a>';
    $output .= '<table class="graphic-list striped"><tbody>';
    if (isset($data['assigning_graphics']) && !$data['assigning_graphics'] == '') {
        foreach ($data['assigning_graphics'] as $graphic_id => $name) {
            $output .= '<tr data-option="graphic-' . $graphic_id . '" class="variation-row">';
            if (isset($data['product_option']['graphics']) && in_array($graphic_id, $data['product_option']['graphics'])) {
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
function render_products_type_filter()
{
    $current_product_type = isset($_REQUEST['product_type']) ? wc_clean(wp_unslash($_REQUEST['product_type'])) : false; // WPCS: input var ok, sanitization ok.
    $output               = '<select name="product_type" id="dropdown_product_type"><option value="">' . __('Filter by product type', 'unidress') . '</option>';

    foreach (wc_get_product_types() as $value => $label) {
        $output .= '<option value="' . esc_attr($value) . '" ';
        $output .= selected($value, $current_product_type, false);
        $output .= '>' . esc_html($label) . '</option>';

        if ('simple' === $value) {

            $output .= '<option value="downloadable" ';
            $output .= selected('downloadable', $current_product_type, false);
            $output .= '> ' . (is_rtl() ? '&larr;' : '&rarr;') . ' ' . __('Downloadable', 'unidress') . '</option>';

            $output .= '<option value="virtual" ';
            $output .= selected('virtual', $current_product_type, false);
            $output .= '> ' . (is_rtl() ? '&larr;' : '&rarr;') . ' ' . __('Virtual', 'unidress') . '</option>';
        }
    }

    $output .= '</select>';
    return $output; // WPCS: XSS ok.
}
function render_products_category_filter()
{
    $categories = get_categories(
        array(
            'taxonomy'           => 'product_cat',
        )
    );
    $output               = '<select name="category"><option value="">' . esc_html__('Filter by category', 'unidress') . '</option>';

    foreach ($categories as $cat) {
        $output .= '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
    }
    $output .= '</select>';
    return $output;
}
function render_products_stock_status_filter()
{
    $current_stock_status = isset($_REQUEST['stock_status']) ? wc_clean(wp_unslash($_REQUEST['stock_status'])) : false; // WPCS: input var ok, sanitization ok.
    $stock_statuses       = wc_get_product_stock_status_options();
    $output               = '<select name="stock_status"><option value="">' . esc_html__('Filter by stock status', 'unidress') . '</option>';

    foreach ($stock_statuses as $status => $label) {
        $output .= '<option ' . selected($status, $current_stock_status, false) . ' value="' . esc_attr($status) . '">' . esc_html($label) . '</option>';
    }

    $output .= '</select>';
    return $output;
}

//render column value
function render_name_column($post_id)
{
    $title     = _draft_or_post_title($post_id);
    return $title;
}
function render_thumb_column($product_id)
{
    $meta = get_post_meta($product_id, '_thumbnail_id', true);



    return wc_get_gallery_image_html($meta, true); // WPCS: XSS ok.
}

function render_thumb_column_image($product_id, $data, $product_option)
{


    $test_p = wc_get_product($product_id);
    $type = wc_get_product($product_id)->get_type();
    ob_start();


    $image = ' button">Upload image';
    $image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
    $display = 'none'; // display state ot the "Remove image" button
    $thumbnail_id = get_post_meta($product_id, '_thumbnail_id', true);

    $kit_id = $data['kit'];
    $d_id = $data['id'];

    $img_id = $thumbnail_id;
    $camp_varible_img = '';
    // change image if custom
    if ($product_option[$kit_id][$d_id]['camp_varible_img'] != '') {
        $img_id = $product_option[$kit_id][$d_id]['camp_varible_img'];
        $camp_varible_img = $img_id;
    }
    if ($thumbnail_id != $product_option[$kit_id][$d_id]['camp_varible_img'] && $product_option[$kit_id][$d_id]['camp_varible_img'] != '') {
        $clas = 'nipl_grn_border';
    }


    if ($image_attributes = wp_get_attachment_image_src($img_id, 'thumbnail')) {

        // $image_attributes[0] - image URL
        // $image_attributes[1] - image width
        // $image_attributes[2] - image height

        $image = '<img src="' . $image_attributes[0] . '" style="max-width:95%;display:block;" />';
        $display = 'inline-block';
    }
    ?>


    <div class="nipl_varible_wrp" data-thumbid='<?php echo $thumbnail_id; ?>'>

        <input type="hidden" name="product_option[<?php echo $data['kit']; ?>][<?php echo $data['id']; ?>][camp_varible_img]" class="camp_varible_img" value="<?php echo $camp_varible_img; ?>" />
        <a href="#" class="misha_upload_image_button on <?php echo $clas; ?>"> <?php echo $image; ?></a>
        <!-- <a href="#" class="misha_remove_image_button" style="display:inline-block;display:' . $display . '">Remove image</a> -->
    </div>
    <?php

    return ob_get_clean();
}

/*noway*/
function unidress_load_variations($data)
{

    $product_id     = $data['id'];
    $kit_id  = ($data['kit'] != '') ? $data['kit'] : '0';
    $product_option = $data['product_option'];
    $product_object = wc_get_product($product_id);
    $variations     = wc_get_products(
        array(
            'status'  => array('private', 'publish'),
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


    if ($variations) {

        if (count($product_object->get_attributes()) !== 2) {
            foreach($product_object->get_attributes() as $key => $value) {
                if($key == 'pa_color') {
                    
                    $output .= '        <fieldset class="unidress-shops-shipping">';
                    $output .= '        <label class="variation-row">';
                    $output .= '            <span><input class="shipping-all-select" type="checkbox"></span>';
                    $output .=              esc_html__('Select all', 'unidress');
                    $output .= '        </label>';
                    $output .= '        <ul>';

                    foreach ($variations as $variation_object) {

                        $variation_id   = $variation_object->get_id();

                        $checked = '';
                        if (isset($product_option['variation'])) {
                            if (in_array($variation_id, $product_option['variation']))
                                $checked = 'checked';
                        }

                        $product = wc_get_product($variation_id);


                        $output .= '<li class="variation-row">';
                        $output .= '<span><input class="shipping-select" data-variation="' . $variation_id . '" type="checkbox" name="product_option[' . $kit_id . '][' . $product_id . '][variation][]" value="' . esc_html($variation_id) . '" ' . $checked . '></span>';
                        //$output .= '<span>#' . esc_html($variation_id) . '</span>';

                        foreach ($product_object->get_attributes('edit') as $attribute) {
                            if (!$attribute->get_variation()) {
                                continue;
                            }
                            $output .= '<span>' . $product->get_attribute(sanitize_title($attribute->get_name())) . '</span>';
                        }

                        $output .= '</li>';
                    }
                    $output .= '</ul>';
                    $output .= '</fieldset>';
                }
            }
        } else {

            foreach ($variations as $variation_object) {

                $variation_id   = $variation_object->get_id();

                $product = wc_get_product($variation_id);

                // pr($product->get_attributes());
                foreach ($product->get_attributes('edit') as $key => $attribute) {
                    
                        $array_vari_slug[$attribute] = $key;
                        $array_vari[$product->get_ID()][] = $attribute;

                        $atri_ary[$key][] = $attribute;
                        $atri_ary_product[$attribute] = $product->get_ID();
                    
                }
            }

            // pr($atri_ary_product);
            // pr('array-vari');
            // pr($array_vari);
            // pr('varrrrrr_slug');
            // pr($array_vari_slug);
            // pr($product_option['pa_color']);
            // pr($atri_ary);

           
                foreach ($array_vari as $key2 => $attribute2) {

                    $array_vari2[$attribute2[0]][$attribute2[1]] = $key2;
                    if($array_vari_slug[$attribute2[0]] == 'pa_color'){
                        $medata[$attribute2[0]] = $key2;
                    }else{
                        $medata[$attribute2[1]] = $key2;
                    }
                   
                }
            // pr($medata);

            // pr($array_vari2);
                foreach ($medata as $key2 => $attribute2) {

                    // $navigation .= '<div data-tab="tab_' . $i . '" class="tab-pane' . ($i == 0 ? ' show-tab' : '') . ' tab_' . $i . '" >';
                   
                    // foreach ($attribute2 as $attribute22 => $id) {
                    $product = wc_get_product($attribute2);
                    $attr2 = $product->get_attribute(sanitize_title($array_vari_slug[$key2]));

                    if (isset($product_option['pa_color'])) {
                        in_array($key2, $product_option['pa_color']) ? $checked = 'checked="checked"' : $checked = '';
                    }
                    $navigation .= '<div class="variation-row test">';
                    $navigation .= '<span><input class="nipl_variation_checkbox" data-variation="' . $id . '" type="checkbox" name="product_option[' . $kit_id . '][' . $product_id . '][' . $array_vari_slug[$key2] . '][]" value="' . $key2 . '" ' . $checked . '></span>';
                    $navigation .= '<span>' . esc_html($attr2) . '</span>';
                    $navigation .= '</div>';


                    // }
                    // $navigation .= '</div>';
                    $i++;
                }
            $navigation .= '</div>';
            


            //pr($array_vari);
            if (is_array($array_vari)) {
                // $navigation .= '<ul class="navigation-tabs">';


                // pr($product_option['variation']);

                // foreach ($atri_ary as $atriKey => $atriVal) {
                //     $navigation .= '<ul class="navigation-tabs">';

                //     foreach (array_unique($atriVal) as $key2 => $attribute2) {
                //         $conte =                       $product->get_attribute(sanitize_title($attribute2));
                //         pr($conte);
                //         $navigation .= '<li class="button-tab">
                //                     <a><input class="tab_checked" name="product_option[' . $kit_id . '][' . $product_id . '][' . $atriKey . '][]" value="' . $attribute2 . '" type="checkbox">' . esc_html($attribute2) . '</a>
                //                     </li>';
                //         $i++;
                //     }
                //     $navigation .= '</ul>';
                //     // $navigation .= '<div class="tab-content navigation-tabContent">';
                // }


                $checked = '';


                // foreach ($array_vari as $key2 => $attribute2) {
                //     $array_vari2[$attribute2[0]][$attribute2[1]] = $key2;
                // }
                // //pr($array_vari2);
                // $i = 0;
                // $navigation .= '<ul class="navigation-tabs">';


                // foreach ($array_vari2 as $key2 => $attribute2) {

                //     //pr(current($attribute2));
                //     $product = wc_get_product(current($attribute2));
                //     $attr = $product->get_attribute(sanitize_title($array_vari_slug[$key2]));

                //     $navigation .= '
                //     <li class="button-tab' . ($i == 0 ? ' active' : '') . '" data-tab="tab_' . $i . '">
                //     <a><input class="tab_checkedd" data-checked="tab_' . $i . '" type="checkbox" name="product_option[' . $kit_id . '][' . $product_id . '][variation][' . $array_vari_slug[$key2] . '][]" value="' . $attr . '" >' . $attr . '</a>
                //     </li>';
                //     $i++;
                // }
                // $navigation .= '</ul>';


                // $navigation .= '<div class="tab-content navigation-tabContent">';
                // $i = 0;

                // foreach ($array_vari2 as $key2 => $attribute2) {

                //     $navigation .= '<div data-tab="tab_' . $i . '" class="tab-pane' . ($i == 0 ? ' show-tab' : '') . ' tab_' . $i . '" >';

                //     foreach ($attribute2 as $attribute22 => $id) {
                //         $product = wc_get_product($id);
                //         $attr2 = $product->get_attribute(sanitize_title($array_vari_slug[$attribute22]));

                //         if (isset($product_option['variation'])) {
                //             in_array($id, $product_option['variation']) ? $checked = 'checked' : $checked = '';
                //         }
                //         $navigation .= '<div class="variation-row">';
                //         $navigation .= '<span><input data-variation="' . $id . '" type="checkbox" name="product_option[' . $kit_id . '][' . $product_id . '][variation][' . $array_vari_slug[$attribute22] . '][]" value="' . $attribute22 . '" ' . $checked . '></span>';
                //         $navigation .= '<span>' . esc_html($attr2) . '</span>';
                //         $navigation .= '</div>';
                //     }
                //     $navigation .= '</div>';
                //     $i++;
                // }
                // $navigation .= '</div>';
            }
        }
    }
    $output .= '</div>';
    return $header . $navigation . $output;
}

//save meta-box
add_action('save_post', 'save_table_product_to_project');

function save_table_product_to_project($post_id)
{


    if (
        !isset($_POST['table_product_to_project_nonce'])
        || !wp_verify_nonce($_POST['table_product_to_project_nonce'], basename(__FILE__))
    )
        return $post_id;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;

    if (!current_user_can('edit_post', $post_id))
        return $post_id;


    update_post_meta($post_id, 'add_product_to_project', $_POST['add_product_to_project']);


    update_post_meta($post_id, 'product_option', $_POST['product_option']);
    wc_delete_product_transients($post_id);
}


add_action('save_post', 'save_table_product_to_campaign');
function save_table_product_to_campaign($post_id, $data = '')
{

    //pr($_POST['product_option']);
    //die;
    if (isset($_POST['action']) && ($_POST['action'] != 'editpost') && ($data != ''))
        $_POST = $data;

    if (!isset($_POST['table_product_to_campaign_nonce']) || !wp_verify_nonce($_POST['table_product_to_campaign_nonce'], basename(__FILE__)))
        return $post_id;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;

    if (!current_user_can('edit_post', $post_id))
        return $post_id;


    if ($_POST['kits']) {
        // Product options for kit

        $groups             = isset($_POST['groups']) ? $_POST['groups'] : null;
        $required_products  = isset($_POST['required_products']) ? $_POST['required_products'] : null;

        update_post_meta($post_id, 'kits',       $_POST['kits']);
        update_post_meta($post_id, 'groups',  $groups);
        update_post_meta($post_id, 'required_products', $required_products);

        isset($_POST['one_order_toggle'])           ? update_post_meta($post_id, 'one_order_toggle', $_POST['one_order_toggle']) : update_post_meta($post_id, 'one_order_toggle', null);
        isset($_POST['budget'])                     ? update_post_meta($post_id, 'budget', $_POST['budget']) : '';
        isset($_POST['print-type'])                 ? update_post_meta($post_id, 'print-type', $_POST['print-type']) : '';
        isset($_POST['print-location'])             ? update_post_meta($post_id, 'print-location', $_POST['print-location']) : '';
        isset($_POST['closed-list'])                ? update_post_meta($post_id, 'closed-list', $_POST['closed-list']) : '';
        isset($_POST['add_product_to_campaign'])    ? update_post_meta($post_id, 'add_product_to_campaign', $_POST['add_product_to_campaign']) : '';

        isset($_POST['product_option'])             ? update_post_meta($post_id, 'product_option', $_POST['product_option']) : '';
    } else {
        delete_post_meta($post_id, 'kits');
        delete_post_meta($post_id, 'one_order_toggle');
        delete_post_meta($post_id, 'budget');
        delete_post_meta($post_id, 'print-location');
        delete_post_meta($post_id, 'closed-list');
        delete_post_meta($post_id, 'add_product_to_campaign');
        delete_post_meta($post_id, 'product_option');
        delete_post_meta($post_id, 'groups');
        delete_post_meta($post_id, 'required_products');
    }
}



// SORTING UN1-T130
function unid_ksort($data)
{
    $product_option_order = [];
    $count = 1000;
    foreach ($data['product_list'] as $key => $value) {
        if ($data['product_option'][$data['kit']][$value]['order'] == '') {
            $product_option_order[] = $count++;
        } else {
            $product_option_order[] = $data['product_option'][$data['kit']][$value]['order'];
        }
    }
    // var_dump($product_option_order);
    $product_list = array_combine($product_option_order, $data['product_list']);
    ksort($product_list);
    // var_dump($product_list);
    return $product_list;
}


if (!function_exists('pr')) {
    function pr($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}
