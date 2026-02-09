<?php
/**
 * Email Notifications for Ticketing System
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/includes
 */

class Hamnaghsheh_Email_Notifications {

    /**
     * Send email notification when user creates a ticket
     */
    public static function send_new_ticket_notification($ticket_id) {
        global $wpdb;
        
        $ticket = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE id = %d",
            $ticket_id
        ));
        
        if (!$ticket) {
            return false;
        }
        
        $user = get_userdata($ticket->user_id);
        
        // Get all admins with hamnaghsheh_admin capability
        $admins = get_users(['role' => 'administrator']);
        
        $subject = sprintf('[تیکت جدید] %s - %s', $ticket->ticket_number, $ticket->title);
        
        $admin_url = admin_url('admin.php?page=hamnaghsheh-tickets&action=view&id=' . $ticket_id);
        
        $message = sprintf(
            "یک تیکت جدید ثبت شده است:\n\n" .
            "شماره تیکت: %s\n" .
            "عنوان: %s\n" .
            "دسته‌بندی: %s\n" .
            "کاربر: %s\n\n" .
            "مشاهده و پاسخ: %s",
            $ticket->ticket_number,
            $ticket->title,
            self::get_category_label($ticket->category),
            $user->display_name,
            $admin_url
        );
        
        foreach ($admins as $admin) {
            wp_mail($admin->user_email, $subject, $message, self::get_headers());
        }
        
        return true;
    }

    /**
     * Send email notification when admin replies to ticket
     */
    public static function send_admin_reply_notification($ticket_id, $reply_message) {
        global $wpdb;
        
        $ticket = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE id = %d",
            $ticket_id
        ));
        
        if (!$ticket) {
            return false;
        }
        
        $user = get_userdata($ticket->user_id);
        
        if (!$user) {
            return false;
        }
        
        $subject = sprintf('[پاسخ تیکت] %s', $ticket->ticket_number);
        
        $ticket_url = site_url('/tickets/?id=' . $ticket_id);
        
        $message = sprintf(
            "پاسخی به تیکت شما داده شد:\n\n" .
            "شماره تیکت: %s\n" .
            "عنوان: %s\n\n" .
            "پاسخ:\n%s\n\n" .
            "مشاهده تیکت: %s",
            $ticket->ticket_number,
            $ticket->title,
            wp_strip_all_tags($reply_message),
            $ticket_url
        );
        
        wp_mail($user->user_email, $subject, $message, self::get_headers());
        
        return true;
    }

    /**
     * Send email notification when user replies to ticket
     */
    public static function send_user_reply_notification($ticket_id, $reply_message) {
        global $wpdb;
        
        $ticket = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE id = %d",
            $ticket_id
        ));
        
        if (!$ticket) {
            return false;
        }
        
        $user = get_userdata($ticket->user_id);
        
        // Get all admins
        $admins = get_users(['role' => 'administrator']);
        
        $subject = sprintf('[پاسخ کاربر] %s', $ticket->ticket_number);
        
        $admin_url = admin_url('admin.php?page=hamnaghsheh-tickets&action=view&id=' . $ticket_id);
        
        $message = sprintf(
            "کاربر به تیکت پاسخ داده است:\n\n" .
            "شماره تیکت: %s\n" .
            "عنوان: %s\n" .
            "کاربر: %s\n\n" .
            "پاسخ:\n%s\n\n" .
            "مشاهده تیکت: %s",
            $ticket->ticket_number,
            $ticket->title,
            $user->display_name,
            wp_strip_all_tags($reply_message),
            $admin_url
        );
        
        foreach ($admins as $admin) {
            wp_mail($admin->user_email, $subject, $message, self::get_headers());
        }
        
        return true;
    }

    /**
     * Send email notification when ticket is resolved
     */
    public static function send_resolved_notification($ticket_id) {
        global $wpdb;
        
        $ticket = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}hamnaghsheh_tickets WHERE id = %d",
            $ticket_id
        ));
        
        if (!$ticket) {
            return false;
        }
        
        $user = get_userdata($ticket->user_id);
        
        if (!$user) {
            return false;
        }
        
        $subject = sprintf('[تیکت حل شد] %s', $ticket->ticket_number);
        
        $ticket_url = site_url('/tickets/?id=' . $ticket_id);
        
        $message = sprintf(
            "تیکت شما به وضعیت حل شده تغییر یافت:\n\n" .
            "شماره تیکت: %s\n" .
            "عنوان: %s\n\n" .
            "مشاهده تیکت: %s",
            $ticket->ticket_number,
            $ticket->title,
            $ticket_url
        );
        
        wp_mail($user->user_email, $subject, $message, self::get_headers());
        
        return true;
    }

    /**
     * Get email headers for RTL support
     */
    private static function get_headers() {
        $from_email = apply_filters('hamnaghsheh_ticketing_from_email', get_option('admin_email'));
        $from_name = apply_filters('hamnaghsheh_ticketing_from_name', get_bloginfo('name'));
        
        return [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>'
        ];
    }

    /**
     * Get category label in Persian
     */
    private static function get_category_label($category) {
        $categories = [
            'technical_support' => 'پشتیبانی فنی',
            'order_inquiry' => 'سوالات سفارش',
            'project_issue' => 'مشکلات پروژه',
            'general_question' => 'سوالات عمومی',
            'feature_request' => 'درخواست ویژگی',
            'bug_report' => 'گزارش باگ'
        ];
        
        return isset($categories[$category]) ? $categories[$category] : $category;
    }
}
