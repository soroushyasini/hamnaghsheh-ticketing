# Installation and Testing Guide

## Quick Installation

### 1. Upload Plugin

Upload the entire `hamnaghsheh-ticketing` folder to your WordPress installation's `/wp-content/plugins/` directory.

### 2. Activate Plugin

1. Log in to WordPress admin panel
2. Navigate to **Plugins** → **Installed Plugins**
3. Find "Hamnaghsheh Ticketing" in the list
4. Click **Activate**

### 3. Verify Database Tables

After activation, verify that the following tables were created:

- `wp_hamnaghsheh_tickets`
- `wp_hamnaghsheh_ticket_replies`
- `wp_hamnaghsheh_ticket_admin_notes`

You can verify this by running this SQL query in phpMyAdmin or your database tool:

```sql
SHOW TABLES LIKE '%hamnaghsheh_ticket%';
```

### 4. Create Tickets Page

1. Go to **Pages** → **Add New**
2. Title: "تیکت‌ها" (or "Tickets")
3. Content: Add the shortcode `[hamnaghsheh_tickets]`
4. Publish the page
5. Note the page URL (e.g., `/tickets/`)

### 5. Update Permalink Settings (if needed)

1. Go to **Settings** → **Permalinks**
2. Click **Save Changes** to flush rewrite rules
3. This ensures the tickets page works correctly

## Testing Checklist

### User Flow Testing

#### ✅ Test 1: Create a Ticket (User)

1. Log in as a regular user
2. Navigate to the tickets page
3. Click "ایجاد تیکت جدید" (Create New Ticket)
4. Fill in the form:
   - Title: "Test Ticket"
   - Category: Select any
   - Description: "This is a test ticket"
5. Click "ثبت تیکت" (Submit Ticket)
6. **Expected Result**: 
   - Success message appears
   - Redirected to ticket detail page
   - Ticket has a unique number in format: YYYY-MM-DD-XXX

#### ✅ Test 2: View Ticket List (User)

1. Navigate to tickets page
2. **Expected Result**:
   - Your created ticket appears in the list
   - Status shows as "باز" (Open)
   - Category is displayed correctly
   - Persian date is shown

#### ✅ Test 3: View Ticket Detail (User)

1. Click on a ticket from the list
2. **Expected Result**:
   - Ticket details are displayed
   - Original message is shown
   - Reply form is available (if not closed)

#### ✅ Test 4: Reply to Ticket (User)

1. Open a ticket detail page
2. Scroll to reply form
3. Enter a message
4. Optionally attach a file
5. Click "ارسال پاسخ" (Send Reply)
6. **Expected Result**:
   - Success message appears
   - Page reloads showing new reply
   - Reply is marked as user reply (different styling)

#### ✅ Test 5: File Upload (User)

1. Create or reply to a ticket
2. Attach an image (JPG, PNG) under 5MB
3. Submit
4. **Expected Result**:
   - File uploads successfully
   - Image preview or download link appears
   - File is stored in `/wp-content/uploads/hamnaghsheh/tickets/{ticket_id}/`

#### ✅ Test 6: File Upload Validation (User)

1. Try uploading a file over 5MB
2. **Expected Result**: Error message appears

3. Try uploading an invalid file type (e.g., .exe)
4. **Expected Result**: Error message appears

### Admin Flow Testing

#### ✅ Test 7: View Admin Tickets List

1. Log in as administrator
2. Go to admin menu → **تیکتها** (Tickets)
3. **Expected Result**:
   - All tickets are listed
   - Statistics shown at top (Open, In Progress, etc.)
   - Filters work (status, category, priority)

#### ✅ Test 8: View Ticket Detail (Admin)

1. Click on any ticket from admin list
2. **Expected Result**:
   - Full ticket information displayed
   - All replies shown (user and admin)
   - Admin action panel on right side

#### ✅ Test 9: Admin Reply

1. Open a ticket in admin panel
2. Write a reply in the editor
3. Optionally attach files
4. Click "ارسال پاسخ" (Send Reply)
5. **Expected Result**:
   - Reply is added
   - Status changes to "در انتظار پاسخ کاربر" (Waiting on Customer)
   - Reply marked as admin reply (blue background)

#### ✅ Test 10: Change Status

1. Open a ticket in admin panel
2. Change status from dropdown
3. **Expected Result**:
   - Status updates immediately
   - Success message appears

#### ✅ Test 11: Set Priority

1. Open a ticket in admin panel
2. Change priority from dropdown
3. **Expected Result**:
   - Priority updates
   - Badge color changes accordingly

#### ✅ Test 12: Add Internal Note

1. Open a ticket in admin panel
2. Scroll to "یادداشت‌های داخلی" (Internal Notes)
3. Add a note
4. Click submit
5. **Expected Result**:
   - Note is added
   - Note is NOT visible to user (verify by logging in as user)

#### ✅ Test 13: Close Ticket

1. Open a ticket in admin panel
2. Click "بستن تیکت" (Close Ticket)
3. Confirm the action
4. **Expected Result**:
   - Ticket status changes to "بسته شده" (Closed)
   - User cannot reply anymore
   - Ticket cannot be reopened

### Email Notification Testing

#### ✅ Test 14: New Ticket Email

1. Create a new ticket as user
2. **Expected Result**:
   - Admin receives email notification
   - Email contains ticket number, title, and link

#### ✅ Test 15: Admin Reply Email

1. Admin replies to a ticket
2. **Expected Result**:
   - User receives email notification
   - Email contains reply and link to ticket

#### ✅ Test 16: User Reply Email

1. User replies to a ticket
2. **Expected Result**:
   - Admin receives email notification
   - Email contains reply and link

#### ✅ Test 17: Resolved Email

