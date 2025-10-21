<?php
/**
 * The core plugin class
 *
 * @package WhatsApp_Form_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class WAFB_Core {

    /**
     * The unique identifier of this plugin.
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @var string
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->plugin_name = 'whatsapp-form-builder';
        $this->version     = WAFB_VERSION;

        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // Load admin class
        require_once WAFB_PLUGIN_DIR . 'admin/class-wafb-admin.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new WAFB_Admin( $this->get_plugin_name(), $this->get_version() );

        add_action( 'admin_menu', array( $plugin_admin, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ) );
        add_action( 'wp_ajax_wafb_save_form', array( $plugin_admin, 'ajax_save_form' ) );
        add_action( 'wp_ajax_wafb_load_form', array( $plugin_admin, 'ajax_load_form' ) );
        add_action( 'wp_ajax_wafb_delete_form', array( $plugin_admin, 'ajax_delete_form' ) );
    }

    /**
     * Run the plugin.
     */
    public function run() {
        // Plugin is now running
    }

    /**
     * The name of the plugin.
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The version number of the plugin.
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }
}
