(function( $ ) {
    $(document.body).on('click', '#ts-woocommerce-reviews-per-product-enable', function () {
        var data = {
            action: 'wtsr_woocommerce_reviews_per_product_enable'
        };

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,

            success: function(response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    console.log(err);
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        alert(decoded.message);
                        window.location.reload();
                    } else {
                        alert(decoded.message);
                    }
                } else {
                    alert('Something went wrong');
                }
            }
        });

    });

    $(document.body).on('click', '#ts-woocommerce-reviews-enable', function () {
        var btn = $(this);

        var data = {
            action: 'wtsr_woocommerce_reviews_enable'
        };

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,

            success: function(response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    console.log(err);
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        alert(decoded.message);
                        window.location.reload();
                    } else {
                        alert(decoded.message);
                    }
                } else {
                    alert('Something went wrong');
                }
            }
        });
    });

    $(document.body).on('click', '.notice-to-dismiss .dismiss-btn', function () {
        var btn = $(this);
        var dismiss = btn.data('dismiss');
        var dismissContainer = btn.parents('.notice-to-dismiss');
        var dismissSlug = dismissContainer.data('slug');

        var data = {
            action: 'wtsr_dissmiss_notice',
            dismiss: dismiss,
            dismissSlug: dismissSlug
        };

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,

            success: function(response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    console.log(err);
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        dismissContainer.remove();
                    } else {
                        alert(decoded.message);
                    }
                } else {
                    alert('Something went wrong');
                }
            }
        });
    });

    $(document.body).on('click', '.wtsr-install-plugin', function () {
        var btn = $(this);
        var plugin = btn.data('plugin');

        var processingHolder = $('.wtsr-processing-holder .wtsr-spinner-holder');
        processingHolder.addClass('wtsr-processing');

        var data = {
            action: 'wtsr_install_plugin',
            plugin: plugin
        };

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,

            success: function(response) {
                response = response.split('&&&&&');
                console.log(response);
                var decoded;

                try {
                    decoded = $.parseJSON(response[1]);
                } catch(err) {
                    console.log(err);
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        alert(decoded.message);
                        // window.location.reload(false);

                        window.location.href = window.location.href;
                    } else {
                        alert(decoded.message);
                        processingHolder.removeClass('wtsr-processing');
                    }
                } else {
                    alert('Something went wrong');
                    processingHolder.removeClass('wtsr-processing');
                }
            }
        });
    });

    $(document.body).on('click', '.go-to-step', function () {
        var btn = $(this);
        var step = btn.data('step');

        // var processingHolder = $('.wtsr-processing-holder .wtsr-spinner-holder');
        // processingHolder.addClass('wtsr-processing');

        var data = {
            action: 'wtsr_wizard_go_to_step',
            step: step
        };

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,

            success: function(response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    console.log(err);
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        if (decoded.redirect) {
                            window.location.replace(decoded.redirect);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert(decoded.message);
                    }
                } else {
                    alert('Something went wrong');
                }
            }
        });
    });

    $(document.body).on('click', '.data-show-toggle-control', function () {
        var btn = $(this);
        var target_selector = btn.data('toggle-target');
        var target = $('#' + target_selector);

        if (target.length > 0) {
            target.each(function () {
                var container = $(this);

                if (container.hasClass('active')) {
                    container.hide().removeClass('active');
                } else {
                    container.show().addClass('active');
                }
            });
        }
    });

    $(document.body).on('click', '#wtsr-activate-wizard', function() {
       var data = {
           action: 'wtsr_activate_wizard'
       };

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,

            success: function(response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    console.log(err);
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        if (decoded.redirect) {
                            window.location.replace(decoded.redirect);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert(decoded.message);
                    }
                } else {
                    alert('Something went wrong');
                }
            }
        });
    });

    $(document).ready(function () {});
})( jQuery );
