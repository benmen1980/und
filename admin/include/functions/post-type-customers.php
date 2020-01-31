<?php 

// ACTION
add_action( 'admin_enqueue_scripts', 'unid_load_admin_scripts' );

// Load admin scripts & styles
function unid_load_admin_scripts( $hook ) {
	// If the post we're editing isn't a project_summary type, exit this function.
	// var_dump(get_post_type());
	if ( 'customers' === get_post_type() && ($hook == 'post-new.php' || $hook == 'post.php') ) {
		wp_enqueue_script( 'admin_scripts',  plugins_url(MY_PLUGIN_NAME.'/admin/js/functions/validate-form.js'), array( 'jquery' ), false );
	}
}



// add_action('init', 'unid_add_new_project_post');
// function unid_add_new_project_post()
// {

// 	$title = 'no active project';


// 	$my_posts = new WP_Query(array(
// 		'post_type' => 'project',
// 		's' => $title
// 	));

// 	if (!$my_posts->have_posts()) {
// 		$my_post = array(
// 			'post_type' 	=> 'project',
// 			'post_title'    => $title,
// 			'post_content'  => $title, // контент
// 			'post_status'   => 'publish' // опубликованный пост
// 		);
// 		$my_post_id = wp_insert_post( $my_post);
// 	}
	
	
// }

