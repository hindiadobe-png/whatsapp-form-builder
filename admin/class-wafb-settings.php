<?php
/**
 * Settings handler
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_Settings Class
 */
class WAFB_Settings {
    
    /**
     * Get settings tabs
     */
    public static function get_tabs() {
        return array(
            'general' => __('General', 'whatsapp-form-builder'),
            'recaptcha' => __('reCAPTCHA', 'whatsapp-form-builder'),
            'license' => __('License', 'whatsapp-form-builder'),
        );
    }
}
