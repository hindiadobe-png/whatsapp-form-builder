/**
 * WhatsApp Form Builder - Public JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Handle form submission
        $('.wafb-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $message = $form.find('.wafb-form-message');
            var $submitBtn = $form.find('.wafb-submit-btn');
            
            // Validate form
            if (!validateForm($form)) {
                return false;
            }
            
            // Disable submit button
            $submitBtn.prop('disabled', true);
            $form.addClass('wafb-loading');
            
            // Hide previous messages
            $message.removeClass('wafb-success wafb-error').hide();
            
            // Get form data
            var formData = $form.serialize();
            
            // Submit form
            $.ajax({
                url: wafbPublic.ajax_url,
                type: 'POST',
                data: formData + '&action=wafb_submit_form',
                success: function(response) {
                    $form.removeClass('wafb-loading');
                    $submitBtn.prop('disabled', false);
                    
                    if (response.success) {
                        $message.addClass('wafb-success').html(response.data.message).fadeIn();
                        
                        // Reset form
                        $form[0].reset();
                        
                        // Redirect to WhatsApp after a short delay
                        setTimeout(function() {
                            if (response.data.whatsapp_url) {
                                window.open(response.data.whatsapp_url, '_blank');
                            }
                        }, 1000);
                    } else {
                        $message.addClass('wafb-error').html(response.data.message).fadeIn();
                    }
                },
                error: function() {
                    $form.removeClass('wafb-loading');
                    $submitBtn.prop('disabled', false);
                    $message.addClass('wafb-error').html(wafbPublic.i18n.error).fadeIn();
                }
            });
        });
        
        /**
         * Validate form
         */
        function validateForm($form) {
            var isValid = true;
            
            // Remove previous error states
            $form.find('.wafb-field-error').removeClass('wafb-field-error');
            
            // Check required fields
            $form.find('[required]').each(function() {
                var $field = $(this);
                var value = $field.val();
                
                if (!value || value.trim() === '') {
                    $field.addClass('wafb-field-error');
                    isValid = false;
                }
                
                // Validate email
                if ($field.attr('type') === 'email' && value) {
                    if (!isValidEmail(value)) {
                        $field.addClass('wafb-field-error');
                        isValid = false;
                    }
                }
                
                // Validate phone
                if ($field.attr('type') === 'phone' && value) {
                    if (!isValidPhone(value)) {
                        $field.addClass('wafb-field-error');
                        isValid = false;
                    }
                }
            });
            
            if (!isValid) {
                // Scroll to first error
                var $firstError = $form.find('.wafb-field-error').first();
                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 500);
                }
            }
            
            return isValid;
        }
        
        /**
         * Validate email
         */
        function isValidEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
        
        /**
         * Validate phone
         */
        function isValidPhone(phone) {
            var re = /^[+]?[0-9\s\-\(\)]+$/;
            return re.test(phone);
        }
        
        // Add error styling
        var errorStyles = '<style>' +
            '.wafb-field-error { border-color: #d32f2f !important; }' +
            '.wafb-field-error:focus { border-color: #d32f2f !important; box-shadow: 0 0 0 2px rgba(211, 47, 47, 0.1); }' +
            '</style>';
        $('head').append(errorStyles);
        
    });
    
})(jQuery);
