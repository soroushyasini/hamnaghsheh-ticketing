<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/includes
 */

class Hamnaghsheh_Ticketing_Deactivator {

    /**
     * Plugin deactivation handler.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
