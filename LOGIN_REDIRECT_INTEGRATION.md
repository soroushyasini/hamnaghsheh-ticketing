# Smart Login Redirect Integration Guide

This guide explains how to integrate the smart login redirect functionality into your theme or existing Hamnaghsheh plugin.

## Overview

The ticketing system includes smart login redirect that automatically sends users to the login page if they're not logged in, and then redirects them back to the tickets page after successful login.

## Method 1: Using Helper Function (Recommended)

### In Your Theme Header

Add a ticketing button to your header navigation:

```php
<a href="<?php echo hamnaghsheh_ticketing_url(); ?>" class="tickets-link">
    تیکت‌ها
</a>
```

### With Badge Count

Display the number of open tickets:

```php
<?php
$open_count = hamnaghsheh_get_user_open_tickets_count();
?>
<a href="<?php echo hamnaghsheh_ticketing_url(); ?>" class="tickets-link">
    تیکت‌ها
    <?php if ($open_count > 0): ?>
        <span class="badge"><?php echo $open_count; ?></span>
    <?php endif; ?>
</a>
```

## Method 2: Manual Redirect

### In a Template File

```php
<?php
if (is_user_logged_in()) {
    // User is logged in - go directly to tickets
    wp_redirect(site_url('/tickets/'));
} else {
    // User not logged in - go to login with redirect
    $redirect_url = urlencode(site_url('/tickets/'));
    wp_redirect(site_url('/auth/?redirect_to=' . $redirect_url));
}
exit;
?>
```

### In a Button Click Handler

```html
<button onclick="goToTickets()">تیکت‌های من</button>

<script>
function goToTickets() {
    <?php if (is_user_logged_in()): ?>
        window.location.href = '<?php echo site_url('/tickets/'); ?>';
    <?php else: ?>
        window.location.href = '<?php echo site_url('/auth/?redirect_to=' . urlencode(site_url('/tickets/'))); ?>';
    <?php endif; ?>
}
</script>
```

## Method 3: WordPress Menu Integration

### Add to WordPress Nav Menu

```php
/**
 * Add tickets link to WordPress menu
 */
function add_tickets_to_menu($items, $args) {
    if ($args->theme_location == 'primary') {
        $tickets_url = hamnaghsheh_ticketing_url();
        $open_count = is_user_logged_in() ? hamnaghsheh_get_user_open_tickets_count() : 0;
        
        $badge = $open_count > 0 ? ' <span class="badge">' . $open_count . '</span>' : '';
        
        $items .= '<li class="menu-item"><a href="' . esc_url($tickets_url) . '">تیکت‌ها' . $badge . '</a></li>';
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'add_tickets_to_menu', 10, 2);
```

## Method 4: Shortcode for Button

Use the built-in shortcode in any page or post:

```php
[hamnaghsheh_tickets_button]
```

This will render a button that automatically handles login redirect.

## Method 5: Programmatic Redirect

### In Plugin or Theme Functions

```php
// Redirect user to ticketing system with login check
hamnaghsheh_redirect_to_ticketing();
```

## Login Page Integration

### Standard WordPress Login

The plugin automatically hooks into WordPress login redirect:

```php
// This is already handled by the plugin
add_filter('login_redirect', 'hamnaghsheh_login_redirect', 10, 3);
```

### Custom Login Page

If you have a custom login page at `/auth/`:

```php
// In your login handler
if (isset($_GET['redirect_to'])) {
    $redirect_to = urldecode($_GET['redirect_to']);
    wp_redirect($redirect_to);
    exit;
}
```

## Complete Example: Dashboard Widget

```php
/**
 * Add ticketing dashboard widget
 */
function hamnaghsheh_dashboard_widget() {
    if (!is_user_logged_in()) {
        return;
    }
    
    $open_count = hamnaghsheh_get_user_open_tickets_count();
    ?>
    <div class="ticketing-widget">
        <h3>تیکت‌های من</h3>
        <p>تیکت‌های باز: <strong><?php echo $open_count; ?></strong></p>
        <a href="<?php echo hamnaghsheh_ticketing_url(); ?>" class="button button-primary">
            مشاهده تیکت‌ها
        </a>
    </div>
    <?php
}
```

