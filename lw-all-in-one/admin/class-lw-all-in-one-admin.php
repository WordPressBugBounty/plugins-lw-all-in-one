<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 * @author     sajdoko <sajmir.doko@localweb.it>
 */
class Lw_All_In_One_Admin {

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
   * @param  string  $plugin_name       The name of this plugin.
   * @param  string  $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  public function enqueue_styles($hook){
    if (preg_match('/page_lw_all_in_one/', $hook)) {
      $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
      wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/lw-all-in-one-admin'.$min.'.css', array(), $this->version, 'all');
      // Css rules for Color Picker
      wp_enqueue_style( 'wp-color-picker' );
    }
  }

  public function enqueue_scripts($hook){
    // echo $hook;
    // die();
    if (preg_match('/page_lw_all_in_one/', $hook)) {
      $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
      wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/lw-all-in-one-admin'.$min.'.js', array('jquery', 'wp-i18n', 'wp-color-picker'), $this->version, false);
      wp_set_script_translations($this->plugin_name, 'lw_all_in_one');
      wp_localize_script($this->plugin_name, 'lw_all_in_one_admin_ajax_object',
        array(
          'ajaxurl' => admin_url('admin-ajax.php'),
          'security' => wp_create_nonce($this->plugin_name),
          // 'data_var_1' => 'value 1',
          // 'data_var_2' => 'value 2',
        )
      );
    }
  }

  public function lw_all_in_one_add_admin_menu() {

    add_menu_page(__('LocalWeb All In One Options', 'lw_all_in_one'), __('LW AIO Options', 'lw_all_in_one'), 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page'), trailingslashit(plugin_dir_url(__FILE__)) . 'img/lw-fav.png', 81
    );
  }

  public function display_plugin_setup_page() {
    include_once 'partials/lw-all-in-one-admin-display.php';
  }

