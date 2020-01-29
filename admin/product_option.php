<?php
/**
 * Attributes Page
 *
 * The attributes section lets users add custom attributes to assign to products - they can also be used in the "Filter Products by Attribute" widget.
 *
 * @package WooCommerce/Admin
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

// save all options there
add_action( 'init', 'create_options_post_type' );
function create_options_post_type() {
    $args = array(
        'labels' => array(
            'name'                  => 'Options',
            'singular_name'         => 'Option',
            'add_new'               => 'Add Option',
            'add_new_item'          => 'Add Option',
            'edit_item'             => 'Edit Option',
        ),
        'public' => false,
    );
    register_post_type( 'product_options', $args );
}

function product_options_output() {

    $result = '';
    $action = '';

    // Action to perform: add, edit, delete or none.
    if ( ! empty( $_POST['add_new_option'] ) ) {
        $action = 'add';
    } elseif ( ! empty( $_POST['save_attribute'] ) && ! empty( $_GET['edit'] ) ) {
        $action = 'edit';
    } elseif ( ! empty( $_GET['delete'] ) ) {
        $action = 'delete';
    }
    switch ( $action ) {
        case 'add':
            $result = process_add_option();
            break;
        case 'edit':
            $result = process_edit_option();
            break;
        case 'delete':
            $result = process_delete_option();
            break;
    }

    if ( is_wp_error( $result ) ) {
        echo '<div id="woocommerce_errors" class="error"><p>' . wp_kses_post( $result->get_error_message() ) . '</p></div>';
    }

    // Show admin interface.
    if ( ! empty( $_GET['edit'] ) ) {
        edit_product_option();
    } else {
        add_product_option();
    }
}

 function get_posted_option() {
    $attribute = array(
        'attribute_label'   => isset( $_POST['attribute_label'] )   ? wc_clean( stripslashes( $_POST['attribute_label'] ) ) : '',
        'attribute_name'    => isset( $_POST['attribute_name'] )    ? wc_sanitize_taxonomy_name( stripslashes( $_POST['attribute_name'] ) ) : '',
        'attribute_type'    => isset( $_POST['attribute_type'] )    ? wc_clean( $_POST['attribute_type'] ) : 'select',
        'attribute_orderby' => isset( $_POST['attribute_orderby'] ) ? wc_clean( $_POST['attribute_orderby'] ) : '',
        'attribute_public'  => isset( $_POST['attribute_public'] )  ? 1 : 0,
    );
    if ( empty( $attribute['attribute_type'] ) ) {
        $attribute['attribute_type'] = 'select';
    }
    if ( empty( $attribute['attribute_label'] ) ) {
        $attribute['attribute_label'] = ucfirst( $attribute['attribute_name'] );
    }
    if ( empty( $attribute['attribute_name'] ) ) {
        $attribute['attribute_name'] = wc_sanitize_taxonomy_name( $attribute['attribute_label'] );
    }

    return $attribute;
} //ok

/**
 * Add an option.
 *
 * @return bool|WP_Error
 */
 function process_add_option() {
    check_admin_referer( 'woocommerce-add-new_option' );

    $attribute = get_posted_option();
    $args      = array(
        'name'         => $attribute['attribute_label'],
        'slug'         => $attribute['attribute_name'],
        'type'         => $attribute['attribute_type'],
        'order_by'     => $attribute['attribute_orderby'],
        'has_archives' => $attribute['attribute_public'],
    );

    $id = create_product_option( $args );

    if ( is_wp_error( $id ) ) {
        return $id;
    }

    return true;
}

/**
 * Edit an option.
 *
 * @return bool|WP_Error
 */
 function process_edit_option() {
    $attribute_id = absint( $_GET['edit'] );
    check_admin_referer( 'woocommerce-save-option_' . $attribute_id );

    $attribute = get_posted_option();
    $args      = array(
        'name'         => $attribute['attribute_label'],
        'slug'         => $attribute['attribute_name'],
        'type'         => $attribute['attribute_type'],
        'order_by'     => $attribute['attribute_orderby'],
        'has_archives' => $attribute['attribute_public'],
    );
    $id = update_product_option( $attribute_id, $args );

    if ( is_wp_error( $id ) ) {
        return $id;
    }

    echo '<div class="updated"><p>' . __( 'Attribute updated successfully', 'woocommerce' ) . '</p><p><a href="' . esc_url( admin_url( 'edit.php?post_type=product&amp;page=product_option' ) ) . '">' . __( 'Back to Attributes', 'woocommerce' ) . '</a></p></div>';

    return true;
} // ok