## Available Helper Functions

### `hamnaghsheh_ticketing_url()`
Returns the appropriate URL (tickets page if logged in, login page if not)

### `get_hamnaghsheh_ticketing_url($force_login = false)`
Get URL with option to force login redirect

### `hamnaghsheh_redirect_to_ticketing()`
Programmatically redirect to ticketing system

### `hamnaghsheh_get_user_open_tickets_count($user_id = null)`
Get count of open tickets for a user

### `hamnaghsheh_user_can_view_ticket($ticket_id, $user_id = null)`
Check if user has permission to view a ticket

### `hamnaghsheh_get_ticket($ticket_id)`
Get ticket by ID

### `hamnaghsheh_get_ticket_by_number($ticket_number)`
Get ticket by ticket number

## Styling the Button

### CSS Example

```css
.tickets-link {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    background: #09375B;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 600;
    transition: background 0.2s;
}

.tickets-link:hover {
    background: #072847;
}

.tickets-link .badge {
    display: inline-block;
    margin-right: 5px;
    padding: 2px 6px;
    background: #FFCF00;
    color: #09375B;
    border-radius: 10px;
    font-size: 12px;
    font-weight: bold;
}
```

## User Account Menu Integration

### For Existing User Dashboard

```php
/**
 * Add tickets to user account menu
 */
add_filter('your_theme_account_menu', 'add_tickets_menu_item');

function add_tickets_menu_item($menu_items) {
    $open_count = hamnaghsheh_get_user_open_tickets_count();
    
    $menu_items['tickets'] = [
        'title' => 'تیکت‌ها' . ($open_count > 0 ? ' (' . $open_count . ')' : ''),
        'url' => site_url('/tickets/'),
        'icon' => 'dashicons-tickets'
    ];
    
    return $menu_items;
}
```

## WooCommerce Integration

### Add to My Account Menu

```php
/**
 * Add tickets to WooCommerce My Account menu
 */
add_filter('woocommerce_account_menu_items', 'add_tickets_to_wc_account');

function add_tickets_to_wc_account($items) {
    $new_items = [];
    
    foreach ($items as $key => $label) {
        $new_items[$key] = $label;
        
        // Add after dashboard
        if ($key === 'dashboard') {
            $new_items['tickets'] = 'تیکت‌ها';
        }
    }
    
    return $new_items;
}

/**
 * Add endpoint
 */
add_action('init', 'add_tickets_endpoint');

function add_tickets_endpoint() {
    add_rewrite_endpoint('tickets', EP_ROOT | EP_PAGES);
}

/**
 * Display tickets page content
 */
add_action('woocommerce_account_tickets_endpoint', 'display_tickets_endpoint');

function display_tickets_endpoint() {
    echo do_shortcode('[hamnaghsheh_tickets]');
}
```

## Troubleshooting

### Redirect Not Working

1. **Check WordPress Login URL**: Ensure `/auth/` is your actual login page, or update the URL in helper functions
2. **Permalink Issues**: Go to Settings → Permalinks and save to flush rewrite rules
3. **Plugin Conflicts**: Deactivate other plugins temporarily to test

### redirect_to Parameter Not Recognized

If your custom login page doesn't recognize `redirect_to`:

```php
// In your custom login handler
$redirect = isset($_GET['redirect_to']) ? urldecode($_GET['redirect_to']) : home_url();
wp_redirect($redirect);
```

### Badge Count Not Showing

Make sure user is logged in:

```php
if (is_user_logged_in()) {
    $count = hamnaghsheh_get_user_open_tickets_count();
    echo $count;
}
```

## Security Notes

- All redirect URLs are properly escaped and sanitized
- Only authenticated users can create/view tickets
- Ticket ownership is verified before displaying
- Nonce verification on all AJAX requests

## Support

For additional integration help, refer to `TICKETING_SYSTEM.md` or contact support.
