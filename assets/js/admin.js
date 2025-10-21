/**
 * Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Form builder drag and drop
        if ($('#wfbp-form-fields').length) {
            initFormBuilder();
        }
        
        // Copy shortcode
        $('.wfbp-copy-shortcode').on('click', function(e) {
            e.preventDefault();
            var shortcode = $(this).data('shortcode');
            copyToClipboard(shortcode);
            $(this).text(wfbp_admin.messages.save_success);
            setTimeout(function() {
                $('.wfbp-copy-shortcode').text('Copy');
            }, 2000);
        });
        
        // Delete form
        $('.wfbp-delete-form').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm(wfbp_admin.messages.delete_confirm)) {
                return;
            }
            
            var formId = $(this).data('form-id');
            var $button = $(this);
            
            $.ajax({
                url: wfbp_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wfbp_delete_form',
                    nonce: wfbp_admin.nonce,
                    form_id: formId
                },
                success: function(response) {
                    if (response.success) {
                        $button.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message || wfbp_admin.messages.save_error);
                    }
                }
            });
        });
        
        // Save form
        $('#wfbp-save-form').on('click', function(e) {
            e.preventDefault();
            
            var formId = $(this).data('form-id');
            var formName = $('#wfbp-form-name').val();
            var formFields = collectFormFields();
            var formSettings = collectFormSettings();
            
            if (!formName) {
                alert('Please enter a form name.');
                return;
            }
            
            var $button = $(this);
            $button.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: wfbp_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wfbp_save_form',
                    nonce: wfbp_admin.nonce,
                    form_id: formId,
                    form_name: formName,
                    form_fields: JSON.stringify(formFields),
                    form_settings: JSON.stringify(formSettings)
                },
                success: function(response) {
                    if (response.success) {
                        alert(wfbp_admin.messages.save_success);
                        if (!formId) {
                            window.location.href = window.location.href + '&form_id=' + response.data.form_id;
                        }
                    } else {
                        alert(response.data.message || wfbp_admin.messages.save_error);
                    }
                },
                complete: function() {
                    $button.prop('disabled', false).text('Save Form');
                }
            });
        });
        
    });
    
    /**
     * Initialize form builder
     */
    function initFormBuilder() {
        // Make fields sortable
        $('#wfbp-form-fields').sortable({
            handle: '.wfbp-drag-handle',
            placeholder: 'wfbp-field-placeholder',
            update: function() {
                updateFieldIndices();
            }
        });
        
        // Make field types draggable
        $('.wfbp-field-type').draggable({
            helper: 'clone',
            connectToSortable: '#wfbp-form-fields',
            start: function(event, ui) {
                $('.wfbp-placeholder').hide();
            },
            stop: function(event, ui) {
                var type = $(ui.helper).data('type');
                if (type) {
                    // Replace dragged element with field template
                    var fieldHtml = createFieldHtml(type);
                    $(ui.helper).replaceWith(fieldHtml);
                    updateFieldIndices();
                }
            }
        });
        
        // Remove field
        $(document).on('click', '.wfbp-remove-field', function(e) {
            e.preventDefault();
            $(this).closest('.wfbp-form-field').fadeOut(300, function() {
                $(this).remove();
                updateFieldIndices();
                if ($('.wfbp-form-field').length === 0) {
                    $('.wfbp-placeholder').show();
                }
            });
        });
    }
    
    /**
     * Create field HTML
     */
    function createFieldHtml(type) {
        var template = $('#wfbp-field-template').html();
        var fieldTypes = {
            'text': 'Text',
            'email': 'Email',
            'phone': 'Phone',
            'tel': 'Telephone',
            'textarea': 'Textarea',
            'select': 'Select',
            'radio': 'Radio',
            'checkbox': 'Checkbox',
            'number': 'Number',
            'url': 'URL',
            'date': 'Date'
        };
        
        var optionsField = '';
        if (['select', 'radio', 'checkbox'].indexOf(type) !== -1) {
            optionsField = '<textarea class="wfbp-field-options" placeholder="Options (one per line)"></textarea>';
        }
        
        var html = template
            .replace(/{{index}}/g, Date.now())
            .replace(/{{type}}/g, type)
            .replace(/{{type_label}}/g, fieldTypes[type])
            .replace(/{{options_field}}/g, optionsField);
        
        return html;
    }
    
    /**
     * Update field indices
     */
    function updateFieldIndices() {
        $('.wfbp-form-field').each(function(index) {
            $(this).data('index', index);
        });
    }
    
    /**
     * Collect form fields data
     */
    function collectFormFields() {
        var fields = [];
        
        $('.wfbp-form-field').each(function() {
            var $field = $(this);
            var type = $field.find('.wfbp-field-type').val() || $field.find('.wfbp-field-type-label').text().toLowerCase();
            
            var field = {
                type: type,
                label: $field.find('.wfbp-field-label').val(),
                name: $field.find('.wfbp-field-name').val(),
                placeholder: $field.find('.wfbp-field-placeholder').val(),
                hint: $field.find('.wfbp-field-hint').val(),
                required: $field.find('.wfbp-field-required').is(':checked')
            };
            
            // Get options for select, radio, checkbox
            if (['select', 'radio', 'checkbox'].indexOf(type) !== -1) {
                var optionsText = $field.find('.wfbp-field-options').val();
                field.options = optionsText ? optionsText.split('\n').filter(function(opt) {
                    return opt.trim() !== '';
                }) : [];
            }
            
            fields.push(field);
        });
        
        return fields;
    }
    
    /**
     * Collect form settings
     */
    function collectFormSettings() {
        var settings = {
            phone_number: $('#wfbp-phone-number').val(),
            button_text: $('#wfbp-button-text').val(),
            enable_recaptcha: $('#wfbp-enable-recaptcha').is(':checked')
        };
        
        // Pro features
        if ($('#wfbp-message-template').length) {
            settings.message_template = $('#wfbp-message-template').val();
        }
        if ($('#wfbp-use-business-api').length) {
            settings.use_business_api = $('#wfbp-use-business-api').is(':checked');
        }
        if ($('#wfbp-enable-auto-response').length) {
            settings.enable_auto_response = $('#wfbp-enable-auto-response').is(':checked');
        }
        if ($('#wfbp-webhook-url').length) {
            settings.webhook_url = $('#wfbp-webhook-url').val();
        }
        
        return settings;
    }
    
    /**
     * Copy to clipboard
     */
    function copyToClipboard(text) {
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
    }
    
})(jQuery);
