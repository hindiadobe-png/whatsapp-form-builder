/**
 * Admin JavaScript for WhatsApp Form Builder
 */

(function($) {
    'use strict';

    let fieldIdCounter = 0;
    let currentEditingField = null;

    $(document).ready(function() {
        initDragAndDrop();
        initFormControls();
        initFieldModal();
        updatePreview();
    });

    /**
     * Initialize drag and drop functionality
     */
    function initDragAndDrop() {
        // Make fields draggable from palette
        $('.wafb-field-item').draggable({
            helper: 'clone',
            connectToSortable: '#wafb-form-fields',
            revert: 'invalid',
            cursor: 'move',
            start: function(event, ui) {
                $(ui.helper).css('opacity', '0.7');
            }
        });

        // Make drop zone droppable and sortable
        $('#wafb-form-fields').sortable({
            placeholder: 'wafb-field-placeholder-sort',
            tolerance: 'pointer',
            cursor: 'move',
            receive: function(event, ui) {
                const fieldType = ui.item.data('field-type');
                const newField = createFormField(fieldType);
                ui.item.replaceWith(newField);
                updatePreview();
            },
            update: function(event, ui) {
                updatePreview();
            }
        });

        // Highlight drop zone on drag over
        $('.wafb-drop-zone').on('dragover', function() {
            $(this).addClass('drag-over');
        }).on('dragleave drop', function() {
            $(this).removeClass('drag-over');
        });
    }

    /**
     * Create a form field element
     */
    function createFormField(fieldType) {
        fieldIdCounter++;
        const fieldId = 'field_' + fieldIdCounter;

        let fieldLabel = '';
        let fieldPlaceholder = '';
        let inputType = 'text';
        let icon = 'dashicons-edit';

        switch(fieldType) {
            case 'text':
                fieldLabel = 'Text Field';
                fieldPlaceholder = 'Enter text';
                inputType = 'text';
                icon = 'dashicons-edit';
                break;
            case 'email':
                fieldLabel = 'Email Address';
                fieldPlaceholder = 'Enter email';
                inputType = 'email';
                icon = 'dashicons-email';
                break;
            case 'phone':
                fieldLabel = 'Phone Number';
                fieldPlaceholder = 'Enter phone number';
                inputType = 'tel';
                icon = 'dashicons-phone';
                break;
            case 'message':
                fieldLabel = 'Message';
                fieldPlaceholder = 'Enter your message';
                inputType = 'textarea';
                icon = 'dashicons-format-aside';
                break;
        }

        const fieldHtml = `
            <div class="wafb-form-field" data-field-id="${fieldId}" data-field-type="${fieldType}">
                <div class="wafb-field-header">
                    <span class="wafb-field-type">
                        <span class="dashicons ${icon}"></span>
                        ${fieldLabel}
                    </span>
                    <div class="wafb-field-actions">
                        <button type="button" class="button button-small wafb-edit-field" data-field-id="${fieldId}">
                            Edit
                        </button>
                        <button type="button" class="button button-small wafb-remove-field" data-field-id="${fieldId}">
                            Remove
                        </button>
                    </div>
                </div>
                <div class="wafb-field-body">
                    <label>
                        ${fieldLabel}
                    </label>
                    ${inputType === 'textarea' 
                        ? `<textarea placeholder="${fieldPlaceholder}" disabled></textarea>`
                        : `<input type="${inputType}" placeholder="${fieldPlaceholder}" disabled />`
                    }
                </div>
                <input type="hidden" class="field-label" value="${fieldLabel}" />
                <input type="hidden" class="field-placeholder" value="${fieldPlaceholder}" />
                <input type="hidden" class="field-required" value="0" />
            </div>
        `;

        const $field = $(fieldHtml);

        // Add event listeners
        $field.find('.wafb-edit-field').on('click', function() {
            editField(fieldId);
        });

        $field.find('.wafb-remove-field').on('click', function() {
            removeField(fieldId);
        });

        return $field;
    }

    /**
     * Edit field settings
     */
    function editField(fieldId) {
        const $field = $(`.wafb-form-field[data-field-id="${fieldId}"]`);
        currentEditingField = $field;

        const label = $field.find('.field-label').val();
        const placeholder = $field.find('.field-placeholder').val();
        const required = $field.find('.field-required').val() === '1';

        $('#wafb-field-label').val(label);
        $('#wafb-field-placeholder').val(placeholder);
        $('#wafb-field-required').prop('checked', required);

        $('#wafb-field-modal').fadeIn();
    }

    /**
     * Remove field
     */
    function removeField(fieldId) {
        if (confirm('Are you sure you want to remove this field?')) {
            $(`.wafb-form-field[data-field-id="${fieldId}"]`).fadeOut(300, function() {
                $(this).remove();
                updatePreview();
            });
        }
    }

    /**
     * Initialize field settings modal
     */
    function initFieldModal() {
        // Close modal
        $('.wafb-modal-close, #wafb-cancel-field-settings').on('click', function() {
            $('#wafb-field-modal').fadeOut();
            currentEditingField = null;
        });

        // Save field settings
        $('#wafb-save-field-settings').on('click', function() {
            if (currentEditingField) {
                const label = $('#wafb-field-label').val();
                const placeholder = $('#wafb-field-placeholder').val();
                const required = $('#wafb-field-required').is(':checked');

                currentEditingField.find('.field-label').val(label);
                currentEditingField.find('.field-placeholder').val(placeholder);
                currentEditingField.find('.field-required').val(required ? '1' : '0');

                // Update display
                currentEditingField.find('.wafb-field-body label').html(
                    label + (required ? '<span class="wafb-required-badge">*</span>' : '')
                );
                currentEditingField.find('.wafb-field-body input, .wafb-field-body textarea').attr('placeholder', placeholder);

                $('#wafb-field-modal').fadeOut();
                updatePreview();
                currentEditingField = null;
            }
        });

        // Close modal on outside click
        $(window).on('click', function(event) {
            if ($(event.target).is('#wafb-field-modal')) {
                $('#wafb-field-modal').fadeOut();
                currentEditingField = null;
            }
        });
    }

    /**
     * Initialize form controls
     */
    function initFormControls() {
        // Save form
        $('#wafb-save-form').on('click', function() {
            saveForm();
        });

        // New form
        $('#wafb-new-form').on('click', function() {
            if (confirm('Create a new form? Any unsaved changes will be lost.')) {
                $('#wafb-form-id').val('0');
                $('#wafb-form-name').val('');
                $('#wafb-form-fields').empty();
                fieldIdCounter = 0;
                updatePreview();
            }
        });

        // Load form
        $(document).on('click', '.wafb-load-form', function() {
            const formId = $(this).data('form-id');
            loadForm(formId);
        });

        // Delete form
        $(document).on('click', '.wafb-delete-form', function() {
            const formId = $(this).data('form-id');
            if (confirm(wafbAdmin.strings.confirmDelete)) {
                deleteForm(formId);
            }
        });
    }

    /**
     * Save form via AJAX
     */
    function saveForm() {
        const formId = $('#wafb-form-id').val();
        const formName = $('#wafb-form-name').val().trim();

        if (!formName) {
            showNotice('Please enter a form name.', 'error');
            return;
        }

        const formData = serializeFormFields();

        $.ajax({
            url: wafbAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wafb_save_form',
                nonce: wafbAdmin.nonce,
                form_id: formId,
                form_name: formName,
                form_data: JSON.stringify(formData)
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    $('#wafb-form-id').val(response.data.form_id);
                    
                    // Refresh forms list
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotice(response.data.message || wafbAdmin.strings.saveError, 'error');
                }
            },
            error: function() {
                showNotice(wafbAdmin.strings.saveError, 'error');
            }
        });
    }

    /**
     * Load form via AJAX
     */
    function loadForm(formId) {
        $.ajax({
            url: wafbAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wafb_load_form',
                nonce: wafbAdmin.nonce,
                form_id: formId
            },
            success: function(response) {
                if (response.success) {
                    const form = response.data.form;
                    $('#wafb-form-id').val(form.id);
                    $('#wafb-form-name').val(form.form_name);
                    
                    // Clear existing fields
                    $('#wafb-form-fields').empty();
                    fieldIdCounter = 0;

                    // Load form fields
                    if (form.form_data) {
                        const formData = JSON.parse(form.form_data);
                        formData.forEach(function(field) {
                            const newField = createFormField(field.type);
                            newField.find('.field-label').val(field.label);
                            newField.find('.field-placeholder').val(field.placeholder);
                            newField.find('.field-required').val(field.required ? '1' : '0');
                            
                            // Update display
                            newField.find('.wafb-field-body label').html(
                                field.label + (field.required ? '<span class="wafb-required-badge">*</span>' : '')
                            );
                            newField.find('.wafb-field-body input, .wafb-field-body textarea').attr('placeholder', field.placeholder);
                            
                            $('#wafb-form-fields').append(newField);
                        });
                    }

                    updatePreview();
                    showNotice('Form loaded successfully!', 'success');
                } else {
                    showNotice(response.data.message || wafbAdmin.strings.loadError, 'error');
                }
            },
            error: function() {
                showNotice(wafbAdmin.strings.loadError, 'error');
            }
        });
    }

    /**
     * Delete form via AJAX
     */
    function deleteForm(formId) {
        $.ajax({
            url: wafbAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'wafb_delete_form',
                nonce: wafbAdmin.nonce,
                form_id: formId
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    
                    // Remove from list and reload
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                showNotice('Error deleting form.', 'error');
            }
        });
    }

    /**
     * Serialize form fields to JSON
     */
    function serializeFormFields() {
        const fields = [];

        $('#wafb-form-fields .wafb-form-field').each(function() {
            const $field = $(this);
            fields.push({
                type: $field.data('field-type'),
                label: $field.find('.field-label').val(),
                placeholder: $field.find('.field-placeholder').val(),
                required: $field.find('.field-required').val() === '1'
            });
        });

        return fields;
    }

    /**
     * Update form preview
     */
    function updatePreview() {
        const $preview = $('#wafb-form-preview');
        $preview.empty();

        const fields = $('#wafb-form-fields .wafb-form-field');

        if (fields.length === 0) {
            $preview.html('<p class="preview-empty">Add fields to see preview</p>');
            return;
        }

        fields.each(function() {
            const $field = $(this);
            const fieldType = $field.data('field-type');
            const label = $field.find('.field-label').val();
            const placeholder = $field.find('.field-placeholder').val();
            const required = $field.find('.field-required').val() === '1';

            let inputHtml = '';
            
            if (fieldType === 'message') {
                inputHtml = `<textarea placeholder="${placeholder}" ${required ? 'required' : ''}></textarea>`;
            } else {
                let inputType = 'text';
                if (fieldType === 'email') inputType = 'email';
                if (fieldType === 'phone') inputType = 'tel';
                
                inputHtml = `<input type="${inputType}" placeholder="${placeholder}" ${required ? 'required' : ''} />`;
            }

            const previewField = `
                <div class="preview-field">
                    <label>${label}${required ? '<span class="wafb-required-badge">*</span>' : ''}</label>
                    ${inputHtml}
                </div>
            `;

            $preview.append(previewField);
        });
    }

    /**
     * Show notification
     */
    function showNotice(message, type) {
        const $notice = $('<div class="wafb-notice ' + type + '">' + message + '</div>');
        $('body').append($notice);

        setTimeout(function() {
            $notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

})(jQuery);
