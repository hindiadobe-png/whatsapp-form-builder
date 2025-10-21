<?php
/**
 * Internationalization functionality
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_i18n {
    
    /**
     * Load the plugin text domain for translation
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'whatsapp-form-builder',
            false,
            dirname(WFBP_PLUGIN_BASENAME) . '/languages/'
        );
    }
}
