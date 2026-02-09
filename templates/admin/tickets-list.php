<?php
/**
 * Admin Template for Tickets List
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/templates/admin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$admin_tickets = new Hamnaghsheh_Admin_Tickets();

// Get filters
$filters = [
    'status' => isset($_GET['filter_status']) ? sanitize_text_field($_GET['filter_status']) : '',
    'category' => isset($_GET['filter_category']) ? sanitize_text_field($_GET['filter_category']) : '',
    'priority' => isset($_GET['filter_priority']) ? sanitize_text_field($_GET['filter_priority']) : '',
    'search' => isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '',
];

// Get tickets
$tickets = $admin_tickets->get_all_tickets($filters);

// Get stats
$stats = $admin_tickets->get_ticket_stats();

// Get options
$statuses = Hamnaghsheh_Tickets::get_statuses();
$categories = Hamnaghsheh_Tickets::get_categories();
$priorities = Hamnaghsheh_Admin_Tickets::get_priorities();
?>

<div class="wrap" dir="rtl">
    <h1 class="wp-heading-inline">مدیریت تیکت‌ها</h1>
    <hr class="wp-header-end">

    <!-- Stats -->
    <div class="ticket-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0;">
        <div style="background: #fff; padding: 15px; border-radius: 8px; border-right: 4px solid #2271b1; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <div style="font-size: 24px; font-weight: bold; color: #2271b1;"><?php echo $stats['open']; ?></div>
            <div style="color: #666;">تیکت‌های باز</div>
        </div>
        <div style="background: #fff; padding: 15px; border-radius: 8px; border-right: 4px solid #f59e0b; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <div style="font-size: 24px; font-weight: bold; color: #f59e0b;"><?php echo $stats['in_progress']; ?></div>
            <div style="color: #666;">در حال بررسی</div>
        </div>
        <div style="background: #fff; padding: 15px; border-radius: 8px; border-right: 4px solid #f97316; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <div style="font-size: 24px; font-weight: bold; color: #f97316;"><?php echo $stats['waiting_customer']; ?></div>
            <div style="color: #666;">در انتظار پاسخ کاربر</div>
        </div>
        <div style="background: #fff; padding: 15px; border-radius: 8px; border-right: 4px solid #10b981; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <div style="font-size: 24px; font-weight: bold; color: #10b981;"><?php echo $stats['resolved']; ?></div>
            <div style="color: #666;">حل شده</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="tablenav top">
        <form method="get" action="">
            <input type="hidden" name="page" value="hamnaghsheh-tickets">
            
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px;">وضعیت:</label>
                    <select name="filter_status" style="height: 32px;">
                        <option value="">همه</option>
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filters['status'], $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px;">دسته‌بندی:</label>
                    <select name="filter_category" style="height: 32px;">
                        <option value="">همه</option>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filters['category'], $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px;">اولویت:</label>
                    <select name="filter_priority" style="height: 32px;">
                        <option value="">همه</option>
                        <?php foreach ($priorities as $key => $data): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filters['priority'], $key); ?>>
                                <?php echo esc_html($data['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 5px;">جستجو:</label>
                    <input type="text" 
                           name="search" 
                           value="<?php echo esc_attr($filters['search']); ?>" 
                           placeholder="شماره تیکت یا عنوان"
                           style="height: 30px;">
                </div>

                <button type="submit" class="button">اعمال فیلتر</button>
                <a href="?page=hamnaghsheh-tickets" class="button">پاک کردن</a>
            </div>
        </form>
    </div>

    <!-- Tickets Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 120px;">شماره تیکت</th>
                <th>کاربر</th>
                <th>عنوان</th>
                <th>دسته‌بندی</th>
                <th>وضعیت</th>
                <th>اولویت</th>
                <th>تاریخ ایجاد</th>
                <th>آخرین به‌روزرسانی</th>
                <th style="width: 100px;">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tickets)): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 40px;">
                        هیچ تیکتی یافت نشد.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): 
                    $priority_data = $priorities[$ticket->priority] ?? $priorities['normal'];
                ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($ticket->ticket_number); ?></strong>
                        </td>
                        <td><?php echo esc_html($ticket->user_name); ?></td>
                        <td>
                            <a href="?page=hamnaghsheh-tickets&action=view&id=<?php echo esc_attr($ticket->id); ?>" 
                               style="font-weight: 500;">
                                <?php echo esc_html($ticket->title); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($categories[$ticket->category] ?? $ticket->category); ?></td>
                        <td>
                            <span class="status-badge" 
                                  style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; background: <?php echo Hamnaghsheh_Tickets::get_status_badge_class($ticket->status); ?>;">
                                <?php echo esc_html($statuses[$ticket->status] ?? $ticket->status); ?>
                            </span>
                        </td>
                        <td>
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; color: white; background: <?php echo esc_attr($priority_data['color']); ?>;">
                                <?php echo esc_html($priority_data['label']); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($ticket->created_at))); ?></td>
                        <td><?php echo esc_html(Hamnaghsheh_Ticketing_Jalali::jdate('Y/m/d H:i', strtotime($ticket->updated_at))); ?></td>
                        <td>
                            <a href="?page=hamnaghsheh-tickets&action=view&id=<?php echo esc_attr($ticket->id); ?>" 
                               class="button button-small">
                                مشاهده
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.status-badge {
    white-space: nowrap;
}
.bg-blue-600 { background-color: #2563eb !important; color: white; }
.bg-yellow-500 { background-color: #eab308 !important; color: white; }
.bg-orange-500 { background-color: #f97316 !important; color: white; }
.bg-green-600 { background-color: #16a34a !important; color: white; }
.bg-gray-500 { background-color: #6b7280 !important; color: white; }
</style>
