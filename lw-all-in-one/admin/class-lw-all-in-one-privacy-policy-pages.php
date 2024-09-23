<?php

/**
 * Creates LocalWeb Privacy&Policy pages.
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 */

/**
 * Creates LocalWeb Privacy&Policy pages.
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 * @author     sajdoko <sajmir.doko@localweb.it>
 */
class Lw_All_In_One_Privacy_Policy_Pages {

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

  /**
   * Initialize the class and set its properties.
   *
   * @param      string    $plugin_name       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct($plugin_name, $version) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  public function lw_all_in_one_privacy_policy_admin_menu() {
    add_submenu_page($this->plugin_name, __('LocalWeb Privacy&Policy Pages Options', 'lw_all_in_one'), __('Privacy&Policy Pages', 'lw_all_in_one'), 'manage_options', 'lw_all_in_one' . '_privacy_policy', array($this, 'lw_all_in_one_privacy_policy_display_page'));
  }

  public function lw_all_in_one_privacy_policy_display_page() {
    include_once 'partials/lw-all-in-one-admin-privacy-policy-display.php';
  }

  public function lw_all_in_one_remove_italy_cookie_choices() {
    if (is_plugin_active('italy-cookie-choices/italy-cookie-choices.php')) {
      deactivate_plugins('italy-cookie-choices/italy-cookie-choices.php');
    } elseif (is_plugin_inactive('italy-cookie-choices/italy-cookie-choices.php')) {
      delete_plugins(array('italy-cookie-choices/italy-cookie-choices.php'));
    }
  }

  public function lw_all_in_one_create_privacy_pages() {
    if (!check_ajax_referer($this->plugin_name, 'security')) {
      wp_send_json_error(__('Security is not valid!', 'lw_all_in_one'));
      die();
    }
    if (isset($_POST['action']) && $_POST['action'] === "lw_all_in_one_create_privacy_pages") {

      $create_cookie_page = isset($_POST[ $this->plugin_name . '_cookie_page']) && $_POST[ $this->plugin_name . '_cookie_page'] == 'on';
      $create_privacy_page = isset($_POST[ $this->plugin_name . '_privacy_page']) && $_POST[ $this->plugin_name . '_privacy_page'] == 'on';
      $create_info_dati_page = isset($_POST[ $this->plugin_name . '_trattamento_dati_page']) && $_POST[ $this->plugin_name . '_trattamento_dati_page'] == 'on';

      $date = date('d-m-Y', time());
      $domain = get_option('siteurl', $_SERVER['HTTP_HOST']);
      $site_lang = get_locale();
      $create_pages_resposes = array();
      $created_pages_save_option = array();

      if ($create_cookie_page) {
        if ($site_lang == 'es_ES') {
          $page_cookie = get_page_by_path('las-cookies-que-utilizamos');
          $post_title = "Las cookies que utilizamos";
          $cookie_file = file_get_contents( plugin_dir_path(dirname(__FILE__)) . 'admin/privacy-pages/cookie-es_ES.html');
        } else {
          $page_cookie = get_page_by_path('cookie-policy');
          $post_title = "Cookie Policy";
          $cookie_file = file_get_contents( plugin_dir_path(dirname(__FILE__)) . 'admin/privacy-pages/cookie-it_IT.html');
        }
        include_once 'privacy-pages/cookie.php';
        if ($create_pages_resposes['cookie-policy']['status'] == 'success') {
          $created_pages_save_option['cookie-policy'] = array(
            'action' => $create_pages_resposes['cookie-policy']['action'],
            'post_id' => $create_pages_resposes['cookie-policy']['post_id'],
          );
        }
      }

      if ($create_privacy_page) {
        if ($site_lang == 'es_ES') {
          $post_title = "Información Sobre El Tratamiento De Datos Personales";
          $page_policy = get_page_by_path('informacion-sobre-el-tratamiento-de-datos-personales');
          $policy_file = file_get_contents( plugin_dir_path(dirname(__FILE__)) . 'admin/privacy-pages/privacy-policy-es_ES.html');
        } else {
          $post_title = "Informativa sul trattamento dei dati personali";
          $page_policy = get_page_by_path('informativa-sul-trattamento-dei-dati-personali');
          $policy_file = file_get_contents( plugin_dir_path(dirname(__FILE__)) . 'admin/privacy-pages/privacy-policy-it_IT.html');
        }

        include_once 'privacy-pages/privacy.php';
        if ($create_pages_resposes['informativa-sul-trattamento-dei-dati-personali']['status'] == 'success') {
          $created_pages_save_option['informativa-sul-trattamento-dei-dati-personali'] = array(
            'action' => $create_pages_resposes['informativa-sul-trattamento-dei-dati-personali']['action'],
            'post_id' => $create_pages_resposes['informativa-sul-trattamento-dei-dati-personali']['post_id'],
          );
        }
      }

      if ($create_info_dati_page) {

        if ($site_lang == 'es_ES') {
          $post_title = "Informacion sobre el tratamiento de datos";
          $page_contact = get_page_by_path('informacion-sobre-el-tratamiento-de-datos');
          $contact_file = file_get_contents( plugin_dir_path(dirname(__FILE__)) . 'admin/privacy-pages/contact-es_ES.html');
        } else {
          $post_title = "Informativa trattamento dati";
          $page_contact = get_page_by_path('informativa-trattamento-dati');
          $contact_file = file_get_contents( plugin_dir_path(dirname(__FILE__)) . 'admin/privacy-pages/contact-it_IT.html');
        }

        include_once 'privacy-pages/contact.php';
        if ($create_pages_resposes['informativa-trattamento-dati']['status'] == 'success') {
          $created_pages_save_option['informativa-trattamento-dati'] = array(
            'action' => $create_pages_resposes['informativa-trattamento-dati']['action'],
            'post_id' => $create_pages_resposes['informativa-trattamento-dati']['post_id'],
          );
        }
      }

      $exiting_option = get_option($this->plugin_name . '_privacy_pages');
      if ($exiting_option) {
        $created_pages_save_option = array_merge($exiting_option, $created_pages_save_option);
      }
      update_option($this->plugin_name . '_privacy_pages', $created_pages_save_option);
      wp_send_json_success($create_pages_resposes);
    } else {
      wp_send_json_error(__('Action is not valid!', 'lw_all_in_one'));
    }
	  die();
  }

}
