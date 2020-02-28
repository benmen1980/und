<?php

class Unidress_User_Report extends WP_List_Table {
    protected $users;
    protected $users_array = array();

    function __construct(){
        parent::__construct(array(
            'singular' => 'report',
            'plural'   => 'reports',
            'ajax'     => false,
        ));

        $this->bulk_action_handler();

        add_screen_option( 'per_page', array(
            'default' => 10,
            'option'  => 'user_report_per_page',
        ) );
        $this->get_users();

        $this->prepare_items();

    }

    function prepare_items(){
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();

        if (isset($_GET['export_users'])) {
            require_once(WP_PLUGIN_DIR . '/unidress/admin/include/PhpSpreadsheet/Psr/autoloader.php');
            require_once(WP_PLUGIN_DIR . '/unidress/admin/include/PhpSpreadsheet/autoloader.php');
            $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet;
            //header
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', esc_attr__( 'Username', 'unidress' ));
            $sheet->setCellValue('B1', esc_attr__( 'First Name and Last Name', 'unidress' ));
            $sheet->setCellValue('C1', esc_attr__( 'Customer', 'unidress' ));
            $sheet->setCellValue('D1', esc_attr__( 'Branch', 'unidress' ));
            $sheet->setCellValue('E1', esc_attr__( 'Department', 'unidress' ));
            $sheet->setCellValue('F1', esc_attr__( 'Kit', 'unidress' ));
            $sheet->setCellValue('G1', esc_attr__( 'Campaign', 'unidress' ));
            $sheet->setCellValue('H1', esc_attr__( 'Project', 'unidress' ));
            $sheet->setCellValue('I1', esc_attr__( 'Budget', 'unidress' ));
            $sheet->setCellValue('J1', esc_attr__( 'Budget utilization', 'unidress' ));
            $sheet->setCellValue('K1', esc_attr__( 'Budget remaining', 'unidress' ));

            $sheet->setCellValue('L1', esc_attr__( 'Groups assigning', 'unidress' ));
            $sheet->setCellValue('L2', esc_attr__( 'Group', 'unidress' ));
            $sheet->setCellValue('M2', esc_attr__( 'Limit', 'unidress' ));
            $sheet->setCellValue('N2', esc_attr__( 'Utilization', 'unidress' ));
            $sheet->setCellValue('O2', esc_attr__( 'Remaining', 'unidress' ));

            $sheet->mergeCells('A1:A2');
            $sheet->mergeCells('B1:B2');
            $sheet->mergeCells('C1:C2');
            $sheet->mergeCells('D1:D2');
            $sheet->mergeCells('E1:E2');
            $sheet->mergeCells('F1:F2');
            $sheet->mergeCells('G1:G2');
            $sheet->mergeCells('H1:H2');
            $sheet->mergeCells('I1:I2');
            $sheet->mergeCells('J1:J2');
            $sheet->mergeCells('K1:N1');
            $sheet->mergeCells('L1:L2');

            $sheet->getStyle('A1:O2')
                  ->getAlignment()-> setHorizontal(PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:O2')
                  ->getAlignment()-> setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $users = $this->get_user_data_array();

            $i = 3;
            $borderStyleArray = array(
                'borders' => array(
                    'outline' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'horizontal' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'vertical' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            );

            if (is_array($users))
                foreach ($users as $user ) {

                    if (count($user['groups']) > 0 ) {
                        $i--;

                        foreach ($user['groups'] as $group_value ) {
                            $i++;
                            $sheet->setCellValue('A'.$i, html_entity_decode($user['login']              ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('B'.$i, html_entity_decode($user['login']              ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('C'.$i, html_entity_decode($user['customer']           ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('D'.$i, html_entity_decode($user['branch']             ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('E'.$i, html_entity_decode($user['department']         ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('F'.$i, html_entity_decode($user['kit']                ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('G'.$i, html_entity_decode($user['campaign']           ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('H'.$i, html_entity_decode($user['project']            ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('I'.$i, html_entity_decode($user['budget']             ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('J'.$i, html_entity_decode($user['budget_left']        ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('K'.$i, html_entity_decode($user['budget_remaining']   ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('L'.$i, html_entity_decode($group_value['group']       ,ENT_QUOTES,'UTF-8'));

                            $sheet->setCellValue('M'.$i, html_entity_decode($group_value['limit']       ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('N'.$i, html_entity_decode($group_value['utilization'] ,ENT_QUOTES,'UTF-8'));
                            $sheet->setCellValue('O'.$i, html_entity_decode($group_value['remaining']   ,ENT_QUOTES,'UTF-8'));

                        }
                    } else  {

                        $sheet->setCellValue('A'.$i, html_entity_decode($user['login']            ,ENT_QUOTES,'UTF-8'));
                        $sheet->setCellValue('B'.$i, html_entity_decode($user['login']            ,ENT_QUOTES,'UTF-8'));
                        $sheet->setCellValue('C'.$i, html_entity_decode($user['customer']         ,ENT_QUOTES,'UTF-8'));
                        $sheet->setCellValue('D'.$i, html_entity_decode($user['branch']           ,ENT_QUOTES,'UTF-8'));
                        $sheet->setCellValue('E'.$i, html_entity_decode($user['department']       ,ENT_QUOTES,'UTF-8'));
                        $sheet->setCellValue('F'.$i, html_entity_decode($user['kit']              ,ENT_QUOTES,'UTF-8'));
                        $sheet->setCellValue('G'.$i, html_entity_decode($user['campaign']         ,ENT_QUOTES,'UTF-8'));
                        $sheet->setCellValue('H'.$i, html_entity_decode($user['project']          ,ENT_QUOTES,'UTF-8'));
                        $sheet->setCellValue('I'.$i, html_entity_decode($user['budget']           ,ENT_QUOTES,'UTF-8'));
                        $sheet->setCellValue('J'.$i, html_entity_decode($user['budget_left']      ,ENT_QUOTES,'UTF-8'));
                        $sheet->setCellValue('K'.$i, html_entity_decode($user['budget_remaining'] ,ENT_QUOTES,'UTF-8'));
                    }

                    $i++;
                }

            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('J')->setAutoSize(true);
            $sheet->getColumnDimension('K')->setAutoSize(true);
            $sheet->getColumnDimension('L')->setAutoSize(true);
            $sheet->getColumnDimension('M')->setAutoSize(true);
            $sheet->getColumnDimension('N')->setAutoSize(true);
            $sheet->getColumnDimension('O')->setAutoSize(true);

            $sheet->getStyle('A1:O' . $i)->applyFromArray($borderStyleArray);

            $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            $dir = wp_get_upload_dir();
            $inputFileName = $dir['basedir'] . '/temp/unidress_export_report.xlsx';

            $writer->save($inputFileName);

            if(file_exists($inputFileName)){
                //redirect to xlsx url
            } else {
                echo 'File not exist';
            }

        }

        //sorter
        if (isset($_GET['order']))
            uasort($data, array($this, 'uni_sort_' . $_GET['order']));

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

    function uni_sort_asc($a, $b){
        $column = $_GET['orderby'];
        return ($a->$column > $b->$column);
    }
    function uni_sort_desc($a, $b){
        $column = $_GET['orderby'];
        return ($a->$column <= $b->$column);
    }
    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        $data = array();

        $users = $this->users;

        foreach ($users as $index => $user) {

            $user_data = $this->get_user_report_data($user);

            $data[] = (object) array(
                //'cb'                    =>  $index,
                'user_id'               =>  $user_data['user_id']         ,
                'username'              =>  $user_data['login']           ,
                'user_fio'              =>  $user_data['first_name'].' '.
                                            $user_data['last_name']       ,
                'customer'              =>  $user_data['customer']        ,
                'branch'                =>  $user_data['branch']          ,
                'department'            =>  $user_data['department']      ,
                'kit'                   =>  $user_data['kit']             ,
                'campaign'              =>  $user_data['campaign']        ,
                'project'               =>  $user_data['project']         ,
                'budget'                =>  $user_data['budget']          ,
                'budget_utilization'    =>  $user_data['budget_left']     ,
                'budget_remaining'      =>  $user_data['budget_remaining'],
                'groups'                =>  $user_data['groups']          ,
            );

        }

        return $data;
    }

    function get_columns(){
        return array(
          //'cb'                    => '<input type="checkbox" />',
            'username'              => esc_attr__( 'Username', 'unidress' )  ,
            'user_fio'              => esc_attr__( 'First Name and Last Name', 'unidress' )  ,
            'customer'              => esc_attr__( 'Customer', 'unidress' )  ,
            'branch'                => esc_attr__( 'Branch', 'unidress' )    ,
            'department'            => esc_attr__( 'Department', 'unidress' ),
            'kit'                   => esc_attr__( 'Kit', 'unidress' ),
            'campaign'              => esc_attr__( 'Campaign', 'unidress' ),
            'project'               => esc_attr__( 'Project', 'unidress' ),
            'budget'                => esc_attr__( 'Budget', 'unidress' ),
            'budget_utilization'    => esc_attr__( 'Budget utilization', 'unidress' ),
            'budget_remaining'      => esc_attr__( 'Budget remaining', 'unidress' ),
            'groups'                => esc_attr__( 'Groups assigning', 'unidress' ),
        );
    }

    function get_user_data_array () {

        $output = array();
        $users = $this->users;

        $users_data = $this->users_array;

        foreach ($users as $user) {
            $customer_id        = $users_data[$user->ID]['customers']['id'];
            $branch_id          = $users_data[$user->ID]['branch']['id'];
            $kit_id             = $users_data[$user->ID]['kits']['id'];
            $department         = get_user_meta($user->ID, 'user_department', true);

            $customer           = get_the_title($customer_id);
            $branch             = get_the_title($branch_id);
            $kit                = get_the_title($kit_id);

            $customer_type      = get_post_meta($customer_id, 'customer_type', true);

            $output[$user->ID]['groups'] = array();

            if ($customer_type == 'campaign') {
                $campaign_id                = isset($users_data[$user->ID]['customers']['campaign']) ? $users_data[$user->ID]['customers']['campaign'] : '';
                $campaign                   = get_the_title($campaign_id);

                $budgets_in_campaign        = get_post_meta($campaign_id, 'budget', true);
                $budget_in_kit              = isset($budgets_in_campaign[$kit_id]) ? $budgets_in_campaign[$kit_id] : 0;

                $user_budget_left           = get_user_meta($user->ID, 'user_budget_limits', true);
                $user_budget_left_of_kit    = isset($user_budget_left[$campaign_id][$kit_id]) ? $user_budget_left[$campaign_id][$kit_id] : 0;
                $user_budget_remaining      = (int)$budget_in_kit - (int)$user_budget_left_of_kit;

                $groups_in_campaign         = get_post_meta($campaign_id, 'groups', true);
                $user_limits                = get_user_meta($user->ID, 'user_limits', true);

                $groups_in_kit              = isset($groups_in_campaign[$kit_id]) ? $groups_in_campaign[$kit_id] : '';

                if (is_array($groups_in_kit)){
                    foreach ($groups_in_kit as $group_ID=>$group) {
                        $output[$user->ID]['groups'][$group_ID]['group'] = $group['name'];
                        $output[$user->ID]['groups'][$group_ID]['limit'] = $group['amount'];
                        $output[$user->ID]['groups'][$group_ID]['utilization']= isset($user_limits[$group_ID]) ? (int)$user_limits[$group_ID] : 0;
                        $output[$user->ID]['groups'][$group_ID]['remaining']= $group['amount'] - (isset($user_limits[$group_ID]) ? (int)$user_limits[$group_ID] : 0);
                    }
                }

            } else {
                $campaign                    = '';
                $budget_in_kit               = '';
                $user_budget_left_of_kit     = '';
                $user_budget_remaining       = '';
            }
            if ($customer_type == 'project') {
                $project_id         = isset($users_data[$user->ID]['customers']['project'])  ? $users_data[$user->ID]['customers']['project'] : '';
                $project = get_the_title($project_id);
            } else {
                $project = '';
            }

            $output[$user->ID]['login']                = $user->data->user_login;
            $output[$user->ID]['user_fio']             = $user->data->user_login;
            $output[$user->ID]['customer']             = $customer;
            $output[$user->ID]['branch']               = $branch;
            $output[$user->ID]['department']           = $department;
            $output[$user->ID]['kit']                  = $kit;
            $output[$user->ID]['campaign']             = $campaign;
            $output[$user->ID]['project']              = $project;
            $output[$user->ID]['budget']               = $budget_in_kit;
            $output[$user->ID]['budget_left']          = $user_budget_left_of_kit;
            $output[$user->ID]['budget_remaining']     = $user_budget_remaining;

        }


        return $output;
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
            'username'      => array( 'username', 'desc' ),
            'user_fio'      => array( 'user_fio', 'desc' ),
            'customer'      => array( 'customer', 'desc' ),
            'branch'        => array( 'branch', 'desc' ),
            'department'    => array( 'department', 'desc' ),
            'kit'           => array( 'kit', 'desc' ),

            'campaign'      => array( 'campaign', 'desc' ),
            'project'       => array( 'project', 'desc' ),
            //'username' => array( 'username', 'desc' ),
        );
    }

    //protected function get_bulk_actions() {
    //  return array(
    //      //'delete' => 'Delete',
    //  );
    //}

    function extra_tablenav( $which ){
        if( $which != 'top' ) return;

        echo '<div class="alignleft filter-users-report">';

        echo $this->unidress_dropdown_filter_users('customers', 'user_customer');
        echo $this->unidress_dropdown_filter_users('branch', 'user_branch');
        echo $this->unidress_dropdown_filter_users('kits', 'user_kit');
        echo $this->unidress_dropdown_filter_posts_report2('customers', 'campaign', 'active_campaign');
        echo $this->unidress_dropdown_filter_posts_report2('customers', 'project', 'active_campaign');
        echo $this->unidress_dropdown_filter_by_user_meta('department','user_department');

        echo '<input type="hidden" name="page" id="post-query-submit" class="button" value="user_report_table_page">';

        echo '<input type="submit" name="filter_action" id="post-query-submit" class="button" value="' .  __('Filter', 'unidress' ) . '">';
        echo '<input type="submit" name="export_users" id="post-query-submit2" class="button" value="' .  __('Export', 'unidress' ) . '">';
        echo '<a href="' . site_url() . '/wp-admin/users.php?page=user_report_table_page">' .  __('Reset', 'unidress' ) . '</a>';
        echo '</div>';
    }

    function get_users() {
        $array_filter = array();
        if (isset($_GET['customers']) ) {

            if ($_GET['customers'] > 0) {
                $array_filter[] = array(
                    "key"       =>  "user_customer",
                    "value"     =>  intval($_GET['customers']),
                    "compare"   =>  "="
                );
            }

            if ($_GET['customers'] == 0) {
                $array_filter[] = array(
                    "key"       =>  "user_customer",
                    "compare"   =>  "NOT EXISTS"
                );
            }

        }

        if (isset($_GET['branch']) ) {

            if ($_GET['branch'] > 0) {
                $array_filter[] = array(
                    "key"       =>  "user_branch",
                    "value"     =>  intval($_GET['branch']),
                    "compare"   =>  "="
                );
            }

            if ($_GET['branch'] == 0) {
                $array_filter[] = array(
                    "key"       =>  "user_branch",
                    "compare"   =>  "NOT EXISTS"
                );
            }

        }

        if (isset($_GET['kits']) ) {

            if ($_GET['kits'] > 0) {
                $array_filter[] = array(
                    "key"       =>  "user_kit",
                    "value"     =>  intval($_GET['kits']),
                    "compare"   =>  "="
                );
            }

            if ($_GET['kits'] == 0) {
                $array_filter[] = array(
                    "key"       =>  "user_kit",
                    "compare"   =>  "NOT EXISTS"
                );
            }

        }

        if (isset($_GET['user_department']) ) {

            if ($_GET['user_department'] == 0 ) {

                $array_filter[] = array(
                    "key"       =>  "user_department",
                    "compare"   =>  "NOT EXISTS"
                );

            }
            if ($_GET['user_department'] > 0) {
                $value = get_user_meta($_GET['user_department'], 'user_department' ,true);
                $array_filter[] = array(
                    "key"       =>  "user_department",
                    "value"     =>  $value,
                    "compare"   =>  "="
                );

            }

        }

        if (isset($_GET['campaign']) ) {

            if ($_GET['campaign'] > 0) {
                $meta_query[] = array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'active_campaign',
                        'value'   => $_GET['campaign'],
                    ),
                );

                $args = array(
                    'meta_query'        => $meta_query,
                    'post_type'         => 'customers',
                    'suppress_filters'  => true,
                );

                $customers_array = array();
                $customers = get_posts($args);
                foreach ($customers as $customer) {
                    $customers_array[] = $customer->ID;
                }

                $array_filter[] = array(
                    "key"       =>  "user_customer",
                    "value"     =>  $customers_array,
                    "compare"   =>  "IN"
                );

            }

        }

        if (isset($_GET['project']) ) {

            if ($_GET['project'] > 0) {
                $meta_query[] = array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'active_campaign',
                        'value'   => $_GET['project'],
                    ),
                );

                $args = array(
                    'meta_query'        => $meta_query,
                    'post_type'         => 'customers',
                    'suppress_filters'  => true,
                );
                $customers_array = array();
                $customers = get_posts($args);
                foreach ($customers as $customer) {
                    $customers_array[] = $customer->ID;
                }
                $array_filter[] = array(
                    "key"       =>  "user_customer",
                    "value"     =>  $customers_array,
                    "compare"   =>  "IN"
                );

            }

        }

        $args = array(
                'orderby' => 'ID',
                'order' => 'ASC',
                'meta_query'      => $array_filter,
        );

        $this->users = get_users($args);

        foreach ($this->users as $user) {

            $this->users_array[$user->ID]['customers']['id']    = get_user_meta($user->ID, 'user_customer', true);
            $this->users_array[$user->ID]['branch']['id']       = get_user_meta($user->ID, 'user_branch', true);
            $this->users_array[$user->ID]['kits']['id']         = get_user_meta($user->ID, 'user_kit', true);
            $this->users_array[$user->ID]['department']['id']   = get_user_meta($user->ID, 'user_department', true);

            $customer = $this->users_array[$user->ID]['customers']['id'];

            if ($customer) {

                $customer_type = get_post_meta($customer, 'customer_type', true);
                $active_campaign = get_post_meta($customer, 'active_campaign', true);

                if ($customer_type == "campaign") {
                    $this->users_array[$user->ID]['customers']['campaign']  = $active_campaign;
                }

                if ($customer_type == "project") {
                    $this->users_array[$user->ID]['customers']['project']  = $active_campaign;
                }

            }

        }

    }

    function unidress_dropdown_filter_by_user_meta( $meta_key_name = '', $meta_key = false) {
        $empty_posts = false;
        $array_filter = array();

        $users_array = $this->users_array;
        foreach ($users_array as $user_id => $user_meta) {

            $user_meta = get_user_meta($user_id, $meta_key, true);

            if ($user_meta) {
                $array_filter[$user_id] = $user_meta;
            } else {
                $empty_posts = true;
            }

        }
        $array_filter = array_unique($array_filter);

        $output  = '<select class="select2-transform" name="' . $meta_key . '">';
        $output .= '<option value="-1"> - ' . esc_attr__( $meta_key_name , 'unidress' ) . ' - </option>';


        if ($empty_posts) {
            $output .= '<option value="0"'. selected(0, @ $_GET[$meta_key], 0) .'> - ' . esc_attr__( 'empty' , 'unidress' ) . ' - </option>';
        }

        foreach( $array_filter as $user_id => $meta_value ){
            $output .= '<option value="' . $user_id . '" '. selected($user_id, @ $_GET[$meta_key], 0) .'>' . $meta_value . '</option>';
        }

        $output .= '</select>';

        return $output;

    }
    function unidress_dropdown_filter_posts_report2( $post_type, $filter_post_type = false, $meta_key = false) {

        $empty_posts = false;
        $array_filter = array();

        $users_array = $this->users_array;
        foreach ($users_array as $user_id => $user_meta) {
            if (isset($user_meta['customers'][$filter_post_type]) && $user_meta['customers'][$filter_post_type] ) {
                $array_filter[] = $user_meta['customers'][$filter_post_type];
            } else {
               // $empty_posts = true;
            }
        }

        $array_filter = array_unique($array_filter);

        $output  = '<select class="select2-transform" name="' . $filter_post_type . '">';
        $output .= '<option value="-1"> - ' . esc_attr__( $filter_post_type , 'unidress' ) . ' - </option>';

        if ($empty_posts) {
            $output .= '<option value="0"'. selected(0, @ $_GET[$filter_post_type], 0) .'> - ' . esc_attr__( 'empty' , 'unidress' ) . ' - </option>';
        }

        foreach( $array_filter as $filter_id ){
            setup_postdata($filter_id);
            $output .= '<option value="' . $filter_id . '" '. selected($filter_id, @ $_GET[$filter_post_type], 0) .'>' . get_the_title($filter_id) . '</option>';
        }

        $output .= '</select>';

        wp_reset_postdata();

        return $output;

    }
    function unidress_dropdown_filter_posts_report( $post_type, $filter_post_type = false, $meta_key = false) {
        $array_active_campaigns = array();
        $empty_posts = false;

        $customer_type = 'customers_' . $filter_post_type;

            $args = array(
            'numberposts'       => -1,
            'include'           => $this->$customer_type,
            'suppress_filters'  => true,
        );
        $customers = get_posts($args);
        foreach( $customers as $customer ){
            $active_campaign = get_post_meta($customer->ID, $meta_key, true);

            if ($active_campaign) {
                $array_active_campaigns[$customer->ID] = $active_campaign;
            } else {
                $empty_posts = true;
            }

        }

        $output  = '<select class="select2-transform" name="' . $filter_post_type . '">';
        $output .= '<option value="-1"> - ' . esc_attr__( $filter_post_type , 'unidress' ) . ' - </option>';

        if ($empty_posts) {
            $output .= '<option value="0"'. selected(0, @ $_GET[$filter_post_type], 0) .'> - ' . esc_attr__( 'empty' , 'unidress' ) . ' - </option>';
        }

        foreach( $array_active_campaigns as $active_campaign_id ){
            setup_postdata($active_campaign_id);
            $output .= '<option value="' . $active_campaign_id . '" '. selected($active_campaign_id, @ $_GET[$filter_post_type], 0) .'>' . get_the_title($active_campaign_id) . '</option>';
        }

        $output .= '</select>';

        wp_reset_postdata();

        return $output;

    }

    function unidress_dropdown_filter_users( $filter_post_type = false, $meta_key = false) {
        $array_search = array();
        $empty_posts = false;

        if ($meta_key) {

            $users = $this->users;

            foreach( $users as $user ){
                $meta_value = get_user_meta($user->ID, $meta_key, true);
                if ($meta_value) {
                    $array_search[] = $meta_value;
                } else {
                    $empty_posts = true;
                }
            }
        }

        $output  = '<select class="select2-transform" name="' . $filter_post_type . '">';
        $output .= '<option value="-1"> - ' . esc_attr__( $filter_post_type , 'unidress' ) . ' - </option>';

        if ($empty_posts) {
            $output .= '<option value="0"'. selected(0, @ $_GET[$filter_post_type], 0) .'> - ' . esc_attr__( 'empty' , 'unidress' ) . ' - </option>';
        }

        if ($array_search) {

            $posts = get_posts( array(
                'numberposts' => -1,
                'orderby'     => 'title',
                'order'       => 'ASC',
                'post_type'   => $filter_post_type,
                'include'     => $array_search,
                'suppress_filters' => true,
            ) );

            foreach( $posts as $post ){
                setup_postdata($post);
                $output .= '<option value="' . $post->ID . '" '. selected($post->ID, @ $_GET[$filter_post_type], 0) .'>' . $post->post_title . '</option>';
            }
        }

        $output .= '</select>';

        wp_reset_postdata();

        return $output;

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

        $users_data = $this->users_array;
        $customer_id        = $users_data[$user->ID]['customers']['id'];
        $branch_id          = $users_data[$user->ID]['branch']['id'];
        $kit_id             = $users_data[$user->ID]['kits']['id'];
        $department         = get_user_meta($user->ID, 'user_department', true);

        $customer           = get_the_title($customer_id);
        $branch             = get_the_title($branch_id);
        $kit                = get_the_title($kit_id);

        $customer_type      = get_post_meta($customer_id, 'customer_type', true);

        if ($customer_type == 'campaign') {
            $campaign_id                = isset($users_data[$user->ID]['customers']['campaign']) ? $users_data[$user->ID]['customers']['campaign'] : '';
            $campaign                   = get_the_title($campaign_id);

            $budgets_in_campaign        = get_post_meta($campaign_id, 'budget', true);
            $budget_in_kit              = isset($budgets_in_campaign[$kit_id]) ? $budgets_in_campaign[$kit_id] : 0;

            $user_budget_left           = get_user_meta($user->ID, 'user_budget_limits', true);
            $user_budget_left_of_kit    = isset($user_budget_left[$campaign_id][$kit_id]) ? $user_budget_left[$campaign_id][$kit_id] : 0;
            $user_budget_remaining      = (int)$budget_in_kit - (int)$user_budget_left_of_kit;

        } else {
            $campaign                           = '';
            $budget_in_kit                      = '';
            $user_budget_left_of_kit            = '';
            $user_budget_remaining              = '';
        }
        if ($customer_type == 'project') {
            $project_id         = isset($users_data[$user->ID]['customers']['project'])  ? $users_data[$user->ID]['customers']['project'] : '';
            $project            = get_the_title($project_id);
        } else {
            $project            = '';
        }

        $output = array();
        $output['user_id']              = $user->ID;
        $output['login']                = $user->data->user_login;
        $output['first_name']           = $user->first_name;
        $output['last_name']            = $user->last_name;
        $output['customer']             = $customer;
        $output['branch']               = $branch;
        $output['department']           = $department;
        $output['kit']                  = $kit;
        $output['campaign']             = $campaign;
        $output['project']              = $project;
        $output['budget']               = $budget_in_kit;
        $output['budget_left']          = $user_budget_left_of_kit;
        $output['budget_remaining']     = $user_budget_remaining;
        $output['groups']               = $this->get_user_groups($user->ID);

        return $output;
    }

    //
    function get_user_name($user) {
        return $user->data->user_login;
    }
    function get_user_customer_id($user_id) {
        $customer_id = get_user_meta($user_id, 'user_customer', true);
        return $customer_id;
    }
    function get_user_active_campaign_id($user_id) {
        $customer_id = $this->get_user_customer_id($user_id);
        $active_campaign = get_post_meta($customer_id, 'active_campaign', true);
        return $active_campaign;
    }
    function get_user_kit_id($user_id) {
        $kit_id = get_user_meta($user_id, 'user_kit', true);
        return $kit_id;
    }
    function get_user_customer($user_id) {
        $customer = get_the_title($this->get_user_customer_id($user_id));
        return $customer;
    }
    function get_user_brunch($user_id) {
        $branch = get_the_title(get_user_meta($user_id, 'user_branch', true));
        return $branch;
    }
    function get_user_kit($user_id) {
        $kit = get_the_title($this->get_user_kit_id($user_id));
        return $kit;
    }
    function get_user_campaign($user_id) {
        $customer_type = get_post_meta($this->get_user_customer_id($user_id), 'customer_type', true);
        if ($customer_type == 'campaign') {
            $active_campaign = get_the_title($this->get_user_active_campaign_id($user_id));
        } else {
            $active_campaign = '';
        }
        return $active_campaign;
    }
    function get_user_project($user_id) {
        $customer_type = get_post_meta($this->get_user_customer_id($user_id), 'customer_type', true);
        if ($customer_type == 'project') {
            $active_campaign = get_the_title($this->get_user_active_campaign_id($user_id));
        } else {
            $active_campaign = '';
        }
        return $active_campaign;
    }
    function get_user_budget($user_id) {
        $customer_type = get_post_meta($this->get_user_customer_id($user_id), 'customer_type', true);
        if ($customer_type != 'campaign')
            return;

        $kit_id          = $this->get_user_kit_id($user_id);
        $active_campaign        = $this->get_user_active_campaign_id($user_id);
        $budgets_in_campaign    = get_post_meta($active_campaign, 'budget', true);
        $budget                 = $budgets_in_campaign[$kit_id] ?: 0;
        return $budget;
    }
    function get_user_budget_left($user_id) {
        $customer_type = get_post_meta($this->get_user_customer_id($user_id), 'customer_type', true);
        if ($customer_type != 'campaign')
            return;

        $campaign_id = $this->get_user_active_campaign_id($user_id);
        $user_budget_left = get_user_meta($user_id, 'user_budget_limits', true);
        $user_budget_left_of_kit = isset($user_budget_left[$campaign_id]) ? $user_budget_left[$campaign_id] : 0;
        return $user_budget_left_of_kit;
    }
    function get_user_budget_remaining ($user_id) {
        $customer_type = get_post_meta($this->get_user_customer_id($user_id), 'customer_type', true);
        if ($customer_type != 'campaign')
            return;

        $user_budget_remaining = $this->get_user_budget($user_id) - $this->get_user_budget_left($user_id);
        return $user_budget_remaining;
    }
    function get_user_groups ($user_id) {
        $kit_id          = $this->get_user_kit_id($user_id);
        $campaign_id            = $this->get_user_active_campaign_id($user_id);
        $groups_in_campaign     = get_post_meta($campaign_id, 'groups', true);
        $user_limits            = get_user_meta($user_id, 'user_limits', true);

        $groups_in_kit   = isset($groups_in_campaign[$kit_id]) ? $groups_in_campaign[$kit_id] : '';

        $output = '';

        if (is_array($groups_in_kit)){
            $output .= '<table class="wp-list-table widefat striped">';
            $output .=      '<thead>';
            $output .=      '<tr>';
            $output .=          '<th>'. esc_attr__( 'Group', 'unidress' ) . '</th>';
            $output .=          '<th>'. esc_attr__( 'Limit', 'unidress' ) . '</th>';
            $output .=          '<th>'. esc_attr__( 'Utilization', 'unidress' ) . '</th>';
            $output .=          '<th>'. esc_attr__( 'Remaining', 'unidress' ) . '</th>';
            $output .=      '</tr>';
            $output .=      '</thead>';
            $output .=      '<tbody>';
            foreach ($groups_in_kit as $group_ID=>$group) {
                $output .=      '<tr>';
                $output .=          '<td>' . $group['name'] . ':</td>';
                $output .=          '<td class="center">' . $group['amount'] . '</td>';
                $output .=          '<td class="center">' . (isset($user_limits[$group_ID]) ? (int)$user_limits[$group_ID] : 0) . '</td>';
                $output .=          '<td class="center">' . ($group['amount'] - (isset($user_limits[$group_ID]) ? (int)$user_limits[$group_ID] : 0)) . '</td>';
                $output .=      '</tr>';
            }
            $output .= '</tbody>';
            $output .= '</table>';
        }
        return $output;

    }

    protected function display_tablenav( $which ) {
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">

            <?php

            $this->extra_tablenav( $which );
            $this->pagination( $which );
            ?>

            <br class="clear" />
        </div>
        <?php
    }
}