  public function lw_all_in_one_add_action_links($links) {
    $settings_link = array(
      '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings', 'lw_all_in_one') . '</a>',
    );
    return array_merge($settings_link, $links);
  }

  public function validate_lw_all_in_one_settings($input) {
    $valid = array();
    $valid['ga_activate'] = (isset($input['ga_activate']) && $input['ga_activate'] === 'on') ? 'on' : '';
    $valid['ga_fields']['tracking_id'] = (isset($input['ga_fields']['tracking_id'])) ? sanitize_text_field($input['ga_fields']['tracking_id']) : '';
    if ($valid['ga_fields']['tracking_id'] !== '' && !lw_all_in_one_validate_tracking_id($valid['ga_fields']['tracking_id'])) {
      $valid['ga_fields']['tracking_id'] = $this->get_plugin_options('ga_fields', 'tracking_id');
      $valid['ga_fields']['save_ga_events'] = $this->get_plugin_options('ga_fields', 'save_ga_events');
      $valid['ga_fields']['monitor_email_link'] = $this->get_plugin_options('ga_fields', 'monitor_email_link');
      $valid['ga_fields']['monitor_tel_link'] = $this->get_plugin_options('ga_fields', 'monitor_tel_link');
      $valid['ga_fields']['monitor_form_submit'] = $this->get_plugin_options('ga_fields', 'monitor_form_submit');
      $valid['ga_fields']['monitor_woocommerce_data'] = $this->get_plugin_options('ga_fields', 'monitor_woocommerce_data');
      add_settings_error(
        $this->plugin_name,
        $this->plugin_name . '_ga_fields_tracking_id_not_valid',
        __('Tracking ID is NOT valid!', 'lw_all_in_one'),
        'error'
      );
    } else {
      $valid['ga_fields']['save_ga_events'] = (isset($input['ga_fields']['save_ga_events']) && $input['ga_fields']['save_ga_events'] === 'on') ? 'on' : '';
      $valid['ga_fields']['monitor_email_link'] = (isset($input['ga_fields']['monitor_email_link']) && $input['ga_fields']['monitor_email_link'] === 'on') ? 'on' : '';
      $valid['ga_fields']['monitor_tel_link'] = (isset($input['ga_fields']['monitor_tel_link']) && $input['ga_fields']['monitor_tel_link'] === 'on') ? 'on' : '';
      $valid['ga_fields']['monitor_form_submit'] = (isset($input['ga_fields']['monitor_form_submit']) && $input['ga_fields']['monitor_form_submit'] === 'on') ? 'on' : '';
      $valid['ga_fields']['monitor_woocommerce_data'] = (isset($input['ga_fields']['monitor_woocommerce_data']) && $input['ga_fields']['monitor_woocommerce_data'] === 'on') ? 'on' : '';
    }

    $valid['wim_activate'] = (isset($input['wim_activate']) && $input['wim_activate'] === 'on') ? 'on' : '';

    if (isset($input['wim_fields']['verification_status']) && isset($input['wim_fields']['save_wim_options']) && isset($input['wim_fields']['token']) && strlen($input['wim_fields']['token']) == 32) {
      $domain = clean_domain(get_option('siteurl', $_SERVER['HTTP_HOST']));
      $wim_settings_arr = array();
      $wim_settings_arr['wim_fields']['verification_status'] = sanitize_text_field($input['wim_fields']['verification_status']);
      $wim_settings_arr['wim_fields']['token'] = sanitize_text_field($input['wim_fields']['token']);
      $wim_settings_arr['wim_fields']['rag_soc'] = (isset($input['wim_fields']['rag_soc'])) ? sanitize_text_field($input['wim_fields']['rag_soc']) : "";
      $wim_settings_arr['wim_fields']['auto_show_wim'] = (isset($input['wim_fields']['auto_show_wim'])) ? sanitize_text_field($input['wim_fields']['auto_show_wim']) : "SI";
      $wim_settings_arr['wim_fields']['show_wim_after'] = (isset($input['wim_fields']['show_wim_after'])) ? sanitize_text_field($input['wim_fields']['show_wim_after']) : "5";
      $wim_settings_arr['wim_fields']['show_mobile'] = (isset($input['wim_fields']['show_mobile'])) ? sanitize_text_field($input['wim_fields']['show_mobile']) : "SI";
      $wim_settings_arr['wim_fields']['lingua'] = (isset($input['wim_fields']['lingua'])) ? sanitize_text_field($input['wim_fields']['lingua']) : "it";
      $wim_settings_arr['wim_fields']['messaggio_0'] = (isset($input['wim_fields']['messaggio_0'])) ? sanitize_textarea_field($input['wim_fields']['messaggio_0']) : "Salve! Come posso esserle utile?";
      $wim_settings_arr['wim_fields']['messaggio_1'] = (isset($input['wim_fields']['messaggio_1'])) ? sanitize_textarea_field($input['wim_fields']['messaggio_1']) : "Gentilmente, mi può lasciare un contatto telefonico o email in modo da poterla eventualmente ricontattare?";

      $api_url = 'https://localweb.it/chat/api/cliente/aggiorna.php';
      $response = wp_remote_post($api_url, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'blocking' => true,
        'headers' => array(),
        'body' => json_encode(array('stato_wim' => $valid['wim_activate'], 'domain' => $domain, 'plugin_token' => $wim_settings_arr['wim_fields']['token'], 'auto_show_wim' => $wim_settings_arr['wim_fields']['auto_show_wim'], 'show_wim_after' => $wim_settings_arr['wim_fields']['show_wim_after'], 'show_mobile' => $wim_settings_arr['wim_fields']['show_mobile'], 'lingua' => $wim_settings_arr['wim_fields']['lingua'], 'messaggio_0' => $wim_settings_arr['wim_fields']['messaggio_0'], 'messaggio_1' => $wim_settings_arr['wim_fields']['messaggio_1'])),
        'cookies' => array(),
      )
      );
      $ret_body = wp_remote_retrieve_body($response);
      $data = recursive_sanitize_array_object(json_decode($ret_body));

      if (is_wp_error($response)) {
        $valid['wim_fields']['verification_status'] = '';
        $error_message = $response->get_error_message();
        add_settings_error(
          $this->plugin_name,
          $this->plugin_name . '_settings_not_updated_error',
          _e('Something went wrong!', 'lw_all_in_one'),
          'error'
        );
      } elseif ((isset($data->response)) && $data->response == 'success') {
        $valid = array_merge($valid, $wim_settings_arr);
      } elseif ((isset($data->response)) && $data->response == 'danger') {
        $valid['wim_fields']['verification_status'] = '';
        add_settings_error(
          $this->plugin_name,
          $this->plugin_name . '_settings_not_updated_danger',
          ($data != 'null') ? json_encode($data) : __('Invalid response from API server!', 'lw_all_in_one'),
          'error'
        );
      } else {
        $valid['wim_fields']['verification_status'] = '';
        add_settings_error(
          $this->plugin_name,
          $this->plugin_name . '_settings_not_updated_not_known',
          ($data != 'null') ? json_encode($data) : __('Invalid response from API server!', 'lw_all_in_one'),
          'error'
        );
      }
    }

    $valid['cf7_activate'] = (isset($input['cf7_activate']) && $input['cf7_activate'] === 'on') ? 'on' : '';
    $valid['lw_cf7_fields']['save_cf7_subm'] = (isset($input['lw_cf7_fields']['save_cf7_subm']) && $input['lw_cf7_fields']['save_cf7_subm'] === 'on') ? 'on' : '';
    $valid['lw_cf7_fields']['opt_scr_deliv'] = (isset($input['lw_cf7_fields']['opt_scr_deliv']) && $input['lw_cf7_fields']['opt_scr_deliv'] === 'on') ? 'on' : '';
    $valid['lw_cf7_fields']['tipo_contratto'] = (isset($input['lw_cf7_fields']['tipo_contratto'])) ? sanitize_text_field($input['lw_cf7_fields']['tipo_contratto']) : '';
    $valid['ck_activate'] = (isset($input['ck_activate']) && $input['ck_activate'] === 'on') ? 'on' : '';
    $valid['ck_fields']['banner_position'] = (isset($input['ck_fields']['banner_position'])) ? sanitize_text_field($input['ck_fields']['banner_position']) : '';
    $valid['ck_fields']['ck_page_slug'] = (isset($input['ck_fields']['ck_page_slug'])) ? sanitize_text_field($input['ck_fields']['ck_page_slug']) : '';
    $valid['ck_fields']['primary_color'] = (isset($input['ck_fields']['primary_color'])) ? sanitize_text_field($input['ck_fields']['primary_color']) : '';
    $valid['ck_fields']['secondary_color'] = (isset($input['ck_fields']['secondary_color'])) ? sanitize_text_field($input['ck_fields']['secondary_color']) : '';
    $valid['ck_fields']['heading_message'] = (isset($input['ck_fields']['heading_message'])) ? sanitize_text_field($input['ck_fields']['heading_message']) : '';
    $valid['ck_fields']['gdpr_message'] = (isset($input['ck_fields']['gdpr_message'])) ? sanitize_textarea_field($input['ck_fields']['gdpr_message']) : '';
    $valid['ck_fields']['about_ck_message'] = (isset($input['ck_fields']['about_ck_message'])) ? sanitize_textarea_field($input['ck_fields']['about_ck_message']) : '';

    $valid['lw_cf7_fields']['id_contratto'] = (isset($input['lw_cf7_fields']['id_contratto'])) ? sanitize_text_field($input['lw_cf7_fields']['id_contratto']) : '';
    if ($valid['cf7_activate'] !== '' && !in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      $valid['cf7_activate'] = '';
      $valid['lw_cf7_fields']['save_cf7_subm'] = '';
      if (!file_exists(WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php')) {
        add_settings_error(
          $this->plugin_name,
          $this->plugin_name . '_lw_cf7_main_not_installed',
          sprintf(__('Contact Form 7 plugin is not installed! <a href="%s" title="Contact Form 7">Install It Now!</a>', 'lw_all_in_one'), wp_nonce_url(admin_url('update.php?action=install-plugin&plugin=contact-form-7'), 'install-plugin_contact-form-7')),
          'error'
        );
      } else if (!in_array('contact-form-7/wp-contact-form-7.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        add_settings_error(
          $this->plugin_name,
          $this->plugin_name . '_lw_cf7_main_not_installed',
          sprintf(__('Contact Form 7 plugin is not activated! <a href="%s" title="Contact Form 7">Activate It Now!</a>', 'lw_all_in_one'), wp_nonce_url(admin_url('plugins.php?action=activate&plugin=contact-form-7/wp-contact-form-7.php'), 'activate-plugin_contact-form-7/wp-contact-form-7.php')),
          'error'
        );
      }
    }
    $valid['lw_hf_fields']['insert_header'] = (isset($input['lw_hf_fields']['insert_header'])) ? $this->sanitize_header_footer_scripts($input['lw_hf_fields']['insert_header']) : '';
    $valid['lw_hf_fields']['insert_footer'] = (isset($input['lw_hf_fields']['insert_footer'])) ? $this->sanitize_header_footer_scripts($input['lw_hf_fields']['insert_footer']) : '';

    $valid['lw_aio_fields']['delete_data'] = (isset($input['lw_aio_fields']['delete_data']) && $input['lw_aio_fields']['delete_data'] === 'on') ? 'on' : '';
    $valid['lw_aio_fields']['data_retention'] = (isset($input['lw_aio_fields']['data_retention']) && $input['lw_aio_fields']['data_retention'] === 'on') ? 'on' : '';

    $exiting_options = get_option($this->plugin_name);
    if ($exiting_options) {
      $valid = array_merge($exiting_options, $valid);
    }
    return $valid;
  }

  public function lw_all_in_one_options_update() {
    if (!current_user_can('manage_options')) {
      return;
    }
    register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate_lw_all_in_one_settings'));
  }

  public function get_plugin_options($parent_key = false, $key = false) {
    $options = get_option($this->plugin_name);

    if ($parent_key !== false) {
      if (isset($options[$parent_key]) && isset($options[$parent_key][$key])) {
        return $options[$parent_key][$key];
      } else {
        return false;
      }
    } else {
      if (isset($options[$key])) {
        return $options[$key];
      } else {
        return false;
      }
    }
  }


  public function lw_all_in_one_header_scripts_from_tab() {
    //Plugin options
    $options = get_option($this->plugin_name);
    $lw_hf_fields_insert_header = (isset($options['lw_hf_fields']['insert_header'])) ? $options['lw_hf_fields']['insert_header'] : '';

    if ($lw_hf_fields_insert_header !== '') {
      echo ($this->lw_all_in_one_is_base64($lw_hf_fields_insert_header)) ? (base64_decode($lw_hf_fields_insert_header)) : $lw_hf_fields_insert_header, "\n";
    }
  }

  public function lw_all_in_one_footer_scripts_from_tab() {
    //Plugin options
    $options = get_option($this->plugin_name);
    $lw_hf_fields_insert_footer = (isset($options['lw_hf_fields']['insert_footer'])) ? $options['lw_hf_fields']['insert_footer'] : '';

    if ($lw_hf_fields_insert_footer !== '') {
      echo ($this->lw_all_in_one_is_base64($lw_hf_fields_insert_footer)) ? (base64_decode($lw_hf_fields_insert_footer)) : $lw_hf_fields_insert_footer, "\n";
    }
    echo '<style>.grecaptcha-badge{visibility: hidden !important}</style>', "\n";
  }

  public function sanitize_header_footer_scripts($scripts) {
    // Remove PHP code
    $scripts = preg_replace('/<\?php.+?\?>$/ms', '', $scripts);
    return base64_encode($scripts);
  }

  public function lw_all_in_one_auto_update($update, $item) {
    $plugins = array(
      'lw-all-in-one',
    );
    if (in_array($item->slug, $plugins)) {
      return true;
    } else {
      return $update;
    }
  }

  public function lw_all_in_one_is_base64($string) {
    return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string);
  }

  public function lw_all_in_one_admin_footer_text($text) {
    $current_screen = get_current_screen();
    if ($current_screen->parent_base == 'lw_all_in_one') {
      $lw_aio_plugin_data = get_plugin_data(LW_ALL_IN_ONE_PLUGIN_MAIN_FILE);
      $plugin_name = $lw_aio_plugin_data['Name'];
      return $plugin_name . sprintf(__(' | Version %s', 'lw_all_in_one'), LW_ALL_IN_ONE_VERSION);
    } else {
      return $text;
    }
  }

  public function lw_all_in_one_reset_plugin_options() {
    if (!current_user_can('manage_options')) {
      return;
    }
    if (!check_ajax_referer($this->plugin_name, 'security')) {
      wp_send_json_error(__('Security is not valid!', 'lw_all_in_one'));
      die();
    }
    if (isset($_POST['action']) && $_POST['action'] === "lw_all_in_one_reset_plugin_options") {
      global $wpdb;
      $charset_collate = $wpdb->get_charset_collate();
      $a_events_table = $wpdb->prefix . LW_ALL_IN_ONE_A_EVENTS_TABLE;
      $cf7_table = $wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE;
      $wpdb->query("DROP TABLE IF EXISTS {$a_events_table}");
      $wpdb->query("DROP TABLE IF EXISTS {$cf7_table}");

      delete_option('lw_all_in_one');
      delete_option('lw_all_in_one_version');
      delete_option('lw_all_in_one_privacy_pages');

      if (wp_next_scheduled('lw_all_in_one_data_retention')) {
        wp_clear_scheduled_hook('lw_all_in_one_data_retention');
      }
      if (wp_next_scheduled('lw_all_in_one_cf7_sync')) {
        wp_clear_scheduled_hook('lw_all_in_one_cf7_sync');
      }

      if (get_locale() == 'es_ES') {
        $ck_fields['ck_page_slug'] = 'las-cookies-que-utilizamos';
        $ck_fields['heading_message'] = 'Este sitio web utiliza cookies';
        $ck_fields['gdpr_message'] = 'Utilizamos cookies para personalizar contenido y anuncios, para proporcionar funciones de redes sociales y para analizar nuestro tráfico. También compartimos información sobre su uso de nuestro sitio con nuestros socios de redes sociales, publicidad y análisis, que pueden combinarla con otra información que les haya proporcionado o que hayan recopilado a partir del uso de sus servicios.';
        $ck_fields['about_ck_message'] = 'Las cookies son pequeños archivos de texto que pueden ser utilizados por los sitios web para hacer que la experiencia del usuario sea más eficiente. La ley establece que podemos almacenar cookies en su dispositivo si son estrictamente necesarias para el funcionamiento de este sitio. Para todos los demás tipos de cookies necesitamos su permiso. Este sitio utiliza diferentes tipos de cookies. Algunas cookies son colocadas por servicios de terceros que aparecen en nuestras páginas. En cualquier momento puede cambiar o retirar su consentimiento de la Declaración de cookies en nuestro sitio web. Obtenga más información sobre quiénes somos, cómo puede contactarnos y cómo tratamos los datos personales en nuestra Política de privacidad. Especifique su ID de consentimiento y la fecha en que nos contactó con respecto a su consentimiento.';
      } elseif (get_locale() == 'it_IT') {
        $ck_fields['ck_page_slug'] = 'cookie-policy';
        $ck_fields['heading_message'] = 'Questo sito web utilizza i cookie!';
        $ck_fields['gdpr_message'] = 'Utilizziamo i cookie per personalizzare contenuti ed annunci, per fornire funzionalità dei social media e per analizzare il nostro traffico. Condividiamo inoltre informazioni sul modo in cui utilizza il nostro sito con i nostri partner che si occupano di analisi dei dati web, pubblicità e social media, i quali potrebbero combinarle con altre informazioni che ha fornito loro o che hanno raccolto dal suo utilizzo dei loro servizi.';
        $ck_fields['about_ck_message'] = "I cookie sono piccoli file di testo che possono essere utilizzati dai siti web per rendere più efficiente l’esperienza per l’utente. La legge afferma che possiamo memorizzare i cookie sul suo dispositivo se sono strettamente necessari per il funzionamento di questo sito. Per tutti gli altri tipi di cookie abbiamo bisogno del suo permesso. Questo sito utilizza diversi tipi di cookie. Alcuni cookie sono collocati da servizi di terzi che compaiono sulle nostre pagine. In qualsiasi momento è possibile modificare o revocare il proprio consenso dalla Dichiarazione dei cookie sul nostro sito Web. Scopra di più su chi siamo, come può contattarci e come trattiamo i dati personali nella nostra Informativa sulla privacy. Specifica l’ID del tuo consenso e la data di quando ci hai contattati per quanto riguarda il tuo consenso.";
      } else {
        $ck_fields['ck_page_slug'] = 'cookie-policy';
        $ck_fields['heading_message'] = 'This website uses cookies!';
        $ck_fields['gdpr_message'] = 'We use cookies to personalize content and ads, to provide social media features and to analyze our traffic. We also share information about how you use our site with our analytics, advertising and social media partners, who may combine it with other information that you have provided to them or that they have collected from your use of their services.';
        $ck_fields['about_ck_message'] = "Cookies are small text files that can be used by websites to make the user experience more efficient. The law states that we can store cookies on your device if they are strictly necessary for the operation of this site. For all other types of cookies we need your permission. This site uses different types of cookies. Some cookies are placed by third-party services that appear on our pages. You can change or withdraw your consent at any time from the Cookie Declaration on our website. Find out more about who we are, how you can contact us and how we process personal data in our Privacy Policy. Specify your consent ID and the date you contacted us regarding your consent.";
      }

      $initial_attivation_options = array(
        'ga_activate' => '',
        'ga_fields' => array(
          'tracking_id' => '',
          'save_ga_events' => '',
          'monitor_email_link' => '',
          'monitor_tel_link' => '',
          'monitor_form_submit' => '',
          'monitor_woocommerce_data' => '',
        ),
        'wim_activate' => '',
        'wim_fields' => array(
          'verification_status' => '',
          'token' => '',
          'rag_soc' => '',
          'auto_show_wim' => '',
          'show_wim_after' => '',
          'show_mobile' => '',
          'lingua' => '',
          'messaggio_0' => '',
          'messaggio_1' => '',
        ),
        'cf7_activate' => '',
        'lw_cf7_fields' => array(
          'save_cf7_subm' => '',
          'opt_scr_deliv' => '',
        ),
        'ck_activate' => '',
        'ck_fields' => array(
          'banner_position' => 'bottom',
          'ck_page_slug' => $ck_fields['ck_page_slug'],
          'primary_color' => '#18a300',
          'secondary_color' => '#333333',
          'heading_message' => $ck_fields['heading_message'],
          'gdpr_message' => $ck_fields['gdpr_message'],
          'about_ck_message' => $ck_fields['about_ck_message'],
        ),
        'lw_hf_fields' => array(
          'insert_header' => '',
          'insert_footer' => '',
        ),
        'lw_aio_fields' => array(
          'delete_data' => '',
          'data_retention' => 'on',
        ),
      );
      // wp_die(var_dump($initial_attivation_options));
      add_option('lw_all_in_one', $initial_attivation_options);
      add_option('lw_all_in_one'.'_version', LW_ALL_IN_ONE_VERSION);

      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
      if ($wpdb->get_var("show tables like '$a_events_table'") != $a_events_table) {
        $sql1 = "CREATE TABLE $a_events_table (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
              ga_category varchar(250) DEFAULT '' NULL,
              ga_action varchar(250) DEFAULT '' NULL,
              ga_label varchar(250) DEFAULT '' NULL,
              PRIMARY KEY (id)
            ) $charset_collate;";
        dbDelta($sql1);
      }
      if ($wpdb->get_var("show tables like '$cf7_table'") != $cf7_table) {
        $sql2 = "CREATE TABLE $cf7_table (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              subject text DEFAULT '' NULL,
              message text DEFAULT '' NULL,
              name varchar(150) DEFAULT '' NULL,
              surname varchar(150) DEFAULT '' NULL,
              time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
              email varchar(100) DEFAULT '' NULL,
              phone varchar(100) DEFAULT '' NULL,
              tipo_Contratto varchar(10) DEFAULT '' NULL,
              id_Contratto varchar(10) DEFAULT '' NULL,
              submited_page text DEFAULT '' NULL,
              sent varchar(2) DEFAULT '' NULL,
              PRIMARY KEY (id)
            ) $charset_collate;";
        dbDelta($sql2);
      }

      wp_send_json_success(array('message' => __('Options were reset to defaults!', 'lw_all_in_one')));
      die();
    }
  }
}
