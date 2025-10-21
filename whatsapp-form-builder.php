<?php
/**
 * Plugin Name: WhatsApp Form Builder
 * Plugin URI: https://github.com/hindiadobe-png/whatsapp-form-builder
 * Description: A drag-and-drop WhatsApp form builder for WordPress that allows users to create customizable WhatsApp contact forms with reCAPTCHA support and structured message sending.
 * Version: 1.0.0
 * Author: Hindi Adobe
 * Author URI: https://github.com/hindiadobe-png
 * Text Domain: whatsapp-form-builder
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WAFB_VERSION', '1.0.0');
define('WAFB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WAFB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WAFB_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Require the autoloader
require_once WAFB_PLUGIN_DIR . 'includes/class-whatsapp-form-builder.php';

/**
 * Main instance of WhatsApp Form Builder
 */
function WAFB() {
    return WhatsApp_Form_Builder::instance();
}

// Initialize the plugin
WAFB();
