<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/includes
 * @author     sajdoko <sajmir.doko@localweb.it>
 */
class Lw_All_In_One_Deactivator {

  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   */
  public static function deactivate() {

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

  }

}
