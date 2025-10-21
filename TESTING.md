# Testing Guide

This document provides instructions for testing the WhatsApp Form Builder plugin.

## Prerequisites

- WordPress installation (5.0 or higher)
- PHP 7.0 or higher
- MySQL 5.6 or higher
- Admin access to WordPress

## Installation Testing

### 1. Plugin Activation

1. Upload the plugin to `/wp-content/plugins/whatsapp-form-builder/`
2. Navigate to **Plugins** in WordPress admin
3. Locate **WhatsApp Form Builder**
4. Click **Activate**

**Expected Result**: Plugin activates successfully without errors

### 2. Database Table Creation

After activation, verify the database table was created:

```sql
SHOW TABLES LIKE 'wp_wafb_forms';
DESCRIBE wp_wafb_forms;
```

**Expected Result**: Table exists with correct schema:
- id (bigint)
- form_name (varchar)
- form_data (longtext)
- form_settings (longtext)
- created_at (datetime)
- updated_at (datetime)

### 3. Admin Menu

Check that the menu item appears:

1. Navigate to WordPress admin dashboard
2. Look for **Form Builder** in the main menu (with feedback icon)

**Expected Result**: Menu item appears for admin users

## Functional Testing

### 1. Create New Form

1. Go to **Form Builder** in admin menu
2. Enter "Contact Form" in the form name field
3. Drag a **Text Field** from the palette to the drop zone
4. Drag an **Email Field** to the drop zone
5. Click **Save Form**

**Expected Result**: 
- Success message appears
- Form is saved to database
- Form appears in the saved forms list

### 2. Edit Field Settings

1. Click **Edit** on a field in the form
2. Modal window opens
3. Change label to "Your Name"
4. Change placeholder to "Enter your full name"
5. Check "Required Field"
6. Click **Save Settings**

**Expected Result**:
- Modal closes
- Field label updates
- Required badge (*) appears
- Preview updates

### 3. Drag and Drop Functionality

1. Drag **Phone Field** from palette
2. Drop between existing fields
3. Reorder fields by dragging within the drop zone

**Expected Result**:
- Fields can be dragged from palette
- Fields can be dropped in the zone
- Fields can be reordered
- Preview updates after each change

### 4. Form Preview

1. Add multiple fields to the form
2. Observe the preview section

**Expected Result**:
- Preview shows all fields
- Preview reflects field order
- Preview shows labels and placeholders
- Required fields show asterisk

### 5. Load Existing Form

1. Create and save a form
2. Click **New Form** button
3. Click **Load** on the saved form

**Expected Result**:
- Form loads with all fields
- Field settings preserved
- Preview renders correctly

### 6. Delete Form

1. Create a test form
2. Click **Delete** on the form
3. Confirm deletion

**Expected Result**:
- Confirmation dialog appears
- Form is removed from list
- Form is deleted from database
- Success message appears

### 7. New Form

1. Have a form loaded in the builder
2. Click **New Form** button
3. Confirm the action

**Expected Result**:
- Confirmation dialog appears
- Form builder resets
- All fields cleared
- Form ID reset to 0

## Security Testing

### 1. Capability Checks

Test as non-admin user:
1. Log in as Editor or Subscriber
2. Try to access `/wp-admin/admin.php?page=wafb-form-builder`

**Expected Result**: Access denied or redirect

### 2. Nonce Verification

1. Open browser developer tools
2. Open Network tab
3. Save a form
4. Copy the AJAX request
5. Try to replay without nonce or with invalid nonce

**Expected Result**: Request fails with error

### 3. Input Sanitization

Try to save form with:
- Form name with HTML tags: `<script>alert('xss')</script>`
- Form name with SQL: `'; DROP TABLE wp_wafb_forms; --`

**Expected Result**: 
- HTML tags stripped or escaped
- SQL injection prevented
- Form saves safely

### 4. Direct File Access

Try to access files directly via URL:
- `/wp-content/plugins/whatsapp-form-builder/admin/class-wafb-admin.php`
- `/wp-content/plugins/whatsapp-form-builder/includes/class-wafb-core.php`

**Expected Result**: Blank page or error (not PHP code execution)

## User Interface Testing

### 1. Responsive Design

