<?php
/**
 * Plugin Name: WhatsApp Form Builder
 * Plugin URI: https://github.com/hindiadobe-png/whatsapp-form-builder
 * Description: A drag-and-drop form builder that integrates with WhatsApp for sending form submissions.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://github.com/hindiadobe-png
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: whatsapp-form-builder
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin constants
define( 'WAFB_VERSION', '1.0.0' );
define( 'WAFB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WAFB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WAFB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_whatsapp_form_builder() {
    require_once WAFB_PLUGIN_DIR . 'includes/class-wafb-activator.php';
    WAFB_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_whatsapp_form_builder() {
    require_once WAFB_PLUGIN_DIR . 'includes/class-wafb-deactivator.php';
    WAFB_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_whatsapp_form_builder' );
register_deactivation_hook( __FILE__, 'deactivate_whatsapp_form_builder' );

/**
 * The core plugin class.
 */
require_once WAFB_PLUGIN_DIR . 'includes/class-wafb-core.php';

/**
 * Begins execution of the plugin.
 */
function run_whatsapp_form_builder() {
    $plugin = new WAFB_Core();
    $plugin->run();
}

run_whatsapp_form_builder();
