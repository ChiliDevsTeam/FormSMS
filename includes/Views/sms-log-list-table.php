<?php
/**
 * SmsLogTable class
 *
 * Manage all SmsLogTable related functionality
 *
 * @package ChiliDevs\FormSMS
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * SmsLogTable class.
 */
class SmsLogTable extends WP_List_Table {

	/**
	 * Load automatically when class initiate
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'singular_form',
			'plural'   => 'plural_form',
			'ajax'     => true,
		) );

		$this->prepare_items();  
		
		echo "<div class='wrap'>";
		echo "<h1 class='wp-heading-inline'>" . esc_html__( 'Sms Log Lists', 'form-sms-pro' ) . '</h1>';
		echo "<form method='POST' action='" . $_SERVER['PHP_SELF'] . "?page=sms-log' >";
		$this->search_box( 'Search Log', 'search_log_id' );
		echo '</form>';
		$this->display();
		echo '</div>';
	}

	/**
	 * Prepare Data for Dispaly.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$orderby     = isset( $_GET['orderby'] ) ? trim( $_GET['orderby'] ) : '';
		$order       = isset( $_GET['order'] ) ? trim( $_GET['order'] ) : '';
		$search_term = isset( $_POST['s'] ) ? trim( $_POST['s'] ) : '';  

		$datas        = $this->wp_list_table_data( $orderby, $order, $search_term );
		$per_page     = 15;
		$current_page = $this->get_pagenum();
		$total_item   = count( $datas );
		
		$this->set_pagination_args(
			[ 'total_items' => $total_item ],
			[ 'per_page' => $per_page ]
		);

		$this->items = array_slice( $datas, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable ); 
		$this->process_bulk_action();
	}
	
	/**
	 * Initi gateway in settings.
	 *
	 * @param array $item Item Array.
	 *
	 * @return array
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}

	/**
	 * Set Table columns.
	 *
	 * @return $columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'         => "<input type='checkbox' />",
			'form_name'  => __( 'Form Name', 'form-sms-pro' ),
			'form_data'  => __( 'Form Data', 'form-sms-pro' ),
			'status'     => __( 'Status', 'form-sms-pro' ),
			'created_at' => __( 'Created At', 'form-sms-pro' ),
		);
		return $columns;
	}

	/**
	 * Retrive Data from Database.
	 *
	 * @param string $orderby orderbay form_name.
	 * @param string $order Dispaly Data ASC or DSC.
	 * @param string $search_term Search Data.
	 *
	 * @return $data
	 */
	public function wp_list_table_data( $orderby = '', $order = '', $search_term = '' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'form_sms_log' ;

		if ( ! empty( $search_term ) ) {        
			$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE 1=%d AND (form_name LIKE '%$search_term%' OR created_at LIKE '%$search_term%' )", 1 ), ARRAY_A );
		} else {
			if ( 'form_name' == $orderby && 'desc' == $order ) {
				$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE 1=%d ORDER BY form_name DESC", 1 ), ARRAY_A );
	
			} elseif ( 'created_at' == $orderby && 'desc' == $order ) {
				$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE 1=%d ORDER BY created_at DESC", 1 ), ARRAY_A ); 
	
			} else {
				$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE 1=%d ORDER BY form_name, created_at ASC", 1 ), ARRAY_A );     
			}
		} 

		return $data;			
	}

	/**
	 * Hide Columns
	 *
	 * @return array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Short Columns on ASC or DSC.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'form_name'  => [ 'form_name', false ],
			'created_at' => [ 'created_at', false ],
		);
	}

	/**
	 * Get Bulk Action.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => __( 'Delete', 'form-sms-pro' ),
		];
		return $actions;
	}

	/**
	 * Get Bulk Action.
	 */
	public function process_bulk_action() {

	}

	/**
	 * Initi gateway in settings.
	 *
	 * @param array $item Get Items.
	 * @param array $column_name Get Columns Name.
	 *
	 * @return array
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'form_name':
			case 'form_data':
				$form_data = maybe_unserialize( $item[ $column_name ] );
				$form_data = implode( ', ', $form_data );
				return $form_data;
			case 'status':
			case 'created_at':
				return $item[ $column_name ];
			default:
				return 'No data';    

		}
	}

	/**
	 * Delete option on specific colums.
	 *
	 * @param array $item Get Items.
	 *
	 * @return array
	 */
	public function column_form_name( $item ) {
		$actions = array(
			'delete' => sprintf( '<a href="?page=%s&action=%s&log_id=%s" class="submitdelete" aria-label=""> Delete <a/>', $_GET['page'], 'log-delete', $item['id'] ),
			
		);
		
		return sprintf( '%1$s %2$s', $item['form_name'], $this->row_actions( $actions ) );
	}

}

function show_sms_log_list_table() {
	$smslog_table = new SmsLogTable();  
}

show_sms_log_list_table();
