<?php

require_once(__DIR__ . '/include/PhpSpreadsheet/Psr/autoloader.php');
require_once(__DIR__ . '/include/PhpSpreadsheet/autoloader.php');

add_action( 'admin_menu', function(){
	add_submenu_page( 'users.php', esc_attr__( 'Import Users', 'unidress' ), esc_attr__( 'Import Users', 'unidress' ), 'manage_options', 'import_users', 'unidress_upload_customers' );
	add_submenu_page( 'edit.php?post_type=branch', esc_attr__( 'Import Branch', 'unidress' ), esc_attr__( 'Import Branch', 'unidress' ), 'manage_options', 'import_branch', 'unidress_upload_branch' );
} );

function unidress_upload_customers() {
	render_form('users');
}

function unidress_upload_branch() {
	render_form('branch');
}
function render_form($page_type){
	echo '<div class="wrap">';
	?>
    <h2><?php echo get_admin_page_title() ?></h2>

    <form id="featured_upload" method="post" action="#" enctype="multipart/form-data">
        <input type="file" name="import_file" id="import_file"  multiple="false" />
	<?php
        if('users'==$page_type){
            ?>
                <br><br>
            <input type="checkbox" name="is_reset_user_data" id="is_reset_user_data" />
            <label>Reset user history?</label><br><br>
            <?php
        }
        ?>    
        <input type="hidden" name="import_type" id="import_type" value="<?php echo $page_type ?>" />
		<?php wp_nonce_field( 'import_file', 'import_file_nonce' ); ?>
        <input id="submit_import_file" name="submit_import_file" type="submit" value="Upload" />
    </form>

	<?php
	if (isset( $_POST['import_file_nonce']))
		if (
			isset($_POST['import_type'] )
			&& wp_verify_nonce( $_POST['import_file_nonce'], 'import_file' )
			&& current_user_can( 'manage_options' )
		) {
			/*
			if ($_FILES['import_file']['type'] !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
				echo "Not valid file type";
				return;
			}
			*/
			$dir = wp_get_upload_dir();

			$name = $_POST['import_type'] . '.xlsx';
			$uploaddir = $dir['basedir'] . '/temp/';
			$uploadfile = $uploaddir . basename($name);

			echo '<pre>';
			if (move_uploaded_file($_FILES['import_file']['tmp_name'], $uploadfile)) {
				echo "Correct upload file.\n";
			} else {
				echo "Not correct upload file\n";
				return;
			}
			print "</pre>";

			if ($page_type === "users") {
				import_users($page_type);
            } elseif ($page_type === "branch") {
				import_branch($page_type);
			}

		}
    echo '</div>';
}

