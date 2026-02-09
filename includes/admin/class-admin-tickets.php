<?php
/**
 * Admin Ticket Management
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/includes/admin
 */

class Hamnaghsheh_Admin_Tickets {

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        $open_count = $this->get_open_tickets_count();
        
        $menu_title = 'تیکتها';
        if ($open_count > 0) {
            $menu_title = sprintf(
                'تیکتها <span class="update-plugins count-%d"><span class="update-count">%d</span></span>',
                $open_count,
                $open_count
            );
        }
        
        add_menu_page(
            'تیکتها',
            $menu_title,
            'manage_options',
            'hamnaghsheh-tickets',
            [$this, 'render_tickets_page'],
            'dashicons-tickets',
            25
        );
    }

    /**
     * Render admin tickets page
     */
    public function render_tickets_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        if ($action === 'view' && isset($_GET['id'])) {
            $ticket_id = intval($_GET['id']);
            include HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'templates/admin/ticket-detail-admin.php';
        } else {
            include HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'templates/admin/tickets-list.php';
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_hamnaghsheh-tickets') {
            return;
        }
        
        wp_enqueue_style(
            'hamnaghsheh-admin-tickets',
            HAMNAGHSHEH_TICKETING_PLUGIN_URL . 'assets/css/tickets.css',
            [],
            HAMNAGHSHEH_TICKETING_VERSION
        );

        wp_enqueue_script(
            'hamnaghsheh-admin-tickets',
            HAMNAGHSHEH_TICKETING_PLUGIN_URL . 'assets/js/admin-tickets.js',
            ['jquery'],
            HAMNAGHSHEH_TICKETING_VERSION,
            true
        );

        wp_localize_script('hamnaghsheh-admin-tickets', 'hamnaghshehAdminTickets', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hamnaghsheh_admin_ticket_nonce'),
            'strings' => [
                'error' => 'خطایی رخ داد. لطفا دوباره تلاش کنید.',
                'success' => 'عملیات با موفقیت انجام شد.',
                'confirm_close' => 'آیا از بستن این تیکت اطمینان دارید؟ این عمل قابل بازگشت نیست.'
            ]
        ]);
    }

    /**
     * Get all tickets
     */
    public function get_all_tickets($filters = []) {
        global $wpdb;
        
        $query = "SELECT t.*, u.display_name as user_name 
                  FROM {$wpdb->prefix}hamnaghsheh_tickets t
                  LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $query .= " AND t.status = %s";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['category'])) {
            $query .= " AND t.category = %s";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['priority'])) {
            $query .= " AND t.priority = %s";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['search'])) {
            $query .= " AND (t.ticket_number LIKE %s OR t.title LIKE %s)";
            $search = '%' . $wpdb->esc_like($filters['search']) . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $query .= " ORDER BY t.updated_at DESC";
        
        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($query, $params));
        }
        
        return $wpdb->get_results($query);
    }

    /**
     * Get ticket by ID
     */
    public function get_ticket($ticket_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT t.*, u.display_name as user_name, u.user_email 
             FROM {$wpdb->prefix}hamnaghsheh_tickets t
             LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID
             WHERE t.id = %d",
            $ticket_id
        ));
    }

    /**
     * Get ticket replies
     */
    public function get_ticket_replies($ticket_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, u.display_name as user_name 
             FROM {$wpdb->prefix}hamnaghsheh_ticket_replies r
             LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
             WHERE r.ticket_id = %d 
             ORDER BY r.created_at ASC",
            $ticket_id
        ));
    }

    /**
     * Get admin notes
     */
    public function get_admin_notes($ticket_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT n.*, u.display_name as admin_name 
             FROM {$wpdb->prefix}hamnaghsheh_ticket_admin_notes n
             LEFT JOIN {$wpdb->users} u ON n.admin_id = u.ID
             WHERE n.ticket_id = %d 
             ORDER BY n.created_at DESC",
            $ticket_id
        ));
    }

    /**
     * Get open tickets count
     */
    private function get_open_tickets_count() {
        global $wpdb;
        
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hamnaghsheh_tickets 
             WHERE status IN ('open', 'in_progress')"
        );
    }

    /**
     * Get ticket stats
     */
    public function get_ticket_stats() {
        global $wpdb;
        
        $stats = [];
        
        $stats['open'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE status = 'open'"
        );
        
        $stats['in_progress'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE status = 'in_progress'"
        );
        
        $stats['waiting_customer'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE status = 'waiting_customer'"
        );
        
        $stats['resolved'] = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE status = 'resolved'"
        );
        
        return $stats;
    }

    /**
     * AJAX: Admin reply to ticket
     */
    public function ajax_reply_ticket() {
        check_ajax_referer('hamnaghsheh_admin_ticket_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'شما اجازه این عملیات را ندارید.']);
        }
        
        $ticket_id = intval($_POST['ticket_id']);
        $message = wp_kses_post($_POST['message']);
        
        if (empty($message)) {
            wp_send_json_error(['message' => 'لطفا پیام خود را وارد کنید.']);
        }
        
        global $wpdb;
        
        // Handle file attachments
        $attachments = [];
        if (!empty($_FILES['attachments'])) {
            $attachments = $this->handle_file_uploads($ticket_id, $_FILES['attachments']);
        }
        
        // Insert reply
        $wpdb->insert(
            $wpdb->prefix . 'hamnaghsheh_ticket_replies',
            [
                'ticket_id' => $ticket_id,
                'user_id' => get_current_user_id(),
                'message' => $message,
                'is_admin_reply' => 1,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%d', '%s', '%s']
        );
        
        // Update ticket status to waiting_customer and updated_at
        $wpdb->update(
            $wpdb->prefix . 'hamnaghsheh_tickets',
            [
                'status' => 'waiting_customer',
                'updated_at' => current_time('mysql')
            ],
            ['id' => $ticket_id],
            ['%s', '%s'],
            ['%d']
        );
        
        // Send email notification to user
        Hamnaghsheh_Email_Notifications::send_admin_reply_notification($ticket_id, $message);
        
        wp_send_json_success(['message' => 'پاسخ شما ثبت شد.']);
    }

    /**
     * AJAX: Update ticket status
     */
    public function ajax_update_status() {
        check_ajax_referer('hamnaghsheh_admin_ticket_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'شما اجازه این عملیات را ندارید.']);
        }
        
        $ticket_id = intval($_POST['ticket_id']);
        $status = sanitize_text_field($_POST['status']);
        
        global $wpdb;
        
        $update_data = [
            'status' => $status,
            'updated_at' => current_time('mysql')
        ];
        
        // If status is resolved, send notification
        if ($status === 'resolved') {
            Hamnaghsheh_Email_Notifications::send_resolved_notification($ticket_id);
        }
        
        $wpdb->update(
            $wpdb->prefix . 'hamnaghsheh_tickets',
            $update_data,
            ['id' => $ticket_id],
            ['%s', '%s'],
            ['%d']
        );
        
        wp_send_json_success(['message' => 'وضعیت تیکت به‌روزرسانی شد.']);
    }

    /**
     * AJAX: Set ticket priority
     */
    public function ajax_set_priority() {
        check_ajax_referer('hamnaghsheh_admin_ticket_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'شما اجازه این عملیات را ندارید.']);
        }
        
        $ticket_id = intval($_POST['ticket_id']);
        $priority = sanitize_text_field($_POST['priority']);
        
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'hamnaghsheh_tickets',
            ['priority' => $priority, 'updated_at' => current_time('mysql')],
            ['id' => $ticket_id],
            ['%s', '%s'],
            ['%d']
        );
        
        wp_send_json_success(['message' => 'اولویت تیکت به‌روزرسانی شد.']);
    }

    /**
     * AJAX: Add admin note
     */
    public function ajax_add_note() {
        check_ajax_referer('hamnaghsheh_admin_ticket_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'شما اجازه این عملیات را ندارید.']);
        }
        
        $ticket_id = intval($_POST['ticket_id']);
        $note = wp_kses_post($_POST['note']);
        
        if (empty($note)) {
            wp_send_json_error(['message' => 'لطفا یادداشت خود را وارد کنید.']);
        }
        
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'hamnaghsheh_ticket_admin_notes',
            [
                'ticket_id' => $ticket_id,
                'admin_id' => get_current_user_id(),
                'note' => $note,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%s']
        );
        
        wp_send_json_success(['message' => 'یادداشت اضافه شد.']);
    }

    /**
     * AJAX: Close ticket
     */
    public function ajax_close_ticket() {
        check_ajax_referer('hamnaghsheh_admin_ticket_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'شما اجازه این عملیات را ندارید.']);
        }
        
        $ticket_id = intval($_POST['ticket_id']);
        
        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'hamnaghsheh_tickets',
            [
                'status' => 'closed',
                'closed_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $ticket_id],
            ['%s', '%s', '%s'],
            ['%d']
        );
        
        wp_send_json_success(['message' => 'تیکت بسته شد.']);
    }

    /**
     * Handle file uploads
     */
    private function handle_file_uploads($ticket_id, $files) {
        $upload_dir = wp_upload_dir();
        $ticket_dir = $upload_dir['basedir'] . '/hamnaghsheh/tickets/' . $ticket_id;
        
        if (!file_exists($ticket_dir)) {
            wp_mkdir_p($ticket_dir);
        }
        
        $uploaded_files = [];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 
                         'text/plain', 'application/msword', 
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (is_array($files['name'])) {
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file_type = $files['type'][$i];
                    $file_size = $files['size'][$i];
                    
                    if (!in_array($file_type, $allowed_types) || $file_size > $max_size) {
                        continue;
                    }
                    
                    $filename = wp_generate_uuid4() . '_' . sanitize_file_name($files['name'][$i]);
                    $filepath = $ticket_dir . '/' . $filename;
                    
                    if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                        $uploaded_files[] = $filename;
                    }
                }
            }
        }
        
        return $uploaded_files;
    }

    /**
     * Get priorities
     */
    public static function get_priorities() {
        return [
            'urgent' => ['label' => 'فوری', 'color' => '#DC2626', 'badge' => 'bg-red-600'],
            'high' => ['label' => 'بالا', 'color' => '#F59E0B', 'badge' => 'bg-orange-500'],
            'normal' => ['label' => 'متوسط', 'color' => '#10B981', 'badge' => 'bg-green-500'],
            'low' => ['label' => 'پایین', 'color' => '#6B7280', 'badge' => 'bg-gray-500']
        ];
    }
}