/**
 * Delete an option.
 *
 * @return bool
 */
 function process_delete_option() {
    $attribute_id = absint( $_GET['delete'] );
    check_admin_referer( 'woocommerce-delete-option_' . $attribute_id );

    return delete_product_option( $attribute_id );
}

/**
 * Edit Attribute admin panel.
 *
 * Shows the interface for changing an attributes type between select and text.
 */
function edit_product_option() {
    global $wpdb;

    $edit = absint( $_GET['edit'] );

    $attribute_to_edit = $wpdb->get_row( "SELECT * FROM wp_posts WHERE post_type = 'product_options' and ID = " . $edit);

    ?>
    <div class="wrap woocommerce">
        <h1><?php echo 'Edit option'; ?></h1>

        <?php
        if ( ! $attribute_to_edit ) {
            echo '<div id="woocommerce_errors" class="error"><p>' . 'Error: non-existing option ID.' . '</p></div>';
        } else {
        $att_label   = format_to_edit( unserialize($attribute_to_edit->post_content)['attribute_label']);
            ?>
            <form action="edit.php?post_type=product&amp;page=product_option&amp;edit=<?php echo absint( $edit ); ?>" method="post">
                <table class="form-table">
                    <tbody>
                    <tr class="form-field form-required">
                        <th scope="row" valign="top">
                            <label for="attribute_label"><?php esc_html_e( 'Name', 'woocommerce' ); ?></label>
                        </th>
                        <td>
                            <input name="attribute_label" id="attribute_label" type="text" value="<?php echo esc_attr( $att_label ); ?>" />
                            <p class="description"><?php echo'Name for the option (shown on the front-end).'; ?></p>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <p class="submit"><button type="submit" name="save_attribute" id="submit" class="button-primary" value="<?php esc_attr_e( 'Update', 'woocommerce' ); ?>"><?php esc_html_e( 'Update', 'woocommerce' ); ?></button></p>
                <?php wp_nonce_field( 'woocommerce-save-option_' . $edit ); ?>
            </form>

        <?php } ?>
    </div>
    <?php

}

/**
 * Add Attribute admin panel.
 *
 * Shows the interface for adding new attributes.
 */
