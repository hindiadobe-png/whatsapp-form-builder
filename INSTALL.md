# Installation Guide

## Requirements

Before installing WhatsApp Form Builder, ensure your system meets these requirements:

- **WordPress**: 5.0 or higher
- **PHP**: 7.2 or higher
- **MySQL**: 5.6 or higher
- **HTTPS**: Recommended (required for WhatsApp links to work properly on most devices)

## Installation Methods

### Method 1: WordPress Admin (Recommended)

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin**
5. Choose the downloaded ZIP file
6. Click **Install Now**
7. After installation, click **Activate Plugin**

### Method 2: Manual Installation via FTP

1. Download and extract the plugin ZIP file
2. Connect to your server via FTP
3. Upload the `whatsapp-form-builder` folder to `/wp-content/plugins/`
4. Log in to your WordPress admin panel
5. Navigate to **Plugins**
6. Find "WhatsApp Form Builder" and click **Activate**

### Method 3: WordPress CLI (WP-CLI)

```bash
# Navigate to your WordPress installation directory
cd /path/to/wordpress

# Install from ZIP
wp plugin install whatsapp-form-builder.zip --activate

# Or install from WordPress.org (when available)
wp plugin install whatsapp-form-builder --activate
```

## First-Time Setup

### Step 1: Configure Settings

1. Go to **WhatsApp Forms > Settings**
2. Configure your preferred settings:
   - Enable/disable lead saving
   - Set up reCAPTCHA (optional but recommended)

### Step 2: Set Up reCAPTCHA (Optional)

1. Visit [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
2. Register your site:
   - Label: Your site name
   - reCAPTCHA type: v2 "I'm not a robot" Checkbox
   - Domains: Your domain (e.g., example.com)
3. Accept the terms and submit
4. Copy the Site Key and Secret Key
5. Go to **WhatsApp Forms > Settings > reCAPTCHA**
6. Paste the keys and enable reCAPTCHA

### Step 3: Create Your First Form

1. Go to **WhatsApp Forms > Add New**
2. Give your form a name (e.g., "Contact Form")
3. Drag fields from the left sidebar to the form builder
4. Click on each field to configure:
   - Label
   - Placeholder
   - Required status
   - Hint text
5. Configure form settings:
   - Form title
   - Form description
   - WhatsApp number (with country code, e.g., +1234567890)
   - Submit button text
6. Click **Save Form**

### Step 4: Embed the Form

1. Copy the shortcode shown (e.g., `[whatsapp_form id="1"]`)
2. Go to any page or post where you want the form
3. Paste the shortcode
4. Save/publish the page
5. View the page to see your form in action

## Upgrading from Free to Pro

1. Purchase a Pro license key
2. Go to **WhatsApp Forms > Settings > License**
3. Enter your license key
4. Click **Save Changes**
5. Your Pro features will be activated immediately

## Troubleshooting Installation

### Plugin Activation Fails

**Issue**: Error message when activating plugin

**Solutions**:
- Check PHP version (must be 7.2+)
- Check WordPress version (must be 5.0+)
- Check for conflicting plugins
- Enable WP_DEBUG to see detailed error messages

### Database Tables Not Created

**Issue**: Forms or leads not saving

**Solutions**:
- Deactivate and reactivate the plugin
- Check database user permissions
- Manually run installation:
  ```php
  // Add to wp-config.php temporarily
  define('WAFB_FORCE_INSTALL', true);
  ```
- Contact support with error logs

### White Screen After Activation

**Issue**: Site shows blank page after activation

**Solutions**:
1. Access your site via FTP
2. Navigate to `/wp-content/plugins/`
3. Rename `whatsapp-form-builder` folder to `whatsapp-form-builder-disabled`
4. Check error logs
5. Fix the issue or contact support

### CSS/JS Not Loading

**Issue**: Form appears unstyled or not functional

**Solutions**:
- Clear WordPress cache
- Clear browser cache
- Check if theme is loading wp_head() and wp_footer()
- Disable theme's asset optimization
- Check browser console for errors

## Server Configuration

### Recommended PHP Configuration

```ini
memory_limit = 256M
max_execution_time = 300
max_input_vars = 3000
upload_max_filesize = 64M
post_max_size = 64M
```

### Apache Configuration

Ensure mod_rewrite is enabled for shortcodes to work properly:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
</IfModule>
```

### Nginx Configuration

No special configuration needed, but ensure PHP-FPM is properly configured.

## File Permissions

Recommended file permissions:

- **Folders**: 755
- **Files**: 644
- **wp-config.php**: 600

## Security Recommendations

1. **Use HTTPS**: Essential for secure data transmission
2. **Enable reCAPTCHA**: Protect against spam and bots
3. **Regular Backups**: Backup your forms and leads data
4. **Keep Updated**: Update to the latest version
5. **Strong Passwords**: Use strong passwords for admin accounts

## Database Optimization

For sites with many leads, optimize the database regularly:

```sql
-- Optimize leads table
OPTIMIZE TABLE wp_wafb_leads;

-- Clean old leads (older than 6 months)
DELETE FROM wp_wafb_leads WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
```

## Multisite Installation

The plugin supports WordPress Multisite:

1. Network activate the plugin from Network Admin
2. Each site can have its own forms and settings
3. Pro license applies network-wide

## Uninstallation

### Complete Removal

1. Deactivate the plugin
2. Delete the plugin from Plugins page
3. This will remove:
   - All plugin files
   - All database tables
   - All plugin settings
   - All forms and leads

### Preserve Data

If you want to keep your data:

1. Export leads before uninstalling (Pro feature)
2. Backup database tables manually:
   - wp_wafb_forms
   - wp_wafb_leads

## Migration to Another Site

1. Export forms:
   ```php
   // In WordPress admin, Tools > Export
   // Or backup database tables
   ```

2. Export leads (Pro):
   - Go to Leads page
   - Click Export to CSV

3. On new site:
   - Install plugin
   - Import forms via database
   - Pro: Import leads if needed

## Support Resources

- **Documentation**: [GitHub Wiki](https://github.com/hindiadobe-png/whatsapp-form-builder/wiki)
- **Support Forum**: [GitHub Issues](https://github.com/hindiadobe-png/whatsapp-form-builder/issues)
- **FAQ**: [GitHub Discussions](https://github.com/hindiadobe-png/whatsapp-form-builder/discussions)

## Getting Help

If you need help with installation:

1. Check this guide thoroughly
2. Search existing GitHub issues
3. Check WordPress.org support forum
4. Open a new GitHub issue with:
   - WordPress version
   - PHP version
   - Plugin version
   - Detailed description of the problem
   - Error messages
   - Steps to reproduce

---

**Note**: After installation, we recommend reviewing the [Examples Guide](EXAMPLES.md) for practical usage examples.
