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

add_action( 'lw_all_in_one_cf7_deleted_message', 'lw_all_in_one_cf7_deleted_message' );
function lw_all_in_one_cf7_deleted_message() {
  $status = (isset($_REQUEST['status'])) ? sanitize_text_field($_REQUEST['status']) : '';
  $message = (isset($_REQUEST['message'])) ? sanitize_text_field($_REQUEST['message']) : '';

	if ( '' !== $status && '' !== $message ) {
		echo sprintf( '<div id="message" class="notice notice-%s is-dismissible inline"><p>%s</p></div>', esc_attr( $status ), esc_attr( $message ) );
	}
}

class Lw_All_In_One_Cf7_List_Table {
  public function __construct() {
    $this->lw_all_in_one_cf7_list_table();
  }

  public function lw_all_in_one_cf7_list_table() {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('You do not have permission to view submissions.', 'lw-all-in-one'));
    }

    $ListTableClassCf7 = new Lw_All_In_One_Cf7_List_Table_Class();
    $ListTableClassCf7->prepare_items_cf7();

    $s = (isset($_REQUEST['s'])) ? sanitize_text_field($_REQUEST['s']) : '';
    $page = sanitize_text_field($_REQUEST['page']);
    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">' . esc_html(get_admin_page_title()) . '</h1>';
    echo '<p>' . esc_html__('Undelivered submissions are retained until you deliberately delete them. Automatic delivery stops after eight attempts. Review failed submissions here before taking further action.', 'lw-all-in-one') . '</p>';
    if (Lw_All_In_One_Cf7_Sync::cooling_down()) {
      echo '<div class="notice notice-warning inline"><p>' . esc_html__('The delivery endpoint is cooling down after a failure. Submissions continue to be saved locally.', 'lw-all-in-one') . '</p></div>';
    }

    do_action( 'lw_all_in_one_cf7_deleted_message' );
    echo '<hr class="wp-header-end">';
    if ( ! empty( $s ) ) {
      echo sprintf( '<span class="subtitle">'
        /* translators: %s: Search query entered by the user */
        . esc_html__( 'Search results for &#8220;%s&#8221;', 'lw-all-in-one')
        . '</span>', esc_html( $s ) );
    }

    echo '<form method="POST" action="" id="lw_aio_saved_cf7_records">';
    wp_nonce_field( 'bulk_delete_records_cf7', 'bulk_delete_nonce_cf7' );
    echo '<input type="hidden" name="page" value="' . esc_attr( $page ) . '" />';

    $ListTableClassCf7->search_box( __( 'Search Records', 'lw-all-in-one'), 'lw_all_in_one' . '-s-cf7-record' );

    $ListTableClassCf7->display();
    echo '</form>';
    echo '</div>';
  }
}

