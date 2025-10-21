<?php
/**
 * Form management class
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Form {
    
    /**
     * Get form by ID
     */
    public static function get_form($form_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_forms';
        
        $form = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $form_id
        ));
        
        if ($form) {
            $form->form_fields = json_decode($form->form_fields, true);
            $form->form_settings = json_decode($form->form_settings, true);
        }
        
        return $form;
    }
    
    /**
     * Get all forms
     */
    public static function get_all_forms($limit = null, $offset = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_forms';
        
        $sql = "SELECT * FROM $table_name ORDER BY created_at DESC";
        if ($limit) {
            $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
        }
        
        $forms = $wpdb->get_results($sql);
        
        foreach ($forms as $form) {
            $form->form_fields = json_decode($form->form_fields, true);
            $form->form_settings = json_decode($form->form_settings, true);
        }
        
        return $forms;
    }
    
    /**
     * Save form
     */
    public static function save_form($form_id, $form_name, $form_fields, $form_settings) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_forms';
        
        $data = array(
            'form_name' => sanitize_text_field($form_name),
            'form_fields' => wp_json_encode($form_fields),
            'form_settings' => wp_json_encode($form_settings)
        );
        
        if ($form_id) {
            // Update existing form
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $form_id),
                array('%s', '%s', '%s'),
                array('%d')
            );
            return $form_id;
        } else {
            // Create new form
            $result = $wpdb->insert(
                $table_name,
                $data,
                array('%s', '%s', '%s')
            );
            return $wpdb->insert_id;
        }
    }
    
    /**
     * Delete form
     */
    public static function delete_form($form_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_forms';
        
        return $wpdb->delete(
            $table_name,
            array('id' => $form_id),
            array('%d')
        );
    }
    
    /**
     * Get form count
     */
    public static function get_form_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wfbp_forms';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }
    
    /**
     * Render form HTML
     */
    public static function render_form($form_id) {
        $form = self::get_form($form_id);
        
        if (!$form) {
            return '<p>' . esc_html__('Form not found.', 'whatsapp-form-builder') . '</p>';
        }
        
        $settings = $form->form_settings;
        $fields = $form->form_fields;
        
        // Apply filter for custom rendering
        $html = apply_filters('wfbp_before_form_render', '', $form);
        
        // Start form
        $html .= '<form class="wfbp-form" data-form-id="' . esc_attr($form_id) . '" method="post">';
        $html .= wp_nonce_field('wfbp_submit_form', 'wfbp_nonce', true, false);
        
        // Render fields
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $html .= self::render_field($field);
            }
        }
        
        // Submit button
        $button_text = isset($settings['button_text']) ? $settings['button_text'] : __('Send to WhatsApp', 'whatsapp-form-builder');
        $html .= '<button type="submit" class="wfbp-submit-btn">' . esc_html($button_text) . '</button>';
        
        // reCAPTCHA
        if (isset($settings['enable_recaptcha']) && $settings['enable_recaptcha']) {
            $site_key = get_option('wfbp_recaptcha_site_key', '');
            if ($site_key) {
                $html .= '<div class="g-recaptcha" data-sitekey="' . esc_attr($site_key) . '"></div>';
            }
        }
        
        $html .= '</form>';
        
        // Apply filter after rendering
        $html = apply_filters('wfbp_after_form_render', $html, $form);
        
        return $html;
    }
    
    /**
     * Render individual field
     */
    private static function render_field($field) {
        $type = isset($field['type']) ? $field['type'] : 'text';
        $label = isset($field['label']) ? $field['label'] : '';
        $name = isset($field['name']) ? $field['name'] : '';
        $placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
        $required = isset($field['required']) && $field['required'] ? 'required' : '';
        $hint = isset($field['hint']) ? $field['hint'] : '';
        
        $html = '<div class="wfbp-field wfbp-field-' . esc_attr($type) . '">';
        $html .= '<label>' . esc_html($label);
        if ($required) {
            $html .= ' <span class="required">*</span>';
        }
        $html .= '</label>';
        
        switch ($type) {
            case 'textarea':
                $html .= '<textarea name="' . esc_attr($name) . '" placeholder="' . esc_attr($placeholder) . '" ' . $required . '></textarea>';
                break;
            case 'select':
                $html .= '<select name="' . esc_attr($name) . '" ' . $required . '>';
                if (isset($field['options']) && is_array($field['options'])) {
                    foreach ($field['options'] as $option) {
                        $html .= '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                    }
                }
                $html .= '</select>';
                break;
            case 'radio':
            case 'checkbox':
                if (isset($field['options']) && is_array($field['options'])) {
                    foreach ($field['options'] as $option) {
                        $html .= '<label class="wfbp-option-label">';
                        $html .= '<input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" value="' . esc_attr($option) . '" ' . $required . '>';
                        $html .= esc_html($option);
                        $html .= '</label>';
                    }
                }
                break;
            default:
                $html .= '<input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" placeholder="' . esc_attr($placeholder) . '" ' . $required . '>';
        }
        
        if ($hint) {
            $html .= '<span class="wfbp-hint">' . esc_html($hint) . '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
