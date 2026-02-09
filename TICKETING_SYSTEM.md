# Hamnaghsheh Ticketing System Documentation

## Overview

The Hamnaghsheh Ticketing System is a comprehensive WordPress plugin that provides a complete support ticket management solution with Persian/Farsi language support and RTL layout.

## Features

- ✅ User ticket creation with optional project/order linking
- ✅ Admin ticket management with priority and status control
- ✅ Real-time email notifications
- ✅ File attachments (up to 5MB per file)
- ✅ Persian/Hijri date support for ticket numbers
- ✅ Internal admin notes (hidden from users)
- ✅ Smart login redirect
- ✅ Mobile responsive design
- ✅ RTL (Right-to-Left) layout support

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin panel
3. The plugin will automatically create required database tables
4. Navigate to "تیکتها" in the admin menu to start managing tickets

## User Guide

### Creating a Ticket

1. Go to the tickets page (using the `[hamnaghsheh_tickets]` shortcode)
2. Click "ایجاد تیکت جدید" button
3. Fill in the required fields:
   - **Title**: Brief description of your issue (max 255 characters)
   - **Category**: Select the appropriate category
   - **Description**: Detailed explanation of your problem
4. Optionally:
   - Link to a related project
   - Link to a related order
   - Attach files (images, PDFs, documents)
5. Click "ثبت تیکت" to submit

### Viewing Your Tickets

- All your tickets are listed on the main tickets page
- Filter by status using the dropdown menu
- Click on any ticket to view its details and conversation history

### Replying to a Ticket

1. Open the ticket detail page
2. Scroll to the reply form at the bottom
3. Type your message
4. Optionally attach files
5. Click "ارسال پاسخ" to submit

**Note**: Closed tickets cannot be replied to.

## Admin Guide

### Accessing Admin Panel

Navigate to **تیکتها** in the WordPress admin menu to access the ticket management dashboard.

### Dashboard Overview

The admin dashboard displays:
- **Statistics**: Quick view of ticket counts by status
- **Filters**: Search and filter tickets by status, category, priority
- **Ticket List**: All tickets with key information

### Managing a Ticket

When viewing a ticket detail, admins can:

1. **Reply to Customer**
   - Write a message using the rich text editor
   - Attach files if needed
   - Automatically changes ticket status to "در انتظار پاسخ کاربر"

2. **Change Status**
   - Open: New ticket
   - In Progress: Being worked on
   - Waiting on Customer: Awaiting user response
   - Resolved: Issue fixed
   - Closed: Ticket closed (cannot be reopened)

3. **Set Priority**
   - Urgent (فوری): Red badge
   - High (بالا): Orange badge
   - Normal (متوسط): Green badge
   - Low (پایین): Gray badge

4. **Add Internal Notes**
   - Private notes visible only to admins
   - Use for team collaboration
   - Not visible to customers

5. **Close Ticket**
   - Permanently closes the ticket
   - Cannot be reopened by users
   - Use only when ticket is fully resolved

### Email Notifications

The system automatically sends emails for:

1. **New Ticket Created** → To all administrators
2. **Admin Reply** → To ticket creator
3. **User Reply** → To all administrators
4. **Ticket Resolved** → To ticket creator

## Shortcodes

### `[hamnaghsheh_tickets]`

Displays the ticket list and create ticket form.

**Usage:**
```php
[hamnaghsheh_tickets]
```

Create a WordPress page and add this shortcode to enable ticketing for your users.

### `[hamnaghsheh_ticket_detail]`

Displays a single ticket (automatically used when `?id=123` is in the URL).

**Usage:**
```php
[hamnaghsheh_ticket_detail]
```

This shortcode is typically not used directly, as it's handled by the ticket list page.

## Categories

Available ticket categories:

- **پشتیبانی فنی** (Technical Support)
- **سوالات سفارش** (Order Inquiry)
- **مشکلات پروژه** (Project Issue)
- **سوالات عمومی** (General Question)
- **درخواست ویژگی** (Feature Request)
- **گزارش باگ** (Bug Report)