1. Admin changes status to "حل شده" (Resolved)
2. **Expected Result**:
   - User receives email notification

### Login Redirect Testing

#### ✅ Test 18: Smart Login Redirect

1. Log out of WordPress
2. Navigate to `/tickets/` page directly
3. **Expected Result**:
   - Redirected to login page with `redirect_to` parameter
   - After login, redirected back to `/tickets/` page

#### ✅ Test 19: Tickets Button Shortcode

1. Add `[hamnaghsheh_tickets_button]` to a page or widget
2. **Expected Result**:
   - Button displays correctly
   - When logged out, clicking redirects to login
   - When logged in, clicking goes to tickets page

### Security Testing

#### ✅ Test 20: Ticket Ownership

1. Create a ticket as User A
2. Log in as User B
3. Try to access User A's ticket URL directly
4. **Expected Result**: 
   - Access denied message
   - User B cannot view User A's ticket

#### ✅ Test 21: Admin Access

1. Log in as admin
2. Try to access any ticket
3. **Expected Result**:
   - Admin can view all tickets
   - Admin has full access

#### ✅ Test 22: Closed Ticket Reply Prevention

1. Close a ticket as admin
2. Log in as ticket owner
3. Try to reply
4. **Expected Result**:
   - Reply form is not shown
   - Message indicates ticket is closed

### UI/UX Testing

#### ✅ Test 23: RTL Layout

1. View tickets page
2. **Expected Result**:
   - All text is right-aligned
   - Layout flows right to left
   - Persian text displays correctly

#### ✅ Test 24: Mobile Responsive

1. View tickets page on mobile device or small browser window
2. **Expected Result**:
   - Layout adapts to screen size
   - Tables are readable
   - Forms are usable
   - Buttons are clickable

#### ✅ Test 25: Status Badge Colors

1. View tickets with different statuses
2. **Expected Result**:
   - Open: Blue
   - In Progress: Yellow
   - Waiting on Customer: Orange
   - Resolved: Green
   - Closed: Gray

### Filter and Search Testing

#### ✅ Test 26: Status Filter (User)

1. Go to tickets page
2. Use status filter dropdown
3. Select "Open"
4. **Expected Result**:
   - Only open tickets are shown
   - URL updates with filter parameter

#### ✅ Test 27: Admin Filters

1. Go to admin tickets page
2. Filter by status, category, or priority
3. **Expected Result**:
   - Results update accordingly
   - Multiple filters can be combined

#### ✅ Test 28: Admin Search

1. Go to admin tickets page
2. Search for ticket number or title
3. **Expected Result**:
   - Matching tickets are shown
   - Search is case-insensitive

### Persian Date Testing

#### ✅ Test 29: Ticket Number Format

1. Create multiple tickets on different days
2. **Expected Result**:
   - Ticket numbers follow format: YYYY-MM-DD-XXX
   - Counter increments daily (001, 002, 003...)
   - Date is in Persian/Jalali calendar

#### ✅ Test 30: Date Display

1. View ticket creation and update dates
2. **Expected Result**:
   - Dates shown in Persian format
   - Format: YYYY/MM/DD HH:MM

## Troubleshooting Common Issues

### Issue 1: Database Tables Not Created

**Solution**: 
```php
// Manually deactivate and reactivate plugin
// Or run activation hook directly in WordPress admin → Tools → Site Health → Info → Database
```

### Issue 2: Shortcode Not Working

**Solution**:
- Ensure plugin is activated
- Check page content has correct shortcode: `[hamnaghsheh_tickets]`
- Clear any caching plugins

### Issue 3: File Upload Not Working

**Solution**:
- Check PHP `upload_max_filesize` (should be >= 5MB)
- Verify directory permissions on `/wp-content/uploads/`
- Check file type is allowed

### Issue 4: Emails Not Sending

**Solution**:
- Check WordPress email configuration
- Test with WP Mail SMTP plugin
- Check spam folder
- Verify admin email addresses are correct

### Issue 5: Redirect Loop After Login

**Solution**:
- Clear browser cache
- Ensure `/auth/` is correct login page URL
- Update `site_url('/auth/')` in code if different

### Issue 6: Styles Not Loading

**Solution**:
- Clear WordPress cache
- Hard refresh browser (Ctrl+Shift+R)
- Check browser console for 404 errors

## Performance Testing

### Test 31: Large Ticket Volume

1. Create 100+ test tickets
2. **Expected Result**:
   - Page loads in reasonable time
   - Filters work efficiently
   - Database queries are optimized

### Test 32: Large File Attachments

1. Upload multiple 4-5MB files
2. **Expected Result**:
   - Files upload within reasonable time
   - Server doesn't timeout
   - Memory limit not exceeded

## Final Verification

After all tests pass, verify:

- ✅ All database tables exist and have correct schema
- ✅ All shortcodes work
- ✅ Admin menu appears with badge counter
- ✅ Email notifications are being sent
- ✅ Files are stored securely
- ✅ RTL layout works correctly
- ✅ Mobile responsive
- ✅ Security measures in place
- ✅ Persian dates display correctly
- ✅ No PHP errors in error log

## Support

If any tests fail or you encounter issues:

1. Check the error log: `wp-content/debug.log`
2. Enable WordPress debugging: `define('WP_DEBUG', true);` in `wp-config.php`
3. Review `TICKETING_SYSTEM.md` for detailed documentation
4. Check `LOGIN_REDIRECT_INTEGRATION.md` for integration help

## Success Criteria

The plugin is ready for production when:

- ✅ All 30+ tests pass
- ✅ No console errors
- ✅ No PHP errors or warnings
- ✅ Security audit complete
- ✅ Performance acceptable
- ✅ Documentation complete
