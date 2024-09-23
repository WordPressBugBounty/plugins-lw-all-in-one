<?php

/**
 * The Web Instant Messenger functionality of the plugin.
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 */

/**
 * The Web Instant Messenger functionality of the plugin.
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 * @author     sajdoko <sajmir.doko@localweb.it>
 */
class Lw_All_In_One_Wim {

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

  private $wim_veify_api_url = 'https://localweb.it/chat/api/cliente/verifica-plugin.php';

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

  public function lw_all_in_one_verify_wim_attivation() {
    if (!current_user_can('manage_options')) {
      return;
    }
    if (!check_ajax_referer($this->plugin_name, 'security')) {
      wp_send_json_error(__('Security is not valid!', 'lw_all_in_one'));
      die();
    }
    if (isset($_POST['action']) && $_POST['action'] === "lw_all_in_one_verify_wim_attivation") {
      $domain = clean_domain(get_option('siteurl', $_SERVER['HTTP_HOST']));
      $response = wp_remote_get($this->wim_veify_api_url, array(
        'method' => 'GET',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => array('domain' => urlencode($domain), 'action' => 'get_options'),
        'cookies' => array(),
      )
      );
      $ret_body = wp_remote_retrieve_body($response);
      $data = recursive_sanitize_array_object(json_decode($ret_body));
      if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        wp_send_json_error(__('Something went wrong!', 'lw_all_in_one'));
        die();
      } elseif ($data->response == 'verified') {
        if ($data->token == '') {
          wp_send_json_error(__('WIM authorized but token returned was empty!', 'lw_all_in_one'));
          die();
        } else if (strlen($data->token) == 32) {
          wp_send_json_success(array('fields' => $data, 'message' => __('Web Instant Messenger authorized!', 'lw_all_in_one')));
          die();
        } else {
          wp_send_json_error(__('There was an unknown error!', 'lw_all_in_one'));
          die();
        }
      } elseif ($data->response == 'unverified') {
        wp_send_json_error($data->message);
        die();
      } else {
        wp_send_json_error(__('Not a valid response!', 'lw_all_in_one'));
        die();
      }
    } else {
      wp_send_json_error(__('Action is not valid!', 'lw_all_in_one'));
      die();
    }
  }

  public function lw_all_in_one_old_wim_is_active_deactivate(){
    if (is_plugin_active('web-instant-messenger/web-instant-messenger.php')) {
      delete_option('wim_activation_status');
      delete_option('web-instant-messenger');
      deactivate_plugins('web-instant-messenger/web-instant-messenger.php');
    } elseif (is_plugin_inactive('web-instant-messenger/web-instant-messenger.php')) {
      delete_option('wim_activation_status');
      delete_option('web-instant-messenger');
      delete_plugins(array('web-instant-messenger/web-instant-messenger.php'));
    }
  }

}
