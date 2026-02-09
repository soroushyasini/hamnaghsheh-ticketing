<?php
/**
 * Template for displaying user's ticket list
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/templates/tickets
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$user_id = get_current_user_id();
$tickets_instance = new Hamnaghsheh_Tickets();

// Get filter
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

// Get user's tickets
$tickets = $tickets_instance->get_user_tickets($user_id, $status_filter);

// Get statuses and categories
$statuses = Hamnaghsheh_Tickets::get_statuses();
$categories = Hamnaghsheh_Tickets::get_categories();
?>

<div class="hamnaghsheh-tickets-wrapper" dir="rtl">
    <div class="tickets-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">تیکت‌های من</h2>
        <button onclick="showCreateTicketForm()" class="button button-primary" style="background: #09375B; border-color: #09375B;">
            ایجاد تیکت جدید
        </button>
    </div>

    <!-- Filter -->
    <div class="tickets-filter" style="margin-bottom: 20px;">
        <label>فیلتر بر اساس وضعیت:</label>
        <select id="status-filter" onchange="filterTickets(this.value)" style="margin-right: 10px; padding: 5px;">
            <option value="all" <?php selected($status_filter, 'all'); ?>>همه</option>
            <?php foreach ($statuses as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($status_filter, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Create Ticket Form (Hidden by default) -->
    <div id="create-ticket-form" style="display: none; margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; background: #f9f9f9;">
        <h3>ایجاد تیکت جدید</h3>
        <?php include HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'templates/tickets/ticket-form.php'; ?>
    </div>

    <!-- Tickets List -->
    <?php if (empty($tickets)): ?>
        <div class="no-tickets" style="text-align: center; padding: 40px; background: #f9f9f9; border: 1px solid #ddd;">
            <p>شما هنوز تیکتی ثبت نکرده‌اید.</p>
        </div>
    <?php else: ?>
        <table class="tickets-table" style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <thead>
                <tr style="background: #09375B; color: white;">
                    <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">شماره تیکت</th>
                    <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">عنوان</th>
                    <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">دسته‌بندی</th>
                    <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">وضعیت</th>
                    <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">تاریخ ایجاد</th>
                    <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;"><?php echo esc_html($ticket->ticket_number); ?></td>
                        <td style="padding: 12px;"><?php echo esc_html($ticket->title); ?></td>
                        <td style="padding: 12px;"><?php echo esc_html($categories[$ticket->category] ?? $ticket->category); ?></td>
                        <td style="padding: 12px;">
                            <span class="status-badge <?php echo esc_attr(Hamnaghsheh_Tickets::get_status_badge_class($ticket->status)); ?>" 
                                  style="padding: 4px 12px; border-radius: 12px; font-size: 12px; display: inline-block;">
                                <?php echo esc_html($statuses[$ticket->status] ?? $ticket->status); ?>
                            </span>
                        </td>
                        <td style="padding: 12px;"><?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d', strtotime($ticket->created_at))); ?></td>
                        <td style="padding: 12px;">
                            <a href="?id=<?php echo esc_attr($ticket->id); ?>" class="button button-small">مشاهده</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function showCreateTicketForm() {
    const form = document.getElementById('create-ticket-form');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        window.scrollTo({top: form.offsetTop - 20, behavior: 'smooth'});
    } else {
        form.style.display = 'none';
    }
}

function filterTickets(status) {
    const url = new URL(window.location.href);
    if (status === 'all') {
        url.searchParams.delete('status');
    } else {
        url.searchParams.set('status', status);
    }
    window.location.href = url.toString();
}
</script>
