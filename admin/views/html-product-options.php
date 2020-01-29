<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
 ?>
<div data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>" class="woocommerce_option wc-metabox closed <?php echo esc_attr( implode( ' ', $metabox_class ) ); ?>" rel="<?php echo esc_attr( $i ); ?>">
    <h3>
        <a href="#" class="remove_row delete"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
        <div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'woocommerce' ); ?>"></div>
        <?php if ( $terms['taxonomy_id'] ) : ?>
            <strong class="option_name"><?php echo $product_options[$taxonomy]['label']; ?></strong>
        <?php else : ?>
            <strong class="option_name"><?php echo $terms['label'] ; ?></strong>
        <?php endif; ?>

    </h3>
    <div class="woocommerce_option_data wc-metabox-content hidden">
        <table cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <td class="attribute_option">
                    <label><?php esc_html_e( 'Name', 'woocommerce' ); ?>:</label>

                    <?php if ( $terms['taxonomy_id'] ) : ?>
                        <strong><?php echo $product_options[$taxonomy]['label']; ?></strong>
                        <input class="options"          type="hidden" name="options[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $taxonomy ); ?>" />
                        <input class="options_id"       type="hidden" name="options_id[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $terms['taxonomy_id'] ); ?>" />
                        <input class="options_label"    type="hidden" name="options_label[<?php echo esc_attr( $i ); ?>]" value="<?php echo $product_options[$taxonomy]['label']; ?>" />
                    <?php else : ?>
                        <input type="text" class="option_name" name="options[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $options[$taxonomy]['label'] ); ?>" />
                        <input class="options_label"    type="hidden" name="options_label[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $options[$taxonomy]['label'] ); ?>" />
                    <?php endif; ?>
                    <input type="hidden" name="attribute_position[<?php echo esc_attr( $i ); ?>]" class="attribute_position" value="<?php echo esc_attr( $i ); ?>" />
                </td>
                <td rowspan="3">
                    <label><?php esc_html_e( 'Value(s)', 'woocommerce' ); ?>:</label>
                    <?php
                    $get_options = array();

                    if ($terms['taxonomy_id']) {
                        $attribute_types['select'] = 'Select';
                        ?>
                        <select multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select terms', 'woocommerce' ); ?>" class="multiselect options_values wc-enhanced-select" name="options_values[<?php echo esc_attr( $i ); ?>][]">
                            <?php
                            $args      = array(
                                'orderby'    => 'name',
                                'hide_empty' => 0,
                            );
                            $all_terms = get_terms( $taxonomy, apply_filters( 'woocommerce_product_attribute_terms', $args ) );
                            if ( $all_terms ) {
                                foreach ($all_terms as $term) {
                                    $get_options[] = $term->term_id;
                                }
                                foreach ( $all_terms as $term ) {
                                    $get_options = ! empty( $get_options ) ? $get_options : array();
                                    echo '<option value="' . esc_attr( $term->term_id ) . '"' . wc_selected( $term->term_id, $options[$taxonomy]['terms'] ) . '>' . esc_attr( apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term ) ) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <button class="button plus select_all_options"><?php esc_html_e( 'Select all', 'woocommerce' ); ?></button>
                        <button class="button minus select_no_options"><?php esc_html_e( 'Select none', 'woocommerce' ); ?></button>
                        <button class="button fr plus add_new_options"><?php esc_html_e( 'Add new', 'woocommerce' ); ?></button>

                        <?php

                    } else {
                        /* translators: %s: WC_DELIMITER */
                        ?>
                        <textarea name="options_values[<?php echo esc_attr( $i ); ?>]" cols="5" rows="5" placeholder="<?php printf( esc_attr__( 'Enter some text, or some attributes by "%s" separating values.', 'woocommerce' ), WC_DELIMITER ); ?>"><?php echo esc_textarea( wc_implode_text_attributes($options[$taxonomy]['terms']) ); ?></textarea>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label><input type="checkbox" class="checkbox" <?php echo (isset($options[$taxonomy]['visibility']) && $options[$taxonomy]['visibility']) ? 'checked="checked"':''; ?> name="attribute_visibility[<?php echo esc_attr( $i ); ?>]" value="1" /> <?php esc_html_e( 'Visible on the product page', 'woocommerce' ); ?></label>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
