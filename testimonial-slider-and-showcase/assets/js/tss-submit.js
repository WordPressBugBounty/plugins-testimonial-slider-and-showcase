(function ($) {
    if ($.fn.validate) {

        $('#tss-submit-form').validate({
            submitHandler: function (form) {
                var f = $(form),
                    formData = new FormData(),
                    feature_image = $('#tss_feature_image'),
                    oldData = f.serializeArray(),
                    btn = f.find('.tss-submit-button'),
                    response = $("#tss-submit-response"),
                    recaptcha = $("#tss_recaptcha");

                // Fix: Check if tss.recaptcha exists before accessing its properties
                if (tss && tss.recaptcha && tss.recaptcha.enable && !recaptcha.find('.g-recaptcha-response').val()) {
                    response.addClass('error');
                    recaptcha.focus();
                    response.html("<p>" + tss.error.tss_recaptcha + "</p>");
                    return false;
                }

                oldData.forEach(function (item) {
                    formData.append(item.name, item.value);
                });

                if (feature_image.length) {
                    formData.append('feature_image', feature_image[0].files[0]);
                }

                formData.append('action', 'tss_submit_action');
                formData.append(tss.nonceId, tss.nonce);

                // Post via AJAX
                $.ajax({
                    url: tss.ajaxurl,
                    type: 'POST',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    beforeSend: function () {
                        f.addClass('rtcessing');
                        response.html('');
                        response.removeClass('error');
                        $("<span class='rtcessing rt-animate-spin dashicons dashicons-image-rotate'></span>").insertAfter(btn);
                    },
                    success: function (data) {
                        btn.next('span.rtcessing').remove();
                        f.removeClass('rtcessing');

                        // Handle both JSON and non-JSON responses
                        var message = '';
                        var isError = false;

                        if (typeof data === 'object') {
                            // JSON response
                            isError = data.error || false;
                            message = data.msg || 'Request completed';
                        } else {
                            // Plain text/HTML response
                            message = data;
                        }

                        if (!isError) {
                            response.addClass('success').removeClass('error');
                            response.html("<p>" + (tss.success.tss_thankyou || 'Thank you for your testimonial!') + "</p>");
                            f[0].reset();
                        } else {
                            response.addClass('error').removeClass('success');
                            response.html($("<p />").append(message));
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log("AJAX Error:", textStatus, errorThrown);
                        console.log("Response:", jqXHR.responseText);
                        btn.next('span.rtcessing').remove();
                        f.removeClass('rtcessing');
                        response.addClass('error').removeClass('success');
                        response.html("<p>" + (message || tss.error.tss_error) + "</p>");

                    }
                });
            }
        });
    }

    /* Rating */
    $('.rt-rating').on('click', 'span', function () {
        var self = $(this),
            parent = self.parent(),
            star = parseInt(self.data('star'), 10);
        parent.find('.rating-value').val(star);
        parent.addClass('selected');
        parent.find('span').removeClass('active');
        self.addClass('active');
    });

})(jQuery);