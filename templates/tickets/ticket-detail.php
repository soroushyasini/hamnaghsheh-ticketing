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

$upload_dir = wp_upload_dir();
$ticket_dir_url = $upload_dir['baseurl'] . '/hamnaghsheh/tickets/' . $ticket->id;

$is_closed = $ticket->status === 'closed';
?>

<div class="hamnaghsheh-ticket-detail-wrapper" dir="rtl">
    <!-- Back Button -->
    <div style="margin-bottom: 20px;">
        <a href="<?php echo esc_url(remove_query_arg('id')); ?>" class="button">← بازگشت به لیست تیکت‌ها</a>
    </div>

    <!-- Ticket Header -->
    <div class="ticket-header" style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
            <div>
                <h2 style="margin: 0 0 10px 0;"><?php echo esc_html($ticket->title); ?></h2>
                <p style="margin: 0; color: #666;">
                    شماره تیکت: <strong><?php echo esc_html($ticket->ticket_number); ?></strong>
                </p>
            </div>
            <div>
                <span class="status-badge <?php echo esc_attr(Hamnaghsheh_Tickets::get_status_badge_class($ticket->status)); ?>" 
                      style="padding: 6px 16px; border-radius: 16px; font-size: 14px; display: inline-block;">
                    <?php echo esc_html($statuses[$ticket->status] ?? $ticket->status); ?>
                </span>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; padding-top: 15px; border-top: 1px solid #eee;">
            <div>
                <small style="color: #666;">دسته‌بندی:</small>
                <p style="margin: 5px 0 0 0; font-weight: bold;">
                    <?php echo esc_html($categories[$ticket->category] ?? $ticket->category); ?>
                </p>
            </div>
            <div>
                <small style="color: #666;">تاریخ ایجاد:</small>
                <p style="margin: 5px 0 0 0; font-weight: bold;">
                    <?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($ticket->created_at))); ?>
                </p>
            </div>
            <div>
                <small style="color: #666;">آخرین به‌روزرسانی:</small>
                <p style="margin: 5px 0 0 0; font-weight: bold;">
                    <?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($ticket->updated_at))); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Conversation Thread -->
    <div class="ticket-conversation" style="margin-bottom: 30px;">
        <?php foreach ($replies as $reply): 
            $reply_user = get_userdata($reply->user_id);
            $is_admin = (bool) $reply->is_admin_reply;
            $attachments = !empty($reply->attachments) ? json_decode($reply->attachments, true) : [];
        ?>
            <div class="reply-item" style="background: <?php echo $is_admin ? '#f0f8ff' : 'white'; ?>; padding: 20px; margin-bottom: 15px; border-radius: 8px; border-right: 4px solid <?php echo $is_admin ? '#09375B' : '#FFCF00'; ?>; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div class="reply-header" style="display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                    <div>
                        <strong style="color: <?php echo $is_admin ? '#09375B' : '#333'; ?>;">
                            <?php echo esc_html($reply_user ? $reply_user->display_name : 'کاربر'); ?>
                            <?php if ($is_admin): ?>
                                <span style="background: #09375B; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-right: 5px;">مدیر</span>
                            <?php endif; ?>
                        </strong>
                    </div>
                    <div style="color: #666; font-size: 14px;">
                        <?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($reply->created_at))); ?>
                    </div>
                </div>
                
                <div class="reply-message" style="line-height: 1.6;">
                    <?php echo wp_kses_post($reply->message); ?>
                </div>
                
                <?php if (!empty($attachments)): ?>
                    <div class="reply-attachments" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                        <strong style="display: block; margin-bottom: 10px;">فایل‌های پیوست:</strong>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <?php foreach ($attachments as $file): 
                                $file_url = $ticket_dir_url . '/' . $file;
                                $file_ext = pathinfo($file, PATHINFO_EXTENSION);
                                $is_image = in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png', 'gif']);
                            ?>
                                <?php if ($is_image): ?>
                                    <a href="<?php echo esc_url($file_url); ?>" target="_blank">
                                        <img src="<?php echo esc_url($file_url); ?>" 
                                             alt="<?php echo esc_attr($file); ?>"
                                             style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; border-radius: 4px;">
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo esc_url($file_url); ?>" 
                                       download
                                       class="attachment-file"
                                       style="display: inline-block; padding: 8px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333;">
                                        📎 <?php echo esc_html($file); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Reply Form -->
    <?php if ($is_closed): ?>
        <div class="ticket-closed-notice" style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; text-align: center;">
            <strong>این تیکت بسته شده است و امکان پاسخ به آن وجود ندارد.</strong>
        </div>
    <?php else: ?>
        <div class="reply-form-wrapper" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">ارسال پاسخ</h3>
            
            <form id="reply-form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="ticket_id" value="<?php echo esc_attr($ticket->id); ?>">
                
                <div class="form-row" style="margin-bottom: 15px;">
                    <label for="reply-message" style="display: block; margin-bottom: 5px; font-weight: bold;">
                        پیام شما <span style="color: red;">*</span>
                    </label>
                    <textarea id="reply-message" 
                              name="message" 
                              required 
                              rows="5"
                              style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                              placeholder="پاسخ خود را بنویسید..."></textarea>
                </div>

                <div class="form-row" style="margin-bottom: 15px;">
                    <label for="reply-attachments" style="display: block; margin-bottom: 5px; font-weight: bold;">
                        فایل‌های پیوست (اختیاری)
                    </label>
                    <input type="file" 
                           id="reply-attachments" 
                           name="attachments[]" 
                           multiple
                           accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.doc,.docx"
                           style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <small style="display: block; margin-top: 5px; color: #666;">
                        حداکثر حجم هر فایل: 5MB
                    </small>
                </div>

                <div class="form-row">
                    <button type="submit" 
                            class="button button-primary" 
                            style="background: #09375B; border-color: #09375B; padding: 10px 30px;">
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
                    $('#reply-message-box').html('<div class="notice notice-success" style="padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 4px;">' + response.data.message + '</div>');
                    
                    // Reload page to show new reply
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    $('#reply-message-box').html('<div class="notice notice-error" style="padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px;">' + response.data.message + '</div>');
                    submitBtn.prop('disabled', false).text('ارسال پاسخ');
                }
            },
            error: function() {
                $('#reply-message-box').html('<div class="notice notice-error" style="padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px;">' + hamnaghshehTickets.strings.error + '</div>');
                submitBtn.prop('disabled', false).text('ارسال پاسخ');
            }
        });
    });
});
</script>
