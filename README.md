# Hamnaghsheh Ticketing System

A comprehensive WordPress ticketing system plugin with Persian/Farsi language support and RTL layout.

## Features

- 🎫 Complete ticket management system
- 👥 User and admin interfaces
- 📧 Automatic email notifications
- 📎 File attachment support (up to 5MB)
- 🗓️ Persian/Hijri date support
- 🔒 Comprehensive security measures
- 📱 Mobile responsive design
- 🌐 RTL (Right-to-Left) layout support
- 🔔 Real-time status updates
- 📝 Internal admin notes

## Installation

1. Upload the plugin to `/wp-content/plugins/hamnaghsheh-ticketing/`
2. Activate the plugin through WordPress admin
3. Database tables will be created automatically
4. Add `[hamnaghsheh_tickets]` shortcode to a page
5. Start managing tickets!

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## Usage

### For Users

1. Navigate to the tickets page
2. Click "Create New Ticket"
3. Fill in the form with your issue details
4. Submit and wait for admin response

### For Admins

1. Go to "تیکتها" in admin menu
2. View all tickets and their statuses
3. Click on a ticket to view details
4. Reply to customers
5. Change status and priority
6. Add internal notes
7. Close tickets when resolved

## Documentation

See [TICKETING_SYSTEM.md](TICKETING_SYSTEM.md) for complete documentation.

## Shortcodes

- `[hamnaghsheh_tickets]` - Display ticket list and create form
- `[hamnaghsheh_ticket_detail]` - Display single ticket (auto-used)

## Ticket Categories

- پشتیبانی فنی (Technical Support)
- سوالات سفارش (Order Inquiry)
- مشکلات پروژه (Project Issue)
- سوالات عمومی (General Question)
- درخواست ویژگی (Feature Request)
- گزارش باگ (Bug Report)

## Ticket Statuses

- Open (باز)
- In Progress (در حال بررسی)
- Waiting on Customer (در انتظار پاسخ کاربر)
- Resolved (حل شده)
- Closed (بسته شده)

## Priority Levels

- Urgent (فوری) - Red
- High (بالا) - Orange
- Normal (متوسط) - Green
- Low (پایین) - Gray

## Security

- ✅ Nonce verification for all actions
- ✅ Capability checks
- ✅ Input sanitization
- ✅ SQL injection protection
- ✅ File upload validation
- ✅ Ticket ownership verification

## Support

For support and feature requests, please open an issue on GitHub.

## License

GPL v2 or later

## Author

Hamnaghsheh

## Version

1.0.0
