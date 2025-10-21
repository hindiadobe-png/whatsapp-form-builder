<?php
/**
 * Shortcode functionality
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Shortcode {
    
    /**
     * Register shortcode
     */
    public function register_shortcode() {
        add_shortcode('whatsapp_form', array($this, 'render_shortcode'));
    }
    
    /**
     * Render shortcode
     * 
     * Usage: [whatsapp_form id="1"]
     */
    public function render_shortcode($atts) {
        // Extract attributes
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'whatsapp_form');
        
        $form_id = intval($atts['id']);
        
        if (!$form_id) {
            return '<p>' . esc_html__('Please provide a valid form ID.', 'whatsapp-form-builder') . '</p>';
        }
        
        // Render form
        return WFBP_Form::render_form($form_id);
    }
}