function add_product_option() {
    ?>
    <div class="wrap woocommerce">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <br class="clear" />
        <div id="col-container">
            <div id="col-right">
                <div class="col-wrap">
                    <table class="widefat attributes-table wp-list-table ui-sortable" style="width:100%">
                        <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e( 'Name', 'woocommerce' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Terms', 'woocommerce' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if ( $attribute_taxonomies = get_options_taxonomies() ) :
                            foreach ( $attribute_taxonomies as $tax ) :
                                ?>
                                <tr>
                                    <td>
                                        <strong><a href="edit-tags.php?taxonomy=<?php echo esc_html( wc_sanitize_taxonomy_name( $tax->post_name ) ); ?>&amp;post_type=graphic"><?php echo esc_html( $tax->post_title ); ?></a></strong>
                                        <div class="row-actions"><span class="edit"><a href="<?php echo esc_url( add_query_arg( 'edit', $tax->ID, 'edit.php?post_type=product&amp;page=product_option' ) ); ?>"><?php esc_html_e( 'Edit', 'woocommerce' ); ?></a> | </span><span class="delete"><a class="delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'delete', $tax->ID, 'edit.php?post_type=product&amp;page=product_option' ), 'woocommerce-delete-option_' . $tax->ID ) ); ?>"><?php esc_html_e( 'Delete', 'woocommerce' ); ?></a></span></div>
                                    </td>
                                    <td class="attribute-terms">
                                        <?php
                                        $taxonomy =  esc_html($tax->post_name);

                                        if ( taxonomy_exists( $taxonomy ) ) {
                                            if ( 'menu_order' === wc_attribute_orderby( $taxonomy ) ) {
                                                $terms = get_terms( $taxonomy, 'hide_empty=0&menu_order=ASC' );
                                            } else {
                                                $terms = get_terms( $taxonomy, 'hide_empty=0&menu_order=false' );
                                            }

                                            $terms_string = implode( ', ', wp_list_pluck( $terms, 'name' ) );
                                            if ( $terms_string ) {
                                                echo esc_html( $terms_string );
                                            } else {
                                                echo '<span class="na">&ndash;</span>';
                                            }
                                        } else {
                                            echo '<span class="na">&ndash;</span>';
                                        }
                                        ?>
                                        <br /><a href="edit-tags.php?taxonomy=<?php echo esc_html( wc_sanitize_taxonomy_name(  $tax->post_name ) ); ?>&amp;post_type=product" class="configure-terms"><?php esc_html_e( 'Configure terms', 'woocommerce' ); ?></a>
                                    </td>
                                </tr>
                            <?php
                            endforeach;
                        else :
                            ?>
                            <tr>
                                <td colspan="6"><?php esc_html_e( 'No attributes currently exist.', 'woocommerce' ); ?></td>
                            </tr>
                        <?php
                        endif;
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="col-left">
                <div class="col-wrap">
                    <div class="form-wrap">
                        <h2><?php echo 'Add new option' ?></h2>
                        <form action="edit.php?post_type=product&amp;page=product_option" method="post">
                            <?php do_action( 'woocommerce_before_add_attribute_fields' ); ?>

                            <div class="form-field">
                                <label for="attribute_label"><?php esc_html_e( 'Name', 'woocommerce' ); ?></label>
                                <input name="attribute_label" id="attribute_label" type="text" value="" />
                                <p class="description"><?php esc_html_e( 'Name for the option (shown on the front-end).', 'woocommerce' ); ?></p>
                            </div>

                            <?php
                            if ( wc_has_custom_attribute_types() ) {
                                ?>
                                <div class="form-field">
                                    <label for="attribute_type"><?php esc_html_e( 'Type', 'woocommerce' ); ?></label>
                                    <select name="attribute_type" id="attribute_type">
                                        <?php foreach ( wc_get_attribute_types() as $key => $value ) : ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $value ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php esc_html_e( "Determines how this attribute's values are displayed.", 'woocommerce' ); ?></p>
                                </div>
                                <?php
                            }
                            ?>
                            <p class="submit"><button type="submit" name="add_new_option" id="submit" class="button button-primary" value="add_option"><?php echo'Add option'; ?></button></p>
                            <?php wp_nonce_field( 'woocommerce-add-new_option' ); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            /* <![CDATA[ */

            jQuery( 'a.delete' ).click( function() {
                if ( window.confirm( '<?php echo 'Are you sure you want to delete this option?' ?>' ) ) {
                    return true;
                }
                return false;
            });

            /* ]]> */
        </script>
    </div>
    <?php
}

function get_options_taxonomies() {

    global $wpdb;

    $attribute_taxonomies = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_type = 'product_options' ORDER BY post_name ASC;" );
    return (array) array_filter( apply_filters( 'product_option_taxonomies', $attribute_taxonomies ) );
}

function create_product_option( $args ) {
    global $wpdb;
    $args   = wp_unslash( $args );
    $id     = ! empty( $args['id'] ) ? intval( $args['id'] ) : 0;

    // Name is required.
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'missing_attribute_name', __( 'Please, provide an attribute name.', 'woocommerce' ), array( 'status' => 400 ) );
    }
    // Set the attribute slug.
    if ( empty( $args['slug'] ) ) {
        $slug = wc_sanitize_taxonomy_name( $args['name'] );
    } else {
        $slug = preg_replace( '/^po\_/', '', wc_sanitize_taxonomy_name( $args['slug'] ) );
    }

    // Validate slug.
    if ( strlen( $slug ) >= 28 ) {
        /* translators: %s: attribute slug */
        return new WP_Error( 'invalid_product_attribute_slug_too_long', sprintf( __( 'Slug "%s" is too long (28 characters max). Shorten it, please.', 'woocommerce' ), $slug ), array( 'status' => 400 ) );
    } elseif ( wc_check_if_attribute_name_is_reserved( $slug ) ) {
        /* translators: %s: attribute slug */
        return new WP_Error( 'invalid_product_attribute_slug_reserved_name', sprintf( __( 'Slug "%s" is not allowed because it is a reserved term. Change it, please.', 'woocommerce' ), $slug ), array( 'status' => 400 ) );
    } elseif ( ( 0 === $id && taxonomy_exists( wc_option_taxonomy_name( $slug ) ) ) || ( isset( $args['old_slug'] ) && $args['old_slug'] !== $slug && taxonomy_exists( wc_option_taxonomy_name( $slug ) ) ) ) {
        /* translators: %s: attribute slug */
        return new WP_Error( 'invalid_product_attribute_slug_already_exists', sprintf( __( 'Slug "%s" is already in use. Change it, please.', 'woocommerce' ), $slug ), array( 'status' => 400 ) );
    }

    $data = array(
        'attribute_label'   => $args['name'],
        'attribute_name'    => $slug,
        'attribute_type'    => $args['type'],
        'attribute_public'  => isset( $args['has_archives'] ) ? (int) $args['has_archives'] : 0,
    );

    // Create or update.
    if ( 0 === $id ) {
        $post_data = array(
            'post_title'    => wp_strip_all_tags( $data['attribute_label'] ),
            'post_content'  => serialize($data),
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'product_options',
            'post_name'     => rand(),
        );

        $results = wp_insert_post( wp_slash($post_data), true );

        if ( is_wp_error( $results ) ) {
            return new WP_Error( 'cannot_create_attribute', $results->get_error_message(), array( 'status' => 400 ) );
        }
    } else {
        $post_data = array(
            'ID'            => $id,
            'post_title'    => wp_strip_all_tags( $data['attribute_label'] ),
            'post_content'  => serialize($data),
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'product_options',
            'post_name'     => $data['attribute_name'],
        );

        // insert post to DB
        $results = wp_insert_post( wp_slash($post_data), true );
        if ( false === $results ) {
            return new WP_Error( 'cannot_update_attribute', __( 'Could not update the attribute.', 'woocommerce' ), array( 'status' => 400 ) );
        }
        // Set old slug to check for database changes.
        $old_slug = ! empty( $args['old_slug'] ) ? ( $args['old_slug'] ) : $slug;

        if ( $old_slug !== $slug ) {
            // Update taxonomies in the wp term taxonomy table.
            $wpdb->update(
                $wpdb->term_taxonomy,
                array( 'taxonomy' => wc_option_taxonomy_name( $data['attribute_name'] ) ),
                array( 'taxonomy' => 'po_' . $old_slug )
            );

            // Update taxonomy ordering term meta.
            $table_name = get_option( 'db_version' ) < 34370 ? $wpdb->prefix . 'woocommerce_termmeta' : $wpdb->termmeta;
            $wpdb->update(
                $table_name,
                array( 'meta_key' => 'order_po_' . sanitize_title( $data['attribute_name'] ) ), // WPCS: slow query ok.
                array( 'meta_key' => 'order_po_' . sanitize_title( $old_slug ) ) // WPCS: slow query ok.
            );

            // Update product attributes which use this taxonomy.
            $old_taxonomy_name = 'po_' . $old_slug;
            $new_taxonomy_name = 'po_' . $data['attribute_name'];
            $metadatas         = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_product_attributes' AND meta_value LIKE %s",
                    '%' . $wpdb->esc_like( $old_taxonomy_name ) . '%'
                ),
                ARRAY_A );
            foreach ( $metadatas as $metadata ) {
                $product_id        = $metadata['post_id'];
                $unserialized_data = maybe_unserialize( $metadata['meta_value'] );
                if ( ! $unserialized_data || ! is_array( $unserialized_data ) || ! isset( $unserialized_data[ $old_taxonomy_name ] ) ) {
                    continue;
                }

                $unserialized_data[ $new_taxonomy_name ] = $unserialized_data[ $old_taxonomy_name ];
                unset( $unserialized_data[ $old_taxonomy_name ] );
                $unserialized_data[ $new_taxonomy_name ]['name'] = $new_taxonomy_name;
                update_post_meta( $product_id, '_product_attributes', $unserialized_data );
            }

            // Update variations which use this taxonomy.
            $wpdb->update(
                $wpdb->postmeta,
                array( 'meta_key' => 'attribute_po_' . sanitize_title( $data['attribute_name'] ) ), // WPCS: slow query ok.
                array( 'meta_key' => 'attribute_po_' . sanitize_title( $old_slug ) ) // WPCS: slow query ok.
            );
        }
    }
    // Clear cache and flush rewrite rules.
    wp_schedule_single_event( time(), 'woocommerce_flush_rewrite_rules' );
    delete_transient( 'wc_attribute_taxonomies' );
    return $id;
}

