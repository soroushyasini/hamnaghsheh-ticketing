<?php
/**
 * Template for displaying single ticket detail
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/templates/tickets
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$tickets_instance = new Hamnaghsheh_Tickets();
$replies = $tickets_instance->get_ticket_replies($ticket->id);
$statuses = Hamnaghsheh_Tickets::get_statuses();
$categories = Hamnaghsheh_Tickets::get_categories();
$priorities = Hamnaghsheh_Tickets::get_priorities();

$upload_dir = wp_upload_dir();
$ticket_dir_url = $upload_dir['baseurl'] . '/hamnaghsheh/tickets/' . $ticket->id;

$is_closed = $ticket->status === 'closed';
?>

<div class="hamnaghsheh-ticket-detail-wrapper">
    <!-- Back Button -->
    <div style="margin-bottom: 20px;">
        <a href="<?php echo esc_url(remove_query_arg('id')); ?>" class="btn-secondary">← بازگشت به لیست تیکت‌ها</a>
    </div>

    <!-- Ticket Header -->
    <div class="ticket-detail-header">
        <h1><?php echo esc_html($ticket->title); ?></h1>
        
        <div class="ticket-badges">
            <span class="ticket-number">#<?php echo esc_html($ticket->ticket_number); ?></span>
            <span class="status-badge status-<?php echo esc_attr($ticket->status); ?>">
                <?php echo esc_html($statuses[$ticket->status] ?? $ticket->status); ?>
            </span>
            <span class="category-badge"><?php echo esc_html($categories[$ticket->category] ?? $ticket->category); ?></span>
            <?php if (isset($ticket->priority) && $ticket->priority !== 'normal'): ?>
                <span class="priority-badge priority-<?php echo esc_attr($ticket->priority); ?>">
                    <?php echo esc_html($priorities[$ticket->priority] ?? $ticket->priority); ?>
                </span>
            <?php endif; ?>
        </div>
        
        <div class="ticket-info-grid">
            <div class="info-item">
                <label>تاریخ ایجاد</label>
                <span><?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($ticket->created_at))); ?></span>
            </div>
            <div class="info-item">
                <label>آخرین بروزرسانی</label>
                <span><?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($ticket->updated_at))); ?></span>
            </div>
            <?php if (isset($ticket->project_id) && $ticket->project_id): ?>
            <div class="info-item">
                <label>پروژه مرتبط</label>
                <span><?php echo esc_html($ticket->project_id); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Conversation Thread -->
    <div class="conversation-container">
        <?php foreach ($replies as $reply): 
            $reply_user = get_userdata($reply->user_id);
            $is_admin = (bool) $reply->is_admin_reply;
            $attachments = !empty($reply->attachments) ? json_decode($reply->attachments, true) : [];
        ?>
            <div class="message-bubble <?php echo $is_admin ? 'admin-message' : 'user-message'; ?>">
                <div class="message-header">
                    <span class="message-author">
                        <?php echo $is_admin ? '👨‍💼 پشتیبانی همنقشه' : '👤 شما'; ?>
                    </span>
                    <span class="message-time">
                        <?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($reply->created_at))); ?>
                    </span>
                </div>
                <div class="message-content">
                    <?php echo nl2br(esc_html($reply->message)); ?>
                </div>
                <?php if (!empty($attachments)): ?>
                    <div class="message-attachments">
                        <?php foreach ($attachments as $file): 
                            $file_url = $ticket_dir_url . '/' . $file;
                            $file_ext = pathinfo($file, PATHINFO_EXTENSION);
                            $is_image = in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png', 'gif']);
                        ?>
                            <?php if ($is_image): ?>
                                <a href="<?php echo esc_url($file_url); ?>" target="_blank">
                                    <img src="<?php echo esc_url($file_url); ?>" 
                                         alt="<?php echo esc_attr($file); ?>"
                                         class="attachment-image">
                                </a>
                            <?php else: ?>
                                <a href="<?php echo esc_url($file_url); ?>" 
                                   download
                                   class="attachment-file">
                                    📎 <?php echo esc_html($file); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Reply Form -->
    <?php if ($is_closed): ?>
        <div class="ticket-closed-notice">
            <strong>این تیکت بسته شده است و امکان پاسخ به آن وجود ندارد.</strong>
        </div>
    <?php else: ?>
        <div class="reply-form-container">
            <h3>ارسال پاسخ</h3>
            
            <form id="reply-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="ticket_id" value="<?php echo esc_attr($ticket->id); ?>">
                
                <div class="form-group">
                    <textarea id="reply-message" 
                              name="message" 
                              required 
                              rows="5"
                              placeholder="پیام خود را بنویسید..."></textarea>
                </div>

                <div class="form-group">
                    <input type="file" 
                           id="reply-attachments" 
                           name="attachments[]" 
                           multiple
                           accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.doc,.docx">
                </div>

                <div style="text-align: center;">
                    <button type="submit" class="btn-primary">
                        ارسال پاسخ
                    </button>
                </div>

                <div id="reply-message-box" style="margin-top: 15px;"></div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('#reply-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'hamnaghsheh_reply_ticket');
        formData.append('nonce', hamnaghshehTickets.nonce);
        
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('در حال ارسال...');
        
        $.ajax({
            url: hamnaghshehTickets.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#reply-message-box').html('<div class="notice notice-success">' + response.data.message + '</div>');
                    
                    // Reload page to show new reply
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $('#reply-message-box').html('<div class="notice notice-error">' + response.data.message + '</div>');
                    submitBtn.prop('disabled', false).text('ارسال پاسخ');
                }
            },
            error: function() {
                $('#reply-message-box').html('<div class="notice notice-error">' + hamnaghshehTickets.strings.error + '</div>');
                submitBtn.prop('disabled', false).text('ارسال پاسخ');
            }
        });
    });
});
</script>
