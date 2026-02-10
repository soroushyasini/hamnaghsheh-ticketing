<?php
/**
 * Template for creating a new ticket
 * 
 * Note: This template is designed to be included within ticket-list.php
 * and relies on the showCreateTicketForm() function defined there.
 * If using independently, ensure JavaScript function is available.
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/templates/tickets
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$categories = Hamnaghsheh_Tickets::get_categories();
?>

<form id="create-ticket-form-element" class="ticket-form" method="post" enctype="multipart/form-data">
    <h2>فرم ثبت تیکت</h2>
    
    <div class="form-group">
        <label for="ticket-title">
            عنوان تیکت <span style="color: #dc2626;">*</span>
        </label>
        <input type="text" 
               id="ticket-title" 
               name="title" 
               required 
               maxlength="255"
               placeholder="عنوان مشکل یا سوال خود را وارد کنید">
    </div>

    <div class="form-group">
        <label for="ticket-category">
            دستهبندی <span style="color: #dc2626;">*</span>
        </label>
        <select id="ticket-category" 
                name="category" 
                required>
            <option value="">انتخاب کنید...</option>
            <?php foreach ($categories as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="ticket-message">
            توضیحات <span style="color: #dc2626;">*</span>
        </label>
        <textarea id="ticket-message" 
                  name="message" 
                  required 
                  rows="6"
                  placeholder="لطفا مشکل یا سوال خود را با جزئیات شرح دهید..."></textarea>
    </div>

    <div class="form-group">
        <label for="ticket-project">
            لینک به پروژه (اختیاری)
        </label>
        <select id="ticket-project" 
                name="project_id">
            <option value="">انتخاب نشده</option>
            <!-- Projects will be loaded via AJAX if needed -->
        </select>
    </div>

    <div class="form-group">
        <label for="ticket-order">
            لینک به سفارش (اختیاری)
        </label>
        <select id="ticket-order" 
                name="order_id">
            <option value="">انتخاب نشده</option>
            <!-- Orders will be loaded via AJAX if needed -->
        </select>
    </div>

    <div class="form-group">
        <label for="ticket-attachments">
            پیوست فایل (حداکثر 5 مگابایت)
        </label>
        <div class="file-upload-area">
            <input type="file" 
                   id="ticket-attachments" 
                   name="attachments[]" 
                   multiple
                   accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.doc,.docx">
            <p style="margin: 10px 0 0 0; color: #6b7280; font-size: 14px;">
                فایلهای خود را بکشید یا کلیک کنید
            </p>
        </div>
    </div>

    <div class="form-group" style="text-align: center;">
        <button type="submit" class="btn-primary">
            ثبت تیکت
        </button>
        <button type="button" 
                onclick="showCreateTicketForm()" 
                class="btn-secondary" 
                style="margin-right: 10px;">
            انصراف
        </button>
    </div>

    <div id="form-message" style="margin-top: 15px;"></div>
</form>

<script>
jQuery(document).ready(function($) {
    $('#create-ticket-form-element').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'hamnaghsheh_create_ticket');
        formData.append('nonce', hamnaghshehTickets.nonce);
        
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('در حال ثبت...');
        
        $.ajax({
            url: hamnaghshehTickets.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#form-message').html('<div class="notice notice-success">' + response.data.message + '</div>');
                    
                    // Redirect to ticket detail page
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 1000);
                } else {
                    $('#form-message').html('<div class="notice notice-error">' + response.data.message + '</div>');
                    submitBtn.prop('disabled', false).text('ثبت تیکت');
                }
            },
            error: function() {
                $('#form-message').html('<div class="notice notice-error">' + hamnaghshehTickets.strings.error + '</div>');
                submitBtn.prop('disabled', false).text('ثبت تیکت');
            }
        });
    });
    
    // Load projects and orders via AJAX if needed
    // This would connect to existing systems
});
</script>