function update_product_option( $id, $args ) {
    global $wpdb;

    $attribute = get_product_option( $id );
    $args['id'] = $attribute ? $attribute->id : 0;

    if ( $args['id'] && empty( $args['name'] ) ) {
        $args['name'] = $attribute->name;
    }

    $args['old_slug'] = $wpdb->get_var(
        $wpdb->prepare(
            "
				SELECT post_name
				FROM {$wpdb->prefix}posts
		        WHERE ID = %d
			", $args['id']
        )
    );
    return create_product_option( $args );
}

function get_product_option( $id ) {
    global $wpdb;

    $data = $wpdb->get_row(
        $wpdb->prepare(
            "
		SELECT *
		FROM {$wpdb->prefix}posts
		WHERE ID = %d
	 ", $id
        )
    );

    if ( is_wp_error( $data ) || is_null( $data ) ) {
        return null;
    }

    $attribute               = new stdClass();
    $attribute->id           = (int) $data->ID;
    $attribute->name         = unserialize($data->post_content)['attribute_label'];
    $attribute->slug         = unserialize($data->post_content)['attribute_name'];
    $attribute->type         = unserialize($data->post_content)['attribute_type'];
    $attribute->has_archives = (bool) unserialize($data->post_content)['attribute_public'];

    return $attribute;
}

