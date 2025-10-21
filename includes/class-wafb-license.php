<?php
/**
 * License handler
 *
 * @package WhatsApp_Form_Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WAFB_License Class
 */
class WAFB_License {
    
    /**
     * License API URL
     */
    private $api_url = 'https://your-license-server.com/api/';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', array($this, 'check_license'));
        add_action('wafb_daily_license_check', array($this, 'check_license'));
        
        // Schedule daily license check
        if (!wp_next_scheduled('wafb_daily_license_check')) {
            wp_schedule_event(time(), 'daily', 'wafb_daily_license_check');
        }
    }
    
    /**
     * Activate license
     */
    public function activate_license($license_key) {
        $response = wp_remote_post($this->api_url . 'activate', array(
            'body' => array(
                'license_key' => $license_key,
                'site_url' => get_site_url(),
                'product' => 'whatsapp-form-builder-pro'
            ),
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => __('Could not connect to license server', 'whatsapp-form-builder')
            );
        }
        
        $result = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!empty($result['success'])) {
            update_option('wafb_license_key', $license_key);
            update_option('wafb_license_status', 'active');
            update_option('wafb_license_expires', $result['expires']);
            
            return array(
                'success' => true,
                'message' => __('License activated successfully', 'whatsapp-form-builder')
            );
        }
        
        return array(
            'success' => false,
            'message' => !empty($result['message']) ? $result['message'] : __('License activation failed', 'whatsapp-form-builder')
        );
    }
    
    /**
     * Deactivate license
     */
    public function deactivate_license() {
        $license_key = get_option('wafb_license_key');
        
        if (empty($license_key)) {
            return array(
                'success' => false,
                'message' => __('No license key found', 'whatsapp-form-builder')
            );
        }
        
        $response = wp_remote_post($this->api_url . 'deactivate', array(
            'body' => array(
                'license_key' => $license_key,
                'site_url' => get_site_url()
            ),
            'timeout' => 15
        ));
        
        update_option('wafb_license_status', 'free');
        delete_option('wafb_license_key');
        delete_option('wafb_license_expires');
        
        return array(
            'success' => true,
            'message' => __('License deactivated', 'whatsapp-form-builder')
        );
    }
    
    /**
     * Check license status
     */
    public function check_license() {
        $license_key = get_option('wafb_license_key');
        
        if (empty($license_key)) {
            update_option('wafb_license_status', 'free');
            return;
        }
        
        $response = wp_remote_post($this->api_url . 'check', array(
            'body' => array(
                'license_key' => $license_key,
                'site_url' => get_site_url()
            ),
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            return;
        }
        
        $result = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!empty($result['valid'])) {
            update_option('wafb_license_status', 'active');
            update_option('wafb_license_expires', $result['expires']);
        } else {
            update_option('wafb_license_status', 'invalid');
        }
    }
    
    /**
     * Check if pro features are available
     */
    public static function is_pro_active() {
        return get_option('wafb_license_status') === 'active';
    }
}
