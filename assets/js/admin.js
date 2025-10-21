/**
 * WhatsApp Form Builder - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Copy shortcode to clipboard
        $('.wafb-copy-shortcode').on('click', function() {
            var shortcode = $(this).data('shortcode');
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(shortcode).select();
            document.execCommand('copy');
            $temp.remove();
            
            var $btn = $(this);
            var originalText = $btn.text();
            $btn.text(wafbAdmin.i18n.saved);
            setTimeout(function() {
                $btn.text(originalText);
            }, 2000);
        });
        
        // Delete form
        $('.wafb-delete-form').on('click', function() {
            if (!confirm(wafbAdmin.i18n.delete_confirm)) {
                return;
            }
            
            var formId = $(this).data('form-id');
            var $row = $(this).closest('tr');
            
            $.ajax({
                url: wafbAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wafb_delete_form',
                    form_id: formId,
                    nonce: wafbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });
        
        // Duplicate form
        $('.wafb-duplicate-form').on('click', function() {
            var formId = $(this).data('form-id');
            var $btn = $(this);
            
            $btn.prop('disabled', true);
            
            $.ajax({
                url: wafbAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wafb_duplicate_form',
                    form_id: formId,
                    nonce: wafbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message);
                        $btn.prop('disabled', false);
                    }
                }
            });
        });
        
        // Form Builder
        if ($('.wafb-form-builder-page').length) {
            initFormBuilder();
        }
        
        // Leads filter
        $('#wafb-filter-form').on('change', function() {
            var formId = $(this).val();
            var url = new URL(window.location.href);
            if (formId > 0) {
                url.searchParams.set('form_id', formId);
            } else {
                url.searchParams.delete('form_id');
            }
            window.location.href = url.toString();
        });
        
        // View lead data
        $('.wafb-view-lead-data').on('click', function() {
            var leadId = $(this).data('lead-id');
            var $data = $('.wafb-lead-data[data-lead-id="' + leadId + '"]');
            
            if ($data.is(':visible')) {
                $data.slideUp();
                $(this).text(wafbAdmin.i18n.view || 'View');
            } else {
                $data.slideDown();
                $(this).text(wafbAdmin.i18n.hide || 'Hide');
            }
        });
        
        // Delete lead
        $('.wafb-delete-lead').on('click', function() {
            if (!confirm(wafbAdmin.i18n.delete_confirm)) {
                return;
            }
            
            var leadId = $(this).data('lead-id');
            var $row = $(this).closest('tr');
            
            $.ajax({
                url: wafbAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wafb_delete_lead',
                    lead_id: leadId,
                    nonce: wafbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });
        
        // Export leads
        $('.wafb-export-leads').on('click', function() {
            var formId = $(this).data('form-id');
            
            $.ajax({
                url: wafbAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wafb_export_leads',
                    form_id: formId,
                    nonce: wafbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        exportToCSV(response.data.leads);
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });
        
    });
    
    /**
     * Initialize form builder
     */
    function initFormBuilder() {
        var formFields = [];
        var currentEditingField = null;
        
        // Load existing fields if editing
        var $existingFields = $('.wafb-form-field-builder');
        if ($existingFields.length) {
            $existingFields.each(function() {
                var fieldData = $(this).data('field');
                if (fieldData) {
                    formFields.push(fieldData);
                }
            });
        }
        
        // Make field types draggable
        $('.wafb-field-type').on('dragstart', function(e) {
            e.originalEvent.dataTransfer.effectAllowed = 'copy';
            e.originalEvent.dataTransfer.setData('text/html', $(this).html());
            e.originalEvent.dataTransfer.setData('field-type', $(this).data('type'));
        });
        
        // Make form fields container droppable
        var $fieldsContainer = $('#wafb-form-fields');
        
        $fieldsContainer.on('dragover', function(e) {
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'copy';
        });
        
        $fieldsContainer.on('drop', function(e) {
            e.preventDefault();
            var fieldType = e.originalEvent.dataTransfer.getData('field-type');
            
            if (fieldType) {
                addField(fieldType);
            }
        });
        
        // Add field to form
        function addField(type) {
            var field = {
                type: type,
                name: 'field_' + Date.now(),
                label: getFieldTypeLabel(type),
                placeholder: '',
                hint: '',
                required: false,
                options: []
            };
            
            formFields.push(field);
            renderFields();
            openFieldEditor(formFields.length - 1);
        }
        
        // Get field type label
        function getFieldTypeLabel(type) {
            var labels = {
                'text': 'Text Field',
                'email': 'Email',
                'phone': 'Phone',
                'textarea': 'Message',
                'select': 'Dropdown',
                'radio': 'Radio Buttons',
                'checkbox': 'Checkboxes',
                'url': 'Website URL'
            };
            return labels[type] || type;
        }
        
        // Render all fields
        function renderFields() {
            $('.wafb-empty-message').remove();
            $fieldsContainer.empty();
            
            formFields.forEach(function(field, index) {
                var $field = $('<div class="wafb-form-field-builder" data-index="' + index + '"></div>');
                
                var $header = $('<div class="wafb-field-builder-header"></div>');
                $header.append('<span class="wafb-field-builder-title">' + field.label + (field.required ? ' <span class="wafb-required">*</span>' : '') + '</span>');
                
                var $actions = $('<div class="wafb-field-builder-actions"></div>');
                $actions.append('<button type="button" class="button button-small wafb-edit-field" data-index="' + index + '">Edit</button>');
                $actions.append('<button type="button" class="button button-small wafb-delete-field" data-index="' + index + '">Delete</button>');
                $header.append($actions);
                
                $field.append($header);
                $field.append('<div class="wafb-field-preview">' + renderFieldPreview(field) + '</div>');
                
                $fieldsContainer.append($field);
            });
            
            // Make fields sortable
            $fieldsContainer.sortable({
                handle: '.wafb-field-builder-header',
                placeholder: 'wafb-field-placeholder',
                update: function() {
                    var newOrder = [];
                    $fieldsContainer.children().each(function() {
                        var index = $(this).data('index');
                        newOrder.push(formFields[index]);
                    });
                    formFields = newOrder;
                    renderFields();
                }
            });
        }
        
        // Render field preview
        function renderFieldPreview(field) {
            var html = '';
            switch (field.type) {
                case 'text':
                case 'email':
                case 'phone':
                case 'url':
                    html = '<input type="' + field.type + '" placeholder="' + field.placeholder + '" disabled>';
                    break;
                case 'textarea':
                    html = '<textarea placeholder="' + field.placeholder + '" disabled></textarea>';
                    break;
                case 'select':
                    html = '<select disabled><option>' + field.placeholder + '</option></select>';
                    break;
                case 'radio':
                case 'checkbox':
                    html = '<div>' + (field.options.length || 0) + ' options</div>';
                    break;
            }
            return html;
        }
        
        // Open field editor
        function openFieldEditor(index) {
            currentEditingField = index;
            var field = formFields[index];
            
            var $modal = $('#wafb-field-editor-modal');
            $modal.find('[name="field_label"]').val(field.label);
            $modal.find('[name="field_placeholder"]').val(field.placeholder);
            $modal.find('[name="field_hint"]').val(field.hint);
            $modal.find('[name="field_required"]').prop('checked', field.required);
            
            if (field.type === 'select' || field.type === 'radio' || field.type === 'checkbox') {
                $modal.find('.wafb-field-options-row').show();
                $modal.find('[name="field_options"]').val(field.options.join('\n'));
            } else {
                $modal.find('.wafb-field-options-row').hide();
            }
            
            $modal.show();
        }
        
        // Edit field
        $(document).on('click', '.wafb-edit-field', function() {
            var index = $(this).data('index');
            openFieldEditor(index);
        });
        
        // Delete field
        $(document).on('click', '.wafb-delete-field', function() {
            if (!confirm(wafbAdmin.i18n.delete_confirm)) {
                return;
            }
            var index = $(this).data('index');
            formFields.splice(index, 1);
            renderFields();
        });
        
        // Save field
        $('.wafb-save-field').on('click', function() {
            if (currentEditingField !== null) {
                var field = formFields[currentEditingField];
                field.label = $('[name="field_label"]').val();
                field.placeholder = $('[name="field_placeholder"]').val();
                field.hint = $('[name="field_hint"]').val();
                field.required = $('[name="field_required"]').is(':checked');
                
                if (field.type === 'select' || field.type === 'radio' || field.type === 'checkbox') {
                    var optionsText = $('[name="field_options"]').val();
                    field.options = optionsText.split('\n').filter(function(opt) {
                        return opt.trim() !== '';
                    });
                }
                
                renderFields();
                $('#wafb-field-editor-modal').hide();
            }
        });
        
        // Close modal
        $('.wafb-modal-close').on('click', function() {
            $('#wafb-field-editor-modal').hide();
        });
        
        // Save form
        $('.wafb-save-form').on('click', function() {
            var formId = $(this).data('form-id');
            var formName = $('#wafb-form-name').val();
            
            if (!formName) {
                alert(wafbAdmin.i18n.required_field);
                return;
            }
            
            // Collect settings
            var settings = {};
            $('.wafb-form-settings input, .wafb-form-settings textarea, .wafb-form-settings select').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    if ($(this).attr('type') === 'checkbox') {
                        settings[name] = $(this).is(':checked');
                    } else {
                        settings[name] = $(this).val();
                    }
                }
            });
            
            var $btn = $(this);
            var originalText = $btn.text();
            $btn.text(wafbAdmin.i18n.saving).prop('disabled', true);
            
            $.ajax({
                url: wafbAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'wafb_save_form',
                    form_id: formId,
                    name: formName,
                    fields: JSON.stringify(formFields),
                    settings: JSON.stringify(settings),
                    nonce: wafbAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.text(wafbAdmin.i18n.saved);
                        setTimeout(function() {
                            if (formId === 0) {
                                window.location.href = '?page=wafb-form-builder&form_id=' + response.data.form_id;
                            } else {
                                $btn.text(originalText).prop('disabled', false);
                            }
                        }, 1000);
                    } else {
                        alert(response.data.message);
                        $btn.text(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    alert(wafbAdmin.i18n.error);
                    $btn.text(originalText).prop('disabled', false);
                }
            });
        });
    }
    
    /**
     * Export data to CSV
     */
    function exportToCSV(data) {
        if (!data || data.length === 0) {
            alert('No data to export');
            return;
        }
        
        var csv = [];
        var headers = ['ID', 'Form ID', 'Data', 'Page URL', 'IP Address', 'Date'];
        csv.push(headers.join(','));
        
        data.forEach(function(row) {
            var line = [
                row.id,
                row.form_id,
                '"' + row.data.replace(/"/g, '""') + '"',
                row.page_url,
                row.ip_address,
                row.created_at
            ];
            csv.push(line.join(','));
        });
        
        var csvContent = csv.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', 'leads_export_' + Date.now() + '.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
})(jQuery);