function delete_product_option( $id ) {
    global $wpdb;

    $name = $wpdb->get_var(
        $wpdb->prepare(
            "
		SELECT post_name
        FROM {$wpdb->prefix}posts
        WHERE ID = %d
	", $id
        )
    );

    $taxonomy = $name;

    /**
     * Before deleting an attribute.
     *
     * @param int    $id       Attribute ID.
     * @param string $name     Attribute name.
     * @param string $taxonomy Attribute taxonomy name.
     */

    if ( $name && $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}posts WHERE ID = %d", $id ) ) ) {
        if ( taxonomy_exists( $taxonomy ) ) {
            $terms = get_terms( $taxonomy, 'orderby=name&hide_empty=0' );
            foreach ( $terms as $term ) {
                wp_delete_term( $term->term_id, $taxonomy );
            }
        }

        /**
         * After deleting an attribute.
         *
         * @param int    $id       Attribute ID.
         * @param string $name     Attribute name.
         * @param string $taxonomy Attribute taxonomy name.
         */
        do_action( 'woocommerce_attribute_deleted', $id, $name, $taxonomy );
        wp_schedule_single_event( time(), 'woocommerce_flush_rewrite_rules' );
        delete_transient( 'wc_attribute_taxonomies' );

        return true;
    }

    return false;
}

function wc_option_taxonomy_name( $attribute_name ) {
    return $attribute_name ? 'po_' . wc_sanitize_option_taxonomy_name( $attribute_name ) : '';
}

function wc_sanitize_option_taxonomy_name( $taxonomy ) {
    return $taxonomy;
}