## Status Flow

```
Open (باز)
  ↓
In Progress (در حال بررسی)
  ↓
Waiting on Customer (در انتظار پاسخ کاربر)
  ↓
Resolved (حل شده)
  ↓
Closed (بسته شده) [FINAL]
```

**Automatic Status Changes:**
- When admin replies → Status changes to "Waiting on Customer"
- When user replies to "Waiting on Customer" → Status changes to "In Progress"

## File Attachments

**Allowed File Types:**
- Images: jpg, jpeg, png, gif
- Documents: pdf, txt, doc, docx

**Maximum Size:** 5MB per file

**Storage Location:** `/wp-content/uploads/hamnaghsheh/tickets/{ticket_id}/`

## Ticket Number Format

Tickets are assigned unique numbers in Persian/Hijri date format:

**Format:** `YYYY-MM-DD-XXX`

**Example:** `1404-11-20-001`

Where:
- `1404-11-20`: Persian date
- `001`: Daily incremental counter (resets each day)

## Login Redirect

When users click the ticketing button in the header:

```php
if (is_user_logged_in()) {
    // Direct access to tickets page
    wp_redirect(site_url('/tickets/'));
} else {
    // Redirect to login with return URL
    wp_redirect(site_url('/auth/?redirect_to=' . urlencode(site_url('/tickets/'))));
}
```

After successful login, users are automatically redirected back to the tickets page.

## Security Features

The plugin implements comprehensive security measures:

1. **Nonce Verification**: All AJAX requests verify nonces
2. **Capability Checks**: Admin actions require `manage_options` capability
3. **Ticket Ownership**: Users can only view their own tickets
4. **Input Sanitization**: All user inputs are sanitized
5. **SQL Injection Protection**: All database queries use prepared statements
6. **File Upload Security**: 
   - File type validation
   - File size limits
   - MIME type checking
   - Sanitized filenames

## Database Tables

### `wp_hamnaghsheh_tickets`
Main ticket information

### `wp_hamnaghsheh_ticket_replies`
Ticket conversation messages

### `wp_hamnaghsheh_ticket_admin_notes`
Internal admin notes (not visible to users)

## Troubleshooting

### Emails Not Sending

1. Check WordPress email configuration
2. Verify SMTP settings if using SMTP plugin
3. Check spam folder
4. Ensure admin users exist with valid email addresses

### File Upload Issues

1. Check PHP `upload_max_filesize` setting (should be > 5MB)
2. Verify directory permissions on `/wp-content/uploads/`
3. Check file type is in allowed list

### Tickets Not Showing

1. Ensure user is logged in
2. Check if shortcode is added to the page correctly
3. Verify database tables were created during activation

### Persian Date Not Working

The plugin includes a built-in Persian/Jalali calendar converter. If dates appear incorrect:

1. Check server timezone settings
2. Verify WordPress timezone in Settings → General

## Developer Notes

### Hooks and Filters

The plugin provides several hooks for customization:

**Actions:**
- `hamnaghsheh_ticket_created`: Fired when a new ticket is created
- `hamnaghsheh_ticket_replied`: Fired when a ticket receives a reply
- `hamnaghsheh_ticket_status_changed`: Fired when ticket status changes

**Filters:**
- `hamnaghsheh_ticket_categories`: Modify available categories
- `hamnaghsheh_ticket_statuses`: Modify available statuses
- `hamnaghsheh_ticket_priorities`: Modify priority levels

### Custom Integration

To integrate with existing projects/orders systems, modify:

- `ajax_get_user_projects()` in `class-tickets.php`
- `ajax_get_user_orders()` in `class-tickets.php`

### Styling Customization

Override styles by adding custom CSS in your theme:

```css
.hamnaghsheh-tickets-wrapper {
    /* Your custom styles */
}
```

## Support

For support and bug reports, please contact the Hamnaghsheh development team.

## License

GPL v2 or later

## Changelog

### Version 1.0.0
- Initial release
- Complete ticketing system implementation
- Persian/Hijri date support
- Email notifications
- File attachments
- Admin management interface
