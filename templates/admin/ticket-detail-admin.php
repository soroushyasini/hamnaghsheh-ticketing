<?php
/**
 * Admin Template for Ticket Detail
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/templates/admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$admin_tickets = new Hamnaghsheh_Admin_Tickets();
$ticket = $admin_tickets->get_ticket($ticket_id);

if (!$ticket) {
    echo '<div class="wrap"><h1>تیکت یافت نشد</h1></div>';
    return;
}

$replies = $admin_tickets->get_ticket_replies($ticket_id);
$admin_notes = $admin_tickets->get_admin_notes($ticket_id);

$statuses = Hamnaghsheh_Tickets::get_statuses();
$categories = Hamnaghsheh_Tickets::get_categories();
$priorities = Hamnaghsheh_Admin_Tickets::get_priorities();

$upload_dir = wp_upload_dir();
$ticket_dir_url = $upload_dir['baseurl'] . '/hamnaghsheh/tickets/' . $ticket_id;

$is_closed = $ticket->status === 'closed';
$priority_data = $priorities[$ticket->priority] ?? $priorities['normal'];
?>

<div class="wrap" dir="rtl">
    <h1 class="wp-heading-inline">جزئیات تیکت #<?php echo esc_html($ticket->ticket_number); ?></h1>
    <a href="?page=hamnaghsheh-tickets" class="page-title-action">← بازگشت به لیست</a>
    <hr class="wp-header-end">

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
        <!-- Main Content -->
        <div>
            <!-- Ticket Info -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2><?php echo esc_html($ticket->title); ?></h2>
                </div>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th>شماره تیکت:</th>
                            <td><strong><?php echo esc_html($ticket->ticket_number); ?></strong></td>
                        </tr>
                        <tr>
                            <th>کاربر:</th>
                            <td>
                                <?php echo esc_html($ticket->user_name); ?>
                                <br><small><?php echo esc_html($ticket->user_email); ?></small>
                            </td>
                        </tr>
                        <tr>
                            <th>دسته‌بندی:</th>
                            <td><?php echo esc_html($categories[$ticket->category] ?? $ticket->category); ?></td>
                        </tr>
                        <tr>
                            <th>تاریخ ایجاد:</th>
                            <td><?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($ticket->created_at))); ?></td>
                        </tr>
                        <tr>
                            <th>آخرین به‌روزرسانی:</th>
                            <td><?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($ticket->updated_at))); ?></td>
                        </tr>
                        <?php if ($ticket->project_id): ?>
                        <tr>
                            <th>پروژه مرتبط:</th>
                            <td>پروژه #<?php echo esc_html($ticket->project_id); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($ticket->order_id): ?>
                        <tr>
                            <th>سفارش مرتبط:</th>
                            <td>سفارش #<?php echo esc_html($ticket->order_id); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Conversation -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>گفتگو</h2>
                </div>
                <div class="inside">
                    <?php foreach ($replies as $reply): 
                        $reply_user = get_userdata($reply->user_id);
                        $is_admin = (bool) $reply->is_admin_reply;
                        $attachments = !empty($reply->attachments) ? json_decode($reply->attachments, true) : [];
                    ?>
                        <div style="background: <?php echo $is_admin ? '#f0f8ff' : '#f9f9f9'; ?>; padding: 15px; margin-bottom: 15px; border-radius: 5px; border-right: 3px solid <?php echo $is_admin ? '#2271b1' : '#f0c36d'; ?>;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
                                <div>
                                    <strong><?php echo esc_html($reply->user_name); ?></strong>
                                    <?php if ($is_admin): ?>
                                        <span style="background: #2271b1; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-right: 5px;">مدیر</span>
                                    <?php endif; ?>
                                </div>
                                <small style="color: #666;">
                                    <?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($reply->created_at))); ?>
                                </small>
                            </div>
                            
                            <div><?php echo wp_kses_post($reply->message); ?></div>
                            
                            <?php if (!empty($attachments)): ?>
                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                                    <strong style="display: block; margin-bottom: 5px;">فایل‌های پیوست:</strong>
                                    <?php foreach ($attachments as $file): 
                                        $file_url = $ticket_dir_url . '/' . $file;
                                        $file_ext = pathinfo($file, PATHINFO_EXTENSION);
                                        $is_image = in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png', 'gif']);
                                    ?>
                                        <?php if ($is_image): ?>
                                            <a href="<?php echo esc_url($file_url); ?>" target="_blank">
                                                <img src="<?php echo esc_url($file_url); ?>" style="max-width: 150px; margin: 5px; border: 1px solid #ddd;">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo esc_url($file_url); ?>" download style="display: inline-block; margin: 5px; padding: 5px 10px; background: #fff; border: 1px solid #ddd; text-decoration: none;">
                                                📎 <?php echo esc_html($file); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Reply Form -->
            <?php if (!$is_closed): ?>
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>ارسال پاسخ</h2>
                </div>
                <div class="inside">
                    <form id="admin-reply-form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="ticket_id" value="<?php echo esc_attr($ticket_id); ?>">
                        
                        <p>
                            <label><strong>پیام:</strong></label>
                            <?php wp_editor('', 'admin_reply_message', ['textarea_rows' => 5, 'media_buttons' => false]); ?>
                        </p>

                        <p>
                            <label><strong>فایل‌های پیوست:</strong></label>
                            <input type="file" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.doc,.docx">
                            <br><small>حداکثر حجم هر فایل: 5MB</small>
                        </p>

                        <p>
                            <button type="submit" class="button button-primary">ارسال پاسخ</button>
                        </p>

                        <div id="admin-reply-message"></div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Admin Notes -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>یادداشت‌های داخلی (فقط برای مدیران)</h2>
                </div>
                <div class="inside">
                    <?php if (!empty($admin_notes)): ?>
                        <?php foreach ($admin_notes as $note): ?>
                            <div style="background: #fffbea; padding: 10px; margin-bottom: 10px; border-radius: 5px; border-right: 3px solid #f59e0b;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                    <strong><?php echo esc_html($note->admin_name); ?></strong>
                                    <small style="color: #666;">
                                        <?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($note->created_at))); ?>
                                    </small>
                                </div>
                                <div><?php echo wp_kses_post($note->note); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #666;">هنوز یادداشتی اضافه نشده است.</p>
                    <?php endif; ?>

                    <hr>

                    <form id="admin-note-form" method="post">
                        <input type="hidden" name="ticket_id" value="<?php echo esc_attr($ticket_id); ?>">
                        
                        <p>
                            <label><strong>یادداشت جدید:</strong></label>
                            <textarea name="note" rows="3" style="width: 100%;" placeholder="یادداشت داخلی (این یادداشت برای کاربر نمایش داده نمی‌شود)"></textarea>
                        </p>

                        <p>
                            <button type="submit" class="button">افزودن یادداشت</button>
                        </p>

                        <div id="admin-note-message"></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Status & Priority -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>وضعیت و اولویت</h2>
                </div>
                <div class="inside">
                    <p>
                        <label><strong>وضعیت:</strong></label>
                        <select id="ticket-status" style="width: 100%;">
                            <?php foreach ($statuses as $key => $label): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($ticket->status, $key); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p>
                        <label><strong>اولویت:</strong></label>
                        <select id="ticket-priority" style="width: 100%;">
                            <?php foreach ($priorities as $key => $data): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected($ticket->priority, $key); ?>>
                                    <?php echo esc_html($data['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <div id="status-priority-message"></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="postbox" style="margin-top: 20px;">
                <div class="postbox-header">
                    <h2>عملیات</h2>
                </div>
                <div class="inside">
                    <?php if (!$is_closed): ?>
                        <p>
                            <button type="button" id="close-ticket-btn" class="button button-secondary" style="width: 100%; background: #dc2626; color: white; border-color: #dc2626;">
                                بستن تیکت
                            </button>
                        </p>
                    <?php else: ?>
                        <p style="text-align: center; color: #666;">
                            <strong>این تیکت بسته شده است</strong>
                            <br><small>تاریخ بسته شدن: <?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($ticket->closed_at))); ?></small>
                        </p>
                    <?php endif; ?>

                    <div id="actions-message"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const ticketId = <?php echo (int) $ticket_id; ?>;

    // Admin Reply Form
    $('#admin-reply-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const message = tinymce.get('admin_reply_message').getContent();
        
        if (!message.trim()) {
            alert('لطفا پیام خود را وارد کنید.');
            return;
        }
        
        formData.append('action', 'hamnaghsheh_admin_reply_ticket');
        formData.append('nonce', hamnaghshehAdminTickets.nonce);
        formData.append('message', message);
        
        $.ajax({
            url: hamnaghshehAdminTickets.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    $('#admin-reply-message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            }
        });
    });

    // Admin Note Form
    $('#admin-note-form').on('submit', function(e) {
        e.preventDefault();
        
        const note = $(this).find('textarea[name="note"]').val();
        
        if (!note.trim()) {
            alert('لطفا یادداشت خود را وارد کنید.');
            return;
        }
        
        $.ajax({
            url: hamnaghshehAdminTickets.ajaxurl,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_admin_add_note',
                nonce: hamnaghshehAdminTickets.nonce,
                ticket_id: ticketId,
                note: note
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    $('#admin-note-message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            }
        });
    });

    // Status Change
    $('#ticket-status').on('change', function() {
        const status = $(this).val();
        
        $.ajax({
            url: hamnaghshehAdminTickets.ajaxurl,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_admin_update_status',
                nonce: hamnaghshehAdminTickets.nonce,
                ticket_id: ticketId,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    $('#status-priority-message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $('#status-priority-message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            }
        });
    });

    // Priority Change
    $('#ticket-priority').on('change', function() {
        const priority = $(this).val();
        
        $.ajax({
            url: hamnaghshehAdminTickets.ajaxurl,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_admin_set_priority',
                nonce: hamnaghshehAdminTickets.nonce,
                ticket_id: ticketId,
                priority: priority
            },
            success: function(response) {
                if (response.success) {
                    $('#status-priority-message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                } else {
                    $('#status-priority-message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            }
        });
    });

    // Close Ticket
    $('#close-ticket-btn').on('click', function() {
        if (!confirm(hamnaghshehAdminTickets.strings.confirm_close)) {
            return;
        }
        
        $.ajax({
            url: hamnaghshehAdminTickets.ajaxurl,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_admin_close_ticket',
                nonce: hamnaghshehAdminTickets.nonce,
                ticket_id: ticketId
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    $('#actions-message').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                }
            }
        });
    });
});
</script>
