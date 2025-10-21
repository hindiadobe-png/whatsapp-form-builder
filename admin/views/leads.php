<?php
/**
 * Admin view: Leads
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Leads', 'whatsapp-form-builder'); ?></h1>
    
    <div class="wfbp-leads-filters">
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <input type="hidden" name="page" value="wfbp-leads">
            
            <select name="form_id">
                <option value="0"><?php esc_html_e('All Forms', 'whatsapp-form-builder'); ?></option>
                <?php foreach ($forms as $form_option): ?>
                    <option value="<?php echo esc_attr($form_option->id); ?>" <?php selected($form_id, $form_option->id); ?>>
                        <?php echo esc_html($form_option->form_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="button"><?php esc_html_e('Filter', 'whatsapp-form-builder'); ?></button>
            
            <?php if (WFBP_License::is_pro_enabled()): ?>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-ajax.php?action=wfbp_export_leads&form_id=' . $form_id), 'wfbp_admin_nonce', 'nonce')); ?>" class="button">
                    <?php esc_html_e('Export to CSV', 'whatsapp-form-builder'); ?>
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (empty($leads)): ?>
        <div class="notice notice-info">
            <p><?php esc_html_e('No leads found.', 'whatsapp-form-builder'); ?></p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'whatsapp-form-builder'); ?></th>
                    <th><?php esc_html_e('Form', 'whatsapp-form-builder'); ?></th>
                    <th><?php esc_html_e('Data', 'whatsapp-form-builder'); ?></th>
                    <th><?php esc_html_e('Page URL', 'whatsapp-form-builder'); ?></th>
                    <th><?php esc_html_e('IP Address', 'whatsapp-form-builder'); ?></th>
                    <th><?php esc_html_e('Date', 'whatsapp-form-builder'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td><?php echo esc_html($lead->id); ?></td>
                        <td>
                            <?php 
                            $lead_form = WFBP_Form::get_form($lead->form_id);
                            echo $lead_form ? esc_html($lead_form->form_name) : esc_html__('Unknown', 'whatsapp-form-builder');
                            ?>
                        </td>
                        <td>
                            <?php if (is_array($lead->form_data)): ?>
                                <details>
                                    <summary><?php esc_html_e('View Data', 'whatsapp-form-builder'); ?></summary>
                                    <dl>
                                        <?php foreach ($lead->form_data as $key => $value): ?>
                                            <dt><strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>:</strong></dt>
                                            <dd><?php echo esc_html($value); ?></dd>
                                        <?php endforeach; ?>
                                    </dl>
                                </details>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url($lead->page_url); ?>" target="_blank">
                                <?php echo esc_html(wp_trim_words($lead->page_url, 5)); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($lead->user_ip); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($lead->created_at))); ?></td>
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
                        'prev_text' => __('&laquo;', 'whatsapp-form-builder'),
                        'next_text' => __('&raquo;', 'whatsapp-form-builder'),
                        'total' => $total_pages,
                        'current' => $page,
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
