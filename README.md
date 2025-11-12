# Offer Redirects - WordPress Plugin

A powerful WordPress plugin that enables time-based page redirects with both global and individual user-specific rules. Perfect for limited-time offers, promotional campaigns, and time-sensitive content.

## 🚀 Features

### Global Redirect Rules
- Set specific start date and time for redirects
- Define duration in minutes
- Applies to all users visiting the page
- Automatically redirects users after the specified time expires

### Individual User Redirect Rules
- Create per-user redirect rules based on first visit
- Set validity period in minutes
- Tracks each user's first visit timestamp
- Redirects only after the validity period expires from their first visit
- Works for both logged-in and anonymous users

### Additional Features
- ✅ Timezone-aware: Displays times in user's local timezone
- ✅ UTC storage: Stores all timestamps in UTC for consistency
- ✅ Real-time server time display
- ✅ Cookie-based tracking for anonymous users
- ✅ Admin-friendly interface
- ✅ Multiple rules per page
- ✅ Easy to add, edit, and remove rules
- ✅ Excludes admin users from redirects

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher

## 📦 Installation

### Method 1: Manual Installation

1. Download the plugin ZIP file or clone this repository
2. Upload the `offer-redirects` folder to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to 'Offer Redirects' in the WordPress admin menu

### Method 2: GitHub Clone

```bash
cd wp-content/plugins/
git clone https://github.com/codersemon/offer-redirects.git
```

Then activate the plugin from WordPress admin.

## 🎯 Use Cases

### Global Rules Use Cases
- **Flash Sales**: Redirect users to a "sale ended" page after promotional period
- **Limited Offers**: Automatically expire special offer pages
- **Event Registration**: Close registration pages after deadline
- **Webinar Pages**: Redirect to replay page after webinar ends

### Individual User Rules Use Cases
- **Trial Periods**: Give each user X minutes access to premium content
- **One-time Offers**: Show special offer only for first X minutes of user's visit
- **Time-limited Coupons**: Display coupon code for limited time per user
- **Content Teasing**: Allow preview access for limited duration per visitor

## 📖 Usage

### Setting Up Global Redirect Rules

1. Go to **WordPress Admin → Offer Redirects**
2. Under **Global Offer Redirect Rules** section:
   - Select the **Promoted Page** (page to apply redirect rule)
   - Set **Start Date & Time** (in your local timezone)
   - Enter **Duration in Minutes**
   - Choose **Redirect To** page
3. Click **Save All Rules**

**Example:**
- Promoted Page: "Black Friday Sale"
- Start Date & Time: 2024-11-25 09:00
- Duration: 1440 minutes (24 hours)
- Redirect To: "Sale Ended"

Result: All users visiting "Black Friday Sale" page after 24 hours from start time will be redirected to "Sale Ended" page.

### Setting Up Individual User Redirect Rules

1. Go to **WordPress Admin → Offer Redirects**
2. Under **Individual User Redirect Rules** section:
   - Select the **Promoted Page**
   - Enter **Validity in Minutes**
   - Choose **Redirect To** page
3. Click **Save All Rules**

**Example:**
- Promoted Page: "Free Trial Dashboard"
- Validity: 180 minutes (3 hours)
- Redirect To: "Upgrade to Premium"

Result: 
- User visits at 10:00 AM → Timer starts, no redirect
- User visits at 11:30 AM → Still within 3 hours, no redirect
- User visits at 1:15 PM → More than 3 hours passed, redirects to upgrade page

## 🔧 How It Works

### Global Rules Logic
1. Plugin stores the start time and duration in UTC
2. When a user visits the promoted page, it checks current time vs. expiry time
3. If current time >= (start time + duration), redirect happens
4. All users experience the same redirect behavior

### Individual User Rules Logic
1. When a user first visits the promoted page, their visit timestamp is recorded
2. Logged-in users: Tracked by WordPress user ID
3. Anonymous users: Tracked by secure cookie (valid for 10 years)
4. On subsequent visits, plugin calculates: first_visit_time + validity_minutes
5. If current time >= expiry time, redirect happens
6. Each user has independent timer

### Timezone Handling
- **Display**: All times shown in admin are in your local timezone
- **Storage**: All timestamps stored in UTC for consistency
- **Conversion**: Automatic conversion happens via JavaScript
- **Server Time**: Shows current server time (UTC) for reference

## 🛡️ Security Features

- Nonce verification for form submissions
- Data sanitization and escaping
- Admin capability checks
- Direct file access prevention
- Secure cookie implementation

## 🎨 Admin Interface

The plugin provides a clean, intuitive admin interface with:
- Separate tables for Global and Individual rules
- Real-time server time display
- Add/Remove buttons for managing rules
- Dropdown selections for pages
- Datetime picker for scheduling
- Success messages after saving

## 🗄️ Database Structure

The plugin stores data in WordPress options:

- `offer_redirect_rules` - Global redirect rules
- `offer_redirect_user_rules` - Individual user redirect rules
- `offer_redirect_user_visits` - User visit timestamps

## 🔌 Hooks and Filters

### Actions Used
- `admin_menu` - Adds admin menu page
- `admin_init` - Handles form submissions
- `template_redirect` - Performs frontend redirects
- `wp_ajax_render_offer_redirect_row` - AJAX for adding rows
- `wp_ajax_get_server_time` - AJAX for time updates

### Filters Used
Currently no custom filters (may be added in future versions)

## 🐛 Debugging

The plugin includes comprehensive error logging. To enable debug logs:

1. Enable WordPress debugging in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

2. Check logs at `wp-content/debug.log`

Log entries include:
- Rule processing details
- Redirect triggers
- User visit tracking
- Timestamp conversions

## 🔄 Uninstallation

When you uninstall the plugin (not just deactivate), it will:
- Remove all redirect rules
- Delete user visit tracking data
- Clean up all database options

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 Changelog

### Version 1.0.0
- Initial release
- Global redirect rules with start time and duration
- Individual user redirect rules with validity tracking
- Cookie-based anonymous user tracking
- Timezone-aware interface
- Real-time server time display

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 👨‍💻 Author

**Your Name**
- GitHub: [@codersemon](https://github.com/codersemon)
- Website: [emonkhan.me](https://emonkhan.me)

## 🙏 Support

If you find this plugin helpful, please consider:
- ⭐ Starring the repository
- 🐛 Reporting bugs
- 💡 Suggesting new features
- 📖 Improving documentation

## 📧 Contact

For questions or support, please open an issue on GitHub or contact at your.email@example.com

---

**Made with ❤️ for the WordPress community**