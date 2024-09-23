<?php

/**
 * The Google Analytics integration functionality of the plugin.
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 */

/**
 * The Google Analytics integration functionality of the plugin.
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/admin
 * @author     sajdoko <sajmir.doko@localweb.it>
 */
class Lw_All_In_One_Ga_Events {

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

  public function lw_all_in_one_ga_events_admin_menu() {
    add_submenu_page($this->plugin_name, __('Saved Google Analytics Events', 'lw_all_in_one'), __('Saved GA Events', 'lw_all_in_one'), 'manage_options', $this->plugin_name . '_ga_events', array($this, 'lw_all_in_one_ga_events_display_page'));
  }

  public function lw_all_in_one_ga_events_display_page() {
    include_once 'partials/lw-all-in-one-admin-ga-events-display.php';
  }

  public function lw_all_in_one_gadwp_is_active_deactivate() {
    if (is_plugin_active('google-analytics-dashboard-for-wp/gadwp.php')) {
		  global $wpdb;
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'gadwp_cache_%%'" );
			delete_option( 'gadwp_options' );
			delete_option( 'gadwp_redeemed_code' );
			delete_option( 'exactmetrics_tracking_notice');
			delete_option( 'exactmetrics_usage_tracking_last_checkin');
			delete_option( 'exactmetrics_usage_tracking_config');
      wp_clear_scheduled_hook( 'exactmetrics_usage_tracking_cron' );
      deactivate_plugins('google-analytics-dashboard-for-wp/gadwp.php');
    } elseif (is_plugin_inactive('google-analytics-dashboard-for-wp/gadwp.php')) {
		  global $wpdb;
			$sqlquery = $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'gadwp_cache_%%'" );
			delete_option( 'gadwp_options' );
			delete_option( 'gadwp_redeemed_code' );
			delete_option( 'exactmetrics_tracking_notice');
			delete_option( 'exactmetrics_usage_tracking_last_checkin');
			delete_option( 'exactmetrics_usage_tracking_config');
      wp_clear_scheduled_hook( 'exactmetrics_usage_tracking_cron' );
      delete_plugins(array('google-analytics-dashboard-for-wp/gadwp.php'));
    }
  }

  /**
   * @deprecated
   * WooCommerce Google Analytics Integration fallback notice.
   *
   * @return string
   */
  public function woocommerce_google_analytics_missing_notice() {
    trigger_error('Method ' . __METHOD__ . ' is deprecated', E_USER_DEPRECATED);
    if (!current_user_can('manage_options')) {
      return;
    }
    // Checks if WooCommerce is installed.
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
      if (!file_exists(WP_PLUGIN_DIR . '/woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php')) {
        echo '<div class="error"><p><img src="' . trailingslashit(plugin_dir_url(__FILE__)) . 'img/icon.png' . '"/> ' . sprintf(__('You have Woocommerce active. Install <strong>WooCommerce Google Analytics Integration</strong> to better track your store events. <a href="%s" title="WooCommerce Google Analytics Integration">Install Now!</a>', 'lw_all_in_one'), wp_nonce_url(admin_url('update.php?action=install-plugin&plugin=woocommerce-google-analytics-integration'), 'install-plugin_woocommerce-google-analytics-integration')) . '</p></div>';
      } else if (!in_array('woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        echo '<div class="error"><p><img src="' . trailingslashit(plugin_dir_url(__FILE__)) . 'img/icon.png' . '"/> ' . sprintf(__('You have Woocommerce active. Install <strong>WooCommerce Google Analytics Integration</strong> to better track your store events. <a href="%s" title="WooCommerce Google Analytics Integration">Activate Now!</a>', 'lw_all_in_one'), wp_nonce_url(admin_url('plugins.php?action=activate&plugin=woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php'), 'activate-plugin_woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php')) . '</p></div>';
      }
    }
  }

  public function lw_all_in_one_ga_events_screen_options( $result, $option, $value ) {
    // wp_die($value);
    return $value;
  }

  public function lw_all_in_one_ga_events_set_screen_options() {
		$option = 'per_page';
		$args = [
			'label'   => __('Number of records per page', 'lw_all_in_one'),
			'default' => 10,
			'option'  => 'records_per_page'
		];
		add_screen_option( $option, $args );
	}

}
