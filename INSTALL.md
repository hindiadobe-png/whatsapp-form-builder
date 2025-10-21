# Installation Guide - WhatsApp Form Builder

## Quick Start

### Method 1: Manual Installation

1. **Download the Plugin**
   - Download or clone this repository
   - Zip the `whatsapp-form-builder` folder if needed

2. **Upload to WordPress**
   - Go to your WordPress admin dashboard
   - Navigate to **Plugins > Add New > Upload Plugin**
   - Choose the zip file and click **Install Now**
   - Click **Activate Plugin**

3. **Alternative: FTP Upload**
   - Upload the `whatsapp-form-builder` folder to `/wp-content/plugins/`
   - Go to **Plugins** in WordPress admin
   - Activate **WhatsApp Form Builder**

### Method 2: Direct Folder Upload

```bash
# Navigate to your WordPress plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Clone the repository
git clone https://github.com/hindiadobe-png/whatsapp-form-builder.git

# Or copy the folder directly
cp -r /path/to/whatsapp-form-builder .
```

Then activate the plugin in WordPress admin.

## Post-Installation Setup

### Step 1: Verify Installation
After activation, you should see:
- "WhatsApp Forms" menu item in WordPress admin sidebar
- Three database tables created:
  - `wp_wfbp_forms`
  - `wp_wfbp_leads`
  - `wp_wfbp_logs`

### Step 2: Configure reCAPTCHA (Optional)
1. Visit [Google reCAPTCHA](https://www.google.com/recaptcha/admin)
2. Register your site for reCAPTCHA v2
3. Copy the Site Key and Secret Key
4. Go to **WhatsApp Forms > Settings**
5. Paste the keys in the reCAPTCHA section
6. Save settings

### Step 3: Create Your First Form

1. **Navigate to Form Builder**
   - Click **WhatsApp Forms > Add New Form**

2. **Set Form Name**
   - Enter a descriptive name (e.g., "Contact Form")

3. **Add Fields**
   - Drag field types from the left sidebar
   - Drop them into the form builder area
   - Configure each field:
     - Label: Display name
     - Name: Internal identifier (no spaces)
     - Placeholder: Helper text
     - Hint: Additional guidance
     - Required: Toggle as needed

4. **Configure Form Settings**
   - **WhatsApp Phone Number**: Enter with country code (e.g., +1234567890)
   - **Button Text**: Customize submit button text
   - **Enable reCAPTCHA**: Toggle if configured

5. **Save Form**
   - Click **Save Form** button
   - Copy the generated shortcode

### Step 4: Display the Form

**In Posts/Pages:**
```
[whatsapp_form id="1"]
```

**In PHP Templates:**
```php
<?php echo do_shortcode('[whatsapp_form id="1"]'); ?>
```

**In Block Editor:**
- Add a Shortcode block
- Paste the shortcode

**In Classic Editor:**
- Simply paste the shortcode in the editor

## Pro Version Setup (Optional)

### Activate License

1. Go to **WhatsApp Forms > License**
2. Enter your license key
3. Click **Activate License**
4. Once activated, Pro features will be unlocked

### Configure Pro Features

1. **Message Templates**
   - Go to form builder
   - Use placeholders like `{field_name}` in message template
   - Example: "Hello {name}, your message: {message}"

2. **Business API**
   - Go to **WhatsApp Forms > Settings**
   - Enter API Key and API URL
   - Enable "Use Business API" in form settings

3. **Webhooks**
   - In form settings, enter webhook URL
   - Form data will be sent as JSON POST request

4. **Auto-responses**
   - Enable auto-response in form settings
   - Configure subject and message

## Testing the Plugin

### Test Form Submission

1. Visit a page with the form shortcode
2. Fill out all required fields
3. Submit the form
4. Verify:
   - Success message appears
   - WhatsApp opens with pre-filled message
   - Lead is saved in **WhatsApp Forms > Leads**

### Check Leads

1. Go to **WhatsApp Forms > Leads**
2. View submitted leads
3. Filter by form
4. Export to CSV (Pro version)

## Troubleshooting

### Plugin doesn't appear after activation
- Check PHP version (requires 7.0+)
- Check WordPress version (requires 5.0+)
- Check for PHP errors in error logs

### Forms not saving
- Check user permissions (requires `manage_options` capability)
- Check browser console for JavaScript errors
- Verify AJAX URL is correct

### WhatsApp not opening
- Verify phone number format (+[country code][number])
- Check browser popup blockers
- Test on mobile device

### reCAPTCHA not working
- Verify Site Key and Secret Key are correct
- Check domain is registered with Google reCAPTCHA
- Ensure reCAPTCHA is enabled in form settings

### Database tables not created
- Deactivate and reactivate the plugin
- Check database user has CREATE TABLE permissions
- Check WordPress database prefix

## Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.0 or higher
- **MySQL**: 5.6 or higher
- **jQuery**: Included with WordPress

## File Permissions

Ensure proper permissions:
```bash
# Plugin directory
chmod 755 /wp-content/plugins/whatsapp-form-builder

# PHP files
find /wp-content/plugins/whatsapp-form-builder -type f -name "*.php" -exec chmod 644 {} \;

# JavaScript and CSS files
find /wp-content/plugins/whatsapp-form-builder/assets -type f -exec chmod 644 {} \;
```

## Uninstallation

To completely remove the plugin:

1. **Deactivate** the plugin from WordPress admin
2. **Delete** the plugin
3. **Manual cleanup** (if needed):
   ```sql
   DROP TABLE wp_wfbp_forms;
   DROP TABLE wp_wfbp_leads;
   DROP TABLE wp_wfbp_logs;
   
   DELETE FROM wp_options WHERE option_name LIKE 'wfbp_%';
   ```

## Support

For issues or questions:
- Open an issue on [GitHub](https://github.com/hindiadobe-png/whatsapp-form-builder/issues)
- Check the [README.md](README.md) for documentation
- Review WordPress error logs for PHP errors

## Updates

To update the plugin:
1. Deactivate the current version
2. Replace plugin files with new version
3. Reactivate the plugin
4. Database changes will be handled automatically

## Best Practices

1. **Backup First**: Always backup before installation/updates
2. **Test on Staging**: Test on staging environment first
3. **Keep Updated**: Use latest WordPress and PHP versions
4. **Monitor Leads**: Regularly check and export leads
5. **Secure Keys**: Never commit API keys to version control
