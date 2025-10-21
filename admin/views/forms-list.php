<?php
/**
 * Admin view: Forms list
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('WhatsApp Forms', 'whatsapp-form-builder'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=wfbp-add-form')); ?>" class="page-title-action">
        <?php esc_html_e('Add New', 'whatsapp-form-builder'); ?>
    </a>
    <hr class="wp-header-end">
    
    <?php if (empty($forms)): ?>
        <div class="notice notice-info">
            <p>
                <?php esc_html_e('No forms found. Create your first WhatsApp form!', 'whatsapp-form-builder'); ?>
            </p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Form Name', 'whatsapp-form-builder'); ?></th>
                    <th><?php esc_html_e('Shortcode', 'whatsapp-form-builder'); ?></th>
                    <th><?php esc_html_e('Leads', 'whatsapp-form-builder'); ?></th>
                    <th><?php esc_html_e('Created', 'whatsapp-form-builder'); ?></th>
                    <th><?php esc_html_e('Actions', 'whatsapp-form-builder'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forms as $form): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($form->form_name); ?></strong>
                        </td>
                        <td>
                            <code>[whatsapp_form id="<?php echo esc_attr($form->id); ?>"]</code>
                            <button class="button button-small wfbp-copy-shortcode" data-shortcode='[whatsapp_form id="<?php echo esc_attr($form->id); ?>"]'>
                                <?php esc_html_e('Copy', 'whatsapp-form-builder'); ?>
                            </button>
                        </td>
                        <td>
                            <?php 
                            $lead_count = WFBP_Lead::get_lead_count($form->id);
                            echo esc_html($lead_count);
                            ?>
                        </td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($form->created_at))); ?></td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wfbp-add-form&form_id=' . $form->id)); ?>" class="button button-small">
                                <?php esc_html_e('Edit', 'whatsapp-form-builder'); ?>
                            </a>
                            <button class="button button-small wfbp-delete-form" data-form-id="<?php echo esc_attr($form->id); ?>">
                                <?php esc_html_e('Delete', 'whatsapp-form-builder'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
