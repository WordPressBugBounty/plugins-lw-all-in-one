<?php
/**
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin/partials
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

add_action( 'lw_all_in_one_ga_events_deleted_message', 'lw_all_in_one_ga_events_deleted_message' );
function lw_all_in_one_ga_events_deleted_message() {
  $status = (isset($_REQUEST['status'])) ? sanitize_text_field($_REQUEST['status']) : '';
  $message = (isset($_REQUEST['message'])) ? sanitize_text_field($_REQUEST['message']) : '';

	if ( '' !== $status && '' !== $message ) {
		echo sprintf( '<div id="message" class="notice notice-%s is-dismissible inline"><p>%s</p></div>', esc_attr( $status ), esc_attr( $message ) );
	}
}

class Lw_All_In_One_Ga_Events_List_Table {
  public function __construct() {
    $this->lw_all_in_one_ga_events_list_table();
  }

  public function lw_all_in_one_ga_events_list_table() {

    $ListTableClassGa = new Lw_All_In_One_Ga_Events_List_Table_Class();
    $ListTableClassGa->prepare_items();

    $s = (isset($_REQUEST['s'])) ? sanitize_text_field($_REQUEST['s']) : '';
    $page = sanitize_text_field($_REQUEST['page']);
    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">' . esc_html(get_admin_page_title()) . '</h1>';

    do_action( 'lw_all_in_one_ga_events_deleted_message' );
    echo '<hr class="wp-header-end">';
    if ( ! empty( $s ) ) {
      echo sprintf( '<span class="subtitle">'
        . __( 'Search results for &#8220;%s&#8221;', 'lw_all_in_one' )
        . '</span>', esc_html( $s ) );
    }

    echo '<form method="POST" action="" id="lw_aio_saved_ga_events_records">';
    wp_nonce_field( 'bulk_delete_records_ga', 'bulk_delete_nonce_ga' );
    echo '<input type="hidden" name="page" value="' . esc_attr( $page ) . '" />';

    $ListTableClassGa->search_box( __( 'Search Records', 'lw_all_in_one' ), 'lw_all_in_one' . '-s-ga_events-record' );

    $ListTableClassGa->display();
    echo '</form>';
    echo '</div>';
  }
}

if (!class_exists('WP_List_Table')) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Lw_All_In_One_Ga_Events_List_Table_Class extends WP_List_Table {

  public function prepare_items() {
    $columns = $this->get_columns();
    $hidden = $this->get_hidden_columns();
    $sortable = $this->get_sortable_columns();

    $this->process_bulk_action();

    $data = $this->table_data();
    usort($data, array(&$this, 'sort_data'));

    $perPage = 10;
    $current_screen = get_current_screen();
    // wp_die($current_screen->id);
		$perPage = $this->get_items_per_page( 'records_per_page' );
    $currentPage = $this->get_pagenum();
    $totalItems = count($data);

    $this->set_pagination_args(array(
      'total_items' => $totalItems,
      'per_page' => $perPage,
    ));

    $data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);

    $this->_column_headers = array($columns, $hidden, $sortable);

    $this->items = $data;
  }

  public function column_ga_category($item) {
    $delete_url = wp_nonce_url( '?page='.sanitize_text_field($_REQUEST['page']).'&action=delete-ga&record_id='.absint($item['id']).'', 'delete' );
    $actions = array(
      'delete' => sprintf('<a href="%s" id="lw_aio_delete_record">Delete</a>', $delete_url),
    );

    return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
      /*$1%s*/$item['ga_category'],
      /*$2%s*/$item['id'],
      /*$3%s*/$this->row_actions($actions)
    );
  }

  public function column_cb($item) {
    return sprintf(
      '<input type="checkbox" name="bulk-delete-ga[]" value="%1$s" />',$item['id']
    );
  }

  public function get_columns() {
    $columns = [
      'cb' => '<input type="checkbox" />',
      'ga_category' => esc_attr__('Category', 'lw_all_in_one'),
      'ga_action' => esc_attr__('Action', 'lw_all_in_one'),
      'ga_label' => esc_attr__('Label', 'lw_all_in_one'),
      'time' => esc_attr__('Date', 'lw_all_in_one'),
    ];
    return $columns;
  }

  public function get_hidden_columns() {
    return array();
  }

  public function get_sortable_columns() {
    $sortable_columns = array(
      'ga_category' => array('ga_category', false),
      'ga_action' => array('ga_action', false),
      'ga_label' => array('ga_label', false),
      'time' => array('time', false),
    );
    return $sortable_columns;
  }

  private function table_data() {
    $data = array();

    global $wpdb;
    $table_name = $wpdb->prefix . LW_ALL_IN_ONE_A_EVENTS_TABLE;
    $s = (isset($_REQUEST['s'])) ? sanitize_text_field($_REQUEST['s']) : '';
    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE ga_category LIKE %s OR ga_action LIKE %s OR ga_label LIKE %s", '%'.$s.'%', '%'.$s.'%', '%'.$s.'%');
    $data = $wpdb->get_results($query, ARRAY_A);

    return $data;
  }

  public function column_default($item, $column_name) {
    switch ($column_name) {
    case 'ga_category':
    case 'ga_action':
    case 'ga_label':
    case 'time':
      return $item[$column_name];

    default:
      return print_r($item, true);
    }
  }

  private function sort_data($a, $b) {

    $orderby = 'id';
    $order = 'asc';

    if (!empty($_GET['orderby'])) {
      $orderby = absint(sanitize_text_field($_GET['orderby']));
    }

    if (!empty($_GET['order'])) {
      $order = sanitize_text_field($_GET['order']);
    }

    $result = strcmp($a[$orderby], $b[$orderby]);

    if ($order === 'desc') {
      return $result;
    }

    return -$result;
  }

  public function get_bulk_actions() {
    $actions = ['bulk-delete-ga' => esc_attr__('Delete', 'lw_all_in_one')];
    return $actions;
  }

  protected function process_bulk_action() {

    if ('delete-ga' === $this->current_action()) {
      if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'delete')) {
        wp_die( __('Not valid request!', 'lw_all_in_one') );
      }
      self::wpdb_delete_records(absint(sanitize_text_field($_REQUEST['record_id'])));
    } else if ('bulk-delete-ga' === $this->current_action()) {
      if (!wp_verify_nonce($_REQUEST['bulk_delete_nonce_ga'], 'bulk_delete_records_ga')) {
        wp_die( __('Not valid request!', 'lw_all_in_one') );
      }
      if (isset($_REQUEST['bulk-delete-ga']) && is_array($_REQUEST['bulk-delete-ga'])) {
        $delete_ids = recursive_sanitize_array_object($_REQUEST['bulk-delete-ga']);
        foreach ($delete_ids as $id) {
          self::wpdb_delete_records(absint($id));
        }

      }
    }
  }

  public static function wpdb_delete_records($id) {
    global $wpdb;
    $wpdb->delete($wpdb->prefix . LW_ALL_IN_ONE_A_EVENTS_TABLE, ['id' => absint($id)], ['%d']);
  }

  public function no_items() {
    _e('No records found in the database.', 'lw_all_in_one');
  }

  public static function record_count() {
    global $wpdb;
    $sql = "SELECT COUNT(*) FROM " . $wpdb->prefix . LW_ALL_IN_ONE_A_EVENTS_TABLE;
    return $wpdb->get_var($sql);
  }
}

new Lw_All_In_One_Ga_Events_List_Table();