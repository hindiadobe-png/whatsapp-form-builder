<?php
/**
 * Plugin Name: WhatsApp Form Builder
 * Plugin URI: https://github.com/hindiadobe-png/whatsapp-form-builder
 * Description: A drag-and-drop WhatsApp form builder plugin for WordPress that allows users to create customizable WhatsApp contact forms with advanced features.
 * Version: 1.0.0
 * Author: HindiAdobe
 * Author URI: https://github.com/hindiadobe-png
 * Text Domain: whatsapp-form-builder
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('WFBP_VERSION', '1.0.0');
define('WFBP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WFBP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WFBP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WFBP_PLUGIN_FILE', __FILE__);

// Include required files
require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-activator.php';
require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-deactivator.php';
require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-loader.php';
require_once WFBP_PLUGIN_DIR . 'includes/class-wfbp-main.php';

/**
 * Activation hook
 */
function activate_wfbp() {
    WFBP_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_wfbp');

/**
 * Deactivation hook
 */
function deactivate_wfbp() {
    WFBP_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_wfbp');

/**
 * Initialize the plugin
 */
function run_wfbp() {
    $plugin = new WFBP_Main();
    $plugin->run();
}
run_wfbp();
