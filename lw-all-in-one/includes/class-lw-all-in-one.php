<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/includes
 * @author     sajdoko <sajmir.doko@localweb.it>
 */
class Lw_All_In_One {

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @access   protected
   * @var      Lw_All_In_One_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @access   protected
   * @var      $plugin_name    The string used to uniquely identify this plugin.
   */
  protected $plugin_name;

  /**
   * The current version of the plugin.
   *
   * @access   protected
   * @var      $version    The current version of the plugin.
   */
  protected $version;

  /**
   * Define the core functionality of the plugin.
   */
  public function __construct() {
    if (defined('LW_ALL_IN_ONE_VERSION')) {
      $this->version = LW_ALL_IN_ONE_VERSION;
    } else {
      $this->version = '1.0.0';
    }
    $this->plugin_name = 'lw_all_in_one';

    $this->load_dependencies();
    $this->set_locale();
    $this->lw_all_in_one_schedule_data_retention();
    $this->lw_all_in_one_schedule_single_event();
    $this->define_admin_hooks();
    $this->define_public_hooks();
    if ($this->check_plugin_options(false, 'ga_activate') === 'on') {
      $this->define_ga_events_hooks();
    }
    if ($this->check_plugin_options(false, 'wim_activate') === 'on') {
      $this->define_wim_hooks();
    }
    if ($this->check_plugin_options(false, 'cf7_activate') === 'on') {
      $this->define_cf7_hooks();
      $this->lw_all_in_one_schedule_cf7_sync();
    }
    $this->define_privacy_policy_hooks();
  }

  private function load_dependencies() {

    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-lw-all-in-one-loader.php';

    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-lw-all-in-one-i18n.php';

    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/helpers/lw-all-in-one-helper-functions.php';

    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-lw-all-in-one-admin.php';

    require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-lw-all-in-one-public.php';

    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-lw-all-in-one-ga-events.php';

    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-lw-all-in-one-wim.php';

    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-lw-all-in-one-cf7.php';

    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-lw-all-in-one-privacy-policy-pages.php';

    $this->loader = new Lw_All_In_One_Loader();
  }

  private function set_locale() {

    $plugin_i18n = new Lw_All_In_One_i18n();

    $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
  }

