<?php
/**
 * Admin forms page
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Admin_Forms {
    
    /**
     * Render forms list page
     */
    public function render() {
        $forms = WFBP_Form::get_all_forms();
        
        include_once WFBP_PLUGIN_DIR . 'admin/views/forms-list.php';
    }
    
    /**
     * Render form builder
     */
    public function render_form_builder($form_id = 0) {
        $form = null;
        $form_name = '';
        $form_fields = array();
        $form_settings = array();
        
        if ($form_id) {
            $form = WFBP_Form::get_form($form_id);
            if ($form) {
                $form_name = $form->form_name;
                $form_fields = $form->form_fields;
                $form_settings = $form->form_settings;
            }
        }
        
        // Default settings
        if (empty($form_settings)) {
            $form_settings = array(
                'phone_number' => '',
                'button_text' => __('Send to WhatsApp', 'whatsapp-form-builder'),
                'enable_recaptcha' => false,
                'success_message' => __('Thank you! Your message has been sent.', 'whatsapp-form-builder'),
            );
        }
        
        // Available field types
        $field_types = array(
            'text' => __('Text', 'whatsapp-form-builder'),
            'email' => __('Email', 'whatsapp-form-builder'),
            'phone' => __('Phone', 'whatsapp-form-builder'),
            'tel' => __('Telephone', 'whatsapp-form-builder'),
            'textarea' => __('Textarea', 'whatsapp-form-builder'),
            'select' => __('Select', 'whatsapp-form-builder'),
            'radio' => __('Radio', 'whatsapp-form-builder'),
            'checkbox' => __('Checkbox', 'whatsapp-form-builder'),
            'number' => __('Number', 'whatsapp-form-builder'),
            'url' => __('URL', 'whatsapp-form-builder'),
            'date' => __('Date', 'whatsapp-form-builder'),
        );
        
        include_once WFBP_PLUGIN_DIR . 'admin/views/form-builder.php';
    }
}
