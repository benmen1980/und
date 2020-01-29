<?php
/**
 * Created by PhpStorm.
 * User: Victor
 * Date: 09.06.2019
 * Time: 23:12
 */

add_action( 'admin_menu', function () {
	add_meta_box('add-product-to-project', 'Product to project', 'table_product_to_project2', 'project', 'normal', 'low');
} );

function table_product_to_project2() {

	wp_nonce_field( basename( __FILE__ ), 'table_product_to_project_nonce' );
	wp_enqueue_script( 'product-assign-js', plugins_url( '/unidress/admin/js/product-assign-project.js'), array( 'jquery' ) );

	require_once __DIR__ . '/class-Unidress_Assign_Product_Table.php';

	$GLOBALS['Unidress_Assign_Product_Table'] = new Unidress_Assign_Product_Table();
	?>
    <div class="section-product">
        <div class="product-wrapper table-wrapper">
            <h3>All product</h3>
            <div class="filter-bar">
                <?php echo render_products_category_filter(); ?>
                <?php echo render_products_type_filter(); ?>
                <?php echo render_products_stock_status_filter(); ?>
                <a name="filter_action" table-assign="false" class="product-filter btn-product btn-simple"><?php echo __( 'Filter', 'unidress' ) ?></a>
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input">Search:</label>
                    <input type="search" class="search-input" name="s" value="">
                    <a class="button button-search" table-assign="false">Search</a>
                </p>
            </div>
	        <?php $GLOBALS['Unidress_Assign_Product_Table']->display(); 	?>


        </div>
    </div>

    <?php }

//save screen option "per page"
add_filter( 'set-screen-option', function( $status, $option, $value ){
	return ( $option == 'product_per_page' ) ? (int) $value : $status;
}, 10, 3 );
