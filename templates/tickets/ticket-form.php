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

<form id="create-ticket-form-element" method="post" enctype="multipart/form-data" style="max-width: 800px;">
    <div class="form-row" style="margin-bottom: 15px;">
        <label for="ticket-title" style="display: block; margin-bottom: 5px; font-weight: bold;">
            عنوان تیکت <span style="color: red;">*</span>
        </label>
        <input type="text" 
               id="ticket-title" 
               name="title" 
               required 
               maxlength="255"
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
               placeholder="عنوان مشکل یا سوال خود را وارد کنید">
    </div>

    <div class="form-row" style="margin-bottom: 15px;">
        <label for="ticket-category" style="display: block; margin-bottom: 5px; font-weight: bold;">
            دسته‌بندی <span style="color: red;">*</span>
        </label>
        <select id="ticket-category" 
                name="category" 
                required
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="">انتخاب کنید...</option>
            <?php foreach ($categories as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row" style="margin-bottom: 15px;">
        <label for="ticket-message" style="display: block; margin-bottom: 5px; font-weight: bold;">
            توضیحات <span style="color: red;">*</span>
        </label>
        <textarea id="ticket-message" 
                  name="message" 
                  required 
                  rows="6"
                  style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                  placeholder="لطفا مشکل یا سوال خود را با جزئیات شرح دهید..."></textarea>
    </div>

    <div class="form-row" style="margin-bottom: 15px;">
        <label for="ticket-project" style="display: block; margin-bottom: 5px; font-weight: bold;">
            پروژه مرتبط (اختیاری)
        </label>
        <select id="ticket-project" 
                name="project_id"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="">هیچکدام</option>
            <!-- Projects will be loaded via AJAX if needed -->
        </select>
    </div>

    <div class="form-row" style="margin-bottom: 15px;">
        <label for="ticket-order" style="display: block; margin-bottom: 5px; font-weight: bold;">
            سفارش مرتبط (اختیاری)
        </label>
        <select id="ticket-order" 
                name="order_id"
                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <option value="">هیچکدام</option>
            <!-- Orders will be loaded via AJAX if needed -->
        </select>
    </div>

    <div class="form-row" style="margin-bottom: 15px;">
        <label for="ticket-attachments" style="display: block; margin-bottom: 5px; font-weight: bold;">
            فایل‌های پیوست (اختیاری)
        </label>
        <input type="file" 
               id="ticket-attachments" 
               name="attachments[]" 
               multiple
               accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.doc,.docx"
               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        <small style="display: block; margin-top: 5px; color: #666;">
            حداکثر حجم هر فایل: 5MB | فرمت‌های مجاز: jpg, png, gif, pdf, txt, doc, docx
        </small>
    </div>

    <div class="form-row">
        <button type="submit" 
                class="button button-primary" 
                style="background: #09375B; border-color: #09375B; padding: 10px 30px;">
            ثبت تیکت
        </button>
        <button type="button" 
                onclick="showCreateTicketForm()" 
                class="button" 
                style="margin-right: 10px; padding: 10px 30px;">
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
                    $('#form-message').html('<div class="notice notice-success" style="padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 4px;">' + response.data.message + '</div>');
                    
                    // Redirect to ticket detail page
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 1000);
                } else {
                    $('#form-message').html('<div class="notice notice-error" style="padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px;">' + response.data.message + '</div>');
                    submitBtn.prop('disabled', false).text('ثبت تیکت');
                }
            },
            error: function() {
                $('#form-message').html('<div class="notice notice-error" style="padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 4px;">' + hamnaghshehTickets.strings.error + '</div>');
                submitBtn.prop('disabled', false).text('ثبت تیکت');
            }
        });
    });
    
    // Load projects and orders via AJAX if needed
    // This would connect to existing systems
});
</script>
