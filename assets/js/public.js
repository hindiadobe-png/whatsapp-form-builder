/**
 * Public JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Handle form submission
        $('.wfbp-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var formId = $form.data('form-id');
            var $submitBtn = $form.find('.wfbp-submit-btn');
            
            // Remove previous messages
            $form.find('.wfbp-error, .wfbp-success').remove();
            $form.find('.wfbp-field').removeClass('error');
            
            // Collect form data
            var formData = {};
            $form.find('input, textarea, select').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                
                if (name && name !== 'wfbp_nonce' && name !== '_wp_http_referer') {
                    if ($input.attr('type') === 'checkbox') {
                        if ($input.is(':checked')) {
                            if (!formData[name]) {
                                formData[name] = [];
                            }
                            formData[name].push($input.val());
                        }
                    } else if ($input.attr('type') === 'radio') {
                        if ($input.is(':checked')) {
                            formData[name] = $input.val();
                        }
                    } else {
                        formData[name] = $input.val();
                    }
                }
            });
            
            // Get reCAPTCHA response
            var recaptchaResponse = '';
            if (typeof grecaptcha !== 'undefined' && $form.find('.g-recaptcha').length) {
                recaptchaResponse = grecaptcha.getResponse();
            }
            
            // Disable form and show loading
            $form.addClass('loading');
            $submitBtn.prop('disabled', true);
            var originalBtnText = $submitBtn.text();
            $submitBtn.text(wfbp_ajax.messages.submitting);
            
            // Submit via AJAX
            $.ajax({
                url: wfbp_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wfbp_submit_form',
                    nonce: wfbp_ajax.nonce,
                    form_id: formId,
                    form_data: formData,
                    recaptcha_response: recaptchaResponse,
                    page_url: window.location.href
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        var $success = $('<div class="wfbp-success">' + response.data.message + '</div>');
                        $form.prepend($success);
                        
                        // Reset form
                        $form[0].reset();
                        
                        // Reset reCAPTCHA
                        if (typeof grecaptcha !== 'undefined') {
                            grecaptcha.reset();
                        }
                        
                        // Redirect to WhatsApp after 1 second
                        setTimeout(function() {
                            window.open(response.data.whatsapp_url, '_blank');
                        }, 1000);
                    } else {
                        // Show error message
                        var errorMessage = response.data.message || wfbp_ajax.messages.error;
                        var $error = $('<div class="wfbp-error">' + errorMessage + '</div>');
                        $form.prepend($error);
                        
                        // Highlight error fields if available
                        if (response.data.errors) {
                            highlightErrorFields($form, response.data.errors);
                        }
                        
                        // Scroll to error
                        $('html, body').animate({
                            scrollTop: $error.offset().top - 100
                        }, 300);
                    }
                },
                error: function() {
                    var $error = $('<div class="wfbp-error">' + wfbp_ajax.messages.error + '</div>');
                    $form.prepend($error);
                },
                complete: function() {
                    // Re-enable form
                    $form.removeClass('loading');
                    $submitBtn.prop('disabled', false).text(originalBtnText);
                }
            });
        });
        
        // Client-side validation
        $('.wfbp-form input, .wfbp-form textarea, .wfbp-form select').on('blur', function() {
            validateField($(this));
        });
        
    });
    
    /**
     * Validate individual field
     */
    function validateField($field) {
        var $fieldContainer = $field.closest('.wfbp-field');
        var value = $field.val();
        var required = $field.prop('required');
        var type = $field.attr('type') || 'text';
        
        // Remove previous error
        $fieldContainer.removeClass('error');
        $fieldContainer.find('.wfbp-field-error').remove();
        
        // Required check
        if (required && !value) {
            showFieldError($fieldContainer, 'This field is required.');
            return false;
        }
        
        // Type-specific validation
        if (value) {
            if (type === 'email' && !isValidEmail(value)) {
                showFieldError($fieldContainer, 'Please enter a valid email address.');
                return false;
            }
            
            if ((type === 'tel' || type === 'phone') && !isValidPhone(value)) {
                showFieldError($fieldContainer, 'Please enter a valid phone number.');
                return false;
            }
            
            if (type === 'url' && !isValidUrl(value)) {
                showFieldError($fieldContainer, 'Please enter a valid URL.');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Show field error
     */
    function showFieldError($fieldContainer, message) {
        $fieldContainer.addClass('error');
        var $error = $('<span class="wfbp-field-error" style="color: #d63638; font-size: 12px; display: block; margin-top: 5px;">' + message + '</span>');
        $fieldContainer.append($error);
    }
    
    /**
     * Highlight error fields
     */
    function highlightErrorFields($form, errors) {
        // This is a simple implementation
        // Could be enhanced to match specific fields based on error messages
        $form.find('input[required], textarea[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).closest('.wfbp-field').addClass('error');
            }
        });
    }
    
    /**
     * Validate email
     */
    function isValidEmail(email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    /**
     * Validate phone
     */
    function isValidPhone(phone) {
        var cleaned = phone.replace(/[^0-9+]/g, '');
        return cleaned.length >= 10;
    }
    
    /**
     * Validate URL
     */
    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }
    
})(jQuery);
