<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://localweb.it/
 * @package           Lw_All_In_One
 *
 * @wordpress-plugin
 * Plugin Name:       LocalWeb All In One
 * Description:       LocalWeb All In One should be installed only on websites created by Local Web S.R.L, because it extends certain functionalities of the website which may send certain data to LocalWeb's servers. This is to make possible showing data on LocalWeb App.
 * Version:           1.8.2
 * Author:            Local Web S.R.L
 * Author URI:        https://localweb.it/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lw_all_in_one
 * Domain Path:       /languages
 * Plugin URI:        https://wordpress.org/plugins/lw-all-in-one/
 * GitHub URI:        https://github.com/sajdoko/lw-all-in-one
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

define('LW_ALL_IN_ONE_VERSION', '1.8.2');
define('LW_ALL_IN_ONE_PLUGIN', plugin_basename(__FILE__));
define('LW_ALL_IN_ONE_PLUGIN_MAIN_FILE', __FILE__);

/**
 * Define plugin custom table names.
 */
define('LW_ALL_IN_ONE_A_EVENTS_TABLE', 'lw_aio_a_events');
define('LW_ALL_IN_ONE_CF7_TABLE', 'lw_aio_cf7');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-lw-all-in-one-activator.php
 */
function activate_lw_all_in_one() {
  require_once plugin_dir_path(__FILE__) . 'includes/class-lw-all-in-one-activator.php';
  Lw_All_In_One_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-lw-all-in-one-deactivator.php
 */
function deactivate_lw_all_in_one() {
  require_once plugin_dir_path(__FILE__) . 'includes/class-lw-all-in-one-deactivator.php';
  Lw_All_In_One_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_lw_all_in_one');
register_deactivation_hook(__FILE__, 'deactivate_lw_all_in_one');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-lw-all-in-one.php';

/**
 * Begins execution of the plugin.
 *
 */
function run_lw_all_in_one() {

  $plugin = new Lw_All_In_One();
  $plugin->run();

}
run_lw_all_in_one();
