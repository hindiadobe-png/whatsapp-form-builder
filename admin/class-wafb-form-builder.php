<?php
/**
 * Form builder handler
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_Form_Builder Class
 */
class WAFB_Form_Builder {
    
    /**
     * Get available field types
     */
    public static function get_field_types() {
        return array(
            'text' => array(
                'label' => __('Text Field', 'whatsapp-form-builder'),
                'icon' => 'dashicons-edit',
                'description' => __('Single line text input', 'whatsapp-form-builder')
            ),
            'email' => array(
                'label' => __('Email Field', 'whatsapp-form-builder'),
                'icon' => 'dashicons-email',
                'description' => __('Email address input', 'whatsapp-form-builder')
            ),
            'phone' => array(
                'label' => __('Phone Field', 'whatsapp-form-builder'),
                'icon' => 'dashicons-phone',
                'description' => __('Phone number input', 'whatsapp-form-builder')
            ),
            'textarea' => array(
                'label' => __('Text Area', 'whatsapp-form-builder'),
                'icon' => 'dashicons-text',
                'description' => __('Multi-line text input', 'whatsapp-form-builder')
            ),
            'select' => array(
                'label' => __('Dropdown', 'whatsapp-form-builder'),
                'icon' => 'dashicons-menu-alt',
                'description' => __('Select from dropdown', 'whatsapp-form-builder')
            ),
            'radio' => array(
                'label' => __('Radio Buttons', 'whatsapp-form-builder'),
                'icon' => 'dashicons-marker',
                'description' => __('Select one option', 'whatsapp-form-builder')
            ),
            'checkbox' => array(
                'label' => __('Checkboxes', 'whatsapp-form-builder'),
                'icon' => 'dashicons-yes',
                'description' => __('Select multiple options', 'whatsapp-form-builder')
            ),
            'url' => array(
                'label' => __('URL Field', 'whatsapp-form-builder'),
                'icon' => 'dashicons-admin-links',
                'description' => __('Website URL input', 'whatsapp-form-builder')
            ),
        );
    }
}
