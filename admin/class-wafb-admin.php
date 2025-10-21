<?php
/**
 * The admin-specific functionality of the plugin
 *
 * @package WhatsApp_Form_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The admin-specific functionality of the plugin.
 */
class WAFB_Admin {

    /**
     * The ID of this plugin.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'WhatsApp Form Builder', 'whatsapp-form-builder' ),
            __( 'Form Builder', 'whatsapp-form-builder' ),
            'manage_options',
            'wafb-form-builder',
            array( $this, 'display_admin_page' ),
            'dashicons-feedback',
            30
        );
    }

    /**
     * Display the admin page.
     */
    public function display_admin_page() {
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'whatsapp-form-builder' ) );
        }

        require_once WAFB_PLUGIN_DIR . 'admin/partials/wafb-admin-display.php';
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_styles( $hook ) {
        if ( 'toplevel_page_wafb-form-builder' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name,
            WAFB_PLUGIN_URL . 'assets/css/wafb-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_scripts( $hook ) {
        if ( 'toplevel_page_wafb-form-builder' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-droppable' );

        wp_enqueue_script(
            $this->plugin_name,
            WAFB_PLUGIN_URL . 'assets/js/wafb-admin.js',
            array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ),
            $this->version,
            true
        );

        // Pass data to JavaScript
        wp_localize_script(
            $this->plugin_name,
            'wafbAdmin',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wafb_admin_nonce' ),
                'strings' => array(
                    'confirmDelete' => __( 'Are you sure you want to delete this form?', 'whatsapp-form-builder' ),
                    'saveSuccess'   => __( 'Form saved successfully!', 'whatsapp-form-builder' ),
                    'saveError'     => __( 'Error saving form. Please try again.', 'whatsapp-form-builder' ),
                    'loadError'     => __( 'Error loading form. Please try again.', 'whatsapp-form-builder' ),
                ),
            )
        );
    }

    /**
     * AJAX handler to save form.
     */
    public function ajax_save_form() {
        // Verify nonce
        check_ajax_referer( 'wafb_admin_nonce', 'nonce' );

        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'whatsapp-form-builder' ) ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wafb_forms';

        // Sanitize inputs
        $form_id   = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
        $form_name = isset( $_POST['form_name'] ) ? sanitize_text_field( wp_unslash( $_POST['form_name'] ) ) : '';
        $form_data = isset( $_POST['form_data'] ) ? wp_kses_post( wp_unslash( $_POST['form_data'] ) ) : '';

        // Validate required fields
        if ( empty( $form_name ) ) {
            wp_send_json_error( array( 'message' => __( 'Form name is required.', 'whatsapp-form-builder' ) ) );
        }

        if ( $form_id > 0 ) {
            // Update existing form
            $result = $wpdb->update(
                $table_name,
                array(
                    'form_name' => $form_name,
                    'form_data' => $form_data,
                ),
                array( 'id' => $form_id ),
                array( '%s', '%s' ),
                array( '%d' )
            );

            if ( false === $result ) {
                wp_send_json_error( array( 'message' => __( 'Error updating form.', 'whatsapp-form-builder' ) ) );
            }

            wp_send_json_success( array(
                'message' => __( 'Form updated successfully!', 'whatsapp-form-builder' ),
                'form_id' => $form_id,
            ) );
        } else {
            // Insert new form
            $result = $wpdb->insert(
                $table_name,
                array(
                    'form_name' => $form_name,
                    'form_data' => $form_data,
                ),
                array( '%s', '%s' )
            );

            if ( false === $result ) {
                wp_send_json_error( array( 'message' => __( 'Error creating form.', 'whatsapp-form-builder' ) ) );
            }

            wp_send_json_success( array(
                'message' => __( 'Form created successfully!', 'whatsapp-form-builder' ),
                'form_id' => $wpdb->insert_id,
            ) );
        }
    }

    /**
     * AJAX handler to load form.
     */
    public function ajax_load_form() {
        // Verify nonce
        check_ajax_referer( 'wafb_admin_nonce', 'nonce' );

        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'whatsapp-form-builder' ) ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wafb_forms';

        // Sanitize input
        $form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;

        if ( $form_id <= 0 ) {
            wp_send_json_error( array( 'message' => __( 'Invalid form ID.', 'whatsapp-form-builder' ) ) );
        }

        // Get form from database
        $form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $form_id ), ARRAY_A );

        if ( ! $form ) {
            wp_send_json_error( array( 'message' => __( 'Form not found.', 'whatsapp-form-builder' ) ) );
        }

        wp_send_json_success( array(
            'form' => $form,
        ) );
    }

    /**
     * AJAX handler to delete form.
     */
    public function ajax_delete_form() {
        // Verify nonce
        check_ajax_referer( 'wafb_admin_nonce', 'nonce' );

        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'whatsapp-form-builder' ) ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wafb_forms';

        // Sanitize input
        $form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;

        if ( $form_id <= 0 ) {
            wp_send_json_error( array( 'message' => __( 'Invalid form ID.', 'whatsapp-form-builder' ) ) );
        }

        // Delete form
        $result = $wpdb->delete(
            $table_name,
            array( 'id' => $form_id ),
            array( '%d' )
        );

        if ( false === $result ) {
            wp_send_json_error( array( 'message' => __( 'Error deleting form.', 'whatsapp-form-builder' ) ) );
        }

        wp_send_json_success( array( 'message' => __( 'Form deleted successfully!', 'whatsapp-form-builder' ) ) );
    }
}
