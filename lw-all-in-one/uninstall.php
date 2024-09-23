<?php

/**
 * Fired when the plugin is uninstalled.
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

//Plugin options
$options = get_option('lw_all_in_one');

if (isset($options['lw_aio_fields']['delete_data']) && $options['lw_aio_fields']['delete_data'] === 'on') {
  global $wpdb;

  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lw_aio_a_events");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}lw_aio_cf7");

  delete_option('lw_all_in_one');
  delete_option('lw_all_in_one_version');
  delete_option('lw_all_in_one_privacy_pages');
  delete_option('lw_all_in_one_ga_custom_events');
  delete_option('lw_all_in_one_purified_css');

  if (wp_next_scheduled('lw_all_in_one_data_retention')) {
    wp_clear_scheduled_hook( 'lw_all_in_one_data_retention' );
  }
  if (wp_next_scheduled('lw_all_in_one_cf7_sync')) {
    wp_clear_scheduled_hook( 'lw_all_in_one_cf7_sync' );
  }

  if (is_plugin_active('wp-fastest-cache/wpFastestCache.php')) {
    if(isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')){
      $GLOBALS['wp_fastest_cache']->deleteCache(true);
    }
  }

  wp_cache_flush();
}