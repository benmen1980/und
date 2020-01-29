<?php

class Unidress_Assign_Product_Table extends WP_List_Table {

	protected $list_table_type = 'product';
	public $product_data = '';

	function __construct(){
		add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );

		parent::__construct(array(
			'singular' => 'product_table',
			'plural'   => 'product_tables',
			'ajax'     => true,
		));

		$this->bulk_action_handler();

		add_screen_option( 'per_page', array(
			'default' => 10,
			'option'  => 'product_table_per_page',
		) );
		$this->prepare_items();
	}

	function prepare_items(){
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$data = $this->table_data();
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

	public function get_hidden_columns()
	{
		return array();
	}
	function get_table_classes() {
		return array( 'striped', $this->_args['plural'] );
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
		return isset($item->$colname) ? $item->$colname : '-';
	}

	private function bulk_action_handler(){
		if( empty($_POST['licids']) || empty($_POST['_wpnonce']) ) return;

		if ( ! $action = $this->current_action() ) return;

		if( ! wp_verify_nonce( $_POST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) )
			wp_die('nonce error');

		die( print_r($_POST['licids']) );

	}

	function get_columns(){
		return array(
			'thumb'     => '<span class="wc-image tips" data-tip="' . esc_attr__( 'Image', 'unidress' ) . '">' . __( 'Image', 'unidress' ) . '</span>'  ,
			'name'      => esc_attr__( 'Name', 'unidress' ),
			'sku'       => esc_attr__( 'SKU', 'unidress' ),
			'action'    => '<a class="btn-add-all-product btn-simple">' . esc_attr__( 'Add all', 'unidress' ) . '</a>',
			'count'     => esc_attr__( 'Count', 'unidress' ),
		);
	}

	private function table_data() {
		$data = array();
		$already_assign_product = false;
		$args = array(
			'numberposts' => -1,
			'orderby'     => 'post__in ',
			'order'       => 'ASC',
			'post_type'   => 'product',
			'post__in' => $already_assign_product
		);
		$products = get_posts( $args );

		foreach ($products as $product) {

			$product_data = $this->get_product_data($product);

			$data[] = (object) array(
				'thumb'     => $product_data['thumb'],
				'name'      => $product_data['title'],
				'sku'       => $product_data['sku'],
				'action'    => $product_data['button'],
				'count'     => '0',
			);

		}
		return $data;
	}

	function get_product_data($product) {

		$product_meta = get_post_meta($product->ID, '',true);

		$product_data['id']         = $product->ID;
		$product_data['title']      = $this->render_name_column($product);
		$product_data['sku']        = (isset($product_meta['_sku'][0])) ? $product_meta['_sku'][0] : '-';
		$product_data['price']      = (isset($product_meta['_price'][0])) ? $product_meta['_price'][0] : '0';
		$product_data['thumb']      = $this->render_thumb_column($product->ID);
		$product_data['button']     = $this->render_button_column($product->ID);

		return $product_data;
	}

	function render_name_column($post_id) {
		$title     = _draft_or_post_title( $post_id );
		return $title;
	}
	function render_thumb_column($product_id) {
		$meta = get_post_meta($product_id,'_thumbnail_id',true);
		return wc_get_gallery_image_html( $meta, true ); // WPCS: XSS ok.
	}
	function render_button_column($product_id) {
		return '<a class="btn-add-product btn-simple" data-id="' . $product_id . '">' . esc_attr__( 'Add', 'unidress' ) . '</a>';
	}





}