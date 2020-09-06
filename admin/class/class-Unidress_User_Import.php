<?php

class Unidress_User_Import extends WP_List_Table {

	public $import_data = array();

	function set_import_data($data){
		$this->import_data = $data;
		$this->prepare_items();
	}

	function __construct(){
		parent::__construct(array(
			'singular' => 'user_import',
			'plural'   => 'reports',
			'ajax'     => false,
		));

		$this->bulk_action_handler();

		add_screen_option( 'per_page', array(
			'default' => 20,
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
			'blog_id'      => $GLOBALS['blog_id'],
			'orderby'      => 'include',
			'order'        => 'ASC',
            'include'      => array_keys($this->import_data),
		);

		$users = get_users( $args );

		foreach ($users as $index => $user) {

			$user_data = $this->get_user_report_data($user);

			$data[] = (object) array(
				//'cb'                    =>  $index,
				'user_id'               =>  $user_data['user_id']   ,
				'username'              =>  $user_data['login']     ,
				'first_name'            =>  $user_data['first_name'],
				'last_name'             =>  $user_data['last_name'] ,
				'user_email'            =>  $user_data['user_email'],
				'billing_phone'         =>  $user_data['billing_phone'] ,
				'customer'              =>  $user_data['customer']  ,
				'branch'                =>  $user_data['branch']    ,
				'department'            =>  $user_data['department']    ,
				'kit'                   =>  $user_data['kit']    ,
				'status'                =>  $user_data['status']    ,
				'budget_value'          =>  $user_data['budget_value']    ,
			);

		}

		return $data;
	}

	function get_columns(){
		return array(
          //'cb'                    => '<input type="checkbox" />',
			'user_id'               => esc_attr__( 'ID', 'unidress' )  ,
			'username'              => esc_attr__( 'Username', 'unidress' )  ,
			'first_name'            => esc_attr__( 'First name', 'unidress' ),
			'last_name'             => esc_attr__( 'Last name', 'unidress' ),
			'user_email'            => esc_attr__( 'E-mail', 'unidress' ),
			'billing_phone'         => esc_attr__( 'Phone', 'unidress' ),
			'customer'              => esc_attr__( 'Customer', 'unidress' )  ,
			'branch'                => esc_attr__( 'Branch', 'unidress' )    ,
			'department'            => esc_attr__( 'Department', 'unidress' ),
			'kit'                   => esc_attr__( 'Kit', 'unidress' ),
			'status'                => esc_attr__( 'Status', 'unidress' ),
			'budget_value'          => esc_attr__( 'Budget', 'unidress' ),
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

	function get_user_report_data($user) {
		$login              = $user->data->user_login;
		$customer_id        = get_user_meta($user->ID, 'user_customer', true);
		$branch_id          = get_user_meta($user->ID, 'user_branch', true);
		$department         = get_user_meta($user->ID, 'user_department', true);
		$kit_id             = get_user_meta($user->ID, 'user_kit', true);
		$billing_phone      = get_user_meta($user->ID, 'billing_phone', true);
		$budget_value       = get_user_meta($user->ID, 'unidress_budget', true);

		$customer           = get_the_title($customer_id);
		$branch             = get_the_title($branch_id);
		$kit                = get_the_title($kit_id);

		$output = array();
		$output['user_id']              = $user->ID;
		$output['user_email']           = $user->user_email;
		$output['first_name']           = $user->first_name;
		$output['last_name']            = $user->last_name;
		$output['billing_phone']        = $billing_phone;
		$output['login']                = $login;
		$output['customer']             = $customer;
		$output['branch']               = $branch;
		$output['department']           = $department;
		$output['kit']                  = $kit;
		$output['status']               = $this->get_status($user->ID);
		$output['budget_value']         = $budget_value;

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