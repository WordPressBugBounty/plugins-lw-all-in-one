<?php

/**
 * Performance Optimization Admin Class
 *
 * Handles admin interface for performance optimization settings.
 *
 * @link       https://localweb.it/
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 * @author     Local Web S.R.L
 */

/**
 * Performance Optimization Admin Class
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 * @author     Local Web S.R.L
 */
class Lw_All_In_One_Performance_Admin {

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
   * Performance optimization instance.
   *
   * @access   private
   * @var      Lw_All_In_One_Performance    $performance    Performance instance.
   */
  private $performance;

  /**
   * Initialize the class and set its properties.
   *
   * @param  string  $plugin_name       The name of the plugin.
   * @param  string  $version    The version of the plugin.
   */
  public function __construct($plugin_name, $version) {
    $this->plugin_name = $plugin_name;
    $this->version = $version;

    // Load performance class
    if (!class_exists('Lw_All_In_One_Performance')) {
      require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-lw-all-in-one-performance.php';
    }
    $this->performance = new Lw_All_In_One_Performance($plugin_name, $version);
  }

  /**
   * Add admin menu.
   *
   * @access   public
   */
  public function add_admin_menu() {
    add_submenu_page(
      'lw_all_in_one',
      __('Performance Optimization', 'lw-all-in-one'),
      __('Performance', 'lw-all-in-one'),
      'manage_options',
      'lw-all-in-one-performance',
      array($this, 'render_admin_page')
    );
  }

  /**
   * Enqueue admin styles.
   *
   * @access   public
   * @param    string    $hook    Current admin page hook.
   */
  public function enqueue_admin_styles($hook) {
    if (strpos($hook, 'lw-all-in-one-performance') !== false) {
      $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
      wp_enqueue_style(
        $this->plugin_name . '-performance',
        plugin_dir_url(__FILE__) . 'css/lw-all-in-one-performance.css',
        array(),
        $this->version,
        'all'
      );
    }
  }

  /**
   * Register settings.
   *
   * @access   public
   */
  public function register_settings() {
    register_setting(
      'lw_all_in_one_perf_settings',
      'lw_all_in_one',
      array(
        'sanitize_callback' => array($this, 'sanitize_settings'),
      )
    );
  }

  /**
   * Sanitize settings.
   *
   * @access   public
   * @param    array    $input    Input settings.
   * @return   array              Sanitized settings.
   */
  public function sanitize_settings($input) {
    $sanitized = array();

    if (isset($input['perf_fields'])) {
      $sanitized['perf_fields'] = array();

      // Sanitize checkbox fields
      $checkbox_fields = array(
        'perf_webp',
        'perf_critical_css',
        'perf_defer_js',
        'perf_remove_emoji',
        'perf_disable_xmlrpc',
        'perf_limit_heartbeat',
        'perf_remove_version',
        'perf_remove_query_strings',
        'perf_preload',
        'perf_dns_prefetch',
        'perf_lazy_load',
        'perf_minify_html',
      );

      foreach ($checkbox_fields as $field) {
        $sanitized['perf_fields'][$field] = isset($input['perf_fields'][$field]) ? 'on' : 'off';
      }

      // Sanitize text fields
      if (isset($input['perf_fields']['webp_quality'])) {
        $sanitized['perf_fields']['webp_quality'] = absint($input['perf_fields']['webp_quality']);
        $sanitized['perf_fields']['webp_quality'] = max(50, min(100, $sanitized['perf_fields']['webp_quality']));
      }

      if (isset($input['perf_fields']['no_defer_scripts'])) {
        $sanitized['perf_fields']['no_defer_scripts'] = sanitize_text_field($input['perf_fields']['no_defer_scripts']);
      }

      if (isset($input['perf_fields']['preload_fonts'])) {
        $sanitized['perf_fields']['preload_fonts'] = sanitize_textarea_field($input['perf_fields']['preload_fonts']);
      }

      if (isset($input['perf_fields']['dns_prefetch_domains'])) {
        $sanitized['perf_fields']['dns_prefetch_domains'] = sanitize_textarea_field($input['perf_fields']['dns_prefetch_domains']);
      }
    }

    // Merge with existing options
    $existing = get_option('lw_all_in_one', array());
    return array_merge($existing, $sanitized);
  }

  /**
   * Render admin page.
   *
   * @access   public
   */
  public function render_admin_page() {
    $options = get_option('lw_all_in_one', array());
    $perf_fields = isset($options['perf_fields']) ? $options['perf_fields'] : array();

    // Get cache stats
    $cache_stats = $this->performance->get_cache_stats();

    // Check GD library support
    $gd_supported = function_exists('imagecreatefromstring') && function_exists('imagewebp');

    // Make helper methods available to the template
    $get_checkbox_value = array($this, 'get_checkbox_value');
    $get_text_value = array($this, 'get_text_value');

    include plugin_dir_path(__FILE__) . 'partials/lw-all-in-one-admin-performance-display.php';
  }

  /**
   * Clear cache via AJAX.
   *
   * @access   public
   */
  public function clear_cache() {
    check_ajax_referer('lw_all_in_one', 'security');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(__('Permission denied.', 'lw-all-in-one'));
    }

    $this->performance->clear_all_caches();

    wp_send_json_success(array(
      'message' => __('Cache cleared successfully.', 'lw-all-in-one'),
      'stats' => $this->performance->get_cache_stats(),
    ));
  }

  /**
   * Regenerate critical CSS via AJAX.
   *
   * @access   public
   */
  public function regenerate_critical_css() {
    check_ajax_referer('lw_all_in_one', 'security');

    if (!current_user_can('manage_options')) {
      wp_send_json_error(__('Permission denied.', 'lw-all-in-one'));
    }

    $this->performance->clear_critical_css_cache();

    wp_send_json_success(array(
      'message' => __('Critical CSS cache cleared. New files will be generated on next page load.', 'lw-all-in-one'),
    ));
  }

  /**
   * Get checkbox value.
   *
   * @access   public
   * @param    array     $fields    Fields array.
   * @param    string    $key       Field key.
   * @return   string               Checkbox value.
   */
  public function get_checkbox_value($fields, $key) {
    return isset($fields[$key]) && $fields[$key] === 'on' ? 'on' : 'off';
  }

  /**
   * Get text field value.
   *
   * @access   public
   * @param    array     $fields    Fields array.
   * @param    string    $key       Field key.
   * @param    string    $default   Default value.
   * @return   string               Field value.
   */
  public function get_text_value($fields, $key, $default = '') {
    return isset($fields[$key]) ? esc_attr($fields[$key]) : $default;
  }
}
