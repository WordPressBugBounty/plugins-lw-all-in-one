<?php
/** Plugin uninstall: respect each site's explicit data-deletion setting. */
if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}
require_once __DIR__ . '/includes/class-lw-all-in-one-security.php';
function lw_all_in_one_uninstall_site() {
  foreach (array('lw_all_in_one_security_cleanup', 'lw_all_in_one_single_event', 'lw_all_in_one_cf7_sync', 'lw_all_in_one_data_retention') as $hook) {
    wp_clear_scheduled_hook($hook);
  }
  $options = get_option('lw_all_in_one', array());
  if (($options['lw_aio_fields']['delete_data'] ?? '') !== 'on') {
    return;
  }
  global $wpdb;
  foreach (array('lw_aio_a_events', 'lw_aio_cf7', 'lw_aio_security') as $table) {
    // Identifiers are fixed plugin table names plus the trusted WordPress prefix.
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}$table");
  }
  foreach (array('lw_all_in_one', 'lw_all_in_one_version', 'lw_all_in_one_cf7_schema', 'lw_all_in_one_security_schema', 'lw_all_in_one_cf7_legacy_cutoff', 'lw_all_in_one_cf7_circuit', 'lw_all_in_one_cf7_sync_lock', 'lw_all_in_one_privacy_pages', 'lw_all_in_one_ga_custom_events', 'lw_all_in_one_purified_css', 'lw_all_in_one_legacy_cleanup') as $option) {
    delete_option($option);
  }
  Lw_All_In_One_Security::clear_caches();
}
if (is_multisite()) {
  $offset = 0;
  do {
    $sites = get_sites(array('fields' => 'ids', 'number' => 100, 'offset' => $offset));
    foreach ($sites as $site_id) {
      switch_to_blog($site_id);
      lw_all_in_one_uninstall_site();
      restore_current_blog();
    }
    $offset += 100;
  } while (count($sites) === 100);
} else {
  lw_all_in_one_uninstall_site();
}
