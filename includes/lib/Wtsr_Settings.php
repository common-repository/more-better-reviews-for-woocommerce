<?php


class Wtsr_Settings {
    public static function save_email_send_via() {
        $wtsr_email_send_via = !empty($_POST['emailSendVia']) ? sanitize_text_field($_POST['emailSendVia']) : false;
        $wtsr_email_send_via_woocommerce_delay = !empty($_POST['emailSendViaWoocommerceDelay']) ? trim(sanitize_text_field($_POST['emailSendViaWoocommerceDelay']), ':') : false;

        if (empty($wtsr_email_send_via)) {
            $params = array(
                'message' => __('Select service you want to use for sending email requests.', 'more-better-reviews-for-woocommerce')
            );

            Wtsr_Admin_Ajax::error_response($params);
        }

        if (!empty($wtsr_email_send_via_woocommerce_delay)) {
            $delay_array = explode(':', $wtsr_email_send_via_woocommerce_delay);

            if (!empty($delay_array)) {
                foreach ($delay_array as $delay_days) {
                    if (!is_numeric($delay_days)) {
                        $params = array(
                            'message' => __('Please use only numbers devided with ":" for WooCommerce Email Delay.', 'more-better-reviews-for-woocommerce')
                        );

                        Wtsr_Admin_Ajax::error_response($params);
                    }
                }
            }
        }

        if (!empty($wtsr_email_send_via)) {
            update_option( 'wtsr_email_send_via', $wtsr_email_send_via );

            if ('woocommerce' === $wtsr_email_send_via && !empty($wtsr_email_send_via_woocommerce_delay)) {
                update_option('wtsr_email_send_via_woocommerce_delay', $wtsr_email_send_via_woocommerce_delay);
            } else {
                delete_option( 'wtsr_email_send_via_woocommerce_delay' );
            }
        } else {
            delete_option( 'wtsr_email_send_via' );
            delete_option( 'wtsr_email_send_via_woocommerce_delay' );
        }

        $params = array(
            'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Settings saved.', 'more-better-reviews-for-woocommerce'),
            'reload' => 1,
        );

