<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
wp_enqueue_script( 'product-options-js', plugins_url( '/unidress/admin/js/product-options.js'), array( 'jquery' ) );

global $post, $wpdb, $product_object;
$options = get_post_meta($post->ID, '_product_options', true);

?>
<div id="product_options" class="panel wc-metaboxes-wrapper hidden">
    <div class="toolbar toolbar-top">
		<span class="expand-close">
			<a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
		</span>
        <select name="attribute_options" class="attribute_options">
            <option value=""><?php echo 'Custom product options'; ?></option>
            <?php

            $taxonomies  = get_posts(array(
                'post_type'        => 'product_options',
                'suppress_filters' => false,
            ));
            foreach ($taxonomies as $tax){
                $product_options_label = unserialize($tax->post_content)['attribute_label'] ;
                $product_options_slug = $tax->post_name ;

                if ( isset($options[$tax->post_name]['taxonomy_id']) && $tax->ID == $options[$tax->post_name]['taxonomy_id']) {
                    echo '<option disabled="disabled" value="' . esc_attr( $product_options_slug ) . '">' .  $product_options_label . '</option>';
                } else {
                    echo '<option value="' . esc_attr( $product_options_slug ) . '">' .  $product_options_label . '</option>';
                }

                $terms_obj = get_terms( $product_options_slug, array(
                    'hide_empty' => false,
                ));
                $terms = array();
                foreach ($terms_obj as $term) {
                    $terms[$term->term_id] = $term->name;
                }
                if( isset($terms_obj[0]) ){
                    $product_options[$tax->post_name]['taxonomy_id'] = $tax->ID;
                    $product_options[$tax->post_name]['label'] = $tax->post_title;
                    $product_options[$tax->post_name]['name'] = $tax->post_name;
                    $product_options[$tax->post_name]['terms'] = $terms;
                }
            }
            ?>
        </select>
        <button type="button" class="button add_option"><?php esc_html_e( 'Add', 'woocommerce' ); ?></button>
    </div>
    <div class="product_options wc-metaboxes">
        <?php

        $i = -1;
        if ($options){
            foreach ($options as $taxonomy => $terms ) {
                $i++;
                $metabox_class = array();

                if ( $taxonomy ) {
                    $metabox_class[] = 'taxonomy';
                    $metabox_class[] = $taxonomy;
                }

                if ( (isset($terms['taxonomy_id']) and (isset($product_options['taxonomy_id']) ) ) ) {
                    continue;
                }
                include 'html-product-options.php';

            }
        } ?>
    </div>
    <div class="toolbar">
		<span class="expand-close">
			<a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
		</span>
        <button type="button" class="button save_options button-primary"><?php echo 'Save options'; ?></button>
    </div>
    <?php do_action( 'woocommerce_product_options_attributes' ); ?>
</div>
