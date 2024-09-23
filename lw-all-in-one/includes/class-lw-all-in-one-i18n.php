<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://localweb.it/
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/includes
 */

/**
 * Define the internationalization functionality.
 *
 * @package    Lw_All_In_One
 * @subpackage Lw_All_In_One/includes
 * @author     sajdoko <sajmir.doko@localweb.it>
 */
class Lw_All_In_One_i18n {

  /**
   * Load the plugin text domain for translation.
   *
   */
  public function load_plugin_textdomain() {

    load_plugin_textdomain(
      'lw_all_in_one',
      false,
      dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
    );

  }

}
