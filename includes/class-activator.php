<?php
/**
 * Fired during plugin activation.
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/includes
 */

class Hamnaghsheh_Ticketing_Activator {

    /**
     * Plugin activation handler.
     */
    public static function activate() {
        self::create_ticket_tables();
        self::create_upload_directory();
        
        // Set plugin version
        update_option('hamnaghsheh_ticketing_version', HAMNAGHSHEH_TICKETING_VERSION);
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create ticketing database tables.
     */
    private static function create_ticket_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Table 1: Tickets
        $table_tickets = $wpdb->prefix . 'hamnaghsheh_tickets';
        $sql_tickets = "CREATE TABLE $table_tickets (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ticket_number varchar(50) NOT NULL UNIQUE,
            user_id bigint(20) UNSIGNED NOT NULL,
            title varchar(255) NOT NULL,
            category varchar(50) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'open',
            priority varchar(20) NOT NULL DEFAULT 'normal',
            project_id bigint(20) UNSIGNED NULL,
            order_id bigint(20) UNSIGNED NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            closed_at datetime NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY ticket_number (ticket_number)
        ) $charset_collate;";
        
        // Table 2: Ticket Replies
        $table_replies = $wpdb->prefix . 'hamnaghsheh_ticket_replies';
        $sql_replies = "CREATE TABLE $table_replies (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            message text NOT NULL,
            is_admin_reply tinyint(1) NOT NULL DEFAULT 0,
            attachments text NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY ticket_id (ticket_id)
        ) $charset_collate;";
        
        // Table 3: Ticket Admin Notes
        $table_notes = $wpdb->prefix . 'hamnaghsheh_ticket_admin_notes';
        $sql_notes = "CREATE TABLE $table_notes (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            ticket_id bigint(20) UNSIGNED NOT NULL,
            admin_id bigint(20) UNSIGNED NOT NULL,
            note text NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY ticket_id (ticket_id)
        ) $charset_collate;";
        
        dbDelta($sql_tickets);
        dbDelta($sql_replies);
        dbDelta($sql_notes);
    }

    /**
     * Create upload directory for ticket attachments.
     */
    private static function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $tickets_dir = $upload_dir['basedir'] . '/hamnaghsheh/tickets';
        
        if (!file_exists($tickets_dir)) {
            wp_mkdir_p($tickets_dir);
            
            // Add .htaccess for security
            $htaccess_file = $tickets_dir . '/.htaccess';
            $htaccess_content = "Options -Indexes\n";
            file_put_contents($htaccess_file, $htaccess_content);
            
            // Add index.php for security
            $index_file = $tickets_dir . '/index.php';
            file_put_contents($index_file, '<?php // Silence is golden');
        }
    }
}
