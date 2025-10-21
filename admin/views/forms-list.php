<?php
/**
 * Forms list view
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

$forms = WAFB_Forms_List::get_forms();
?>

<div class="wrap wafb-admin-page">
    <h1 class="wp-heading-inline"><?php _e('WhatsApp Forms', 'whatsapp-form-builder'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=wafb-form-builder'); ?>" class="page-title-action"><?php _e('Add New', 'whatsapp-form-builder'); ?></a>
    <hr class="wp-header-end">
    
    <?php if (get_option('wafb_license_status') !== 'active'): ?>
        <div class="notice notice-info">
            <p>
                <?php _e('Upgrade to Pro for advanced features like multiple recipients, lead management, webhooks, and more!', 'whatsapp-form-builder'); ?>
                <a href="<?php echo admin_url('admin.php?page=wafb-settings&tab=license'); ?>"><?php _e('Activate License', 'whatsapp-form-builder'); ?></a>
            </p>
        </div>
    <?php endif; ?>
    
    <?php if (empty($forms)): ?>
        <div class="wafb-empty-state">
            <h2><?php _e('No forms yet', 'whatsapp-form-builder'); ?></h2>
            <p><?php _e('Create your first WhatsApp form to get started.', 'whatsapp-form-builder'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=wafb-form-builder'); ?>" class="button button-primary"><?php _e('Create Form', 'whatsapp-form-builder'); ?></a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Name', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('Shortcode', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('Status', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('Leads', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('Date', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('Actions', 'whatsapp-form-builder'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($forms as $form): ?>
                    <?php
                    global $wpdb;
                    $leads_count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}wafb_leads WHERE form_id = %d",
                        $form->id
                    ));
                    ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=wafb-form-builder&form_id=' . $form->id); ?>">
                                    <?php echo esc_html($form->name); ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <code class="wafb-shortcode">[whatsapp_form id="<?php echo $form->id; ?>"]</code>
                            <button type="button" class="button button-small wafb-copy-shortcode" data-shortcode='[whatsapp_form id="<?php echo $form->id; ?>"]'>
                                <?php _e('Copy', 'whatsapp-form-builder'); ?>
                            </button>
                        </td>
                        <td>
                            <span class="wafb-status wafb-status-<?php echo esc_attr($form->status); ?>">
                                <?php echo esc_html(ucfirst($form->status)); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=wafb-leads&form_id=' . $form->id); ?>">
                                <?php echo $leads_count; ?>
                            </a>
                        </td>
                        <td><?php echo date_i18n(get_option('date_format'), strtotime($form->created_at)); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=wafb-form-builder&form_id=' . $form->id); ?>" class="button button-small">
                                <?php _e('Edit', 'whatsapp-form-builder'); ?>
                            </a>
                            <button type="button" class="button button-small wafb-duplicate-form" data-form-id="<?php echo $form->id; ?>">
                                <?php _e('Duplicate', 'whatsapp-form-builder'); ?>
                            </button>
                            <button type="button" class="button button-small button-link-delete wafb-delete-form" data-form-id="<?php echo $form->id; ?>">
                                <?php _e('Delete', 'whatsapp-form-builder'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
