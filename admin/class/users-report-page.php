<?php
/**
 * Created by PhpStorm.
 * User: Victor
 * Date: 09.06.2019
 * Time: 23:12
 */


add_action( 'admin_menu', function(){
	$hook = add_submenu_page( 'users.php', esc_attr__( 'User Report', 'unidress' ), esc_attr__( 'User Report', 'unidress' ), 'manage_options', 'user_report_table_page', 'user_report_table_page' );
	add_action( "load-$hook", 'user_report_page_load' );
} );

function user_report_page_load(){
	require_once __DIR__ . '/class-Unidress_User_Report.php';

	$GLOBALS['Unidress_User_Report'] = new Unidress_User_Report();

}

//save screen option "per page"
add_filter( 'set-screen-option', function( $status, $option, $value ){
	return ( $option == 'user_report_per_page' ) ? (int) $value : $status;
}, 10, 3 );

function user_report_table_page(){
	wp_enqueue_style( 'select2', plugins_url( '/js/select2/css/select2.min.css', dirname(__FILE__) ) );
	wp_enqueue_script( 'select2-js', plugins_url( '/js/select2/js/select2.min.js', dirname(__FILE__) ), array( 'jquery' ) );

	echo '<div class="wrap">';
	echo '<h2>' . get_admin_page_title() . '</h2>';
	echo '<form method="get">';

	if ( ! empty( $_GET['_wp_http_referer'] ) ) {
		wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		exit;
	}

	$GLOBALS['Unidress_User_Report']->display();

	echo '</form>';
	echo '</div>';
	if (isset($_GET['export_users'])) {
		$dir = wp_get_upload_dir();
		$outputFileName = $dir['baseurl'] . '/temp/unidress_export_report.xlsx';
		wp_redirect($outputFileName);
	}
}
