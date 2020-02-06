<?php 

add_action( 'admin_enqueue_scripts', 'unid_load_admin_scripts_post_type_customers' );

// LOAD ADMIN SCRIPTS & STYLES
function unid_load_admin_scripts_post_type_customers( $hook ) {
	$post_type = array('customers');
	if ( in_array(get_post_type(), $post_type) && ($hook == 'post-new.php' || $hook == 'post.php') ) {
		wp_enqueue_script( 'admin-scripts-assign-product',  plugins_url(MY_PLUGIN_NAME.'/admin/js/functions/post-type-customers.js'), array( 'jquery' ), '',true );
	}
}