<?php 

// ACTION
add_action( 'admin_enqueue_scripts', 'unid_load_admin_scripts' );





// Load admin scripts & styles
function unid_load_admin_scripts( $hook ) {
	// If the post we're editing isn't a project_summary type, exit this function.
	// var_dump(get_post_type());
	if ( 'customers' === get_post_type() && ($hook == 'post-new.php' || $hook == 'post.php') ) {
		wp_enqueue_script( 'admin_scripts',  plugins_url(MY_PLUGIN_NAME.'/admin/js/functions/post-type-customers.js'), array( 'jquery' ), false );
	}
	// // If we're creating/updating the post, exit this function.
	// if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
	// 	return;
	// }
	// Enqueue JS.
}
