<?php
/**
 * Frontend Ticket Logic
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/includes
 */

class Hamnaghsheh_Tickets {

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'hamnaghsheh-tickets',
            HAMNAGHSHEH_TICKETING_PLUGIN_URL . 'assets/css/tickets.css',
            [],
            HAMNAGHSHEH_TICKETING_VERSION
        );

        wp_enqueue_script(
            'hamnaghsheh-tickets',
            HAMNAGHSHEH_TICKETING_PLUGIN_URL . 'assets/js/tickets.js',
            ['jquery'],
            HAMNAGHSHEH_TICKETING_VERSION,
            true
        );

        wp_localize_script('hamnaghsheh-tickets', 'hamnaghshehTickets', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hamnaghsheh_ticket_nonce'),
            'strings' => [
                'error' => 'خطایی رخ داد. لطفا دوباره تلاش کنید.',
                'success' => 'عملیات با موفقیت انجام شد.',
            ]
        ]);
    }

    /**
     * Render tickets list page (shortcode handler)
     */
    public function render_tickets_page($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            $redirect_url = urlencode(site_url('/tickets/'));
            wp_redirect(site_url('/auth/?redirect_to=' . $redirect_url));
            exit;
        }

        // Check if viewing single ticket
        if (isset($_GET['id'])) {
            return $this->render_ticket_detail();
        }

        ob_start();
        include HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'templates/tickets/ticket-list.php';
        return ob_get_clean();
    }

    /**
     * Render single ticket detail page
     */
    public function render_ticket_detail($atts = []) {
        if (!is_user_logged_in()) {
            $redirect_url = urlencode(site_url('/tickets/'));
            wp_redirect(site_url('/auth/?redirect_to=' . $redirect_url));
            exit;
        }

        $ticket_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$ticket_id) {
            return '<div class="error">تیکت یافت نشد.</div>';
        }

        $ticket = $this->get_ticket($ticket_id);
        
        if (!$ticket) {
            return '<div class="error">تیکت یافت نشد.</div>';
        }

        // Check permission
        if ($ticket->user_id != get_current_user_id() && !current_user_can('administrator')) {
            return '<div class="error">شما اجازه مشاهده این تیکت را ندارید.</div>';
        }

        ob_start();
        include HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'templates/tickets/ticket-detail.php';
        return ob_get_clean();
    }

    /**
     * Get ticket by ID
     */
    public function get_ticket($ticket_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE id = %d",
            $ticket_id
        ));
    }

    /**
     * Get user's tickets
     */
    public function get_user_tickets($user_id, $status = null) {
        global $wpdb;
        
        $query = "SELECT * FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE user_id = %d";
        $params = [$user_id];
        
        if ($status && $status !== 'all') {
            $query .= " AND status = %s";
            $params[] = $status;
        }
        
        $query .= " ORDER BY created_at DESC";
        
        return $wpdb->get_results($wpdb->prepare($query, $params));
    }

    /**
     * Get ticket replies
     */
    public function get_ticket_replies($ticket_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}hamnaghsheh_ticket_replies WHERE ticket_id = %d ORDER BY created_at ASC",
            $ticket_id
        ));
    }

    /**
     * Generate unique ticket number in Hijri format
     */
    public function generate_ticket_number() {
        global $wpdb;
        
        // Get current Persian date
        $persian_date = Hamnaghsheh_Ticketing_Jalali::jdate('Y-m-d');
        
        // Get today's ticket count
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE ticket_number LIKE %s",
            $persian_date . '%'
        ));
        
        $count = intval($count) + 1;
        
        // Format: 1404-11-20-001
        return $persian_date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * AJAX: Create new ticket
     */
    public function ajax_create_ticket() {
        check_ajax_referer('hamnaghsheh_ticket_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفا وارد شوید.']);
        }
        
        $user_id = get_current_user_id();
        
        // Sanitize inputs
        $title = sanitize_text_field($_POST['title']);
        $category = sanitize_text_field($_POST['category']);
        $message = wp_kses_post($_POST['message']);
        $project_id = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;
        $order_id = !empty($_POST['order_id']) ? intval($_POST['order_id']) : null;
        
        // Validate
        if (empty($title) || empty($category) || empty($message)) {
            wp_send_json_error(['message' => 'لطفا تمام فیلدهای الزامی را پر کنید.']);
        }
        
        if (strlen($title) > 255) {
            wp_send_json_error(['message' => 'عنوان نباید بیشتر از 255 کاراکتر باشد.']);
        }
        
        // Generate ticket number
        $ticket_number = $this->generate_ticket_number();
        
        global $wpdb;
        
        // Insert ticket
        $result = $wpdb->insert(
            $wpdb->prefix . 'hamnaghsheh_tickets',
            [
                'ticket_number' => $ticket_number,
                'user_id' => $user_id,
                'title' => $title,
                'category' => $category,
                'status' => 'open',
                'priority' => 'normal',
                'project_id' => $project_id,
                'order_id' => $order_id,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s']
        );
        
        if (!$result) {
            wp_send_json_error(['message' => 'خطا در ایجاد تیکت.']);
        }
        
        $ticket_id = $wpdb->insert_id;
        
        // Handle file attachments
        $attachments = [];
        if (!empty($_FILES['attachments'])) {
            $attachments = $this->handle_file_uploads($ticket_id, $_FILES['attachments']);
        }
        
        // Insert initial message as first reply
        $wpdb->insert(
            $wpdb->prefix . 'hamnaghsheh_ticket_replies',
            [
                'ticket_id' => $ticket_id,
                'user_id' => $user_id,
                'message' => $message,
                'is_admin_reply' => 0,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%d', '%s', '%s']
        );
        
        // Send email notification to admins
        Hamnaghsheh_Email_Notifications::send_new_ticket_notification($ticket_id);
        
        wp_send_json_success([
            'message' => 'تیکت با موفقیت ایجاد شد.',
            'ticket_id' => $ticket_id,
            'redirect_url' => site_url('/tickets/?id=' . $ticket_id)
        ]);
    }

    /**
     * AJAX: Reply to ticket
     */
    public function ajax_reply_ticket() {
        check_ajax_referer('hamnaghsheh_ticket_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفا وارد شوید.']);
        }
        
        $user_id = get_current_user_id();
        $ticket_id = intval($_POST['ticket_id']);
        $message = wp_kses_post($_POST['message']);
        
        if (empty($message)) {
            wp_send_json_error(['message' => 'لطفا پیام خود را وارد کنید.']);
        }
        
        // Get ticket
        $ticket = $this->get_ticket($ticket_id);
        
        if (!$ticket) {
            wp_send_json_error(['message' => 'تیکت یافت نشد.']);
        }
        
        // Check permission
        if ($ticket->user_id != $user_id && !current_user_can('administrator')) {
            wp_send_json_error(['message' => 'شما اجازه پاسخ به این تیکت را ندارید.']);
        }
        
        // Check if ticket is closed
        if ($ticket->status === 'closed') {
            wp_send_json_error(['message' => 'این تیکت بسته شده و نمی‌توان به آن پاسخ داد.']);
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
                'user_id' => $user_id,
                'message' => $message,
                'is_admin_reply' => 0,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
                'created_at' => current_time('mysql')
            ],
            ['%d', '%d', '%s', '%d', '%s', '%s']
        );
        
        // Update ticket status if was waiting_customer
        if ($ticket->status === 'waiting_customer') {
            $wpdb->update(
                $wpdb->prefix . 'hamnaghsheh_tickets',
                ['status' => 'in_progress', 'updated_at' => current_time('mysql')],
                ['id' => $ticket_id],
                ['%s', '%s'],
                ['%d']
            );
        } else {
            $wpdb->update(
                $wpdb->prefix . 'hamnaghsheh_tickets',
                ['updated_at' => current_time('mysql')],
                ['id' => $ticket_id],
                ['%s'],
                ['%d']
            );
        }
        
        // Send email notification to admins
        Hamnaghsheh_Email_Notifications::send_user_reply_notification($ticket_id, $message);
        
        wp_send_json_success(['message' => 'پاسخ شما ثبت شد.']);
    }

    /**
     * Handle file uploads for tickets
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
        
        // Handle multiple files
        if (is_array($files['name'])) {
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file_type = $files['type'][$i];
                    $file_size = $files['size'][$i];
                    
                    // Validate
                    if (!in_array($file_type, $allowed_types)) {
                        continue;
                    }
                    
                    if ($file_size > $max_size) {
                        continue;
                    }
                    
                    $filename = wp_generate_uuid4() . '_' . sanitize_file_name($files['name'][$i]);
                    $filepath = $ticket_dir . '/' . $filename;
                    
                    if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                        $uploaded_files[] = $filename;
                    }
                }
            }
        } else {
            // Single file
            if ($files['error'] === UPLOAD_ERR_OK) {
                $file_type = $files['type'];
                $file_size = $files['size'];
                
                if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                    $filename = wp_generate_uuid4() . '_' . sanitize_file_name($files['name']);
                    $filepath = $ticket_dir . '/' . $filename;
                    
                    if (move_uploaded_file($files['tmp_name'], $filepath)) {
                        $uploaded_files[] = $filename;
                    }
                }
            }
        }
        
        return $uploaded_files;
    }

    /**
     * AJAX: Upload attachment
     */
    public function ajax_upload_attachment() {
        check_ajax_referer('hamnaghsheh_ticket_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفا وارد شوید.']);
        }
        
        $ticket_id = intval($_POST['ticket_id']);
        
        if (empty($_FILES['file'])) {
            wp_send_json_error(['message' => 'فایلی انتخاب نشده است.']);
        }
        
        $uploaded = $this->handle_file_uploads($ticket_id, $_FILES['file']);
        
        if (empty($uploaded)) {
            wp_send_json_error(['message' => 'خطا در آپلود فایل.']);
        }
        
        wp_send_json_success(['files' => $uploaded]);
    }

    /**
     * AJAX: Get user projects
     */
    public function ajax_get_user_projects() {
        check_ajax_referer('hamnaghsheh_ticket_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفا وارد شوید.']);
        }
        
        // This would integrate with existing project system
        // For now, return empty array
        wp_send_json_success(['projects' => []]);
    }

    /**
     * AJAX: Get user orders
     */
    public function ajax_get_user_orders() {
        check_ajax_referer('hamnaghsheh_ticket_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'لطفا وارد شوید.']);
        }
        
        // This would integrate with existing order system (WooCommerce or custom)
        // For now, return empty array
        wp_send_json_success(['orders' => []]);
    }

    /**
     * Get categories
     */
    public static function get_categories() {
        return [
            'technical_support' => 'پشتیبانی فنی',
            'order_inquiry' => 'سوالات سفارش',
            'project_issue' => 'مشکلات پروژه',
            'general_question' => 'سوالات عمومی',
            'feature_request' => 'درخواست ویژگی',
            'bug_report' => 'گزارش باگ'
        ];
    }

    /**
     * Get statuses
     */
    public static function get_statuses() {
        return [
            'open' => 'باز',
            'in_progress' => 'در حال بررسی',
            'waiting_customer' => 'در انتظار پاسخ کاربر',
            'resolved' => 'حل شده',
            'closed' => 'بسته شده'
        ];
    }

    /**
     * Get status badge class
     */
    public static function get_status_badge_class($status) {
        $classes = [
            'open' => 'bg-blue-600 text-white',
            'in_progress' => 'bg-yellow-500 text-white',
            'waiting_customer' => 'bg-orange-500 text-white',
            'resolved' => 'bg-green-600 text-white',
            'closed' => 'bg-gray-500 text-white'
        ];
        
        return isset($classes[$status]) ? $classes[$status] : 'bg-gray-500 text-white';
    }

    /**
     * Render ticketing button shortcode
     * 
     * Shortcode: [hamnaghsheh_tickets_button]
     */
    public function render_tickets_button($atts) {
        $atts = shortcode_atts([
            'text' => 'تیکت‌ها',
            'show_count' => 'yes',
            'class' => 'hamnaghsheh-tickets-button'
        ], $atts);
        
        $url = hamnaghsheh_ticketing_url();
        $text = esc_html($atts['text']);
        $class = esc_attr($atts['class']);
        
        $badge_html = '';
        if ($atts['show_count'] === 'yes' && is_user_logged_in()) {
            $count = hamnaghsheh_get_user_open_tickets_count();
            if ($count > 0) {
                $badge_html = ' <span class="badge">' . $count . '</span>';
            }
        }
        
        return sprintf(
            '<a href="%s" class="%s">%s%s</a>',
            esc_url($url),
            $class,
            $text,
            $badge_html
        );
    }
}
