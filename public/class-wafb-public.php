<?php
/**
 * Public functionality
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_Public Class
 */
class WAFB_Public {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Enqueue public assets
     */
    public function enqueue_assets() {
        // Register CSS
        wp_register_style('wafb-public', WAFB_PLUGIN_URL . 'assets/css/public.css', array(), WAFB_VERSION);
        
        // Register JS
        wp_register_script('wafb-public', WAFB_PLUGIN_URL . 'assets/js/public.js', array('jquery'), WAFB_VERSION, true);
        
        // Localize script
        wp_localize_script('wafb-public', 'wafbPublic', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'i18n' => array(
                'submitting' => __('Submitting...', 'whatsapp-form-builder'),
                'success' => __('Success!', 'whatsapp-form-builder'),
                'error' => __('An error occurred', 'whatsapp-form-builder'),
            )
        ));
    }
}
