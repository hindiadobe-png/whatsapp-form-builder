<?php
/**
 * Admin view: Form builder
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap wfbp-form-builder">
    <h1><?php echo $form_id ? esc_html__('Edit Form', 'whatsapp-form-builder') : esc_html__('Add New Form', 'whatsapp-form-builder'); ?></h1>
    
    <div class="wfbp-builder-container">
        <div class="wfbp-builder-sidebar">
            <h3><?php esc_html_e('Form Fields', 'whatsapp-form-builder'); ?></h3>
            <p class="description"><?php esc_html_e('Drag and drop fields to the form builder', 'whatsapp-form-builder'); ?></p>
            
            <div class="wfbp-field-types">
                <?php foreach ($field_types as $type => $label): ?>
                    <div class="wfbp-field-type" data-type="<?php echo esc_attr($type); ?>">
                        <span class="dashicons dashicons-move"></span>
                        <?php echo esc_html($label); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!WFBP_License::is_pro_enabled()): ?>
                <div class="wfbp-pro-notice">
                    <h4><?php esc_html_e('Pro Features', 'whatsapp-form-builder'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Advanced message templates', 'whatsapp-form-builder'); ?></li>
                        <li><?php esc_html_e('Multiple recipient numbers', 'whatsapp-form-builder'); ?></li>
                        <li><?php esc_html_e('WhatsApp Business API', 'whatsapp-form-builder'); ?></li>
                        <li><?php esc_html_e('Auto-responses', 'whatsapp-form-builder'); ?></li>
                        <li><?php esc_html_e('Webhooks', 'whatsapp-form-builder'); ?></li>
                        <li><?php esc_html_e('Custom design options', 'whatsapp-form-builder'); ?></li>
                    </ul>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wfbp-license')); ?>" class="button button-primary">
                        <?php esc_html_e('Upgrade to Pro', 'whatsapp-form-builder'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="wfbp-builder-main">
            <div class="wfbp-form-settings-header">
                <input type="text" id="wfbp-form-name" class="wfbp-form-name-input" placeholder="<?php esc_attr_e('Form Name', 'whatsapp-form-builder'); ?>" value="<?php echo esc_attr($form_name); ?>">
            </div>
            
            <div id="wfbp-form-fields" class="wfbp-form-fields">
                <?php if (empty($form_fields)): ?>
                    <div class="wfbp-placeholder">
                        <?php esc_html_e('Drag fields here to build your form', 'whatsapp-form-builder'); ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($form_fields as $index => $field): ?>
                        <div class="wfbp-form-field" data-index="<?php echo esc_attr($index); ?>">
                            <div class="wfbp-field-header">
                                <span class="dashicons dashicons-move wfbp-drag-handle"></span>
                                <span class="wfbp-field-type-label"><?php echo esc_html($field_types[$field['type']]); ?></span>
                                <button type="button" class="wfbp-remove-field dashicons dashicons-trash"></button>
                            </div>
                            <div class="wfbp-field-config">
                                <input type="text" class="wfbp-field-label" placeholder="<?php esc_attr_e('Field Label', 'whatsapp-form-builder'); ?>" value="<?php echo esc_attr($field['label']); ?>">
                                <input type="text" class="wfbp-field-name" placeholder="<?php esc_attr_e('Field Name', 'whatsapp-form-builder'); ?>" value="<?php echo esc_attr($field['name']); ?>">
                                <input type="text" class="wfbp-field-placeholder" placeholder="<?php esc_attr_e('Placeholder', 'whatsapp-form-builder'); ?>" value="<?php echo esc_attr(isset($field['placeholder']) ? $field['placeholder'] : ''); ?>">
                                <input type="text" class="wfbp-field-hint" placeholder="<?php esc_attr_e('Hint text', 'whatsapp-form-builder'); ?>" value="<?php echo esc_attr(isset($field['hint']) ? $field['hint'] : ''); ?>">
                                <label>
                                    <input type="checkbox" class="wfbp-field-required" <?php checked(isset($field['required']) && $field['required']); ?>>
                                    <?php esc_html_e('Required', 'whatsapp-form-builder'); ?>
                                </label>
                                <?php if (in_array($field['type'], array('select', 'radio', 'checkbox'))): ?>
                                    <textarea class="wfbp-field-options" placeholder="<?php esc_attr_e('Options (one per line)', 'whatsapp-form-builder'); ?>"><?php echo esc_textarea(isset($field['options']) ? implode("\n", $field['options']) : ''); ?></textarea>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="wfbp-form-settings">
                <h3><?php esc_html_e('Form Settings', 'whatsapp-form-builder'); ?></h3>
                
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('WhatsApp Phone Number', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <input type="text" id="wfbp-phone-number" class="regular-text" value="<?php echo esc_attr($form_settings['phone_number']); ?>" placeholder="+1234567890">
                            <p class="description"><?php esc_html_e('Enter the WhatsApp number with country code (e.g., +1234567890)', 'whatsapp-form-builder'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Button Text', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <input type="text" id="wfbp-button-text" class="regular-text" value="<?php echo esc_attr($form_settings['button_text']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Enable reCAPTCHA', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" id="wfbp-enable-recaptcha" <?php checked(isset($form_settings['enable_recaptcha']) && $form_settings['enable_recaptcha']); ?>>
                                <?php esc_html_e('Enable Google reCAPTCHA protection', 'whatsapp-form-builder'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <?php if (WFBP_License::is_pro_enabled()): ?>
                        <tr>
                            <th><?php esc_html_e('Message Template', 'whatsapp-form-builder'); ?></th>
                            <td>
                                <textarea id="wfbp-message-template" class="large-text" rows="5" placeholder="<?php esc_attr_e('Use {field_name} to insert field values', 'whatsapp-form-builder'); ?>"><?php echo esc_textarea(isset($form_settings['message_template']) ? $form_settings['message_template'] : ''); ?></textarea>
                                <p class="description"><?php esc_html_e('Pro Feature: Customize the WhatsApp message format', 'whatsapp-form-builder'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Use Business API', 'whatsapp-form-builder'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" id="wfbp-use-business-api" <?php checked(isset($form_settings['use_business_api']) && $form_settings['use_business_api']); ?>>
                                    <?php esc_html_e('Send messages via WhatsApp Business API', 'whatsapp-form-builder'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Auto-response', 'whatsapp-form-builder'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" id="wfbp-enable-auto-response" <?php checked(isset($form_settings['enable_auto_response']) && $form_settings['enable_auto_response']); ?>>
                                    <?php esc_html_e('Send automatic email response', 'whatsapp-form-builder'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Webhook URL', 'whatsapp-form-builder'); ?></th>
                            <td>
                                <input type="url" id="wfbp-webhook-url" class="regular-text" value="<?php echo esc_url(isset($form_settings['webhook_url']) ? $form_settings['webhook_url'] : ''); ?>" placeholder="https://example.com/webhook">
                                <p class="description"><?php esc_html_e('Pro Feature: Send form data to external URL', 'whatsapp-form-builder'); ?></p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
                
                <p class="submit">
                    <button type="button" id="wfbp-save-form" class="button button-primary button-large" data-form-id="<?php echo esc_attr($form_id); ?>">
                        <?php esc_html_e('Save Form', 'whatsapp-form-builder'); ?>
                    </button>
                </p>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="wfbp-field-template">
    <div class="wfbp-form-field" data-index="{{index}}">
        <div class="wfbp-field-header">
            <span class="dashicons dashicons-move wfbp-drag-handle"></span>
            <span class="wfbp-field-type-label">{{type_label}}</span>
            <button type="button" class="wfbp-remove-field dashicons dashicons-trash"></button>
        </div>
        <div class="wfbp-field-config">
            <input type="hidden" class="wfbp-field-type" value="{{type}}">
            <input type="text" class="wfbp-field-label" placeholder="<?php esc_attr_e('Field Label', 'whatsapp-form-builder'); ?>">
            <input type="text" class="wfbp-field-name" placeholder="<?php esc_attr_e('Field Name', 'whatsapp-form-builder'); ?>">
            <input type="text" class="wfbp-field-placeholder" placeholder="<?php esc_attr_e('Placeholder', 'whatsapp-form-builder'); ?>">
            <input type="text" class="wfbp-field-hint" placeholder="<?php esc_attr_e('Hint text', 'whatsapp-form-builder'); ?>">
            <label>
                <input type="checkbox" class="wfbp-field-required">
                <?php esc_html_e('Required', 'whatsapp-form-builder'); ?>
            </label>
            {{options_field}}
        </div>
    </div>
</script>
