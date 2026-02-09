<?php
/**
 * Plugin Name: Hamnaghsheh Ticketing
 * Plugin URI: https://github.com/soroushyasini/hamnaghsheh-ticketing
 * Description: Comprehensive ticketing system for Hamnaghsheh WordPress plugin
 * Version: 1.0.0
 * Author: Hamnaghsheh
 * Author URI: https://hamnaghsheh.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hamnaghsheh-ticketing
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('HAMNAGHSHEH_TICKETING_VERSION', '1.0.0');
define('HAMNAGHSHEH_TICKETING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HAMNAGHSHEH_TICKETING_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_hamnaghsheh_ticketing() {
    require_once HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'includes/class-activator.php';
    Hamnaghsheh_Ticketing_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_hamnaghsheh_ticketing() {
    require_once HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'includes/class-deactivator.php';
    Hamnaghsheh_Ticketing_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_hamnaghsheh_ticketing');
register_deactivation_hook(__FILE__, 'deactivate_hamnaghsheh_ticketing');

/**
 * The core plugin class.
 */
require HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'includes/class-hamnaghsheh-ticketing.php';

/**
 * Begins execution of the plugin.
 */
function run_hamnaghsheh_ticketing() {
    $plugin = new Hamnaghsheh_Ticketing();
    $plugin->run();
}
run_hamnaghsheh_ticketing();