Test on different screen sizes:
- Desktop (1920x1080)
- Tablet (768x1024)
- Mobile (375x667)

**Expected Result**: Layout adapts appropriately

### 2. Field Types

Test all field types:
- **Text Field**: Creates text input
- **Email Field**: Creates email input
- **Phone Field**: Creates tel input
- **Message Field**: Creates textarea

**Expected Result**: Correct input types generated

### 3. Notifications

Test notification system:
- Save form successfully
- Try to save without name
- Load form
- Delete form

**Expected Result**: 
- Notifications appear in top-right
- Auto-dismiss after 3 seconds
- Appropriate styling (success/error)

### 4. Modal Functionality

1. Open field settings modal
2. Test close button (X)
3. Test Cancel button
4. Test clicking outside modal

**Expected Result**: All methods close the modal

## Browser Compatibility

Test in the following browsers:

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

**Expected Result**: Full functionality in all browsers

## Performance Testing

### 1. Large Forms

1. Create a form with 20+ fields
2. Save the form
3. Load the form
4. Drag and reorder fields

**Expected Result**: 
- Operations complete in reasonable time
- No browser freezing
- Smooth drag-and-drop

### 2. Multiple Forms

1. Create 50 forms
2. Check forms list loading time
3. Load individual forms

**Expected Result**: Acceptable performance

## Edge Cases

### 1. Empty Form

1. Try to save form with no name
2. Try to save form with name but no fields

**Expected Result**:
- No name: Error message
- No fields: Form saves but empty

### 2. Special Characters

Test form names with:
- Unicode: `测试表单`
- Emojis: `Form 😊`
- Special chars: `Form & Test < >`

**Expected Result**: Characters handled properly

### 3. Long Content

Test with:
- Very long form name (500 chars)
- Very long field label (500 chars)
- Very long placeholder (500 chars)

**Expected Result**: Content handled appropriately

## Cleanup Testing

### 1. Plugin Deactivation

1. Deactivate the plugin
2. Check database

**Expected Result**: 
- Table remains in database
- No PHP errors

### 2. Plugin Reactivation

1. Reactivate the plugin
2. Check admin menu
3. Load existing forms

**Expected Result**: 
- Everything works as before
- Existing forms intact

## Automated Testing

While this plugin doesn't include automated tests, here are recommendations for adding them:

### Unit Tests (PHPUnit)

```php
// Test sanitization
public function test_form_name_sanitization() {
    $input = '<script>alert("xss")</script>';
    $output = sanitize_text_field( $input );
    $this->assertNotContains( '<script>', $output );
}

// Test capability check
public function test_non_admin_access() {
    wp_set_current_user( $this->factory->user->create( array( 'role' => 'subscriber' ) ) );
    $this->assertFalse( current_user_can( 'manage_options' ) );
}
```

### JavaScript Tests (Jest)

```javascript
// Test field creation
test('creates field element', () => {
    const field = createFormField('text');
    expect(field).toBeTruthy();
    expect(field.data('field-type')).toBe('text');
});

// Test serialization
test('serializes form fields', () => {
    // Add fields to form
    const serialized = serializeFormFields();
    expect(Array.isArray(serialized)).toBe(true);
});
```

## Test Checklist

Use this checklist for complete testing:

- [ ] Plugin activates successfully
- [ ] Database table created
- [ ] Admin menu appears
- [ ] Can create new form
- [ ] Can edit field settings
- [ ] Drag and drop works
- [ ] Form preview updates
- [ ] Can save form
- [ ] Can load form
- [ ] Can delete form
- [ ] Can create new form
- [ ] Security checks pass
- [ ] UI is responsive
- [ ] All field types work
- [ ] Notifications display
- [ ] Modal works properly
- [ ] Works in all browsers
- [ ] Performance acceptable
- [ ] Edge cases handled
- [ ] Plugin deactivates cleanly

## Reporting Issues

When reporting issues, include:

1. WordPress version
2. PHP version
3. Browser and version
4. Steps to reproduce
5. Expected vs actual behavior
6. Screenshots if applicable
7. Console errors if any

## Conclusion

This testing guide covers the main functionality and security aspects of the WhatsApp Form Builder plugin. Regular testing should be performed after any code changes to ensure stability and security.
