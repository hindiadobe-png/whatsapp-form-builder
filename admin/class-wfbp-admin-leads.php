<?php
/**
 * Admin leads page
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_Admin_Leads {
    
    /**
     * Render leads page
     */
    public function render() {
        // Get filter parameters
        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 50;
        $offset = ($page - 1) * $per_page;
        
        // Get leads
        if ($form_id) {
            $leads = WFBP_Lead::get_leads_by_form($form_id, $per_page, $offset);
            $total_leads = WFBP_Lead::get_lead_count($form_id);
        } else {
            $leads = WFBP_Lead::get_all_leads($per_page, $offset);
            $total_leads = WFBP_Lead::get_lead_count();
        }
        
        $total_pages = ceil($total_leads / $per_page);
        
        // Get all forms for filter
        $forms = WFBP_Form::get_all_forms();
        
        include_once WFBP_PLUGIN_DIR . 'admin/views/leads.php';
    }
}
