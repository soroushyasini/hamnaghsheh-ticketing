<?php
/**
 * Helper functions for Hamnaghsheh Ticketing
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Smart login redirect for ticketing system
 * 
 * Use this function in your theme's header button
 * 
 * Example:
 * <a href="<?php echo hamnaghsheh_ticketing_url(); ?>">تیکت‌ها</a>
 */
function hamnaghsheh_ticketing_url() {
    if (is_user_logged_in()) {
        // User is logged in, direct to tickets page
        return site_url('/tickets/');
    } else {
        // User not logged in, redirect to login with return URL
        $redirect_url = urlencode(site_url('/tickets/'));
        return site_url('/auth/?redirect_to=' . $redirect_url);
    }
}

/**
 * Get ticketing page URL with smart redirect
 * 
 * @param bool $force_login Force redirect to login even if logged in
 * @return string URL
 */
function get_hamnaghsheh_ticketing_url($force_login = false) {
    if (!$force_login && is_user_logged_in()) {
        return site_url('/tickets/');
    }
    
    $redirect_url = urlencode(site_url('/tickets/'));
    return site_url('/auth/?redirect_to=' . $redirect_url);
}

/**
 * Redirect to ticketing page with login check
 * 
 * Use this function to programmatically redirect to ticketing
 */
function hamnaghsheh_redirect_to_ticketing() {
    if (is_user_logged_in()) {
        wp_redirect(site_url('/tickets/'));
    } else {
        $redirect_url = urlencode(site_url('/tickets/'));
        wp_redirect(site_url('/auth/?redirect_to=' . $redirect_url));
    }
    exit;
}

/**
 * Get ticket by ID
 * 
 * @param int $ticket_id Ticket ID
 * @return object|null Ticket object or null
 */
function hamnaghsheh_get_ticket($ticket_id) {
    global $wpdb;
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE id = %d",
        $ticket_id
    ));
}

/**
 * Get ticket by ticket number
 * 
 * @param string $ticket_number Ticket number
 * @return object|null Ticket object or null
 */
function hamnaghsheh_get_ticket_by_number($ticket_number) {
    global $wpdb;
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE ticket_number = %s",
        $ticket_number
    ));
}

/**
 * Check if user can view ticket
 * 
 * @param int $ticket_id Ticket ID
 * @param int|null $user_id User ID (null for current user)
 * @return bool
 */
function hamnaghsheh_user_can_view_ticket($ticket_id, $user_id = null) {
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    // Admins can view all tickets
    if (user_can($user_id, 'manage_options')) {
        return true;
    }
    
    $ticket = hamnaghsheh_get_ticket($ticket_id);
    
    if (!$ticket) {
        return false;
    }
    
    // Users can only view their own tickets
    return $ticket->user_id == $user_id;
}

/**
 * Get user's open tickets count
 * 
 * @param int|null $user_id User ID (null for current user)
 * @return int
 */
function hamnaghsheh_get_user_open_tickets_count($user_id = null) {
    if ($user_id === null) {
        $user_id = get_current_user_id();
    }
    
    global $wpdb;
    
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}hamnaghsheh_tickets 
         WHERE user_id = %d AND status IN ('open', 'in_progress', 'waiting_customer')",
        $user_id
    ));
}

/**
 * Format Persian date for display
 * 
 * @param string $date MySQL datetime string
 * @param string $format Date format
 * @return string
 */
function hamnaghsheh_format_date($date, $format = 'Y/m/d H:i') {
    $timestamp = strtotime($date);
    return Hamnaghsheh_Ticketing_Jalali::jdate($format, $timestamp);
}

/**
 * Get ticket status label
 * 
 * @param string $status Status key
 * @return string
 */
function hamnaghsheh_get_status_label($status) {
    $statuses = Hamnaghsheh_Tickets::get_statuses();
    return isset($statuses[$status]) ? $statuses[$status] : $status;
}

/**
 * Get category label
 * 
 * @param string $category Category key
 * @return string
 */
function hamnaghsheh_get_category_label($category) {
    $categories = Hamnaghsheh_Tickets::get_categories();
    return isset($categories[$category]) ? $categories[$category] : $category;
}

/**
 * Get file size in human readable format
 * 
 * @param int $bytes File size in bytes
 * @return string
 */
function hamnaghsheh_format_file_size($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Handle login redirect
 * This function hooks into WordPress login to handle redirect_to parameter
 */
function hamnaghsheh_login_redirect($redirect_to, $request, $user) {
    // Check if redirect_to is set in query string
    if (isset($_GET['redirect_to']) && !empty($_GET['redirect_to'])) {
        $redirect_to = urldecode($_GET['redirect_to']);
    }
    
    return $redirect_to;
}
add_filter('login_redirect', 'hamnaghsheh_login_redirect', 10, 3);

/**
 * Add ticketing menu item to user account menu
 * This is a helper function for theme integration
 * 
 * @param array $items Menu items
 * @return array
 */
function hamnaghsheh_add_account_menu_item($items) {
    $open_count = hamnaghsheh_get_user_open_tickets_count();
    
    $label = 'تیکت‌ها';
    if ($open_count > 0) {
        $label .= ' <span class="badge">' . $open_count . '</span>';
    }
    
    $items['tickets'] = [
        'label' => $label,
        'url' => hamnaghsheh_ticketing_url(),
        'icon' => 'dashicons-tickets'
    ];
    
    return $items;
}
// add_filter('your_theme_account_menu_items', 'hamnaghsheh_add_account_menu_item');