  private function define_admin_hooks() {

    $plugin_admin = new Lw_All_In_One_Admin($this->get_plugin_name(), $this->get_version());

    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    $this->loader->add_filter('auto_update_plugin', $plugin_admin, 'lw_all_in_one_auto_update', 10, 2);

    $this->loader->add_action('admin_menu', $plugin_admin, 'lw_all_in_one_add_admin_menu', 99);

    $this->loader->add_action('admin_init', $plugin_admin, 'lw_all_in_one_options_update');

    $this->loader->add_action('wp_ajax_lw_all_in_one_reset_plugin_options', $plugin_admin, 'lw_all_in_one_reset_plugin_options');

    $this->loader->add_filter('admin_footer_text', $plugin_admin, 'lw_all_in_one_admin_footer_text');

    $plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . 'lw-all-in-one.php');
    $this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_admin, 'lw_all_in_one_add_action_links');

    $this->loader->add_action('wp_head', $plugin_admin, 'lw_all_in_one_header_scripts_from_tab');
    $this->loader->add_action('wp_footer', $plugin_admin, 'lw_all_in_one_footer_scripts_from_tab');
  }

  private function define_public_hooks() {

    $plugin_public = new Lw_All_In_One_Public($this->get_plugin_name(), $this->get_version());

    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'lw_all_in_one_dequeue');

    $this->loader->add_action('wp_head', $plugin_public, 'lw_all_in_one_header_scripts');
    $this->loader->add_action('wp_footer', $plugin_public, 'lw_all_in_one_footer_scripts');

    if ($this->check_plugin_options(false, 'ga_activate') === 'on' && $this->check_plugin_options('ga_fields', 'tracking_id') !== '') {
      $this->loader->add_action('wp_ajax_lw_all_in_one_save_ga_event', $plugin_public, 'lw_all_in_one_save_ga_event');
      $this->loader->add_action('wp_ajax_nopriv_lw_all_in_one_save_ga_event', $plugin_public, 'lw_all_in_one_save_ga_event');

      if ($this->check_plugin_options('ga_fields', 'monitor_woocommerce_data') === 'on') {
        $this->loader->add_action('plugins_loaded', $plugin_public, 'include_woocommerce_gtm_tracking');
      }
    }

    if ($this->check_plugin_options(false, 'ck_activate') == 'on') {
      $this->loader->add_action('wp_footer', $plugin_public, 'include_cookie_consent');
    }
  }

  private function define_ga_events_hooks() {

    $plugin_ga_events = new Lw_All_In_One_Ga_Events($this->get_plugin_name(), $this->get_version());

    $this->loader->add_action('admin_menu', $plugin_ga_events, 'lw_all_in_one_ga_events_admin_menu', 99);
    $this->loader->add_filter('set-screen-option', $plugin_ga_events, 'lw_all_in_one_ga_events_screen_options', 10, 3);
    $this->loader->add_action('load-lw-aio-options_page_lw_all_in_one_ga_events', $plugin_ga_events, 'lw_all_in_one_ga_events_set_screen_options');

    // $this->loader->add_action('admin_notices', $plugin_ga_events, 'woocommerce_google_analytics_missing_notice');

    $this->loader->add_action('admin_init', $plugin_ga_events, 'lw_all_in_one_gadwp_is_active_deactivate');
  }

  private function define_wim_hooks() {

    $plugin_wim = new Lw_All_In_One_Wim($this->get_plugin_name(), $this->get_version());

    $this->loader->add_action('wp_ajax_lw_all_in_one_verify_wim_attivation', $plugin_wim, 'lw_all_in_one_verify_wim_attivation');

    $this->loader->add_action('admin_init', $plugin_wim, 'lw_all_in_one_old_wim_is_active_deactivate');
  }

  private function define_cf7_hooks() {

    $options = get_option($this->plugin_name);
    $lw_cf7_fields_saved_save_cf7_subm = (isset($options['lw_cf7_fields']['save_cf7_subm'])) ? sanitize_text_field($options['lw_cf7_fields']['save_cf7_subm']) : '';
    $lw_cf7_fields_saved_tipo_contratto = (isset($options['lw_cf7_fields']['tipo_contratto'])) ? sanitize_text_field($options['lw_cf7_fields']['tipo_contratto']) : '';
    $lw_cf7_fields_saved_id_contratto = (isset($options['lw_cf7_fields']['id_contratto'])) ? sanitize_text_field($options['lw_cf7_fields']['id_contratto']) : '';

    $plugin_cf7 = new Lw_All_In_One_Cf7($this->get_plugin_name(), $this->get_version());

    if ($lw_cf7_fields_saved_save_cf7_subm == 'on' && ($lw_cf7_fields_saved_tipo_contratto == '' || $lw_cf7_fields_saved_id_contratto == '')) {
      $this->loader->add_action('admin_notices', $plugin_cf7, 'lw_all_in_one_cf7_packet_notice');
    }

    $this->loader->add_action('admin_menu', $plugin_cf7, 'lw_all_in_one_cf7_admin_submenu', 99);
    $this->loader->add_filter('set-screen-option', $plugin_cf7, 'lw_all_in_one_cf7_screen_options', 10, 3);
    $this->loader->add_action('load-lw-aio-options_page_lw_all_in_one_cf7', $plugin_cf7, 'lw_all_in_one_cf7_set_screen_options');

    if ($this->check_plugin_options('lw_cf7_fields', 'save_cf7_subm') === 'on') {
      $this->loader->add_action('wpcf7_before_send_mail', $plugin_cf7, 'lw_all_in_one_cf7_to_db');
    }

    $this->loader->add_action('admin_init', $plugin_cf7, 'lw_all_in_one_old_cf7_is_active_deactivate');
  }

  private function define_privacy_policy_hooks() {

    $plugin_privacy_policy = new Lw_All_In_One_Privacy_Policy_Pages($this->get_plugin_name(), $this->get_version());
    $this->loader->add_action('wp_ajax_lw_all_in_one_create_privacy_pages', $plugin_privacy_policy, 'lw_all_in_one_create_privacy_pages');

    $this->loader->add_action('admin_menu', $plugin_privacy_policy, 'lw_all_in_one_privacy_policy_admin_menu', 99);

    // $this->loader->add_action('admin_init', $plugin_privacy_policy, 'lw_all_in_one_remove_italy_cookie_choices');

  }

  public function run() {
    $this->loader->run();
  }

  public function get_plugin_name() {
    return $this->plugin_name;
  }

  public function get_loader() {
    return $this->loader;
  }

  public function get_version() {
    return $this->version;
  }

  private function lw_all_in_one_schedule_single_event() {

    $lw_all_in_one_version = get_option('lw_all_in_one_version', '1.0.0');
    if (version_compare($lw_all_in_one_version,  LW_ALL_IN_ONE_VERSION) < 0) {
      if (!wp_next_scheduled('lw_all_in_one_single_event')) {
        wp_schedule_single_event(time() + 60, 'lw_all_in_one_single_event', array($lw_all_in_one_version));
      }
      add_action('lw_all_in_one_single_event', array(__CLASS__, 'lw_all_in_one_single_event_run'));
    } else {
      if (wp_next_scheduled('lw_all_in_one_single_event')) {
        wp_clear_scheduled_hook('lw_all_in_one_single_event');
      }
    }
  }

  public static function lw_all_in_one_single_event_run($lw_all_in_one_version) {

    if (version_compare($lw_all_in_one_version, '1.8.2') <= 0) {
      // Update translation files
      // If translation files do not exist under /wp-content/languages/plugins/, copy them from the plugin directory /languages/
      $translated_locales = array('es_ES', 'it_IT');
      foreach ($translated_locales as $locale) {
        if (!file_exists(WP_LANG_DIR . '/plugins/lw_all_in_one-'.$locale.'.mo')) {
          copy(dirname(LW_ALL_IN_ONE_PLUGIN_MAIN_FILE) . '/languages/lw_all_in_one-'.$locale.'.po', WP_LANG_DIR . '/plugins/lw_all_in_one-'.$locale.'.po');
          copy(dirname(LW_ALL_IN_ONE_PLUGIN_MAIN_FILE) . '/languages/lw_all_in_one-'.$locale.'.mo', WP_LANG_DIR . '/plugins/lw_all_in_one-'.$locale.'.mo');
        }
      }
    }

    if (version_compare($lw_all_in_one_version, '1.8') <= 0) {
      $options = get_option('lw_all_in_one');
      if (!isset($options['ck_activate'])) {

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
        $options['ck_activate'] = '';
        $options['ck_fields']['banner_position'] = 'bottom';
        $options['ck_fields']['ck_page_slug'] = $ck_fields['ck_page_slug'];
        $options['ck_fields']['primary_color'] = '#18a300';
        $options['ck_fields']['secondary_color'] = '#333333';
        $options['ck_fields']['heading_message'] = $ck_fields['heading_message'];
        $options['ck_fields']['gdpr_message'] = $ck_fields['gdpr_message'];
        $options['ck_fields']['about_ck_message'] = $ck_fields['about_ck_message'];
        update_option('lw_all_in_one', $options);
      }

      // Exclude from cache 'lwaio_*' cookies
      update_option( 'WpFastestCacheExclude', json_encode([["prefix" => "contain", "content" => "lwaio_", "type" => "cookie"]]));
    }

    update_option('lw_all_in_one_version', LW_ALL_IN_ONE_VERSION);
  }

  private function lw_all_in_one_schedule_data_retention() {
    if ($this->check_plugin_options('lw_aio_fields', 'data_retention') === 'on') {
      if (!wp_next_scheduled('lw_all_in_one_data_retention')) {
        wp_schedule_event(time(), 'daily', 'lw_all_in_one_data_retention');
      }
      add_action('lw_all_in_one_data_retention', array(__CLASS__, 'lw_all_in_one_data_retention_run'));
    } else {
      if (wp_next_scheduled('lw_all_in_one_data_retention')) {
        wp_clear_scheduled_hook('lw_all_in_one_data_retention');
      }
    }
  }

  public static function lw_all_in_one_data_retention_run() {
    global $wpdb;
    $cf7_table = $wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE;

    $wpdb->query(" DELETE FROM $cf7_table WHERE DATE(time) < DATE_SUB(DATE(NOW()), INTERVAL 14 DAY) ");
  }

  public function lw_all_in_one_5_min_schedule($schedules) {
    $schedules['lw_all_in_one_every_5_min_schedule'] = array(
      'interval' => 300,
      'display' => __('Every 5 Minutes', 'lw_all_in_one'),
    );
    return $schedules;
  }

  private function lw_all_in_one_schedule_cf7_sync() {
    if ($this->check_plugin_options('lw_cf7_fields', 'save_cf7_subm') === 'on') {
      add_filter('cron_schedules', array($this, 'lw_all_in_one_5_min_schedule'));
      if (!wp_next_scheduled('lw_all_in_one_cf7_sync')) {
        wp_schedule_event(time(), 'lw_all_in_one_every_5_min_schedule', 'lw_all_in_one_cf7_sync');
      }
      add_action('lw_all_in_one_cf7_sync', array(__CLASS__, 'lw_all_in_one_cf7_sync_run'));
    } else {
      if (wp_next_scheduled('lw_all_in_one_cf7_sync')) {
        wp_clear_scheduled_hook('lw_all_in_one_cf7_sync');
      }
    }
  }

  public static function lw_all_in_one_cf7_sync_run() {
    global $wpdb;
    $cf7_table = $wpdb->prefix . LW_ALL_IN_ONE_CF7_TABLE;
    $select_nn_inviato = $wpdb->get_row("SELECT * FROM " . $cf7_table . " WHERE sent !='Si'");
    if ($select_nn_inviato !== null) {
      $re_invia = array();
      $re_invia['nome'] = sanitize_text_field($select_nn_inviato->name);
      $re_invia['cognome'] = sanitize_text_field($select_nn_inviato->surname);
      $re_invia['email'] = sanitize_email($select_nn_inviato->email);
      $re_invia['telefono'] = sanitize_text_field($select_nn_inviato->phone);
      $re_invia['soggetto'] = sanitize_text_field($select_nn_inviato->subject);
      $re_invia['messaggio'] = sanitize_textarea_field($select_nn_inviato->message);
      $re_invia['tipo_Contratto'] = sanitize_text_field($select_nn_inviato->tipo_Contratto);
      $re_invia['id_Contratto'] = sanitize_text_field($select_nn_inviato->id_Contratto);
      $re_invia['submited_page'] = esc_url_raw($select_nn_inviato->submited_page);

      $json_re_invia = json_encode($re_invia);
      $args = array(
        'body' => $json_re_invia,
        'timeout' => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'cookies' => array(),
      );
      $send = wp_remote_post('https://localwebapi.ids.al/contactFormWeb', $args);
      $ret_body = wp_remote_retrieve_body($send);
      $data = recursive_sanitize_array_object(json_decode($ret_body));

      if ($data->response == "OK") {
        $inviato = "Si";
        $id = $select_nn_inviato->id;
        $wpdb->update(
          $cf7_table,
          array(
            'sent' => $inviato,
          ),
          array('id' => $id)
        );
      }
    }
  }

  public function check_plugin_options($parent_key = false, $key = false) {
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
}
