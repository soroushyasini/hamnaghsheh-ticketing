<?php
/**
 * The core plugin class.
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/includes
 */

class Hamnaghsheh_Ticketing {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        $this->version = HAMNAGHSHEH_TICKETING_VERSION;
        $this->plugin_name = 'hamnaghsheh-ticketing';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies.
     */
    private function load_dependencies() {
        require_once HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'includes/class-jalali.php';
        require_once HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'includes/class-tickets.php';
        require_once HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'includes/class-email-notifications.php';
        require_once HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'includes/admin/class-admin-tickets.php';
    }

    /**
     * Register all admin-related hooks.
     */
    private function define_admin_hooks() {
        $admin_tickets = new Hamnaghsheh_Admin_Tickets();
        
        add_action('admin_menu', [$admin_tickets, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$admin_tickets, 'enqueue_admin_assets']);
        
        // Admin AJAX handlers
        add_action('wp_ajax_hamnaghsheh_admin_reply_ticket', [$admin_tickets, 'ajax_reply_ticket']);
        add_action('wp_ajax_hamnaghsheh_admin_update_status', [$admin_tickets, 'ajax_update_status']);
        add_action('wp_ajax_hamnaghsheh_admin_set_priority', [$admin_tickets, 'ajax_set_priority']);
        add_action('wp_ajax_hamnaghsheh_admin_add_note', [$admin_tickets, 'ajax_add_note']);
        add_action('wp_ajax_hamnaghsheh_admin_close_ticket', [$admin_tickets, 'ajax_close_ticket']);
    }

    /**
     * Register all public-facing hooks.
     */
    private function define_public_hooks() {
        $tickets = new Hamnaghsheh_Tickets();
        
        add_action('wp_enqueue_scripts', [$tickets, 'enqueue_assets']);
        add_shortcode('hamnaghsheh_tickets', [$tickets, 'render_tickets_page']);
        add_shortcode('hamnaghsheh_ticket_detail', [$tickets, 'render_ticket_detail']);
        add_shortcode('hamnaghsheh_tickets_button', [$tickets, 'render_tickets_button']);
        
        // Frontend AJAX handlers
        add_action('wp_ajax_hamnaghsheh_create_ticket', [$tickets, 'ajax_create_ticket']);
        add_action('wp_ajax_hamnaghsheh_reply_ticket', [$tickets, 'ajax_reply_ticket']);
        add_action('wp_ajax_hamnaghsheh_upload_ticket_attachment', [$tickets, 'ajax_upload_attachment']);
        add_action('wp_ajax_hamnaghsheh_get_user_projects', [$tickets, 'ajax_get_user_projects']);
        add_action('wp_ajax_hamnaghsheh_get_user_orders', [$tickets, 'ajax_get_user_orders']);
    }

    /**
     * Run the plugin.
     */
    public function run() {
        // Plugin is initialized
    }
}
