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
		wp_localize_script( 'jquery', 'dataAssign', 
				array(
					'per_page' => unid_get_per_page_assign_product()
				)
		  );  
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


// AJAX
// add_action( 'wp_ajax_unid_product_sorted_assign_product', 'unid_product_sorted_assign_product' );
// function unid_product_sorted_assign_product(){
// 	global $wpdb, $userdata;
//    // $pageda = $_GET['assign-paged'] ? $_GET['assign-paged'] : 3;
//    // echo $pageda;
//    parse_str($_POST['order'], $data);
   
//    // if (!is_array($data)    ||  count($data)    <   1)
//    // die();


//    // $mysql_query    =   $wpdb->prepare("SELECT ID FROM ". $wpdb->posts ." 
//    //                                                          WHERE post_type = %s AND post_status IN ('publish', 'pending', 'draft', 'private', 'future')
//    //                                                          ORDER BY menu_order, post_date DESC", 'product');
//    // $results        =   $wpdb->get_results($mysql_query);

//    // $objects_ids    =   array();
//    //  foreach($results    as  $result)
//    //      {
//    //          $objects_ids[]  =   (int)$result->ID;   
//    //      }
//    // var_dump($objects_ids);


    

// }


