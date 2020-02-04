<?php


// ACTION
add_action( 'admin_enqueue_scripts', 'unid_load_admin_scripts_assign_product' );

// LOAD ADMIN SCRIPTS & STYLES
function unid_load_admin_scripts_assign_product( $hook ) {

	// If the post we're editing isn't a project_summary type, exit this function.
	// var_dump(get_post_type());
	$post_type = array('campaign', 'project');
	if ( in_array(get_post_type(), $post_type) && ($hook == 'post-new.php' || $hook == 'post.php') ) {


		add_screen_option( 'per_page', array(
			'label' => 'Number of items per page:',
			'default' => 10,
			'option' => 'unid_post_per_page_assign_product', // название опции, будет записано в метаполе юзера
		) );
		
		wp_enqueue_script( 'admin-scripts-assign-product',  plugins_url(MY_PLUGIN_NAME.'/admin/js/functions/assign-product.js'), array( 'jquery' ), '',true );
	}
}


function unid_get_per_page_assign_product() {
	$per_page_option = get_current_screen()->get_option('per_page');
	$per_page = get_user_meta( get_current_user_id(), $per_page_option['option'], true ) ?: $per_page_option['default'];
	return $per_page;
}


// Теперь чтобы опция сохранялась нужно добавить еще такой хук
// Cохранение опции экрана per_page. Нужно вызывать до события 'admin_menu'
add_filter( 'set-screen-option', function( $status, $option, $value ){
	return ( $option == 'unid_post_per_page_assign_product' ) ? (int) $value : $status;
}, 10, 3 );