function import_users($page_type){

	$dir = wp_get_upload_dir();
	$inputFileName = $dir['basedir'] . '/temp/' .$page_type . '.xlsx';

	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
	$reader->setReadDataOnly(true);
	$spreadsheet = $reader->load($inputFileName);

	$sheetData = $spreadsheet->getActiveSheet()->toArray(null, false, false, true);
	$columnName = array();
	/* disable parse title
	 * foreach ($sheetData[1] as $key=>$row_name) {
		switch (trim($row_name)) {
			case 'User first name':
				$columnName['first_name'] = $key;
				break;
			case 'User last name':
				$columnName['last_name'] = $key;
				break;
			case 'Username':
				$columnName['user_login'] = $key;
				break;
			case 'Password':
				$columnName['user_pass'] = $key;
				break;
			case 'Email':
				$columnName['user_email'] = $key;
				break;
			case 'Phone':
				$columnName['billing_phone'] = $key;
				break;
			case 'Customer Priority Number':
				$columnName['customer_number'] = $key;
				break;
			case 'Customer Name':
				$columnName['customer_name'] = $key;
				break;
			case 'Branch Priority Number':
				$columnName['brunch_number'] = $key;
				break;
			case 'Branch Name':
				$columnName['brunch_name'] = $key;
				break;
			case 'Kit Number':
				$columnName['kit_number'] = $key;
				break;
			case 'Kit Name':
				$columnName['kit_name'] = $key;
				break;
			case 'Department':
				$columnName['department'] = $key;
				break;

		}

	}
	*/
	$columnName['first_name'] = 'A';
	$columnName['last_name'] = 'B';
	$columnName['user_login'] = 'C';
	$columnName['user_pass'] = 'D';
	$columnName['user_email'] = 'E';
	$columnName['billing_phone'] = 'F';
	$columnName['customer_number'] = 'G';
	$columnName['customer_name'] = 'H';
	$columnName['brunch_number'] = 'I';
	$columnName['brunch_name'] = 'J';
	$columnName['kit_number'] = 'K';
	$columnName['kit_name'] = 'L';
	$columnName['department'] = 'M';
	$columnName['budget_value'] = 'N';

	if (!isset($sheetData[1][$columnName['kit_number']])) {
	    echo 'File is wrong';
	    return;
    }

	unset($sheetData[1]);
	$users = array();

	foreach ($sheetData as $row => $data) {

		if (!$data[$columnName['user_login']])
			continue;

		$user = get_user_by('login', $data[$columnName['user_login']]);

		if (!$user) {

			$userdata = array(
				'user_login' => $data[$columnName['user_login']],
				'user_pass'  => isset($data[$columnName['user_pass']]) ? $data[$columnName['user_pass']] : wp_generate_password(),
				'user_email' => $data[$columnName['user_email']],
				'first_name' => $data[$columnName['first_name']],
				'last_name'  => $data[$columnName['last_name']],
				'role'       => 'customer',
			);

			$user_id = wp_insert_user( $userdata ) ;

			if( ! is_wp_error( $user_id ) ) {

				$users[$user_id]['status'] = 'New';
				$users = import_meta_data($user_id, $data, $columnName, $users);
			} else {
				var_dump('username invalid');
				var_dump($user_id);
			    // no user_id because username invalid
				//$users[$user_id]['status'] = 'Error';
				//$users[$user_id]['error'][] = $user_id->get_error_message();

			}

		} else {

			$userdata = array(
				'ID'         => $user->ID,
				'user_login' => $data[$columnName['user_login']],
				'user_email' => $data[$columnName['user_email']],
				'first_name' => $data[$columnName['first_name']],
				'last_name'  => $data[$columnName['last_name']],
			);

			$user_id = wp_insert_user( $userdata );

			if( ! is_wp_error( $user_id ) ) {
				$users[$user_id]['status']  = 'Update';
				$users = import_meta_data($user_id, $data, $columnName, $users);
				wp_set_password( $data[$columnName['user_pass']], $user_id );
			} else {

				$users[$user_id]['status']  = 'Update with error';
				$users[$user_id]['error'][] = $user_id->get_error_message();
			}
		}
		// reset user budget and order
		$user_meta = get_user_meta($user_id);
		if(isset($_POST['is_reset_user_data'])){
            delete_user_meta($user->ID,'user_limits');
		    update_user_meta($user_id, 'user_budget_limits', []);
		    update_user_meta($user->ID,'one_order_value',[]);
		}
		$user_meta = get_user_meta($user_id);
	}

	require_once __DIR__ . '/class/class-Unidress_User_Import.php';
	$Unidress_User_Import = new Unidress_User_Import();
	$Unidress_User_Import->set_import_data($users);
	$Unidress_User_Import->display();
}
function import_branch($page_type){

	$dir = wp_get_upload_dir();
	$inputFileName = $dir['basedir'] . '/temp/' .$page_type . '.xlsx';
	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
	$reader->setReadDataOnly(true);
	$spreadsheet = $reader->load($inputFileName);

	$sheetData = $spreadsheet->getActiveSheet()->toArray(null, false, false, true);
	$columnName = array();
	/*
	 * disable parse title
	foreach ($sheetData[1] as $key=>$row_name) {
		$row_name = str_replace('&amp;nbsp;', '',$row_name);
		$row_name = str_replace( "&nbsp","",$row_name );
		switch (trim($row_name)) {
			case 'Priority Branch Number':
				$columnName['branch_priority_number'] = $key;
				break;
			case 'Branch title':
				$columnName['branch_title'] = $key;
				break;
			case 'Priority Customer Number':
				$columnName['priority_customer_number'] = $key;
				break;
			case 'Branch Address':
			    if (!$columnName['branch_address']) {
                    $columnName['branch_address'] = $key;
				    break;
			    }
			case 'Branch Address':
                $columnName['branch_address2'] = $key;
				break;
			case 'Contact name':
				$columnName['contact_name'] = $key;
				break;
			case 'Contact Phone Number':
				$columnName['billing_phone'] = $key;
				break;
			case 'Customer':
				break;
            default:
                if (!empty(trim($row_name)))
	                $columnName['error'][$key] = trim($row_name);

		}
	}
		print_r($columnName['error']);

	*/
	$columnName['branch_priority_number'] = 'A';
	$columnName['branch_title'] = 'B';
	$columnName['priority_customer_number'] = 'C';
	$columnName['customer_name'] = 'D';
	$columnName['branch_address'] = 'E';
	$columnName['branch_address2'] = 'F';
	$columnName['contact_name'] = 'G';
	$columnName['contact_phone_number'] = 'H';

	if (!isset($sheetData[1][$columnName['branch_title']])) {
		echo 'File is wrong';
		return;
	}

	unset($sheetData[1]);
	$post_array = array();

	foreach ($sheetData as $row => $data) {

		if (!isset($data[$columnName['branch_title']]) || !$data[$columnName['branch_title']])
			continue;

		$post = get_page_by_title($data[$columnName['branch_title']], 'OBJECT', 'branch');
		if (!$post) {
			$post_data = array(
				'post_title'    => $data[$columnName['branch_title']],
				'post_author'   => get_current_user_id(),
				'post_status'   => 'publish',
				'post_type'     => 'branch',
			);

			$post_id = wp_insert_post( wp_slash($post_data) );

			if( ! is_wp_error( $post_id ) ) {

				$post_array[$post_id]['status'] = 'New';
				$post_array = import_meta_data($post_id, $data, $columnName, $post_array);

			} else {

				$post_array[$post_id]['status'] = 'Error';
				$post_array[$post_id]['error'][] = $post_id->get_error_message();

			}
		} else {
			$post_data = array(
				'ID'            => $post->ID,
				'post_title'    => $data[$columnName['branch_title']],
				'post_author'   => get_current_user_id(),
				'post_status'   => 'publish',
				'post_type'     => 'branch',
			);

			$post_id = wp_insert_post( wp_slash($post_data) );

			if( ! is_wp_error( $post_id ) ) {
				$post_array[$post_id]['status']  = 'Update';
				$post_array = import_meta_data($post_id, $data, $columnName, $post_array);

			} else {

				$post_array[$post_id]['status']  = 'Error';
				$post_array[$post_id]['error'][] = $post_id->get_error_message();

			}
		}

	}

	require_once __DIR__ . '/class/class-Unidress_Branch_Import.php';
	$Unidress_Branch_Import = new Unidress_Branch_Import();
	$Unidress_Branch_Import->set_import_data($post_array);
	$Unidress_Branch_Import->display();
}
function import_meta_data($user_id, $data, $columnName, $users) {

	if (isset($columnName['billing_phone'])) {
		update_user_meta( $user_id, 'billing_phone', $data[$columnName['billing_phone']] );
	}

	if (isset($columnName['budget_value'])) {
		$budget_value = (!empty($data[$columnName['budget_value']])) ? $data[$columnName['budget_value']] : 0;
		update_user_meta( $user_id, 'unidress_budget', $budget_value);
	}

    $customer_id = '';

	if (isset($columnName['customer_number'])) {
		if ($data[$columnName['customer_number']]){
		    //Priority Customer Number
            update_user_meta($user_id, 'priority_customer_number', $data[$columnName['customer_number']]);

			$posts = get_posts( array(
				'numberposts' => -1,
				'meta_key'    => 'priority_customer_number',
				'meta_value'  => $data[$columnName['customer_number']],
				'post_type'   => 'customers',
				'suppress_filters' => true,
			) );

			if ( count($posts) == 0 ){
				$users[$user_id]['error'][] = 'Priority customer number not exist';
				update_field('user_customer', '', 'user_' . $user_id);
			}
			if ( count($posts) == 1 ){
				update_field('user_customer', $posts[0]->ID, 'user_' . $user_id);
				$customer_id = $posts[0]->ID;
			}
			if ( count($posts) > 1 ){
                $users[$user_id]['error'][] = 'Too much(' . count($posts) . ') Customers with "Priority customer number" = ' . $data[$columnName['customer_number']];
			}

        } else {
			update_field('user_customer', '', 'user_' . $user_id);
		}

	}

	if ($customer_id) {

		if (isset($columnName['brunch_number'])) {
			if ($data[$columnName['brunch_number']]){

				$meta_query = array(
					'relation' => 'AND',
					array(
						'key'     => 'branch_customer',
						'value'   => $customer_id,
					),
					array(
						'key'     => 'branch_priority_number',
						'value'   => $data[$columnName['brunch_number']],
					),

				);
				$posts = get_posts( array(
					'numberposts' => -1,
					'meta_query'  => $meta_query,
					'post_type'   => 'branch',
					'suppress_filters' => true,
				) );

				if ( count($posts) == 0 ){
					$users[$user_id]['error'][] = 'Priority branch number not exist';
					update_field('user_branch', '', 'user_' . $user_id);
				}
				if ( count($posts) == 1 ){
					update_field('user_branch', $posts[0]->ID, 'user_' . $user_id);
				}
				if ( count($posts) > 1 ){
                    $users[$user_id]['error'][] = 'Too much(' . count($posts) . ') Brunches with "Priority brunch number" = ' . $data[$columnName['brunch_number']];
				}

			} else {
				update_field('user_branch', '', 'user_' . $user_id);
			}

		}

		if (isset($columnName['kit_number'])) {
			if ($data[$columnName['kit_number']]){

				$meta_query = array(
					'relation' => 'AND',
					array(
						'key'     => 'kit_customer',
						'value'   => $customer_id,
					),
					array(
						'key'     => 'kit_number',
						'value'   => $data[$columnName['kit_number']],
					),
				);

				$posts = get_posts( array(
					'numberposts' => -1,
					'meta_query'  => $meta_query,
					'post_type'   => 'kits',
					'suppress_filters' => true,
				) );

				if ( count($posts) == 0 ){
					$users[$user_id]['error'][] = 'Kit number not exist';
					update_field('user_kit', '', 'user_' . $user_id);
				}
				if ( count($posts) == 1 ){
					update_field('user_kit', $posts[0]->ID, 'user_' . $user_id);
				}
				if ( count($posts) > 1 ){
                    $users[$user_id]['error'][] = 'Too much(' . count($posts) . ') kits with "Priority kit number" = ' . $data[$columnName['kit_number']];
				}

			} else {
				update_field('user_kit', '', 'user_' . $user_id);
			}

		}

		if (isset($columnName['department'])) {

		    if ($data[$columnName['department']]){

				update_field('user_department', $data[$columnName['department']], 'user_' . $user_id);

			} else {
				update_field('user_department', '', 'user_' . $user_id);
			}

		}

	} else {
		update_field('user_branch', '', 'user_' . $user_id);
		update_field('user_kit', '', 'user_' . $user_id);
	}
	//Branch meta

	if (isset($columnName['branch_priority_number']))
		update_field('branch_priority_number', $data[$columnName['branch_priority_number']], $user_id);

	if (isset($columnName['branch_address']) || isset($columnName['branch_address2'])) {

		if ( $data[$columnName['branch_address']] && $data[$columnName['branch_address2']] ) {
		    $branch_address = $data[$columnName['branch_address']] . ' ' . $data[$columnName['branch_address2']];
	    } else {
		    $branch_address = $data[$columnName['branch_address']] . $data[$columnName['branch_address2']];
	    }

		update_field('branch_address', $branch_address, $user_id);
	}

	if (isset($columnName['contact_name']))
		update_field('contact_name', $data[$columnName['contact_name']], $user_id);

	if (isset($columnName['contact_phone_number']))
		update_field('contact_phone_number', $data[$columnName['contact_phone_number']], $user_id);

	//if (isset($columnName['customer_name'])) {
	//	if ($data[$columnName['customer_name']]){
    //		$post = get_page_by_title( $data[$columnName['customer_name']], '', 'customers' );
    //		if (isset($post)) {
    //			update_field('branch_customer', $post->ID, $user_id);
    //		} else {
    //			$users[$user_id]['error'][] = 'Customer name does not exist';
    //			update_field('user_kit', '', 'user_' . $user_id);
    //		}
    //	} else {
    //		update_field('user_kit', '', 'user_' . $user_id);
    //	}
    //}

	if (isset($columnName['priority_customer_number'])) {
		if ($data[$columnName['priority_customer_number']]){

			$posts = get_posts( array(
				'numberposts' => -1,
				'meta_key'    => 'priority_customer_number',
				'meta_value'  => $data[$columnName['priority_customer_number']],
				'post_type'   => 'customers',
				'suppress_filters' => true,
			) );

			if ( count($posts) == 0 ){
				$users[$user_id]['error'][] = 'Customer number not exist';
				update_field('branch_customer', '', $user_id);
			}
			if ( count($posts) == 1 ){
				update_field('branch_customer', $posts[0]->ID, $user_id);
			}
			if ( count($posts) > 1 ){
                $users[$user_id]['error'][] = 'Too much(' . count($posts) . ') Customers with "Priority customer number" = ' . $data[$columnName['priority_customer_number']];
			}

		} else {
			update_field('branch_customer', '', $user_id);
		}

	}
	return $users;
}


//save screen option "per page"
add_filter( 'set-screen-option', function( $status, $option, $value ){
	return ( $option == 'user_import_per_page' ) ? (int) $value : $status;
}, 10, 3 );