if (!class_exists('WP_List_Table')) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Lw_All_In_One_Cf7_List_Table_Class extends WP_List_Table {

  public function prepare_items_cf7() {
    $columns = $this->get_columns();
    $hidden = $this->get_hidden_columns();
    $sortable = $this->get_sortable_columns();

    $this->process_bulk_action();

    $perPage = max(1, min(100, $this->get_items_per_page('records_per_page')));
    $currentPage = max(1, $this->get_pagenum());
    $data = $this->table_data($perPage, $currentPage);
    $this->set_pagination_args(array('total_items' => $this->total_records, 'per_page' => $perPage));

    $this->_column_headers = array($columns, $hidden, $sortable);
    $this->items = $data;
  }

  public function column_subject($item) {
    $delete_url = wp_nonce_url( '?page='.sanitize_text_field($_REQUEST['page']).'&action=delete-cf7&record_id='.$item['id'].'', 'delete' );
    $actions = array(
      'delete' => sprintf('<a href="%s" id="lw_aio_delete_record">Delete</a>', esc_url($delete_url)),
    );
    $subject = (strlen($item['subject']) > 10) ? substr($item['subject'], 0, 30) . ' ...' : $item['subject'];
    return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s', esc_html($subject), absint($item['id']), $this->row_actions($actions) );
  }

  public function column_cb($item) {
    return sprintf(
      '<input type="checkbox" name="bulk-delete-cf7[]" value="%1$s" />',$item['id']
    );
  }

  public function get_columns() {
    $columns = [
      'cb' => '<input type="checkbox" />',
      'subject' => esc_attr__('Subject', 'lw-all-in-one'),
      'time' => esc_attr__('Date', 'lw-all-in-one'),
      'name' => esc_attr__('Name', 'lw-all-in-one'),
      'surname' => esc_attr__('Surname', 'lw-all-in-one'),
      'email' => esc_attr__('Email', 'lw-all-in-one'),
      'phone' => esc_attr__('Phone', 'lw-all-in-one'),
      'delivery' => esc_html__('Delivery', 'lw-all-in-one'),
      'attempts' => esc_html__('Attempts', 'lw-all-in-one'),
      'last_attempt' => esc_html__('Last attempt (UTC)', 'lw-all-in-one'),
      'last_error' => esc_html__('Last error', 'lw-all-in-one'),
    ];
    return $columns;
  }

  public function get_hidden_columns() {
    return array();
  }

  public function get_sortable_columns() {
    $sortable_columns = array(
      'time' => array('time', false),
      'name' => array('name', false),
      'surname' => array('surname', false),
    );
    return $sortable_columns;
  }

  private $total_records = 0;

  private function table_data($limit, $page) {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('Permission denied.', 'lw-all-in-one'));
    }
    global $wpdb;
    $table_name = $wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE;
    $search = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
    $like = '%' . $wpdb->esc_like($search) . '%';
    $allowed = array('id', 'time', 'subject', 'name', 'surname');
    $requested = isset($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : 'id';
    $orderby = in_array($requested, $allowed, true) ? $requested : 'id';
    $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
    $this->total_records = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE subject LIKE %s OR name LIKE %s OR surname LIKE %s", $like, $like, $like));
    return $wpdb->get_results($wpdb->prepare(
      "SELECT * FROM $table_name WHERE subject LIKE %s OR name LIKE %s OR surname LIKE %s ORDER BY $orderby $order LIMIT %d OFFSET %d",
      $like, $like, $like, $limit, ($page - 1) * $limit
    ), ARRAY_A);
  }

  public function column_default($item, $column_name) {
    switch ($column_name) {
    case 'delivery':
      if ($item['sent'] === 'Si') {
        return esc_html__('Delivered', 'lw-all-in-one');
      }
      if ((int) $item['attempts'] >= Lw_All_In_One_Cf7_Sync::MAX_ATTEMPTS) {
        return esc_html__('Failed — review required', 'lw-all-in-one');
      }
      if (Lw_All_In_One_Cf7_Sync::held((object) $item)) {
        return esc_html__('Held — old backlog', 'lw-all-in-one');
      }
      return esc_html__('Pending', 'lw-all-in-one');
    case 'attempts':
    case 'last_attempt':
    case 'last_error':
    case 'subject':
    case 'time':
    case 'name':
    case 'surname':
    case 'email':
    case 'phone':
      return esc_html((string) $item[$column_name]);

    default:
      return '';
    }
  }

  public function get_bulk_actions() {
    $actions = ['bulk-delete-cf7' => esc_attr__('Delete', 'lw-all-in-one')];
    return $actions;
  }

  protected function process_bulk_action() {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('You do not have permission to delete submissions.', 'lw-all-in-one'));
    }

    if ('delete-cf7' === $this->current_action()) {
      if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'] ?? '')), 'delete')) {
        wp_die( esc_html__('Not valid request!', 'lw-all-in-one') );
      }
      self::wpdb_delete_records(absint(sanitize_text_field($_REQUEST['record_id'])));
      // $redirect_to_ga = add_query_arg( array( 'status' => 'success', 'message' => __('Record deleted!', 'lw-all-in-one') ), $redirect_to_ga );
      // wp_safe_redirect($redirect_to_ga);
      // return;
    } else if ('bulk-delete-cf7' === $this->current_action()) {
      if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['bulk_delete_nonce_cf7'] ?? '')), 'bulk_delete_records_cf7')) {
        wp_die( esc_html__('Not valid request!', 'lw-all-in-one') );
      }
      if (isset($_REQUEST['bulk-delete-cf7']) && is_array($_REQUEST['bulk-delete-cf7'])) {
        $delete_ids = recursive_sanitize_array_object($_REQUEST['bulk-delete-cf7']);
        foreach ($delete_ids as $id) {
          self::wpdb_delete_records(absint($id));
        }

      }
    }
  }

  public static function wpdb_delete_records($id) {
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table delete
    $wpdb->delete($wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE, ['id' => absint($id)], ['%d']);
  }

  public function no_items() {
    esc_html_e('No records found in the database.', 'lw-all-in-one');
  }

  public static function record_count() {
    global $wpdb;
    $table_name = $wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table count
    return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
  }
}

new Lw_All_In_One_Cf7_List_Table();