        Wtsr_Admin_Ajax::success_response($params);
    }

    public static function save_thankyou_settings($data) {
        $errors = array();

        if ('yes' === $data["thankyou_enabled"]) {
            if ('none' !== $data['discount_type']) {
                if ('' === $data['coupon_amount']) {
                    $errors[] = __('you need to set up coupon amount', 'more-better-reviews-for-woocommerce');
                }

                if ('no' !== $data['coupon_countdown']) {
                    if ('' === $data['coupon_countdown_period']) {
                        $errors[] = __('you need to set up coupon countdown period', 'more-better-reviews-for-woocommerce');
                    }
                }
            }

            if (false !== strpos($data["thankyou_template"], '{coupon_description}')) {
                if ('none' === $data['discount_type']) {
                    $errors[] = __('you need to set up coupon settings', 'more-better-reviews-for-woocommerce');
                } elseif (empty($data["coupon_description"])) {
                    $errors[] = __('you need to set up coupon description', 'more-better-reviews-for-woocommerce');
                }
            }
        }


        if (!empty($errors)) {
            return array(
                'errors' => $errors
            );
        }


        delete_option( 'wtsr_thankyou_settings' );
        update_option('wtsr_thankyou_settings', $data);

        return array(
            'success' => 1
        );
    }

    public static function get_thankyou_settings() {
        $wtsr_thankyou_settings = get_option('wtsr_thankyou_settings', array());
        $default_thankyou_settings = Wtsr_Settings::get_default_thankyou_settings();

        return array_merge($default_thankyou_settings, $wtsr_thankyou_settings);
    }

    public static function get_default_thankyou_settings() {
        return array(
            'thankyou_enabled' => 'no',
            'thankyou_template' => '',
            'discount_type' => 'none',
            'coupon_amount' => '',
            'coupon_expiration' => '',
            'coupon_description' => '',
            'coupon_countdown' => 'no',
            'coupon_countdown_period' => '',
            'coupon_countdown_period_reset' => '',
            'coupon_countdown_description' => __('Leave your product review within next {coupon_countdown_period} hours and get discount coupon', 'more-better-reviews-for-woocommerce'),
        );
    }

    public static function get_want_to_buy_license_link() {
        $site_lang = get_user_locale();

        $de_lang_list = array(
            'de_CH_informal',
            'de_DE_formal',
            'de_AT',
            'de_CH',
            'de_DE'
        );

        if (in_array($site_lang, $de_lang_list)) {
            return "https://wp2leads-for-klick-tipp.com/web/better-reviews-for-woocommerce#ts-preis";
        }

        return "https://wp2leads-for-klick-tipp.com/web/better-reviews-for-woocommerce-price-list-eng/";
    }

    public static function is_pixel_enabled() {
        $thankyou_settings = Wtsr_Settings::get_thankyou_settings();

        if (
            'no' === $thankyou_settings['thankyou_enabled'] ||
            'none' === $thankyou_settings['discount_type'] ||
            '' === $thankyou_settings['coupon_amount'] ||
            'no' === $thankyou_settings['coupon_countdown'] ||
            '' === $thankyou_settings['coupon_countdown_period']
        ) {
            return false;
        }

        return $thankyou_settings['coupon_countdown_period'];
    }

    public static function get_review_meta() {
        $thankyou_settings = Wtsr_Settings::get_thankyou_settings();

        if (
            'no' === $thankyou_settings['thankyou_enabled'] ||
            'none' === $thankyou_settings['discount_type'] ||
            '' === $thankyou_settings['coupon_amount'] ||
            'no' === $thankyou_settings['coupon_countdown'] ||
            '' === $thankyou_settings['coupon_countdown_period']
        ) {
            return false;
        }

        return array(
            'discount_type' => $thankyou_settings['discount_type'],
            'coupon_amount' => $thankyou_settings['coupon_amount'],
            'coupon_countdown_period' => $thankyou_settings['coupon_countdown_period'],
        );
    }

    public static function validate_integration_credentials($data) {
        $result = array(
            'success' => 1,
            'error_array' => array(),
        );

        $is_ts_cred = false;

        if (
            !empty($data["wtsr_ts_mode_enabled"]) ||
            !empty($data['wtsr_ts_id']) ||
            !empty($data['wtsr_ts_email']) ||
            !empty($data['wtsr_ts_password'])
        ) {
            $is_ts_cred = true;
        }

        $error_array = array();

        if ($is_ts_cred) {
            if (empty($data['wtsr_ts_id'])) {
                $error_array[] = __('Trusted Shops ID is required.', 'more-better-reviews-for-woocommerce');
            }

            if (empty($data['wtsr_ts_email'])) {
                $error_array[] = __('Trusted Shops Email is required.', 'more-better-reviews-for-woocommerce');
            } else {
                if (!is_email($data['wtsr_ts_email'])) {
                    $error_array[] = __('Trusted Shops Email is not valid.', 'more-better-reviews-for-woocommerce');
                }
            }

            if (empty($data['wtsr_ts_password'])) {
                $error_array[] = __('Trusted Shops Password is required.', 'more-better-reviews-for-woocommerce');
            }
        }

        if (!empty($error_array)) {
            $result = array(
                'success' => 0,
                'error_array' => $error_array,
            );
        }

        return $result;
    }

    public static function update_integration_credentials($data) {
        // Trustshops credentials
        $is_ts_cred_updated = false;
        $old_ts_cred = TSManager::get_ts_credentials();

        $is_ts_id_updated = !empty($old_ts_cred['ts_id']) && $old_ts_cred['ts_id'] !== $data['wtsr_ts_id'];
        if ($is_ts_id_updated) $is_ts_cred_updated = true;

        if (!$is_ts_cred_updated) {
            $is_ts_email_updated = !empty($old_ts_cred['ts_email']) && $old_ts_cred['ts_email'] !== $data['wtsr_ts_email'];
            if ($is_ts_email_updated) $is_ts_cred_updated = true;
        }

        if (!$is_ts_cred_updated) {
            $is_ts_password_updated = !empty($old_ts_cred['ts_password']) && $old_ts_cred['ts_password'] !== $data['wtsr_ts_password'];
            if ($is_ts_password_updated) $is_ts_cred_updated = true;
        }

        update_option('wtsr_ts_id', $data['wtsr_ts_id']);
        update_option('wtsr_ts_email', $data['wtsr_ts_email']);
        update_option('wtsr_ts_password', $data['wtsr_ts_password']);

        if (!empty($data["wtsr_ts_mode_enabled"])) {
            TSManager::enable_review_mode();
        } else {
            TSManager::disable_review_mode();
        }

        if ($is_ts_cred_updated) {
            // Trusted shops refresh
            delete_transient('wtsr_ts_credential_set');

            delete_option('wtsr_ts_credential_empty_set');
            delete_option('wtsr_ts_domain_credentials_set');

            delete_option('wtsr_check_ts_credentials_empty');
            delete_option('wtsr_check_ts_credentials');
            delete_option('wtsr_check_ts_domain_credentials');

            delete_transient('wtsr_dismiss_check_ts_domain_credentials_warning');
            delete_transient('wtsr_dismiss_check_ts_woocommerce_only_warning');
        }
    }
}