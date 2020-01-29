<?php

class Unidress_Branch_Import extends WP_List_Table {

	public $import_data = array();

	function set_import_data($data){
		$this->import_data = $data;
		$this->prepare_items();
	}

	function __construct(){
		parent::__construct(array(
			'singular' => 'brunch_import',
			'plural'   => 'reports',
			'ajax'     => false,
		));

		$this->bulk_action_handler();

		add_screen_option( 'per_page', array(
			'default' => 10,
			'option'  => 'user_import_per_page',
		) );

	}

	function prepare_items(){
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$data = $this->table_data();
		if (!$data)
			$data = array();
		usort( $data, array( &$this, 'sort_data' ) );
		$perPage = get_user_meta( get_current_user_id(), get_current_screen()->get_option( 'per_page', 'option' ), true ) ?: get_current_screen()->get_option( 'per_page', 'default' );

		$currentPage = $this->get_pagenum();
		$totalItems = count($data);

		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		) );

		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $data;

	}
	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function table_data() {

	    if (!$this->import_data)
	        return;

		$data = array();
		$args = array(
			'numberposts'  => -1,
			'orderby'      => 'include',
			'order'        => 'ASC',
            'include'      => array_keys($this->import_data),
            'post_type'      => 'branch',
            'suppress_filters'      => true,
		);
		$posts = get_posts( $args );

		foreach ($posts as $index => $post) {

			$post_data = $this->get_post_report_data($post);

			$data[] = (object) array(
				//'cb'                    =>  $index,
				'branch_id'             =>  $post_data['branch_id']    ,
				'branch_number'         =>  $post_data['branch_number']    ,
				'branch'                =>  $post_data['branch']    ,
				'customer_number'       =>  $post_data['customer_number']    ,
				'customer'              =>  $post_data['customer']    ,
				'branch_address'        =>  $post_data['branch_address'],
				'contact_name'          =>  $post_data['contact_name'],
				'contact_phone'         =>  $post_data['contact_phone'],
				'status'                =>  $post_data['status']    ,
			);

		}

		return $data;
	}

	function get_columns(){
		return array(
          //'cb'                    => '<input type="checkbox" />',
			'branch_number'        => esc_attr__( 'Priority Branch Number', 'unidress' )  ,
			'branch'               => esc_attr__( 'Branch', 'unidress' )  ,
			'customer_number'      => esc_attr__( 'Priority Customer Number', 'unidress' ),
			'customer'             => esc_attr__( 'Customer', 'unidress' ),
			'branch_address'       => esc_attr__( 'Branch address', 'unidress' ),
			'contact_name'         => esc_attr__( 'Contact name', 'unidress' ),
			'contact_phone'        => esc_attr__( 'Contact phone', 'unidress' )  ,
			'status'               => esc_attr__( 'Status', 'unidress' )    ,
		);
	}

	public function get_hidden_columns()
	{
		return array();
	}

	function get_table_classes() {
		return array( 'widefat', 'striped', $this->_args['plural'] );
	}

	function get_sortable_columns(){
		return array(
			//'username' => array( 'username', 'desc' ),
		);
	}

	protected function get_bulk_actions() {
		return array(
			//'delete' => 'Delete',
		);
	}

	function column_default( $item, $colname ){

		if( $colname === 'username' ){
			$super_admin = '';

			if ( is_multisite() && current_user_can( 'manage_network_users' ) ) {
				if ( in_array( $item->username, get_super_admins(), true ) ) {
					$super_admin = ' &mdash; ' . __( 'Super Admin' );
				}
			}
			$edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $item->user_id ) ) );

			if ( current_user_can( 'edit_user', $item->user_id ) ) {
				$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
			} else {
				$edit = "<strong>{$item->username}{$super_admin}</strong><br />";
			}
			return esc_html( $item->username ) . $this->row_actions( $actions );
		}

		return isset($item->$colname) ? $item->$colname : '-';

	}

	private function bulk_action_handler(){
		if( empty($_POST['licids']) || empty($_POST['_wpnonce']) ) return;

		if ( ! $action = $this->current_action() ) return;

		if( ! wp_verify_nonce( $_POST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) )
			wp_die('nonce error');

		die( print_r($_POST['licids']) );

	}

	function get_post_report_data($post) {

		$customer_id            = get_post_meta($post->ID, 'branch_customer', true);

		$customer               = get_post($customer_id);

		$customer_name          = $customer ? $customer->post_title : '';
		$customer_number        = $customer ? get_post_meta($customer->ID, 'priority_customer_number', true) : '';

		$branch                 = get_the_title($post->ID);
		$branch_number          = get_post_meta($post->ID, 'branch_priority_number', true);
		$branch_branch_address  = get_post_meta($post->ID, 'branch_address', true);
		$branch_contact_name    = get_post_meta($post->ID, 'contact_name', true);
		$branch_contact_phone   = get_post_meta($post->ID, 'contact_phone_number', true);

		$output = array();
		$output['branch_id']        = $post->ID;
		$output['branch_number']    = $branch_number;
		$output['branch']           = $branch;
		$output['customer_number']  = $customer_number;
		$output['customer']         = $customer_name;
		$output['branch_address']   = $branch_branch_address;
		$output['contact_name']     = $branch_contact_name;
		$output['contact_phone']    = $branch_contact_phone;
		$output['status']           = $this->get_status($post->ID);

		return $output;
	}

	function get_status ($user_id){
	    if (!$this->import_data || !isset($this->import_data[$user_id]) )
	        return 'Unknown error';

	    $status = '<div>' . $this->import_data[$user_id]['status'] . '</div>';
	    if (isset($this->import_data[$user_id]['error']) && $this->import_data[$user_id]['error'])
	    foreach ($this->import_data[$user_id]['error'] as $key => $message) {
		    $status .= '<div>' . $key . '.' . $message . '</div>';
	    }
		return $status;
	}

}
