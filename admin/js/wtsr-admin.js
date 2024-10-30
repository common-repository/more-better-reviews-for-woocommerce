(function( $ ) {

    $(document.body).on('click', '#wtsr_ts_enable_review_request_btn', function () {
        var btn = $(this);
        var enable = $('#wtsr_ts_enable_review_request').val();

        var data = {
            action: 'wtsr_ts_enable_review_request',
            enable: enable
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

    $(document.body).on('click', '#remove_all_reviews', function () {
        var btn = $(this);
        var sureMessage = btn.data('warningmsg');
        var sure = confirm(sureMessage);

        if (sure) {
            var data = {
                action: 'wtsr_delete_all_reviews'
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
        }
    });

    $(document.body).on('click', '#wtst-send-selected-reviews', function () {
        var btn = $(this);
        var sureMessage = btn.data('warningmsg');
        var notselectedMessage = btn.data('notselectedmsg');
        var selected_reviews = $('#the-list input:checked');

        if (selected_reviews.length < 1) {
            alert(notselectedMessage);
        } else {
            var sure = confirm(sureMessage);

            if (sure) {
                var reviews_ids = [];

                $.each(selected_reviews, function (index, review) {
                    reviews_ids.push($(review).val());
                });

                var data = {
                    action: 'wtsr_send_selected_reviews',
                    reviews_ids: JSON.stringify(reviews_ids)
                };

                ajaxRequest(
                    data,
                    function() {},
                    function() {}
                );
            }
        }
    });

    $(document.body).on('click', '#remove_selected_reviews', function () {
        var btn = $(this);
        var sureMessage = btn.data('warningmsg');
        var notselectedMessage = btn.data('notselectedmsg');
        var selected_reviews = $('#the-list input:checked');

        if (selected_reviews.length < 1) {
            alert(notselectedMessage);
        } else {
            var sure = confirm(sureMessage);

            if (sure) {
                var reviews_ids = [];

                $.each(selected_reviews, function (index, review) {
                    reviews_ids.push($(review).val());
                });

                var data = {
                    action: 'wtsr_delete_selected_reviews',
                    reviews_ids: JSON.stringify(reviews_ids)
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
            }
        }
    });

    $(document.body).on('click', '#wtsr_email_template_edit', function() {
        var preview_container = $('#wtsr_email_template_preview_container');
        var editor_container = $('#wtsr_email_template_editor');

        preview_container.hide();
        editor_container.show();

        $('html, body').animate({
            scrollTop: $("#wtsr_email_template_editor").offset().top
        }, 500);
	});

    $(document.body).on('click', '.license-action-btn', function() {
        var btn = $(this);
        var licenseAction = btn.data('license-action');
        var licenseEmail = $('#wtsr_license_email').val();
        var licenseKey = $('#wtsr_license_key').val();

        var data = {
            licenseAction: licenseAction,
            licenseEmail: licenseEmail,
            licenseKey: licenseKey,
            action: 'wtsr_license_action'
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

    $(document.body).on('click', '.wizard-license-action-btn', function() {
        var btn = $(this);
        var licenseAction = btn.data('license-action');
        var licenseEmail = $('#wtsr_license_email').val();
        var licenseKey = $('#wtsr_license_key').val();

        var data = {
            licenseAction: licenseAction,
            licenseEmail: licenseEmail,
            licenseKey: licenseKey,
            action: 'wtsr_license_action'
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

    $(document.body).on('click', '.wtsr_remove_license', function() {
        var btn = $(this);
        var licenseEmail = $('#wtsr_license_email').val();
        var licenseKey = $('#wtsr_license_key').val();
        var licenseSite = btn.data('site');

        var data = {
            licenseEmail: licenseEmail,
            licenseKey: licenseKey,
            licenseSite: licenseSite,
            action: 'wtsr_remove_license'
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

    $(document.body).on('click', '#generate_all_selected_reviews', function() {
        var btn = $(this);
        var sureMessage = btn.data('warningmsg');
        var sure = confirm(sureMessage);
        var reviews_ids = JSON.parse($('#generate_all_selected_reviews_input').val());

        if (sure) {
            var data = {
                action: 'wtsr_generate_selected_reviews',
                reviews_ids: JSON.stringify(reviews_ids)
            };

            ajaxRequest(data, function() {}, function() {});
        }

    });

    $(document.body).on('click', '#generate_selected_reviews', function() {
        var btn = $(this);
        var sureMessage = btn.data('warningmsg');
        var notselectedMessage = btn.data('notselectedmsg');
        var selected_reviews = $('#the-list input:checked');

        if (selected_reviews.length < 1) {
            alert(notselectedMessage);
        } else {
            var sure = confirm(sureMessage);

            if (sure) {
                var reviews_ids = [];

                $.each(selected_reviews, function (index, review) {
                    reviews_ids.push($(review).val());
                });

                var data = {
                    action: 'wtsr_generate_selected_reviews',
                    reviews_ids: JSON.stringify(reviews_ids)
                };

                ajaxRequest(data, function() {}, function() {});
            }
        }
    });

    $(document.body).on('click', '.generate-review', function() {
        var btn = $(this);
        var order_id = btn.data('order-id');

        var data = {
            order_id: order_id,
            action: 'wtsr_generate_review'
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

    $(document.body).on('click', '#wtsr_woocommerce_only_mode_enable', function() {
        var btn = $(this);
        var data = {
            action: 'wtsr_woocommerce_only_mode_enable'
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

    $(document.body).on('click', '.delete-review', function() {
        var btn = $(this);
        var review_id = btn.data('review-id');

        var data = {
            review_id: review_id,
            action: 'wtsr_delete_review'
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

    $(document.body).on('submit', '#email-settings_form', function(e) {
        var starLink = $('select.wtsr_ts_star_link');
        var isValid = true;

        starLink.each(function() {
            var link = $(this);
            var selected = link.val();

            if ('wtsr_custom_link' === selected) {
                var parent = link.parents('.wtsr_rating_link_item');
                var customLink = parent.find('.wtsr_rating_custom_link_holder');
                var customLinkVal = customLink.find('input').val();

                if ('' === $.trim(customLinkVal)) {
                    isValid = false;
                }
            }
        });

        return isValid;
	});

    $(document.body).on('change', 'select.wtsr_ts_star_link', function() {
        var selected = $(this).val();
        var parent = $(this).parents('.wtsr_rating_link_item');
        var customLink = parent.find('.wtsr_rating_custom_link_holder');
        var customLinkWarning = parent.find('.warning-text');

        if ('wtsr_custom_link' === selected) {
            customLink.show();
            customLinkWarning.show();
        } else {
            customLink.find('input').val('');
            customLink.hide();
            customLinkWarning.hide();
        }
	});

    $(document.body).on('input', '.wtsr_rating_custom_link_holder input', function() {
        var val = $(this).val();
        var parent = $(this).parents('.wtsr_rating_link_item');
        var customLinkWarning = parent.find('.warning-text');

        if ('' === $.trim(val)) {
            customLinkWarning.show();
        } else {
            customLinkWarning.hide();
        }
    });

    $(document.body).on('click', '#wtsr_save_credential', function() {
        var btn = $(this);
        var formData = $('#wtsr_save_credential_form').serializeArray();

        var data = {
            action: 'wtsr_save_ts_credentials',
            formData: formData
        };

        ajaxRequest(data);
    });

    $(document.body).on('click', '#wizard-wtsr_save_credential', function() {
        var btn = $(this);
        var formData = $('#wtsr_save_wizard_credential_form').serializeArray();

        var data = {
            formData:formData,
            action: 'wtsr_wizard_set_ts_credentials'
        };

        ajaxRequest(data);
    });

    $(document.body).on('click', '#wizard-wtsr_enable_woocommerce_only', function() {
        var btn = $(this);
        var confirm_message = btn.data('confirm-message');
        var sure = confirm(confirm_message);

        if (sure) ajaxRequest({action: 'wtsr_wizard_activate_woocommerce_only'});
    });

    $(document.body).on('click', '#wizard-wtsr_save_general_settings', function() {
        var btn = $(this);
        var wtsr_review_ask = $('#wtsr_review_ask').val();
        var wtsr_review_ask_template_editor = tmce_getContent('wtsr_review_ask_template_editor');
        var wtsr_filter_email_domain = $('#wtsr_filter_email_domain').val();
        var wtsr_review_period = $('#wtsr_review_period').val();
        var wtsr_order_status = $('#wtsr_order_status').val();
        var wtsr_review_variations = $('#wtsr_review_variations').val();
        var wtsr_review_approved = $('#wtsr_review_approved').prop("checked");

        var data = {
            action: 'wtsr_wizard_save_general_settings',
            wtsr_review_ask: wtsr_review_ask,
            wtsr_review_ask_template_editor: wtsr_review_ask_template_editor,
            wtsr_filter_email_domain: wtsr_filter_email_domain,
            wtsr_review_period: wtsr_review_period,
            wtsr_order_status: wtsr_order_status,
            wtsr_review_variations: wtsr_review_variations
        };

        if (wtsr_review_approved) {
            data.wtsr_review_approved = 1;
        }

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

    $(document.body).on('click', '#wizard-wtsr_rating_links_settings', function() {
        var btn = $(this);

        var wtsr_ts_one_star_link = $('#wtsr_ts_one_star_link').val();
        var wtsr_ts_two_star_link = $('#wtsr_ts_two_star_link').val();
        var wtsr_ts_three_star_link = $('#wtsr_ts_three_star_link').val();
        var wtsr_ts_four_star_link = $('#wtsr_ts_four_star_link').val();
        var wtsr_ts_five_star_link = $('#wtsr_ts_five_star_link').val();

        var wtsr_ts_one_star_custom_link = $('#wtsr_ts_one_star_custom_link').val();
        var wtsr_ts_two_star_custom_link = $('#wtsr_ts_two_star_custom_link').val();
        var wtsr_ts_three_star_custom_link = $('#wtsr_ts_three_star_custom_link').val();
        var wtsr_ts_four_star_custom_link = $('#wtsr_ts_four_star_custom_link').val();
        var wtsr_ts_five_star_custom_link = $('#wtsr_ts_five_star_custom_link').val();

        var data = {
            action: 'wtsr_wizard_save_star_rating_settings',
            wtsr_ts_one_star_link: wtsr_ts_one_star_link,
            wtsr_ts_two_star_link: wtsr_ts_two_star_link,
            wtsr_ts_three_star_link: wtsr_ts_three_star_link,
            wtsr_ts_four_star_link: wtsr_ts_four_star_link,
            wtsr_ts_five_star_link: wtsr_ts_five_star_link,
            wtsr_ts_one_star_custom_link: wtsr_ts_one_star_custom_link,
            wtsr_ts_two_star_custom_link: wtsr_ts_two_star_custom_link,
            wtsr_ts_three_star_custom_link: wtsr_ts_three_star_custom_link,
            wtsr_ts_four_star_custom_link: wtsr_ts_four_star_custom_link,
            wtsr_ts_five_star_custom_link: wtsr_ts_five_star_custom_link
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

    $(document.body).on('click', '#wizard-wtsr_save_template', function() {
        var btn = $(this);

        var wtsr_button_bg_color = $('#wtsr_button_bg_color').val();
        var wtsr_button_text_color = $('#wtsr_button_text_color').val();
        var wtsr_image_size = $('#wtsr_image_size').val();
        var wtsr_email_template = tmce_getContent('wtsr_email_template');

        var data = {
            action: 'wtsr_wizard_save_email_template_settings',
            wtsr_button_bg_color: wtsr_button_bg_color,
            wtsr_button_text_color: wtsr_button_text_color,
            wtsr_image_size: wtsr_image_size,
            wtsr_email_template: wtsr_email_template
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

    $(document.body).on('click', '#wtsr_generate_dummy_orders', function() {
        $('.wtsr-processing-holder .wtsr-spinner-holder').addClass('wtsr-processing');
        var domain = $('#wtsr_generate_dummy_orders_domain').val();

        var data = {
            action: 'wtsr_generate_dummy_orders',
            domain: domain,
            number: 10
        };

        ajaxRequest(
            data,
            function() {
                $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');
            },
            function() {
                $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');
            }
        );
    });

    $(document.body).on('click', '#wizard-wtsr_generate_orders', function() {
        $('.wtsr-processing-holder .wtsr-spinner-holder').addClass('wtsr-processing');
        var status = $(this).data('status');

        var data = {
            action: 'wtsr_wizard_generate_dummy_orders'
        };

        if (status) {
            data.status = status;
        }

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

                        $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');

                        window.location.reload();
                    } else {
                        alert(decoded.message);

                        $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');
                    }
                } else {
                    alert('Something went wrong');

                    $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');
                }
            }
        });
    });

    $(document.body).on('click', '#wizard-wtsr_import_activate_map', function() {
        var data = {
            action: 'wtsr_wizard_import_activate_map'
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

                        $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');

                        window.location.reload();
                    } else {
                        alert(decoded.message);

                        $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');
                    }
                } else {
                    alert('Something went wrong');

                    $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');
                }
            }
        });
    });

    $(document.body).on('click', '#wizard-wtsr_activate_map', function() {
        var btn = $(this);
        var map_id = btn.data('map-id');


        var data = {
            action: 'wtsr_wizard_activate_map',
            map_id: map_id,
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

                        $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');

                        window.location.reload();
                    } else {
                        alert(decoded.message);

                        $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');
                    }
                } else {
                    alert('Something went wrong');

                    $('.wtsr-processing-holder .wtsr-spinner-holder').removeClass('wtsr-processing');
                }
            }
        });
    });

    $(document.body).on('change', '#wtsr_email_send_via', function() {
        var control = $(this);
        var sendVia = control.val();
        var additionalSendViaContainer = $('.wtsr_email_send_via_'+sendVia+'_container');
        var additionalContainers = $('.wtsr_email_send_via_container');
        var sendDelay = $('#wtsr_email_send_via_woocommerce_delay');

        if ('woocommerce' !== sendVia) {
            sendDelay.attr('disabled', true);

            if ($('.required-plugin-container').length) {
                $('#wtsr_save_email_service').attr('disabled', true).removeClass('button-primary');
            }
        } else {
            sendDelay.attr('disabled', false);
            $('#wtsr_save_email_service').attr('disabled', false).addClass('button-primary');
        }

        if (additionalContainers.length) {
            additionalContainers.each(function() {
                $(this).hide();
            });
        }

        if (additionalSendViaContainer.length) {
            additionalSendViaContainer.each(function() {
                $(this).show();
            });
        }
    });

    $(document.body).on('click', '.wtsr-install-plugin-wp2leads', function () {
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
                        processingHolder.removeClass('wtsr-processing');
                        btn.parents('.wtsr-processing-holder').remove();

                        $('#wtsr_save_email_service').attr('disabled', false).addClass('button-primary');
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

    $(document.body).on('click', '#wtsr_email_test_score_button', function() {
        var control = $(this);
        var review = control.data('review');
        var emailHolder = $('#wtsr_email_test_score');
        var email = emailHolder.val();
        var template = control.data('template');

        var data = {
            action: 'wtsr_test_spammyness',
            email: email,
            review: review,
            template: template
        };

        ajaxRequest(data);
    });

    $(document.body).on('click', '#wtsr_all_reviews_page_create', function() {
        var title = $('#wtsr_all_reviews_page_title').val();

        var data = {
            title: title,
            action: 'wtsr_all_reviews_page_create'
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

    $(document.body).on('click', '#wtsr_all_reviews_page_colors_save', function() {
        var normal = $('#wtsr_normal_title_color').val();
        var hover = $('#wtsr_hover_title_color').val();
        var normal_button_bg = $('#wtsr_normal_button_bg_color').val();
        var normal_button_txt = $('#wtsr_normal_button_txt_color').val();
        var hover_button_bg = $('#wtsr_hover_button_bg_color').val();
        var hover_button_txt = $('#wtsr_hover_button_txt_color').val();
        var uploaded_image = $('#wtsr_uploaded_image').val();
        var wtsr_all_reviews_page_reviews_min = $('#wtsr_all_reviews_page_reviews_min').val();
        var wtsr_all_reviews_page_reviews_title = $('#wtsr_all_reviews_page_reviews_title').val();
        var wtsr_all_reviews_page_comment_placeholder = $('#wtsr_all_reviews_page_comment_placeholder').val();
        var all_reviews_page_footer_template_editor = tmce_getContent('wtsr_all_reviews_page_footer_template_editor');
        var wtsr_all_reviews_page_description = 'no';
        var wtsr_all_reviews_page_product_link = 'no';

        if($('#wtsr_all_reviews_page_description').is(':checked')) {
            wtsr_all_reviews_page_description = 'yes';
        }

        if($('#wtsr_all_reviews_page_product_link').is(':checked')) {
            wtsr_all_reviews_page_product_link = 'yes';
        }

        var data = {
            normal: normal,
            hover: hover,
            normal_button_bg: normal_button_bg,
            normal_button_txt: normal_button_txt,
            hover_button_bg: hover_button_bg,
            hover_button_txt: hover_button_txt,
            uploaded_image: uploaded_image,
            wtsr_all_reviews_page_reviews_title: wtsr_all_reviews_page_reviews_title,
            all_reviews_page_footer_template_editor: all_reviews_page_footer_template_editor,
            wtsr_all_reviews_page_description: wtsr_all_reviews_page_description,
            wtsr_all_reviews_page_product_link: wtsr_all_reviews_page_product_link,
            wtsr_all_reviews_page_reviews_min: wtsr_all_reviews_page_reviews_min,
            wtsr_all_reviews_page_comment_placeholder: wtsr_all_reviews_page_comment_placeholder,
            action: 'wtsr_all_reviews_page_colors_save'
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

    $(document.body).on('change', '.wtsr_dependency_control', function() {
        var val = $(this).val();
        var condition = $(this).data('enabled');
       var dependency = $(this).data('dependency');
       var depContainers = $('.' + dependency);

       if (depContainers.length) {
           depContainers.each(function(index) {
               var item = $(this);

               if (val == condition) {
                   item.show();
               } else {
                   item.hide();
               }
           });
       }


    });

    $(document.body).on('click', '#wtsr_email_html_templates_preview_control a', function(e) {
        e.preventDefault();

        var tab = $(this).data('tab');
        var items = $('.wtsr_email_html_templates_preview_item');
        var tabsControl = $('#wtsr_email_html_templates_preview_control a');
        var sendEmailBtn = $('#wtsr_email_test_score_button');

        if (tabsControl.length) {
            tabsControl.each(function(index) {
                var item = $(this);
                item.removeClass('nav-tab-active');
            });

            $(this).addClass('nav-tab-active');
        }

        if (items.length) {
            items.each(function(index) {
                var item = $(this);
                item.hide();
            });

            $('#wtsr_' + tab + '_template_preview_container').show();
            sendEmailBtn.data('template', tab);
        }
    });

    $(document.body).on('click', '#wtsr_save_thankyou_settings', function() {
        var thankyou_template = tmce_getContent('thankyou_template');
        $("#thankyou-settings_form #thankyou_template").val(thankyou_template);
        var formData = $("#thankyou-settings_form").serializeArray();

        var data = {
            action: 'wtsr_save_thankyou_settings',
            formData: formData
        };

        ajaxRequest(
            data,
            function() {},
            function() {}
        );
    });

    $(document.body).on('change', '#wtsr_discount_type', function() {
        var val = $(this).val();
        var container = $('#wtsr_coupon_settings');

        if ('none' == val) {
            container.addClass('setting_disabled');
        } else {
            container.removeClass('setting_disabled');
        }
    });

    $(document.body).on('click', '#wtsr_save_email_service', function() {
        var control = $(this);
        var settingsContainer = control.parents('.wtsr-settings-group');
        var emailSendVia = settingsContainer.find('#wtsr_email_send_via').val();
        var emailSendViaWoocommerceDelay = settingsContainer.find('#wtsr_email_send_via_woocommerce_delay').val();

        var data = {
            action: 'wtsr_settings_save',
            settingsGroup: 'email_send_via',
            emailSendVia: emailSendVia,
            emailSendViaWoocommerceDelay: emailSendViaWoocommerceDelay
        };

        ajaxRequest(data);
    });

    $(document.body).on('click', '#wtsr_save_email_service_wizard', function() {
        var control = $(this);
        var settingsContainer = control.parents('.settings-container');
        var emailSendVia = settingsContainer.find('#wtsr_email_send_via').val();
        var emailSendViaWoocommerceDelay = settingsContainer.find('#wtsr_email_send_via_woocommerce_delay').val();

        var data = {
            action: 'wtsr_settings_save',
            settingsGroup: 'email_send_via',
            emailSendVia: emailSendVia,
            emailSendViaWoocommerceDelay: emailSendViaWoocommerceDelay
        };

        ajaxRequest(data);
    });

    $(document.body).on('click', '#wtsr_get_ts_reviews', function() {
        var control = $(this);

        $('#wtsr_get_ts_reviews-processing').addClass('wtsr-processing');
        var data = {action: 'wtsr_get_ts_reviews'};

        ajaxRequest(data, null, function() {
            setTimeout(function() {
                $('#wtsr_get_ts_reviews-processing').removeClass('wtsr-processing');
            }, 500);
        });
    });

    $(document.body).on('click', '#wtsr_map_ts_reviews', function() {
        $('#wtsr_get_ts_reviews-processing').addClass('wtsr-processing');
        setTimeout(function() {
            var control = $(this);
            var formData = $("#wtsr_ts_to_woo_connection_form").serializeArray();

            var data = {
                action: 'wtsr_map_ts_reviews',
                formData: formData
            };

            ajaxRequest(data, null, function() {
                setTimeout(function() {
                    $('#wtsr_get_ts_reviews-processing').removeClass('wtsr-processing');
                }, 500);
            });
        }, 20);
    });

    $(document.body).on('click', '#wtsr_import_ts_reviews', function() {
        $('#wtsr_get_ts_reviews-processing').addClass('wtsr-processing');
        var btn = $(this);

        setTimeout(function() {
            if (confirm(btn.data('confirm'))) {
                ajaxRequest({action: 'wtsr_import_ts_reviews'}, null, function() {
                    setTimeout(function() {
                        $('#wtsr_get_ts_reviews-processing').removeClass('wtsr-processing');
                    }, 500);
                });
            }
        }, 20);
    });

    $(document.body).on('click', '#wtsr_delete_ts_reviews', function() {
        $('#wtsr_get_ts_reviews-processing').addClass('wtsr-processing');
        var btn = $(this);

        setTimeout(function() {
            if (confirm(btn.data('confirm'))) {
                ajaxRequest({action: 'wtsr_delete_ts_reviews'}, null, function() {
                    setTimeout(function() {
                        $('#wtsr_get_ts_reviews-processing').removeClass('wtsr-processing');
                    }, 500);
                });
            } else {
                setTimeout(function() {
                    $('#wtsr_get_ts_reviews-processing').removeClass('wtsr-processing');
                }, 500);
            }
        }, 20);
    });

    $(document.body).on('click', '.review_map_to_wc', function() {
        var container = $(this).parents('.wtsr-settings-group');
        var btn = container.find('.wtsr_map_reviews');
        var btn_import = container.find('.wtsr_import_reviews');
        var mapping_selects = container.find('select.review_map_to_wc');
        var is_update = false;

        if (mapping_selects.length) {
            $.each(mapping_selects, function (index, mapping_select) {
                if ($(mapping_select).val() != $(mapping_select).data('mapped')) {
                    is_update = true;
                    return false;
                }
            });
        }

        if (is_update) {
            btn.removeClass('button-primary').addClass('button-success').attr('disabled', false);
            if (btn_import.length) {
                btn_import.removeClass('button-success').attr('disabled', true);
            }
        } else {
            btn.removeClass('button-success').addClass('button-primary').attr('disabled', true);
            if (btn_import.length) {
                btn_import.addClass('button-success').attr('disabled', false);
            }
        }
    });

    $('body').on('click', '.wtsr-icon-cancel-schedule-email', function(){
        var control = $(this);
        var parent = control.parents('li');
        var review = control.data('review');
        var schedule = control.data('schedule');

        var data = {
            action: 'wtsr_cancel_schedule',
            review: review,
            schedule: schedule
        };

        ajaxRequest(data, function() {
            parent.remove();
        });
    });

    $('body').on('click', '.send-woo-review', function(){
        var control = $(this);
        var review = control.data('review');
        control.addClass('wtsr-loading').addClass('disabled').attr('disabled', true);

        var data = {
            action: 'wtsr_send_review',
            review: review
        };

        ajaxRequest(data,
        null, function() {
            control.removeClass('wtsr-loading').removeClass('disabled').attr('disabled', false);
        });
    });

    $('body').on('click', '.ts-reviews-accordeon', function(){
        var control = $(this);
        var body = control.parents('td').find('.ts-reviews-accordeon-body');

        if (control.hasClass('closed')) {
            control.removeClass('closed').addClass('opened');
            body.removeClass('closed').addClass('opened');
        } else if (control.hasClass('opened')) {
            control.removeClass('opened').addClass('closed');
            body.removeClass('opened').addClass('closed');
        }
    });

    $('body').on('click', '#wtsr_upload_image_btn', function(e){
        e.preventDefault();

        var button = $(this),
            custom_uploader = wp.media({
                title: 'Insert image',
                library : {
                    // uncomment the next line if you want to attach image to the current post
                    // uploadedTo : wp.media.view.settings.post.id,
                    type : 'image'
                },
                button: {
                    text: 'Use this image' // button label text
                },
                multiple: false // for multiple image selection set to true
            }).on('select', function() { // it also has "open" and "close" events
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                var imgHolder = $('#wtsr_uploaded_image_holder');
                imgHolder.html('<img class="true_pre_image" src="' + attachment.url + '" />').next().val(attachment.id);
                $('#wtsr_upload_image_btn').hide();
                $('#wtsr_delete_image_btn').show();
                /* if you sen multiple to true, here is some code for getting the image IDs
                var attachments = frame.state().get('selection'),
                    attachment_ids = new Array(),
                    i = 0;
                attachments.each(function(attachment) {
                     attachment_ids[i] = attachment['id'];
                    console.log( attachment );
                    i++;
                });
                */
            })
                .open();
    });

    $('body').on('click', '#wtsr_delete_image_btn', function(e){
        e.preventDefault();

        var imgHolder = $('#wtsr_uploaded_image_holder');
        imgHolder.empty().next().val('');
        $('#wtsr_upload_image_btn').show();
        $('#wtsr_delete_image_btn').hide();
    });

    $(document.body).on('change',
        '#wtsr_email_send_via, ' +
        '#wtsr_review_ask, ' +
        '#wtsr_order_status, ' +
        '#wtsr_review_variations, ' +
        '#wtsr_review_approved, ' +
        '#wtsr_review_period, ' +
        '#wtsr_filter_email_domain, ' +
        '#wtsr_review_ask_template_editor, ' +
        '#wtsr_button_text_color, ' +
        '#wtsr_button_bg_color, ' +
        '#wtsr_image_size, ' +
        '#wtsr_email_template, ' +
        '.wtsr_ts_star_link, ' +
        '#wtsr_all_reviews_page_reviews_min, ' +
        '#wtsr_all_reviews_page_comment_placeholder, ' +
        '#wtsr_all_reviews_page_reviews_title, ' +
        '#wtsr_all_reviews_page_footer_template_editor, ' +
        '#wtsr_all_reviews_page_product_link, ' +
        '#wtsr_all_reviews_page_description, ' +
        '#wtsr_coupon_amount, ' +
        '#wtsr_coupon_expiration, ' +
        '#wtsr_discount_type, ' +
        '#wtsr_thankyou_enabled, ' +
        '#wtsr_coupon_countdown, ' +
        '#wtsr_coupon_countdown_period, ' +
        '#wtsr_ts_enable_review_request',
        function()
    {
        var warningHolder = $('#navigation-wizard-warning');

        if (warningHolder.length > 0) {
            warningHolder.show();
        }

    });

    $(document.body).on('keyup',
        '#wtsr_review_period, ' +
        '#wtsr_email_send_via_woocommerce_delay, ' +
        '#wtsr_filter_email_domain, ' +
        '#wtsr_review_ask_template_editor, ' +
        '#wtsr_email_template, ' +
        '#wtsr_all_reviews_page_reviews_min, ' +
        '#wtsr_all_reviews_page_comment_placeholder, ' +
        '#wtsr_coupon_amount, ' +
        '#wtsr_coupon_expiration, ' +
        '#wtsr_coupon_description, ' +
        '#wtsr_coupon_countdown_period, ' +
        '#wtsr_coupon_countdown_description, ' +
        '#wtsr_all_reviews_page_reviews_title, #wtsr_all_reviews_page_footer_template_editor',
        function()
    {
        var warningHolder = $('#navigation-wizard-warning');

        if (warningHolder.length > 0) {
            warningHolder.show();
        }

    });

    jQuery( document ).on( 'tinymce-editor-init', function( event, editor ) {
        editor.on('Change', function (e) {
            var warningHolder = $('#navigation-wizard-warning');

            if (warningHolder.length > 0) {
                warningHolder.show();
            }
        });
    });

    $(document).ready(function () {
        $('.wtsr-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            onSelect: dateChanged,
            changeMonth: true,
            changeYear: true
        });

        $('.wtsr-color-picker').wpColorPicker(
            {
                change: function(event, ui){
                    var warningHolder = $('#navigation-wizard-warning');

                    if (warningHolder.length > 0) {
                        warningHolder.show();
                    }
                }
            }
        );

        var mapping_selects = $('select.review_map_to_wc');

        if (mapping_selects.length) {
            var is_focus = true;

            $.each(mapping_selects, function (index, mapping_select) {
                // console.log($(mapping_select).val());
                // console.log($(mapping_select).data('mapped'));
                if ($(mapping_select).val()) {
                    is_focus = false;
                }
            });

            if (is_focus) {
                mapping_selects[0].focus();
                $(mapping_selects[0]).addClass('select-success');
            }
        }
    });

    function tmce_getContent(editor_id, textarea_id) {
        if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
        if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;

        if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
            return tinyMCE.get(editor_id).getContent();
        }else{
            return jQuery('#'+textarea_id).val();
        }
    }

    function dateChanged(date, inst) {
        var dateTimestamp = Date.parse(date);
    }

    function ajaxRequest(data, cb, cbError) {
        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,
            success: function (response) {
                var decoded;

                console.log(response);

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    console.log(err);
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        if (decoded.message) {
                            alert(decoded.message);
                        }

                        if (decoded.url) {
                            window.location.replace(decoded.url);
                        } else if (decoded.reload) {
                            window.location.reload();
                        }

                        if (typeof cb === 'function') {
                            cb();
                        }
                    } else {
                        if (decoded.message) {
                            alert(decoded.message);
                        }

                        if (typeof cbError === 'function') {
                            cbError();
                        }
                    }
                } else {
                    alert('Something went wrong');
                }
            }
        });
    }

})( jQuery );
