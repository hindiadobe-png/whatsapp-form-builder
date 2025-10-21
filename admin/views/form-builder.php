<?php
/**
 * Form builder view
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

$form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
$form = null;

if ($form_id > 0) {
    global $wpdb;
    $table = $wpdb->prefix . 'wafb_forms';
    $form = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $form_id), ARRAY_A);
}

$form_name = $form ? $form['name'] : '';
$form_fields = $form ? json_decode($form['fields'], true) : array();
$form_settings = $form ? json_decode($form['settings'], true) : array();

$field_types = WAFB_Form_Builder::get_field_types();
$is_pro = get_option('wafb_license_status') === 'active';
?>

<div class="wrap wafb-admin-page wafb-form-builder-page">
    <h1><?php echo $form_id ? __('Edit Form', 'whatsapp-form-builder') : __('Create New Form', 'whatsapp-form-builder'); ?></h1>
    
    <div class="wafb-builder-container">
        <div class="wafb-builder-sidebar">
            <h2><?php _e('Form Fields', 'whatsapp-form-builder'); ?></h2>
            <p class="description"><?php _e('Drag fields to the form builder', 'whatsapp-form-builder'); ?></p>
            
            <div class="wafb-field-types">
                <?php foreach ($field_types as $type => $field_type): ?>
                    <div class="wafb-field-type" data-type="<?php echo esc_attr($type); ?>" draggable="true">
                        <span class="dashicons <?php echo esc_attr($field_type['icon']); ?>"></span>
                        <span class="wafb-field-type-label"><?php echo esc_html($field_type['label']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="wafb-builder-main">
            <div class="wafb-form-header">
                <input type="text" id="wafb-form-name" class="wafb-form-name-input" placeholder="<?php _e('Form Name', 'whatsapp-form-builder'); ?>" value="<?php echo esc_attr($form_name); ?>">
                <button type="button" class="button button-primary wafb-save-form" data-form-id="<?php echo $form_id; ?>">
                    <?php _e('Save Form', 'whatsapp-form-builder'); ?>
                </button>
            </div>
            
            <div class="wafb-form-preview">
                <h3><?php _e('Form Preview', 'whatsapp-form-builder'); ?></h3>
                <div id="wafb-form-fields" class="wafb-form-fields-container">
                    <?php if (!empty($form_fields)): ?>
                        <?php foreach ($form_fields as $field): ?>
                            <?php echo $this->render_builder_field($field); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="wafb-empty-message">
                            <?php _e('Drag fields here to build your form', 'whatsapp-form-builder'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="wafb-form-settings">
                <h3><?php _e('Form Settings', 'whatsapp-form-builder'); ?></h3>
                
                <table class="form-table">
                    <tr>
                        <th><?php _e('Form Title', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <input type="text" name="form_title" class="regular-text" value="<?php echo esc_attr($form_settings['form_title'] ?? ''); ?>">
                            <p class="description"><?php _e('Display title above the form', 'whatsapp-form-builder'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Form Description', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <textarea name="form_description" class="large-text" rows="3"><?php echo esc_textarea($form_settings['form_description'] ?? ''); ?></textarea>
                            <p class="description"><?php _e('Display description below the title', 'whatsapp-form-builder'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('WhatsApp Number', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <input type="text" name="whatsapp_number" class="regular-text" value="<?php echo esc_attr($form_settings['whatsapp_number'] ?? ''); ?>" placeholder="+1234567890">
                            <p class="description"><?php _e('Enter WhatsApp number with country code (e.g., +1234567890)', 'whatsapp-form-builder'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Submit Button Text', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <input type="text" name="submit_text" class="regular-text" value="<?php echo esc_attr($form_settings['submit_text'] ?? 'Send to WhatsApp'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Enable reCAPTCHA', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="recaptcha_enabled" value="1" <?php checked(!empty($form_settings['recaptcha_enabled'])); ?>>
                                <?php _e('Enable reCAPTCHA protection', 'whatsapp-form-builder'); ?>
                            </label>
                            <p class="description">
                                <?php printf(__('Configure reCAPTCHA in <a href="%s">Settings</a>', 'whatsapp-form-builder'), admin_url('admin.php?page=wafb-settings&tab=recaptcha')); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php _e('Enable RTL', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="rtl_enabled" value="1" <?php checked(!empty($form_settings['rtl_enabled'])); ?>>
                                <?php _e('Enable right-to-left layout', 'whatsapp-form-builder'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <?php if ($is_pro): ?>
                        <tr>
                            <th><?php _e('Message Template', 'whatsapp-form-builder'); ?> <span class="wafb-pro-badge"><?php _e('PRO', 'whatsapp-form-builder'); ?></span></th>
                            <td>
                                <textarea name="message_template" class="large-text" rows="5"><?php echo esc_textarea($form_settings['message_template'] ?? ''); ?></textarea>
                                <p class="description"><?php _e('Custom message template. Use {field_name} for field values and {page_url} for current page link.', 'whatsapp-form-builder'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Webhook URL', 'whatsapp-form-builder'); ?> <span class="wafb-pro-badge"><?php _e('PRO', 'whatsapp-form-builder'); ?></span></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="webhook_enabled" value="1" <?php checked(!empty($form_settings['webhook_enabled'])); ?>>
                                    <?php _e('Enable webhook', 'whatsapp-form-builder'); ?>
                                </label>
                                <input type="url" name="webhook_url" class="regular-text" value="<?php echo esc_attr($form_settings['webhook_url'] ?? ''); ?>" placeholder="https://example.com/webhook">
                                <p class="description"><?php _e('Send form data to external URL', 'whatsapp-form-builder'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Auto Response', 'whatsapp-form-builder'); ?> <span class="wafb-pro-badge"><?php _e('PRO', 'whatsapp-form-builder'); ?></span></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="auto_response_enabled" value="1" <?php checked(!empty($form_settings['auto_response_enabled'])); ?>>
                                    <?php _e('Send auto-response email', 'whatsapp-form-builder'); ?>
                                </label>
                                <input type="text" name="auto_response_subject" class="regular-text" value="<?php echo esc_attr($form_settings['auto_response_subject'] ?? ''); ?>" placeholder="<?php _e('Subject', 'whatsapp-form-builder'); ?>">
                                <textarea name="auto_response_message" class="large-text" rows="5" placeholder="<?php _e('Message', 'whatsapp-form-builder'); ?>"><?php echo esc_textarea($form_settings['auto_response_message'] ?? ''); ?></textarea>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th colspan="2">
                                <div class="notice notice-info inline">
                                    <p>
                                        <?php _e('Upgrade to Pro to unlock advanced features:', 'whatsapp-form-builder'); ?>
                                    </p>
                                    <ul style="list-style: disc; margin-left: 20px;">
                                        <li><?php _e('Custom message templates', 'whatsapp-form-builder'); ?></li>
                                        <li><?php _e('Multiple recipient numbers', 'whatsapp-form-builder'); ?></li>
                                        <li><?php _e('Webhook integration', 'whatsapp-form-builder'); ?></li>
                                        <li><?php _e('Auto-response emails', 'whatsapp-form-builder'); ?></li>
                                        <li><?php _e('Lead export', 'whatsapp-form-builder'); ?></li>
                                        <li><?php _e('Custom design options', 'whatsapp-form-builder'); ?></li>
                                    </ul>
                                    <p>
                                        <a href="<?php echo admin_url('admin.php?page=wafb-settings&tab=license'); ?>" class="button button-primary"><?php _e('Upgrade Now', 'whatsapp-form-builder'); ?></a>
                                    </p>
                                </div>
                            </th>
                        </tr>
                    <?php endif; ?>
                    
                    <tr>
                        <th><?php _e('Custom CSS', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <textarea name="custom_css" class="large-text code" rows="5"><?php echo esc_textarea($form_settings['custom_css'] ?? ''); ?></textarea>
                            <p class="description"><?php _e('Add custom CSS to style your form', 'whatsapp-form-builder'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Field Editor Modal Template -->
<div id="wafb-field-editor-modal" style="display:none;">
    <div class="wafb-modal-overlay">
        <div class="wafb-modal">
            <div class="wafb-modal-header">
                <h3><?php _e('Edit Field', 'whatsapp-form-builder'); ?></h3>
                <button type="button" class="wafb-modal-close">&times;</button>
            </div>
            <div class="wafb-modal-body">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Label', 'whatsapp-form-builder'); ?></th>
                        <td><input type="text" name="field_label" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php _e('Placeholder', 'whatsapp-form-builder'); ?></th>
                        <td><input type="text" name="field_placeholder" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php _e('Hint', 'whatsapp-form-builder'); ?></th>
                        <td><input type="text" name="field_hint" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php _e('Required', 'whatsapp-form-builder'); ?></th>
                        <td><label><input type="checkbox" name="field_required"> <?php _e('Make this field required', 'whatsapp-form-builder'); ?></label></td>
                    </tr>
                    <tr class="wafb-field-options-row" style="display:none;">
                        <th><?php _e('Options', 'whatsapp-form-builder'); ?></th>
                        <td>
                            <textarea name="field_options" class="regular-text" rows="5" placeholder="<?php _e('One option per line', 'whatsapp-form-builder'); ?>"></textarea>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="wafb-modal-footer">
                <button type="button" class="button button-primary wafb-save-field"><?php _e('Save Field', 'whatsapp-form-builder'); ?></button>
                <button type="button" class="button wafb-modal-close"><?php _e('Cancel', 'whatsapp-form-builder'); ?></button>
            </div>
        </div>
    </div>
</div>
