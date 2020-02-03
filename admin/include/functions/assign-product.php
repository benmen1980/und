<?php


// ACTION
add_action( 'admin_enqueue_scripts', 'unid_load_admin_scripts_assign_product' );

// LOAD ADMIN SCRIPTS & STYLES
function unid_load_admin_scripts_assign_product( $hook ) {
	// If the post we're editing isn't a project_summary type, exit this function.
	// var_dump(get_post_type());
	$post_type = array('campaign', 'project');
	if ( in_array(get_post_type(), $post_type) && ($hook == 'post-new.php' || $hook == 'post.php') ) {
		wp_enqueue_script( 'admin_scripts',  plugins_url(MY_PLUGIN_NAME.'/admin/js/functions/assign-product.js'), array( 'jquery' ), false );
	}
}

