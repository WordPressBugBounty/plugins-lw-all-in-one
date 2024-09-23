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
      wp_register_script($this->plugin_name . '-bts', plugin_dir_url(__FILE__) . 'js/bootstrap.min.js', array('jquery'), $this->version, true);
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
        'lwaio_category_name' => __('Necessary', 'lw_all_in_one'),
        'lwaio_category_slug' => 'necessary',
        'lwaio_category_description' => __('Necessary cookies help make a website usable by enabling basic functions such as page navigation and access to protected areas of the site. The website cannot function properly without these cookies.', 'lw_all_in_one'),
      ],
      [
        'id_lwaio_category' => 2,
        'lwaio_category_name' => __('Preferences', 'lw_all_in_one'),
        'lwaio_category_slug' => 'preferences',
        'lwaio_category_description' => __('Preference cookies allow a website to remember information that changes the way the website behaves or appears, such as your preferred language or the region you are in.', 'lw_all_in_one'),
      ],
      [
        'id_lwaio_category' => 3,
        'lwaio_category_name' => __('Analytics', 'lw_all_in_one'),
        'lwaio_category_slug' => 'analytics',
        'lwaio_category_description' => __('Analytical cookies help website owners understand how visitors interact with sites by collecting and reporting information anonymously.', 'lw_all_in_one'),
      ],
      [
        'id_lwaio_category' => 4,
        'lwaio_category_name' => __('Marketing', 'lw_all_in_one'),
        'lwaio_category_slug' => 'marketing',
        'lwaio_category_description' => __('Marketing cookies are used to track visitors to websites. The intention is to display ads that are relevant and engaging to the individual user and therefore more valuable to publishers and third-party advertisers.', 'lw_all_in_one'),
      ],
    ];
    $cookies = [
      [
        'name' => 'lwaio_consent_acted',
        'category' => 'necessary',
        'domain' => str_replace(array('http://', 'https://'), '', esc_url(home_url())),
        'duration' => __('1 Year', 'lw_all_in_one'),
        'type' => 'HTTP',
        'description' => __('Used to dettermine if user has taken action on the consent banner.', 'lw_all_in_one'),
      ],
      [
        'name' => 'lwaio_consent_preferences',
        'category' => 'necessary',
        'domain' => str_replace(array('http://', 'https://'), '', esc_url(home_url())),
        'duration' => __('1 Year', 'lw_all_in_one'),
        'type' => 'HTTP',
        'description' => __('Cookie consent preferences.', 'lw_all_in_one'),
      ],
    ];

    if (get_option("wpcf7")['recaptcha'] ?? false) {
      array_push($cookies, [
        'name' => '_grecaptcha',
        'category' => 'necessary',
        'domain' => '.' . str_replace(array('http://', 'https://'), '', esc_url(home_url())),
        'duration' => 'persistent',
        'type' => 'HTML',
        'description' => __('This cookie is set by reCAPTCHA. The cookie is used to between humans and bots and store the user\'s consent for cookies.', 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'rc::b',
        'category' => 'necessary',
        'domain' => 'https://www.google.com',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __('This cookie is used to distinguish between humans and bots.', 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'rc::c',
        'category' => 'necessary',
        'domain' => 'https://www.google.com',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __('This cookie is used to distinguish between humans and bots.', 'lw_all_in_one'),
      ]);
    }
    $ga_fields_tracking_id = (isset($this->options['ga_fields']['tracking_id'])) ? sanitize_text_field($this->options['ga_fields']['tracking_id']) : '';
    $ga_activate = (isset($this->options['ga_activate'])) ? $this->options['ga_activate'] : '';
    if ($ga_activate === 'on' && $ga_fields_tracking_id !== '') {
      array_push($cookies, [
        'name' => '_ga',
        'category' => 'analytics',
        'domain' => '.' . str_replace(array('http://', 'https://'), '', esc_url(home_url())),
        'duration' => __('2 Years', 'lw_all_in_one'),
        'type' => 'HTTP',
        'description' => __('This cookie is installed by Google Analytics. The cookie is used to calculate visitor, session, campaign data and keep track of site usage for the site\'s analytics report. The cookies store information anonymously and assign a randomly generated number to identify unique visitors.', 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => '_ga_#',
        'category' => 'analytics',
        'domain' => '.' . str_replace(array('http://', 'https://'), '', esc_url(home_url())),
        'duration' => __('2 Years', 'lw_all_in_one'),
        'type' => 'HTTP',
        'description' => __('Used by Google Analytics to collect data on the number of times a user has visited the website as well as dates for the first and most recent visit.', 'lw_all_in_one'),
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
        'description' => __("Used by WIM to determine the user's geographic positioning.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--latitude',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM to determine the user's geographic positioning.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--country_name',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM to determine the user's geographic positioning.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--country_code',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM to determine the user's geographic positioning.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--ip_address',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM to determine the user's geographic positioning.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--widget_chat_length',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--user_id',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--user_name',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--request_id',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsCookie',
        'category' => 'necessary',
        'domain' => 'www.localweb.it',
        'duration' => 'persistent',
        'type' => 'HTML',
        'description' => __("Used by WIM for chat operation.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-jsSession--page_before_refresh',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Record the user's current browsing URL.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-current_url',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Record the user's current browsing URL.", 'lw_all_in_one'),
      ]);
      array_push($cookies, [
        'name' => 'ultimate_support_chat-ref_url',
        'category' => 'analytics',
        'domain' => 'www.localweb.it',
        'duration' => 'session',
        'type' => 'HTML',
        'description' => __("Record the user's referral URL.", 'lw_all_in_one'),
      ]);
    }

    $preference_cookies = isset($_COOKIE['lwaio_consent_preferences']) ? json_decode(stripslashes(sanitize_text_field(wp_unslash($_COOKIE['lwaio_consent_preferences']))), true) : '';
    $viewed_cookie = isset($_COOKIE['lwaio_consent_acted']) ? sanitize_text_field(wp_unslash($_COOKIE['lwaio_consent_acted'])) : '';
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
      lwaios_list = '<?php echo str_replace("'", "\'", wp_json_encode($categories_json_data)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                      ?>';
      lwaiobar_settings = '<?php echo wp_json_encode($this->lwaiobar_settings); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                            ?>';
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
      'cookie_expiry'                   => __('1 Year', 'lw_all_in_one'),
      'opacity'                         => '1',
      'animate_speed_hide'              => 0,
      'animate_speed_show'              => 0,
      'ck_page_slug'                    => 'cookie-policy',
      'heading_message'                 => '',
      'gdpr_message'                    => '',
      'about_ck_message'                => '',

      'button_accept_text'              => __('Accept Selected', 'lw_all_in_one'),
      'button_accept_text_all'          => __('Accept All Cookies', 'lw_all_in_one'),
      'button_readmore_text'            => __('Read more', 'lw_all_in_one'),
      'button_decline_text'             => __('Refuse', 'lw_all_in_one'),
      'button_settings_text'            => __('Cookie Info', 'lw_all_in_one'),
      'button_confirm_text'             => __('Confirm', 'lw_all_in_one'),
      'button_cancel_text'              => __('Cancel', 'lw_all_in_one'),
      'show_again_text'                 => __('Cookie Settings', 'lw_all_in_one'),
      'no_cookies_in_cat'               => __('We do not use cookies of this type.', 'lw_all_in_one'),
      'tab_1_label'                     => __('Cookie statement', 'lw_all_in_one'),
      'tab_2_label'                     => __('Information about cookies', 'lw_all_in_one'),

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
    $ga_activate = (isset($this->options['ga_activate'])) ? $this->options['ga_activate'] : '';
    $ga_fields_tracking_id = (isset($this->options['ga_fields']['tracking_id'])) ? sanitize_text_field($this->options['ga_fields']['tracking_id']) : '';
    $ga_fields_save_ga_events = (isset($this->options['ga_fields']['save_ga_events'])) ? sanitize_text_field($this->options['ga_fields']['save_ga_events']) : '';
    $ga_fields_monitor_email_link = (isset($this->options['ga_fields']['monitor_email_link'])) ? sanitize_text_field($this->options['ga_fields']['monitor_email_link']) : '';
    $ga_fields_monitor_tel_link = (isset($this->options['ga_fields']['monitor_tel_link'])) ? sanitize_text_field($this->options['ga_fields']['monitor_tel_link']) : '';
    $ga_fields_monitor_form_submit = (isset($this->options['ga_fields']['monitor_form_submit'])) ? sanitize_text_field($this->options['ga_fields']['monitor_form_submit']) : '';

    $preference_cookies = isset($_COOKIE['lwaio_consent_preferences']) ? json_decode(stripslashes(sanitize_text_field(wp_unslash($_COOKIE['lwaio_consent_preferences']))), true) : '';
    $ad_user_data = isset($preference_cookies['marketing']) && $preference_cookies['marketing'] == 'yes' ? 'granted' : 'granted';
    $ad_personalization = isset($preference_cookies['marketing']) && $preference_cookies['marketing'] == 'yes' ? 'granted' : 'granted';
    $analytics_storage = isset($preference_cookies['analytics']) && $preference_cookies['analytics'] == 'yes' ? 'granted' : 'granted';
    $ad_storage = (($ad_user_data == 'granted') || ($ad_personalization == 'granted') || ($analytics_storage == 'granted')) ? 'granted' : 'granted';

    if ($ga_activate === 'on' && $ga_fields_tracking_id !== '') {
      $tag_type = explode('-', $ga_fields_tracking_id, 2)[0];
    ?>
      <script>
        let ad_user_data = '<?php echo $ad_user_data; ?>';
        let ad_personalization = '<?php echo $ad_personalization; ?>';
        let analytics_storage = '<?php echo $analytics_storage; ?>';
        let ad_storage = '<?php echo $ad_storage; ?>';
        let isGtmTag = '<?php echo $tag_type; ?>' === 'GTM';
        let gtmScriptSrc = "https://www.googletagmanager.com/gtm.js?id=<?php echo $ga_fields_tracking_id; ?>";

        window.dataLayer = window.dataLayer || [];

        function gtag() {
          dataLayer.push(arguments);
        }
        gtag('consent', 'default', {
          'ad_user_data': ad_user_data,
          'ad_personalization': ad_personalization,
          'analytics_storage': analytics_storage,
          'ad_storage': ad_storage,
          'wait_for_update': 500,
        });
        if (isGtmTag) {
          dataLayer.push({
            'gtm.start': new Date().getTime(),
            'event': 'gtm.js'
          });
        } else {
          gtag('js', new Date());
          gtag('config', '<?php echo $ga_fields_tracking_id; ?>');
          gtmScriptSrc = "https://www.googletagmanager.com/gtag/js?id=<?php echo $ga_fields_tracking_id; ?>";
        }

        window.addEventListener("LwAioCookieConsentOnAcceptAll", function(e) {
          gtag("consent", "update", {
            ad_user_data: "granted",
            ad_personalization: "granted",
            ad_storage: "granted",
            analytics_storage: "granted",
          });
          loadGtmScript(gtmScriptSrc);
        });
        window.addEventListener("LwAioCookieConsentOnAccept", function(e) {
          ad_user_data = e.detail.lwaio_consent_preferences.marketing === "yes" ? 'granted' : 'denied';
          ad_personalization = e.detail.lwaio_consent_preferences.marketing === "yes" ? 'granted' : 'denied';
          analytics_storage = e.detail.lwaio_consent_preferences.analytics === "yes" ? 'granted' : 'denied';
          ad_storage = (ad_user_data === 'granted' || ad_personalization === 'granted' || analytics_storage === 'granted') ? 'granted' : 'denied';
          gtag("consent", "update", {
            ad_user_data: ad_user_data,
            ad_personalization: ad_personalization,
            analytics_storage: analytics_storage,
            ad_storage: ad_storage,
          });
          loadGtmScript(gtmScriptSrc);
        });
        window.addEventListener("LwAioCookieConsentOnReject", function(e) {
          gtag("consent", "update", {
            ad_user_data: "denied",
            ad_personalization: "denied",
            analytics_storage: "denied",
            ad_storage: "denied",
          });
        });

        if (ad_storage === 'granted') {
          loadGtmScript(gtmScriptSrc);
        }

        function loadGtmScript(gtmScriptSrc) {
          let gtmScript = document.createElement("script");
          gtmScript.async = true;
          gtmScript.src = gtmScriptSrc;
          let firstScript = document.getElementsByTagName('script')[0];
          firstScript.parentNode.insertBefore(gtmScript, firstScript);
        }
      </script>
      <?php
        echo '<script>';
        echo 'const lwAioGaActivate=true;';
        echo 'const lwAioTrackingType="' . $tag_type . '";';
        echo ($ga_fields_save_ga_events === 'on') ? 'const lwAioSaveGaEvents=true;' : 'const lwAioSaveGaEvents=false;';
        echo ($ga_fields_monitor_email_link === 'on') ? 'const lwAioMonitorEmailLink=true;' : 'const lwAioMonitorEmailLink=false;';
        echo ($ga_fields_monitor_tel_link === 'on') ? 'const lwAioMonitorTelLink=true;' : 'const lwAioMonitorTelLink=false;';
        echo ($ga_fields_monitor_form_submit === 'on') ? 'const lwAioMonitorFormSubmit=true;' : 'const lwAioMonitorFormSubmit=false;';
        echo '</script>', PHP_EOL;
    }
  }

  public function lw_all_in_one_footer_scripts() {

    $wim_activate = (isset($this->options['wim_activate'])) ? sanitize_text_field($this->options['wim_activate']) : '';
    $wim_fields_verification_status = (isset($this->options['wim_fields']['verification_status'])) ? sanitize_text_field($this->options['wim_fields']['verification_status']) : '';
    $wim_fields_rag_soc = (isset($this->options['wim_fields']['rag_soc'])) ? sanitize_text_field($this->options['wim_fields']['rag_soc']) : '';

    if ($wim_activate === 'on') {
      if ($wim_fields_verification_status === 'verified' && $wim_fields_rag_soc !== '') {
        echo '<script type="text/javascript">
              (function(d){
                var s = d.getElementsByTagName(\'script\'),f = s[s.length-1], p = d.createElement(\'script\');
                window.WidgetId = "USC_WIDGET";
                p.type = \'text/javascript\';
                p.setAttribute(\'charset\',\'utf-8\');
                p.async = 1;
                p.id = "ultimate_support_chat";
                p.src = "//www.localweb.it/chat/widget/ultimate_chat_widget.js";
                f.parentNode.insertBefore(p, f);
              }(document));
            </script>';
        echo '<p id="rag_soc" style="display:none">';
        echo esc_js($wim_fields_rag_soc);
        echo '</p>';
      } elseif ($wim_fields_verification_status !== 'verified') {
        echo '<script type="text/javascript">
            console.log("' . esc_attr__('WIM not verified!', 'lw_all_in_one') . '");
            </script>';
      } elseif ($wim_fields_rag_soc === '') {
        echo '<script type="text/javascript">
            console.log("' . esc_attr__('Missing business name!', 'lw_all_in_one') . '");
            </script>';
      }
    }
  }

  public function lw_all_in_one_save_ga_event() {
    if (!check_ajax_referer($this->plugin_name, 'security')) {
      wp_send_json_error(__('Security is not valid!', 'lw_all_in_one'));
      die();
    }

    if (isset($_POST['action']) && $_POST['action'] == 'lw_all_in_one_save_ga_event') {
      $event_category = sanitize_text_field($_POST['event_category']);
      $event_action = sanitize_text_field($_POST['event_action']);
      $event_label = sanitize_text_field($_POST['event_label']);

      global $wpdb;
      $table = $wpdb->prefix . LW_ALL_IN_ONE_A_EVENTS_TABLE;
      $data = array('time' => current_time('mysql', 1), 'ga_category' => $event_category, 'ga_action' => $event_action, 'ga_label' => $event_label);
      $format = array('%s', '%s', '%s', '%s');
      if ($wpdb->insert($table, $data, $format)) {
        wp_send_json_success(__('Event Saved!', 'lw_all_in_one'));
      } else {
        wp_send_json_error(__('Event was not Saved!', 'lw_all_in_one'));
      }
    } else {
      wp_send_json_error(__('Action is not valid!', 'lw_all_in_one'));
    }
    die();
  }

  public function lw_all_in_one_dequeue() {
    $opt_scr_deliv = (isset($this->options['lw_cf7_fields']['opt_scr_deliv'])) ? $this->options['lw_cf7_fields']['opt_scr_deliv'] : '';

    if ($opt_scr_deliv === 'on') {
      global $post;
      if (!has_shortcode($post->post_content, 'contact-form-7')) {
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
