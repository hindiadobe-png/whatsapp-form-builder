<?php
/**
 * Shortcodes
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_Shortcodes Class
 */
class WAFB_Shortcodes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('whatsapp_form', array($this, 'render_form'));
    }
    
    /**
     * Render form shortcode
     */
    public function render_form($atts) {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts);
        
        $form_id = intval($atts['id']);
        
        if ($form_id <= 0) {
            return '<p>' . __('Invalid form ID', 'whatsapp-form-builder') . '</p>';
        }
        
        // Get form from database
        global $wpdb;
        $table = $wpdb->prefix . 'wafb_forms';
        $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d AND status = 'active'", $form_id), ARRAY_A);
        
        if (!$form) {
            return '<p>' . __('Form not found', 'whatsapp-form-builder') . '</p>';
        }
        
        $fields = json_decode($form['fields'], true);
        $settings = json_decode($form['settings'], true);
        
        // Enqueue styles and scripts
        wp_enqueue_style('wafb-public');
        wp_enqueue_script('wafb-public');
        
        // Enqueue reCAPTCHA if enabled
        if (!empty($settings['recaptcha_enabled']) && $settings['recaptcha_enabled']) {
            $site_key = get_option('wafb_recaptcha_site_key');
            if (!empty($site_key)) {
                wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
            }
        }
        
        // Start output buffering
        ob_start();
        
        // Get RTL setting
        $rtl_enabled = !empty($settings['rtl_enabled']) && $settings['rtl_enabled'];
        $rtl_class = $rtl_enabled ? 'wafb-rtl' : '';
        
        // Get custom CSS
        $custom_css = !empty($settings['custom_css']) ? $settings['custom_css'] : '';
        
        ?>
        <div class="wafb-form-wrapper <?php echo esc_attr($rtl_class); ?>" id="wafb-form-<?php echo esc_attr($form_id); ?>">
            <?php if (!empty($custom_css)): ?>
                <style><?php echo wp_kses_post($custom_css); ?></style>
            <?php endif; ?>
            
            <form class="wafb-form" data-form-id="<?php echo esc_attr($form_id); ?>">
                <?php wp_nonce_field('wafb_form_submit', 'wafb_nonce'); ?>
                <input type="hidden" name="form_id" value="<?php echo esc_attr($form_id); ?>">
                <input type="hidden" name="page_url" value="<?php echo esc_url(get_permalink()); ?>">
                
                <?php if (!empty($settings['form_title'])): ?>
                    <h2 class="wafb-form-title"><?php echo esc_html($settings['form_title']); ?></h2>
                <?php endif; ?>
                
                <?php if (!empty($settings['form_description'])): ?>
                    <p class="wafb-form-description"><?php echo wp_kses_post($settings['form_description']); ?></p>
                <?php endif; ?>
                
                <div class="wafb-form-fields">
                    <?php foreach ($fields as $field): ?>
                        <?php $this->render_field($field); ?>
                    <?php endforeach; ?>
                </div>
                
                <?php if (!empty($settings['recaptcha_enabled']) && $settings['recaptcha_enabled']): ?>
                    <?php $site_key = get_option('wafb_recaptcha_site_key'); ?>
                    <?php if (!empty($site_key)): ?>
                        <div class="wafb-form-field wafb-recaptcha-field">
                            <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <div class="wafb-form-field wafb-submit-field">
                    <button type="submit" class="wafb-submit-btn">
                        <?php echo !empty($settings['submit_text']) ? esc_html($settings['submit_text']) : __('Send to WhatsApp', 'whatsapp-form-builder'); ?>
                    </button>
                </div>
                
                <div class="wafb-form-message"></div>
            </form>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render individual field
     */
    private function render_field($field) {
        $field_type = $field['type'];
        $field_name = $field['name'];
        $field_label = $field['label'];
        $field_required = isset($field['required']) && $field['required'];
        $field_placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
        $field_hint = isset($field['hint']) ? $field['hint'] : '';
        $field_options = isset($field['options']) ? $field['options'] : array();
        $field_class = isset($field['class']) ? $field['class'] : '';
        
        $required_attr = $field_required ? 'required' : '';
        $required_label = $field_required ? '<span class="wafb-required">*</span>' : '';
        
        ?>
        <div class="wafb-form-field wafb-field-<?php echo esc_attr($field_type); ?> <?php echo esc_attr($field_class); ?>">
            <?php if (!empty($field_label)): ?>
                <label for="wafb-<?php echo esc_attr($field_name); ?>" class="wafb-field-label">
                    <?php echo esc_html($field_label); ?> <?php echo $required_label; ?>
                </label>
            <?php endif; ?>
            
            <?php
            switch ($field_type) {
                case 'text':
                case 'email':
                case 'phone':
                case 'url':
                    ?>
                    <input 
                        type="<?php echo esc_attr($field_type); ?>" 
                        id="wafb-<?php echo esc_attr($field_name); ?>"
                        name="<?php echo esc_attr($field_name); ?>"
                        class="wafb-field-input"
                        placeholder="<?php echo esc_attr($field_placeholder); ?>"
                        <?php echo $required_attr; ?>
                    >
                    <?php
                    break;
                    
                case 'textarea':
                    ?>
                    <textarea 
                        id="wafb-<?php echo esc_attr($field_name); ?>"
                        name="<?php echo esc_attr($field_name); ?>"
                        class="wafb-field-textarea"
                        placeholder="<?php echo esc_attr($field_placeholder); ?>"
                        rows="5"
                        <?php echo $required_attr; ?>
                    ></textarea>
                    <?php
                    break;
                    
                case 'select':
                    ?>
                    <select 
                        id="wafb-<?php echo esc_attr($field_name); ?>"
                        name="<?php echo esc_attr($field_name); ?>"
                        class="wafb-field-select"
                        <?php echo $required_attr; ?>
                    >
                        <option value=""><?php echo esc_html($field_placeholder ?: __('Select an option', 'whatsapp-form-builder')); ?></option>
                        <?php foreach ($field_options as $option): ?>
                            <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php
                    break;
                    
                case 'radio':
                    ?>
                    <div class="wafb-field-options">
                        <?php foreach ($field_options as $index => $option): ?>
                            <label class="wafb-field-option">
                                <input 
                                    type="radio" 
                                    name="<?php echo esc_attr($field_name); ?>"
                                    value="<?php echo esc_attr($option); ?>"
                                    <?php echo $required_attr; ?>
                                >
                                <span><?php echo esc_html($option); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    break;
                    
                case 'checkbox':
                    ?>
                    <div class="wafb-field-options">
                        <?php foreach ($field_options as $index => $option): ?>
                            <label class="wafb-field-option">
                                <input 
                                    type="checkbox" 
                                    name="<?php echo esc_attr($field_name); ?>[]"
                                    value="<?php echo esc_attr($option); ?>"
                                >
                                <span><?php echo esc_html($option); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    break;
            }
            ?>
            
            <?php if (!empty($field_hint)): ?>
                <small class="wafb-field-hint"><?php echo esc_html($field_hint); ?></small>
            <?php endif; ?>
        </div>
        <?php
    }
}
