<?php

/**
 * The Contact Form 7 integration functionality of the plugin.
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 */

/**
 * The Contact Form 7 integration functionality of the plugin.
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 * @author     sajdoko <sajmir.doko@localweb.it>
 */
class Lw_All_In_One_Cf7 {

  /**
   * The ID of this plugin.
   *
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;
  private $lw_api_url = 'https://localwebapi.ids.al/contactFormWeb';

  /**
   * Initialize the class and set its properties.
   *
   * @param  string  $plugin_name       The name of this plugin.
   * @param  string  $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  public function lw_all_in_one_cf7_admin_submenu() {
   add_submenu_page($this->plugin_name, __('Saved Contact Form Submissions', 'lw_all_in_one'), __('Saved CF7', 'lw_all_in_one'), 'manage_options', $this->plugin_name . '_cf7', array($this, 'lw_all_in_one_cf7_display_page'));
  }

  public function lw_all_in_one_cf7_display_page() {
    include_once 'partials/lw-all-in-one-admin-cf7-display.php';
  }

  public function lw_all_in_one_cf7_to_db($WPCF7_ContactForm) {

    //Plugin options
    $options = get_option($this->plugin_name);
    $cf7_activate = (isset($options['cf7_activate'])) ? esc_attr($options['cf7_activate']) : '';
    $lw_cf7_fields_saved_cf7_subm = (isset($options['lw_cf7_fields']['save_cf7_subm'])) ? sanitize_text_field($options['lw_cf7_fields']['save_cf7_subm']) : '';
    $lw_cf7_fields_saved_tipo_contratto = (isset($options['lw_cf7_fields']['tipo_contratto'])) ? sanitize_text_field($options['lw_cf7_fields']['tipo_contratto']) : '';
    $lw_cf7_fields_saved_id_contratto = (isset($options['lw_cf7_fields']['id_contratto'])) ? sanitize_text_field($options['lw_cf7_fields']['id_contratto']) : '';


    $url_path = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $domain = htmlspecialchars($url_path, ENT_QUOTES, 'UTF-8');
    $domain = trim($domain, '/');
    if (!preg_match('#^http(s)?://#', $domain)) {
      $domain = 'http://' . $domain;
    }
    $url_parts = parse_url($domain);
    $submited_page = preg_replace('/^www\./', '', $url_parts['host']);

    $submission = WPCF7_Submission::get_instance();
    $posted_data = &$submission->get_posted_data();

    if (isset($posted_data['nome'])) {
      $mapped_field = array();
      $mapped_field['nome'] = sanitize_text_field($posted_data['nome']);
      $mapped_field['cognome'] = sanitize_text_field($posted_data['cognome']);
      $mapped_field['email'] = sanitize_email($posted_data['email']);
      $mapped_field['telefono'] = sanitize_text_field($posted_data['telefono']);
      $mapped_field['oggetto'] = sanitize_text_field($posted_data['oggetto']);
      $mapped_field['messaggio'] = sanitize_textarea_field($posted_data['messaggio']);
      $mapped_field['tipo_Contratto'] = ($lw_cf7_fields_saved_tipo_contratto != '') ? sanitize_text_field($lw_cf7_fields_saved_tipo_contratto) : sanitize_text_field($posted_data['tipo-contratto']);
      $mapped_field['id_Contratto'] = ($lw_cf7_fields_saved_id_contratto != '') ? sanitize_text_field($lw_cf7_fields_saved_id_contratto) : sanitize_text_field($posted_data['id-contratto']);
      $mapped_field['submited_page'] = esc_url_raw($submited_page);

      $json_mapped_fields = json_encode($mapped_field);

      $args = array(
        'body' => $json_mapped_fields,
        'timeout' => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'cookies' => array(),
      );

      $send = wp_remote_post($this->lw_api_url, $args);
      $ret_body = wp_remote_retrieve_body($send);
      $data = recursive_sanitize_array_object(json_decode($ret_body));
      if (is_wp_error($ret_body)) {
        $sent = "No";
      } elseif ($data->response == "OK") {
        $sent = "Si";
      } else {
        $sent = "No";
      }

      global $wpdb;
      $name = $mapped_field['nome'];
      $surname = $mapped_field['cognome'];
      $email = $mapped_field['email'];
      $phone = $mapped_field['telefono'];
      $subject = $mapped_field['oggetto'];
      $message = $mapped_field['messaggio'];
      $tipo_Contratto = $mapped_field['tipo_Contratto'];
      $id_Contratto = $mapped_field['id_Contratto'];
      $time = current_time('mysql', 1);

      if ($lw_cf7_fields_saved_cf7_subm === 'on') {
        $cf7_table = $wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE;
        $wpdb->insert(
          $cf7_table,
          array(
            'time' => $time,
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
            'tipo_Contratto' => $tipo_Contratto,
            'id_Contratto' => $id_Contratto,
            'submited_page' => $submited_page,
            'sent' => $sent,
          )
        );
      }
    }
  }

  public function lw_all_in_one_cf7_packet_notice() {
    if (!current_user_can('manage_options')) {
      return;
    }
    echo '<div class="error"><p><img src="' . trailingslashit(plugin_dir_url(__FILE__)) . 'img/icon.png' . '"/> ' . sprintf(__('You have activated Contact Form 7 Addon but Packet Type and/or Packet Id seems to be missing. <a href="%s" title="Fix it Now">Fix it Now.</a>', 'lw_all_in_one'), admin_url('admin.php?page=lw_all_in_one&tab=tab_cf7&fix_packet#tipo_contratto')) . '</p></div>';
  }

  public function lw_all_in_one_old_cf7_is_active_deactivate() {
    if (is_plugin_active('lw-contact-form/localweb.php')) {
      deactivate_plugins('lw-contact-form/localweb.php');
    } elseif (is_plugin_inactive('lw-contact-form/localweb.php')) {
      delete_plugins(array('lw-contact-form/localweb.php'));
    }
  }

  public function lw_all_in_one_cf7_screen_options( $result, $option, $value ) {
    return $value;
  }

  public function lw_all_in_one_cf7_set_screen_options() {
		// $option = 'lw-aio-options_page_lw_all_in_one_cf7_per_page';
		$option = 'per_page';
		$args = [
			'label'   => __('Number of records per page:', 'lw_all_in_one'),
			'default' => 10,
			'option'  => 'records_per_page'
		];
		add_screen_option( $option, $args );
	}

}
