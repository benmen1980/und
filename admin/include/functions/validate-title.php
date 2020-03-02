<?php 

// ACTION
add_action( 'admin_enqueue_scripts', 'unid_load_admin_scripts_validate_title' );

// LOAD ADMIN SCRIPTS & STYLES
function unid_load_admin_scripts_validate_title( $hook ) {
	// If the post we're editing isn't a project_summary type, exit this function.
	// var_dump(get_post_type());
	$post_type = array('customers', 'branch', 'kits');
	if ( in_array(get_post_type(), $post_type) && ($hook == 'post-new.php' || $hook == 'post.php') ) {
		wp_enqueue_script( 'admin_scripts',  plugins_url(MY_PLUGIN_NAME.'/admin/js/functions/validate-title.js'), array( 'jquery' ), false );
	}
}

