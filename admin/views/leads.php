<?php
/**
 * Leads view
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

$form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
$paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
$per_page = 20;
$offset = ($paged - 1) * $per_page;

$leads = WAFB_Leads::get_leads($form_id, $per_page, $offset);
$total_leads = WAFB_Leads::get_leads_count($form_id);
$total_pages = ceil($total_leads / $per_page);

// Get all forms for filter
global $wpdb;
$forms = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}wafb_forms ORDER BY name ASC");

$is_pro = get_option('wafb_license_status') === 'active';
?>

<div class="wrap wafb-admin-page">
    <h1><?php _e('Leads', 'whatsapp-form-builder'); ?></h1>
    
    <?php if (!$is_pro): ?>
        <div class="notice notice-warning">
            <p>
                <?php _e('Lead export is a Pro feature.', 'whatsapp-form-builder'); ?>
                <a href="<?php echo admin_url('admin.php?page=wafb-settings&tab=license'); ?>"><?php _e('Upgrade to Pro', 'whatsapp-form-builder'); ?></a>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="wafb-leads-filters">
        <select id="wafb-filter-form">
            <option value="0"><?php _e('All Forms', 'whatsapp-form-builder'); ?></option>
            <?php foreach ($forms as $form): ?>
                <option value="<?php echo $form->id; ?>" <?php selected($form_id, $form->id); ?>>
                    <?php echo esc_html($form->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <?php if ($is_pro): ?>
            <button type="button" class="button wafb-export-leads" data-form-id="<?php echo $form_id; ?>">
                <?php _e('Export to CSV', 'whatsapp-form-builder'); ?>
            </button>
        <?php endif; ?>
    </div>
    
    <?php if (empty($leads)): ?>
        <div class="wafb-empty-state">
            <h2><?php _e('No leads yet', 'whatsapp-form-builder'); ?></h2>
            <p><?php _e('Leads will appear here when users submit forms.', 'whatsapp-form-builder'); ?></p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('ID', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('Form', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('Data', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('Page URL', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('IP Address', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('Date', 'whatsapp-form-builder'); ?></th>
                    <th><?php _e('Actions', 'whatsapp-form-builder'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead): ?>
                    <?php
                    $form_name = $wpdb->get_var($wpdb->prepare(
                        "SELECT name FROM {$wpdb->prefix}wafb_forms WHERE id = %d",
                        $lead->form_id
                    ));
                    $lead_data = json_decode($lead->data, true);
                    ?>
                    <tr>
                        <td><?php echo $lead->id; ?></td>
                        <td><?php echo esc_html($form_name); ?></td>
                        <td>
                            <button type="button" class="button button-small wafb-view-lead-data" data-lead-id="<?php echo $lead->id; ?>">
                                <?php _e('View', 'whatsapp-form-builder'); ?>
                            </button>
                            <div class="wafb-lead-data" style="display:none;" data-lead-id="<?php echo $lead->id; ?>">
                                <?php if (is_array($lead_data)): ?>
                                    <table class="widefat">
                                        <?php foreach ($lead_data as $field_name => $field_data): ?>
                                            <tr>
                                                <th><?php echo esc_html($field_data['label'] ?? $field_name); ?></th>
                                                <td><?php echo esc_html($field_data['value'] ?? ''); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($lead->page_url)): ?>
                                <a href="<?php echo esc_url($lead->page_url); ?>" target="_blank">
                                    <?php echo esc_html(wp_trim_words($lead->page_url, 5)); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($lead->ip_address); ?></td>
                        <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($lead->created_at)); ?></td>
                        <td>
                            <button type="button" class="button button-small button-link-delete wafb-delete-lead" data-lead-id="<?php echo $lead->id; ?>">
                                <?php _e('Delete', 'whatsapp-form-builder'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($total_pages > 1): ?>
            <div class="tablenav">
                <div class="tablenav-pages">
                    <?php
                    echo paginate_links(array(
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo;'),
                        'next_text' => __('&raquo;'),
                        'total' => $total_pages,
                        'current' => $paged
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
