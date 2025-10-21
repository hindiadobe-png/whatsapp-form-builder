<?php
/**
 * License management class
 *
 * @package WhatsApp_Form_Builder
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WFBP_License {
    
    /**
     * License server URL
     */
    private static $license_server = 'https://example.com/license-api/'; // Replace with actual server
    
    /**
     * Activate license
     */
    public static function activate_license($license_key) {
        // Validate license key format
        if (empty($license_key) || strlen($license_key) < 10) {
            return array(
                'success' => false,
                'message' => __('Invalid license key format.', 'whatsapp-form-builder')
            );
        }
        
        // Make API call to license server
        $response = wp_remote_post(self::$license_server . 'activate', array(
            'body' => array(
                'license_key' => $license_key,
                'site_url' => get_site_url(),
                'product' => 'whatsapp-form-builder-pro',
            ),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => __('Could not connect to license server.', 'whatsapp-form-builder')
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['success']) && $result['success']) {
            // Save license information
            update_option('wfbp_license_key', $license_key);
            update_option('wfbp_license_status', 'active');
            update_option('wfbp_pro_enabled', true);
            update_option('wfbp_license_expires', isset($result['expires']) ? $result['expires'] : '');
            
            return array(
                'success' => true,
                'message' => __('License activated successfully!', 'whatsapp-form-builder')
            );
        }
        
        return array(
            'success' => false,
            'message' => isset($result['message']) ? $result['message'] : __('License activation failed.', 'whatsapp-form-builder')
        );
    }
    
    /**
     * Deactivate license
     */
    public static function deactivate_license() {
        $license_key = get_option('wfbp_license_key', '');
        
        if (empty($license_key)) {
            return array(
                'success' => false,
                'message' => __('No license key found.', 'whatsapp-form-builder')
            );
        }
        
        // Make API call to license server
        $response = wp_remote_post(self::$license_server . 'deactivate', array(
            'body' => array(
                'license_key' => $license_key,
                'site_url' => get_site_url(),
            ),
            'timeout' => 30,
        ));
        
        // Update local settings regardless of API response
        update_option('wfbp_license_status', 'inactive');
        update_option('wfbp_pro_enabled', false);
        
        if (is_wp_error($response)) {
            return array(
                'success' => true,
                'message' => __('License deactivated locally.', 'whatsapp-form-builder')
            );
        }
        
        return array(
            'success' => true,
            'message' => __('License deactivated successfully!', 'whatsapp-form-builder')
        );
    }
    
    /**
     * Check license status
     */
    public static function check_license_status() {
        $license_key = get_option('wfbp_license_key', '');
        
        if (empty($license_key)) {
            return false;
        }
        
        // Make API call to license server
        $response = wp_remote_post(self::$license_server . 'check', array(
            'body' => array(
                'license_key' => $license_key,
                'site_url' => get_site_url(),
            ),
            'timeout' => 30,
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        if (isset($result['status'])) {
            update_option('wfbp_license_status', $result['status']);
            
            if ($result['status'] === 'active') {
                update_option('wfbp_pro_enabled', true);
                return true;
            } else {
                update_option('wfbp_pro_enabled', false);
            }
        }
        
        return false;
    }
    
    /**
     * Is pro version enabled
     */
    public static function is_pro_enabled() {
        return get_option('wfbp_pro_enabled', false) && get_option('wfbp_license_status', 'inactive') === 'active';
    }
    
    /**
     * Get license info
     */
    public static function get_license_info() {
        return array(
            'license_key' => get_option('wfbp_license_key', ''),
            'status' => get_option('wfbp_license_status', 'inactive'),
            'expires' => get_option('wfbp_license_expires', ''),
            'pro_enabled' => get_option('wfbp_pro_enabled', false),
        );
    }
}
