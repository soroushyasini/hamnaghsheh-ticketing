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
$priorities = Hamnaghsheh_Tickets::get_priorities();
?>

<div class="hamnaghsheh-tickets-wrapper">
    <div class="tickets-header">
        <h1>تیکتهای پشتیبانی</h1>
        <p>مدیریت و پیگیری درخواستهای شما</p>
    </div>

    <div class="filters-container">
        <div class="filter-group">
            <label>فیلتر بر اساس وضعیت:</label>
            <select id="status-filter" onchange="filterTickets(this.value)">
                <option value="all" <?php selected($status_filter, 'all'); ?>>همه</option>
                <?php foreach ($statuses as $key => $label): ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($status_filter, $key); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 30px;">
        <button onclick="showCreateTicketForm()" class="btn-primary">
            + ایجاد تیکت جدید
        </button>
    </div>

    <!-- Create Ticket Form (Hidden by default) -->
    <div id="create-ticket-form" style="display: none; margin-bottom: 30px;">
        <?php include HAMNAGHSHEH_TICKETING_PLUGIN_DIR . 'templates/tickets/ticket-form.php'; ?>
    </div>

    <!-- Tickets List -->
    <?php if (empty($tickets)): ?>
        <div class="ticket-card" style="text-align: center; cursor: default;">
            <p style="margin: 0; color: #6b7280; font-size: 16px;">شما هنوز تیکتی ثبت نکرده‌اید.</p>
        </div>
    <?php else: ?>
        <div class="tickets-list">
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-card" onclick="location.href='?id=<?php echo esc_attr($ticket->id); ?>'">
                    <div class="ticket-card-header">
                        <span class="ticket-number">#<?php echo esc_html($ticket->ticket_number); ?></span>
                        <div class="ticket-badges">
                            <span class="status-badge status-<?php echo esc_attr($ticket->status); ?>">
                                <?php echo esc_html($statuses[$ticket->status] ?? $ticket->status); ?>
                            </span>
                            <?php if (isset($ticket->priority) && $ticket->priority !== 'normal'): ?>
                                <span class="priority-badge priority-<?php echo esc_attr($ticket->priority); ?>">
                                    <?php echo esc_html($priorities[$ticket->priority] ?? $ticket->priority); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <h3 class="ticket-title"><?php echo esc_html($ticket->title); ?></h3>
                    
                    <span class="category-badge"><?php echo esc_html($categories[$ticket->category] ?? $ticket->category); ?></span>
                    
                    <div class="ticket-meta">
                        <span>📅 <?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d', strtotime($ticket->created_at))); ?></span>
                        <span>🕐 <?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('H:i', strtotime($ticket->updated_at))); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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
