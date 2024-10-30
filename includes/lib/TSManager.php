<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/includes
 */

/**
 * @since      0.0.1
 * @package    Wtsr
 * @subpackage Wtsr/includes/lib
 * @author     Tobias Conrad <tc@santegra.de>
 */
class TSManager {
    public static function get_wtsr_options_labels() {
        return array(
//            'wtsr_transferred_',
            'wtsr_wizard_step',
//            'wtsr_limit_days',
//            'wtsr_limit_message',
//            'wtsr_limit_users',
            'wtsr_license',
            'wtsr_ts_review_request_enabled',
            'wtsr_review_period',
            'wtsr_filter_email_domain',
            'wtsr_review_ask',
            'wtsr_review_ask_template_editor',
            'wtsr_review_approved',
            'wtsr_order_status',
            'wtsr_review_variations',
            'wtsr_email_template',
            'wtsr_rating_links',
            'wtsr_image_size',
            'wtsr_ts_review_request_enabled',
            'wtsr_button_text_color',
            'wtsr_button_bg_color',
            'wtsr_all_reviews_page',
            'wtsr_uploaded_image',
            'wtsr_all_reviews_page_footer_template_editor',
            'wtsr_all_reviews_page_product_link',
            'wtsr_all_reviews_page_description',
            'wtsr_normal_color',
            'wtsr_hover_color',
            'wtsr_normal_button_txt_color',
            'wtsr_normal_button_bg_color',
            'wtsr_hover_button_bg_color',
            'wtsr_hover_button_txt_color',
//            'wtsr_woocommerce_only_mode_enabled',
            'wtsr_all_reviews_page_reviews_title',
            'wtsr_all_reviews_page_comment_placeholder',
            'wtsr_all_reviews_page_reviews_min',
            'wtsr_email_send_via_woocommerce_delay',
            'wtsr_email_send_via',
            'wtsr_thankyou_settings',
//            '_transient_wtsr_limit_set',
//            '_transient_timeout_wtsr_limit_set',
//            'wtsr_limit_days',
//            'wtsr_limit_message',
//            'wtsr_limit_users',
        );
    }

    public static function is_star_rating_type_exists($rating_link_type) {
        $is_woocommerce_only_mode = ReviewServiceManager::is_woocommerce_only_mode();
        $is_woocommerce_reviews_disabled_globally = TSManager::is_woocommerce_reviews_disabled_globally();
        $default_rating_links = ReviewsModel::get_default_rating_links();
        $wtsr_rating_links = get_option('wtsr_rating_links', $default_rating_links);

        foreach ($wtsr_rating_links as $star => $rating_link) {
            if ($is_woocommerce_only_mode && 'wtsr_ts_review_link' === $rating_link) {
                $rating_link = 'wtsr_product_url';
            }

            if ($is_woocommerce_reviews_disabled_globally && 'wtsr_product_url' === $rating_link) {
                $rating_link = 'wtsr_custom_link';
            }

            if ($rating_link_type === $rating_link) {
                return true;
            }
        }

        return false;
    }

    public static function check_ts_domain_credentials($ts_id = '', $ts_email = '', $ts_password = '') {
        $ts_credentials = self::get_ts_credentials();

        if (empty($ts_credentials['ts_id'])) {
            return false;
        }

        if (empty($ts_credentials['ts_email'])) {
            return false;
        }

        if (empty($ts_credentials['ts_password'])) {
            return false;
        }

        $api = new TSApi('restricted', $ts_id, $ts_email, $ts_password);
        $review_request = $api->get_shop_reviews();

        if (is_wp_error($review_request)) {
            return false;
        }

        $is_multisite_mode = defined( 'WTSR_MULTISITE_MODE' ) && WTSR_MULTISITE_MODE;

        if (!$is_multisite_mode) {
            $error = array();
            // TODO: Check if site is correct
            $site = Wtsr_License::get_current_site();
            // $site = 'barf-alarm.de';
            $shop_site = !empty($review_request["data"]["shop"]["url"]) ? $review_request["data"]["shop"]["url"] : '';

            if (!empty($shop_site)) {
                $shop_site = preg_replace('/^http:\/\//i', "", $shop_site );
                $shop_site = preg_replace('/^https:\/\//i', "", $shop_site );
                $shop_site = preg_replace('/^www./i', "", $shop_site );
            }

            if (false === strpos($shop_site, $site)) {
                $error[] = __('Trusted Shops credentials is not for this site.', 'more-better-reviews-for-woocommerce') . ' ' .  __('Maybe you are only testing on this site, than it is okay.', 'more-better-reviews-for-woocommerce');
            }

            if (!empty($error)) {
                $message = implode(', ', $error);

                return $message;
            }
        }

        return false;
    }

    public static function check_ts_credentials($ts_id = '', $ts_email = '', $ts_password = '') {
        $error = array();

        if (!empty($error)) {
            $message = implode(', ', $error);

            return $message;
        }

        $api = new TSApi('restricted', $ts_id, $ts_email, $ts_password);
        $review_request = $api->get_shop_reviews();

        if (is_wp_error($review_request)) {
            $error = array();

            $message = $review_request->get_error_message();
            $code = $review_request->get_error_code();

            if ('ts_id_invalid' === strtolower($message)) {
                $error[] = __('Trusted Shops ID is invalid', 'more-better-reviews-for-woocommerce');
            } elseif ('unauthenticated' === strtolower($message)) {
                $error[] = __('Trusted Shops API email or/and password is invalid', 'more-better-reviews-for-woocommerce');
            }

            if (!empty($error)) {
                $message = implode(', ', $error);

                return $message;
            }
        }

        return false;
    }

    public static function enable_review_mode() {
        update_option('wtsr_ts_mode_enabled', 'on');
        ReviewServiceManager::deactivate_woocommerce_only_mode();
    }

    public static function disable_review_mode() {
        delete_option('wtsr_ts_mode_enabled');
        ReviewServiceManager::activate_woocommerce_only_mode();
    }

    public static function is_review_mode_enabled() {
        $is_enabled = get_option('wtsr_ts_mode_enabled', false);
        $is_empty = self::is_credentials_empty();
        $cred_error = get_option('wtsr_check_ts_credentials', false);

        return $is_enabled && !$is_empty && !$cred_error;
    }

    public static function is_email_valid($email) {
        $email = trim($email);
        $email_valid = filter_var($email, FILTER_VALIDATE_EMAIL);

        if (!$email_valid) {
            return false;
        }

        $email = $email_valid;

        $is_amazon = strpos($email, 'amazon');

        if (false !== $is_amazon) {
            return false;
        }

        $is_ebay = strpos($email, 'ebay');

        if (false !== $is_ebay) {
            return false;
        }

        $filter_emails = get_option('wtsr_filter_email_domain', '');

        if (empty($filter_emails) || !is_array($filter_emails)) {
            return true;
        }

        foreach ($filter_emails as $filter_email) {
            $filtered = strpos($email, $filter_email);

            if (false !== $filtered) {
                return false;
            }
        }

        return true;
    }

    public static function is_review_allowed($email, $order_id, $manual) {
        $is_review_allowed = get_post_meta( $order_id, 'Trusted Shops Review', true );
        $selected_review_ask = get_option('wtsr_review_ask', 'no');

        if (empty($is_review_allowed) && 'yes' === $selected_review_ask && !$manual) {
            return false;
        }

        // Check if email already in our DB
        $existed_email = ReviewsModel::get_last_by_email($email);

        if (empty($existed_email)) {
            return true;
        }

        $outdated = self::maybe_review_outdated($existed_email['id']);

        if (!$outdated) {
            return false;
        }

        return true;
    }

    public static function is_limit_allowed() {
        $license_version = Wtsr_License::get_license_version();

        if ('pro' === $license_version) {
            return true;
        }

        $user_left = TSManager::get_limit_allowed();

        if (0 > $user_left || 0 === $user_left) {
            return false;
        }

        return true;
    }

    public static function get_limit_allowed() {
        global $wpdb;
        $limit_sql = "SELECT option_value FROM {$wpdb->options} WHERE option_name IN ('wtsr_limit_users')";
        $count_sql = "SELECT option_value FROM {$wpdb->options} WHERE option_name IN ('wtsr_limit_count')";

        $user_limit = $wpdb->get_var( $limit_sql );
        $user_count = $wpdb->get_var( $count_sql );

        if (empty($user_limit)) {
            $user_limit = 5;
        }

        if (empty($user_count)) {
            $user_count = 0;
        }

        $user_limit = $user_limit * Wtsr_License::get_license_multi();

        $user_left = (int) $user_limit - (int) $user_count;

        return $user_left;
    }

    // TODO - deprecated
    public static function check_ts_credentials_empty() {
        return self::is_credentials_empty();
    }

    public static function is_credentials_empty() {
        extract(self::get_ts_credentials());
        return empty($ts_id) || empty($ts_email) || empty($ts_password);
    }

    public static function get_ts_credentials() {
        $ts_id = get_option('wtsr_ts_id', '');
        $ts_email = get_option('wtsr_ts_email', '');
        $ts_password = get_option('wtsr_ts_password', '');
        $ts_mode_enabled = get_option('wtsr_ts_mode_enabled', false);

        return array(
            'ts_id' => $ts_id,
            'ts_email' => $ts_email,
            'ts_password' => $ts_password,
            'ts_mode_enabled' => $ts_mode_enabled,
        );
    }

    // TODO - deprecated moved to ReviewServiceManager
    public static function is_woocommerce_only_mode() {
        return ReviewServiceManager::is_woocommerce_only_mode();
    }

    public static function is_woocommerce_email_only_mode() {
        return TSManager::get_email_send_via() === 'woocommerce';
    }

    public static function is_woocommerce_reviews_disabled_globally() {
        return 'yes' !== get_option( 'woocommerce_enable_reviews' );
    }

    public static function is_woocommerce_reviews_disabled_per_product($all = false) {
        if (self::is_woocommerce_reviews_disabled_globally()) {
            return false;
        }

        global $wpdb;

        if (!$all) {
            $sql = "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'product' AND comment_status NOT IN ('open');";
        } else {
            $sql = "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'product';";
        }
        $results = $wpdb->get_results( $sql, ARRAY_A );

        return $results;
    }

    public static function increment_reviews_limit_counter() {
        $license_version = Wtsr_License::get_license_version();

        if ('pro' === $license_version) {
            return true;
        }

        global $wpdb;

        $user_count_sql = "SELECT option_value FROM {$wpdb->options} WHERE option_name IN ('wtsr_limit_count')";
        $user_count = $wpdb->get_var( $user_count_sql );
        // $user_count = get_option('wtsr_limit_count', 0);

        $limit_count_timeout_sql = "SELECT option_value FROM {$wpdb->options} WHERE option_name IN ('wtsr_limit_count_timeout')";
        $limit_count_timeout = $wpdb->get_var( $limit_count_timeout_sql );
        // $limit_count_timeout = get_option('wtsr_limit_count_timeout');

        $limit_days_sql = "SELECT option_value FROM {$wpdb->options} WHERE option_name IN ('wtsr_limit_days')";
        $limit_days = $wpdb->get_var( $limit_days_sql );
        // $limit_days = get_option('wtsr_limit_days');

        if (empty($user_count) || empty($limit_count_timeout)) {
            $timeout = time() + 60 * 60 * 24 * $limit_days;
            $delete = $wpdb->delete( $wpdb->options, array('option_name' => 'wtsr_limit_count_timeout') );

            $insert = $wpdb->insert( $wpdb->options,
                array(
                    'option_name' => 'wtsr_limit_count_timeout',
                    'option_value' => $timeout,
                ),
                array(
                    '%s', '%d'
                )
            );
            // update_option('wtsr_limit_count_timeout', $timeout);
        }

        $user_count = (int) $user_count + 1;

        $delete = $wpdb->delete( $wpdb->options, array('option_name' => 'wtsr_limit_count') );

        $insert = $wpdb->insert( $wpdb->options,
            array(
                'option_name' => 'wtsr_limit_count',
                'option_value' => $user_count,
            ),
            array(
                '%s', '%d'
            )
        );

        // update_option('wtsr_limit_count', $user_count);

        return true;
    }

    public static function reset_reviews_limit_counter() {
        $limit_count_timeout = get_option('wtsr_limit_count_timeout');

        if (!$limit_count_timeout) {
            return true;
        }

        if (time() > (int) $limit_count_timeout) {
            delete_option('wtsr_limit_count');
            delete_option('wtsr_limit_count_timeout');
        }

        return true;
    }

    public static function remove_reviews_limit_counter() {
        delete_option('wtsr_limit_count');
        delete_option('wtsr_limit_count_timeout');

        return true;
    }

    public static function set_reviews_limitation() {
        $limit_users = get_option('wtsr_limit_users');
        $limit_message = get_option('wtsr_limit_message');
        $limit_days = get_option('wtsr_limit_days');

        if ($limit_users && $limit_message && $limit_days) {
            return true;
        }

        $response = wp_remote_get("https://www.klick-tipp.com/api/split/1dg2z7uaz1fzkz58a6?ip=".$_SERVER["REMOTE_ADDR"]."&cookie=".(isset($_COOKIE["KTSTC50Z59362"]) ? $_COOKIE["KTSTC50Z59362"] : -1)."");

        if (is_wp_error($response) || wp_remote_retrieve_response_code( $response ) !== 200) {
            return false;
        }

        $body = sanitize_text_field(wp_remote_retrieve_body( $response ));

        $settings_array = explode(' ', $body);

        update_option('wtsr_limit_users', $settings_array[0]);
        update_option('wtsr_limit_message', $settings_array[1]);
        update_option('wtsr_limit_days', $settings_array[2]);

        return true;
    }

    public static function prepare_response($response, $content_type) {
        $response = trim($response);
        $content_type = strtolower($content_type);

        if (false !== strpos($content_type, 'text/html')) {
            return new WP_Error( 'tsapi_authentication_error', 'UNAUTHENTICATED', array( 'status' => 401 ) );
        }

        if (false !== strpos($content_type, 'application/xml')) {
            return self::prepare_xml_response($response);
        }

        if (false !== strpos($content_type, 'application/json')) {
            return self::prepare_json_response($response);
        }

        return $response;
    }

    public static function prepare_xml_response($response) {
        $xml = simplexml_load_string($response, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $decoded = json_decode($json, true);

        $code = $decoded['code'];
        $message = $decoded['message'];
        $status = $decoded['status'];

        if ('fail' === strtolower($status) || 'error' === strtolower($status)) {
            return new WP_Error( 'tsapi_error', $message, array( 'status' => $code ) );
        }

        $response_array = array (
            'code' => $code,
            'message' => $message,
            'status' => $status,
        );

        if (!empty($decoded['data'])) {
            $response_array['data'] = $decoded['data'];
        }

        // return $decoded;
        return $response_array;
        return $code;
    }

    public static function prepare_json_response($response) {
        $decoded = json_decode($response, true);

        if (!empty($decoded["response"])) {
            $response_code = !empty($decoded["response"]["code"]) ? $decoded["response"]["code"] : 400;

            if (200 != $response_code) {
                $message = !empty($decoded["response"]["message"]) ? $decoded["response"]["message"] : 'UNAUTHENTICATED';
                return new WP_Error( 'tsapi_authentication_error', $message, array( 'status' => $response_code ) );
            } else {
                return $decoded["response"];
            }
        }

        return false;
    }

    public static function get_ts_link_new_order($order_id, $order_data, $order) {
        self::create_new_review_item_bg($order_id);
    }

    public static function get_ts_link_order_status_changed($order_id, $from, $to, $order_data) {
        $selected_order_status = get_option('wtsr_order_status', 'wc-completed');

        if ('wc-' . $to === $selected_order_status) {
            self::create_new_review_item_bg($order_id);
        }
    }

    public static function create_new_review_item_bg($order_id) {
        $is_ts_review_request_enabled = get_option('wtsr_ts_review_request_enabled');
        $is_wizard = Wtsr_Wizard::is_wizard();

        if (empty($is_ts_review_request_enabled) && !$is_wizard) {
            return;
        }
        $result = Wtsr_Background_Review_Request::bg_process($order_id);
    }

    public static function create_new_review_item($order_id, $manual = true) {
        $is_ts_review_request_enabled = get_option('wtsr_ts_review_request_enabled');
        $is_wizard = Wtsr_Wizard::is_wizard();

        if (empty($is_ts_review_request_enabled) && empty($manual) && !$is_wizard) {
            return false;
        }

        $is_test_mode = defined( 'WTSR_TEST_MODE' ) && WTSR_TEST_MODE;
        $real_transfer_mode = defined( 'WTSR_REAL_TRANSFER_MODE' ) && WTSR_REAL_TRANSFER_MODE;

        $order = wc_get_order( $order_id );

        if (!$order) {
            return false;
        }

        $order_data = $order->get_data();

        $order_email = !empty($order_data['billing']['email']) ? $order_data['billing']['email'] : false;

        if (!$order_email || !self::is_email_valid($order_email) || !self::is_review_allowed($order_email, $order_id, $manual)) {
            return false;
        }

        $order_created_via = TSManager::get_created_via($order);

        $is_amazon = strpos($order_created_via, 'amazon');

        if (false !== $is_amazon) {
            return false;
        }

        $is_ebay = strpos($order_created_via, 'ebay');

        if (false !== $is_ebay) {
            return false;
        }

        $is_on_checkout = $order_created_via === 'checkout';

        if (!$is_on_checkout) {
            return false;
        }

        $is_dummy = false;

        if ($is_wizard) {
            $is_dummy = self::is_order_dummy($order_id);
        }

        if (!self::is_limit_allowed() && empty($is_dummy)) {
            return false;
        }

        if ($is_test_mode) {
            $now = time() - ( WTSR_TEST_MODE_PERIOD * 24 * 60 * 60 );
        } else {
            $now = time();
        }

        $created = gmdate( 'Y-m-d H:i:s', $now );

        $data = array(
            'order_id' => $order_id,
            'email' => $order_email,
            'status' => 'pending',
            'review_created' => $created,
        );

        $review_id = ReviewsModel::create($data);
        $is_ts_mode = TSManager::is_review_mode_enabled();
        $is_woocommerce_only_mode = !$is_ts_mode;
        $is_woocommerce_email_only_mode = TSManager::is_woocommerce_email_only_mode();

        if ($is_woocommerce_only_mode) {
            $ts_body = true;
        } else {
            if ($is_ts_mode) {
                $ts_body = ReviewServiceManager::get_api_review_request_body($order_id);
            }
        }

        if ( $ts_body ) {
            if ($is_woocommerce_only_mode) {
                $review_link = '';

                $review_request = array(
                    'data' => array(
                        'reviewRequest' => array(
                            'link' => true
                        )
                    )
                );
            } else {
                if ($is_test_mode) {
                    $uuid = rand(10000000, 99999999);

                    $review_request = array(
                        'data' => array(
                            'reviewRequest' => array(
                                'link' => 'https://www.trustedshops.loc/reviews/evaluate_product?reviewrequest_uuid='. $uuid .'-f3e3-4629-9cbf-396e7dd13416&shop_id=XDCA254DEFA014E61F035DF1412E17FDD'
                            )
                        )
                    );
                } else {
                    if ($is_ts_mode) {
                        $api = new TSApi('restricted');
                        $review_request = $api->get_review_request($ts_body);
                        $review_link = !is_wp_error($review_request) && !empty($review_request['data']['reviewRequest']['link']) && true !== $review_request['data']['reviewRequest']['link'] ? $review_request['data']['reviewRequest']['link'] : false;
                    }
                }
            }

            if ( $is_woocommerce_only_mode || ( !empty($review_link) ) ) {
                $review_link = $review_link ? $review_link : '';
                $review_message = Wtsr_Template::get_review_request_html( $order_id );
                $data['status'] = 'ready';
                $data['review_link'] = $review_link;
                $data['review_message'] = $review_message;

                if (empty($is_dummy)) self::increment_reviews_limit_counter();

                if ($is_test_mode && !$real_transfer_mode) {
                    $data['status'] = 'transferred';
                    $data['review_sent'] = $created;
                    $order_items = TSManager::get_order_items($order);

                    $user_products = get_option('wtsr_transferred_' . $order_email, array());

                    foreach ($order_items as $order_item) {
                        $user_products[] = $order_item['sku'];
                    }

                    update_option('wtsr_transferred_' . $order_email, $user_products, false);
                }

                ReviewsModel::update_meta($review_id, 'trustedshops_review_link', $review_link);
            }
        }

        $data['id'] = $review_id;

        $review_id = ReviewsModel::update($data);
        $review_meta = Wtsr_Settings::get_review_meta();

        if (!empty($review_meta)) {
            foreach ($review_meta as $key => $value) {
                ReviewsModel::update_meta($review_id, $key, $value);
            }
        }

        if ($is_woocommerce_email_only_mode) {
            do_action('wtsr_new_woo_review_item_created', $review_id, $order_id);
        } else {
            do_action('wtsr_new_review_item_created', $review_id, $order_id);
        }

        return $review_id;
    }

    public static function get_default_email_template_editor() {
        ob_start();
        ?><p>{order_items_table_one_col}</p><?php
        return ob_get_clean();
    }

    public static function get_default_review_ask_template_editor() {
        ob_start();
        ?><p><?php echo __('I want to give review', 'more-better-reviews-for-woocommerce'); ?></p><?php
        return ob_get_clean();
    }

    public static function get_max_email_template_length() {
        return apply_filters('wtsr_max_email_template_length', 46000);
    }

    public static function get_array_created_via($order) {
        if (!isset($order['_created_via']) || !is_string($order['_created_via'])) return 'checkout';
        if (strpos($order['_created_via'], 'amazon')) return 'amazon';
        if (strpos($order['_created_via'], 'ebay')) return 'ebay';
        return 'checkout';
    }

    public static function get_created_via($order) {
        $order_created_via = $order->get_created_via();

        if (strpos($order_created_via, 'amazon')) {
            return 'amazon';
        }

        if (strpos($order_created_via, 'ebay')) {
            return 'ebay';
        }

        return 'checkout';
    }

    public static function get_order_items($order, $both = false) {
        return ReviewServiceManager::get_order_items($order, $both);
    }

    public static function get_star_review_title() {
        return array(
            'one_star' => __('Poor', 'more-better-reviews-for-woocommerce'),
            'two_star' => __('Fair', 'more-better-reviews-for-woocommerce'),
            'three_star' => __('Good', 'more-better-reviews-for-woocommerce'),
            'four_star' => __('Very good', 'more-better-reviews-for-woocommerce'),
            'five_star' => __('Excellent', 'more-better-reviews-for-woocommerce'),
        );
    }

    public static function get_default_email_template() {
        $wtsr_email_template_editor = get_option('wtsr_email_template', TSManager::get_default_email_template_editor());
        $wtsr_image_size = self::get_default_image_size();

        if (false !== strpos($wtsr_email_template_editor, '{single_review}')) {
            $single_review = self::get_default_single_review_template();

            $wtsr_email_template_editor = str_replace( '{single_review}', $single_review, $wtsr_email_template_editor );
        }

        if (false !== strpos($wtsr_email_template_editor, '{order_items_table}')) {
            if ($wtsr_image_size !== 'no_image_template') {
                $order_items_table = self::get_default_order_items_table_template();
            } else {
                $order_items_table = self::get_default_order_items_table_one_col_template();
            }

            $wtsr_email_template_editor = str_replace( '{order_items_table}', $order_items_table, $wtsr_email_template_editor );
        }

        if (false !== strpos($wtsr_email_template_editor, '{order_items_table_one_col}')) {
            $order_items_table = self::get_default_order_items_table_one_col_template();

            $wtsr_email_template_editor = str_replace( '{order_items_table_one_col}', $order_items_table, $wtsr_email_template_editor );
        }

        if (false !== strpos($wtsr_email_template_editor, '{order_number}')) {
            $wtsr_email_template_editor = str_replace( '{order_number}', 4587, $wtsr_email_template_editor );
        }

        if (false !== strpos($wtsr_email_template_editor, '{customer_fn}')) {
            $wtsr_email_template_editor = str_replace( '{customer_fn}', __('John', 'more-better-reviews-for-woocommerce'), $wtsr_email_template_editor );
        }

        if (false !== strpos($wtsr_email_template_editor, '{customer_ln}')) {
            $wtsr_email_template_editor = str_replace( '{customer_ln}', __('Smith', 'more-better-reviews-for-woocommerce'), $wtsr_email_template_editor );
        }

        if (false !== strpos($wtsr_email_template_editor, '{order_date}')) {
            $wtsr_email_template_editor = str_replace( '{order_date}', date(get_option( 'date_format' ), time() - 30000), $wtsr_email_template_editor );
        }

        if (false !== strpos($wtsr_email_template_editor, '{coupon_countdown_description}')) {
            $coupon_countdown_description = Wtsr_Template::get_coupon_countdown_description();

            $wtsr_email_template_editor = str_replace( '{coupon_countdown_description}', $coupon_countdown_description, $wtsr_email_template_editor );
        }

        return $wtsr_email_template_editor;
    }

    public static function get_default_single_review_template() {
        if (!class_exists('Wtsr_Default_Templates')) {
            require_once 'Wtsr_Default_Templates.php';
        }
        ob_start();
        ?>
        <div class="items_table" style="max-width:800px;"><div style="padding:5px 0 25px 0;"><?php echo Wtsr_Default_Templates::get_default_stars('single'); ?></div></div><?php
        return ob_get_clean();
    }

    public static function get_default_order_items_table_one_col_template() {
        if (!class_exists('Wtsr_Default_Templates')) {
            require_once 'Wtsr_Default_Templates.php';
        }
        $wtsr_image_size = self::get_default_image_size();

        ob_start();
        ?>
        <div class="items_table">
            <?php
            if ('no_image_template' !== $wtsr_image_size) {
                ?>
                <div style="text-align:left;">
                    <img src="<?php echo wc_placeholder_img_src( $wtsr_image_size ) ?>" alt="" style="max-width:100%;border:1px solid #eee;">
                </div>
                <?php
            }
            ?>

            <div>
                <div style="padding:5px 10px;">
                    <h4 style="margin-top:5px; margin-bottom: 5px;">Product 1 Title</h4>

                    <p style="margin-top:5px; margin-bottom:5px;">Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.</p><div
                            style="padding:5px 0 25px 0;"><?php
                            echo Wtsr_Default_Templates::get_default_stars();
                        ?></div></div></div></div>

        <?php
        return ob_get_clean();
    }

    public static function get_default_order_items_table_template() {
        $wtsr_image_size = self::get_default_image_size();

        ob_start();
        if ('no_image_template' !== $wtsr_image_size) {
            ?>
            <div class="items_table_header">
            <?php
            if ('no_image_template' !== $wtsr_image_size) {
                ?>
                <div style="width:50%;float:left;">
                    <p style="text-align:center;font-weight:bold;"><?php _e('Image', 'more-better-reviews-for-woocommerce') ?></p>
                </div>
                <div style="width:50%;float:left;">
                <?php
            } else {
                ?>
                <div style="width:100%;float:left;">
                <?php
            }
            ?>
            <p style="text-align:center;font-weight:bold;"><?php _e('Description', 'more-better-reviews-for-woocommerce') ?></p>
            </div>
            <div style="clear:both;"></div>
            </div>
            <?php
        }
        ?>

        <div class="items_table">
            <?php
            if ('no_image_template' !== $wtsr_image_size) {
                ?>
                <div style="width:50%;float:left;text-align:center;">
                    <img src="<?php echo wc_placeholder_img_src( $wtsr_image_size ) ?>" alt="" style="max-width:100%;border:1px solid #eee;">
                </div>

                <div style="width:50%;float:left;">
                <?php
            } else {
                ?>
                <div style="width:100%;float:left;">
                <?php
            }
            ?>

                <div style="padding:5px 10px;">
                    <?php
                    if ('no_image_template' === $wtsr_image_size) {
                        ?><h4 style="margin-top:5px; margin-bottom: 5px;text-align: center;">Product 1 Title</h4><?php
                    } else {
                        ?><h4 style="margin-top:5px; margin-bottom: 5px;">Product 1 Title</h4><?php
                    }
                    ?>

                    <p style="margin-top:5px; margin-bottom:5px;">Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.</p>

                    <div style="padding:5px 10px 25px 10px;"><?php echo Wtsr_Default_Templates::get_default_stars('two_col'); ?></div>
                </div>
            </div>

            <div style="clear:both;"></div>
        </div>

        <?php
        return ob_get_clean();
    }

    public static function remove_emoji($string) {

        // Match Emoticons
        $regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clear_string = preg_replace($regex_emoticons, '', $string);

        // Match Miscellaneous Symbols and Pictographs
        $regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clear_string = preg_replace($regex_symbols, '', $clear_string);

        // Match Transport And Map Symbols
        $regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clear_string = preg_replace($regex_transport, '', $clear_string);

        // Match Miscellaneous Symbols
        $regex_misc = '/[\x{2600}-\x{26FF}]/u';
        $clear_string = preg_replace($regex_misc, '', $clear_string);

        // Match Dingbats
        $regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
        $clear_string = preg_replace($regex_dingbats, '', $clear_string);

        return $clear_string;
    }

    public static function remove_wp_emoji( $content ) {
        $emoji  = TSManager::_emoji_list();
        $compat = version_compare( phpversion(), '5.4', '<' );

        foreach ( $emoji as $emojum ) {
            if ( $compat ) {
                $emoji_char = html_entity_decode( $emojum, ENT_COMPAT, 'UTF-8' );
            } else {
                $emoji_char = html_entity_decode( $emojum );
            }
            if ( false !== strpos( $content, $emoji_char ) ) {
                $content = preg_replace( "/$emoji_char/", '', $content );
            }
        }

        return $content;
    }

    private static function _emoji_list() {
        $partials = array( '&#x1f004;', '&#x1f0cf;', '&#x1f170;', '&#x1f171;', '&#x1f17e;', '&#x1f17f;', '&#x1f18e;', '&#x1f191;', '&#x1f192;', '&#x1f193;', '&#x1f194;', '&#x1f195;', '&#x1f196;', '&#x1f197;', '&#x1f198;', '&#x1f199;', '&#x1f19a;', '&#x1f1e6;', '&#x1f1e8;', '&#x1f1e9;', '&#x1f1ea;', '&#x1f1eb;', '&#x1f1ec;', '&#x1f1ee;', '&#x1f1f1;', '&#x1f1f2;', '&#x1f1f4;', '&#x1f1f6;', '&#x1f1f7;', '&#x1f1f8;', '&#x1f1f9;', '&#x1f1fa;', '&#x1f1fc;', '&#x1f1fd;', '&#x1f1ff;', '&#x1f1e7;', '&#x1f1ed;', '&#x1f1ef;', '&#x1f1f3;', '&#x1f1fb;', '&#x1f1fe;', '&#x1f1f0;', '&#x1f1f5;', '&#x1f201;', '&#x1f202;', '&#x1f21a;', '&#x1f22f;', '&#x1f232;', '&#x1f233;', '&#x1f234;', '&#x1f235;', '&#x1f236;', '&#x1f237;', '&#x1f238;', '&#x1f239;', '&#x1f23a;', '&#x1f250;', '&#x1f251;', '&#x1f300;', '&#x1f301;', '&#x1f302;', '&#x1f303;', '&#x1f304;', '&#x1f305;', '&#x1f306;', '&#x1f307;', '&#x1f308;', '&#x1f309;', '&#x1f30a;', '&#x1f30b;', '&#x1f30c;', '&#x1f30d;', '&#x1f30e;', '&#x1f30f;', '&#x1f310;', '&#x1f311;', '&#x1f312;', '&#x1f313;', '&#x1f314;', '&#x1f315;', '&#x1f316;', '&#x1f317;', '&#x1f318;', '&#x1f319;', '&#x1f31a;', '&#x1f31b;', '&#x1f31c;', '&#x1f31d;', '&#x1f31e;', '&#x1f31f;', '&#x1f320;', '&#x1f321;', '&#x1f324;', '&#x1f325;', '&#x1f326;', '&#x1f327;', '&#x1f328;', '&#x1f329;', '&#x1f32a;', '&#x1f32b;', '&#x1f32c;', '&#x1f32d;', '&#x1f32e;', '&#x1f32f;', '&#x1f330;', '&#x1f331;', '&#x1f332;', '&#x1f333;', '&#x1f334;', '&#x1f335;', '&#x1f336;', '&#x1f337;', '&#x1f338;', '&#x1f339;', '&#x1f33a;', '&#x1f33b;', '&#x1f33c;', '&#x1f33d;', '&#x1f33e;', '&#x1f33f;', '&#x1f340;', '&#x1f341;', '&#x1f342;', '&#x1f343;', '&#x1f344;', '&#x1f345;', '&#x1f346;', '&#x1f347;', '&#x1f348;', '&#x1f349;', '&#x1f34a;', '&#x1f34b;', '&#x1f34c;', '&#x1f34d;', '&#x1f34e;', '&#x1f34f;', '&#x1f350;', '&#x1f351;', '&#x1f352;', '&#x1f353;', '&#x1f354;', '&#x1f355;', '&#x1f356;', '&#x1f357;', '&#x1f358;', '&#x1f359;', '&#x1f35a;', '&#x1f35b;', '&#x1f35c;', '&#x1f35d;', '&#x1f35e;', '&#x1f35f;', '&#x1f360;', '&#x1f361;', '&#x1f362;', '&#x1f363;', '&#x1f364;', '&#x1f365;', '&#x1f366;', '&#x1f367;', '&#x1f368;', '&#x1f369;', '&#x1f36a;', '&#x1f36b;', '&#x1f36c;', '&#x1f36d;', '&#x1f36e;', '&#x1f36f;', '&#x1f370;', '&#x1f371;', '&#x1f372;', '&#x1f373;', '&#x1f374;', '&#x1f375;', '&#x1f376;', '&#x1f377;', '&#x1f378;', '&#x1f379;', '&#x1f37a;', '&#x1f37b;', '&#x1f37c;', '&#x1f37d;', '&#x1f37e;', '&#x1f37f;', '&#x1f380;', '&#x1f381;', '&#x1f382;', '&#x1f383;', '&#x1f384;', '&#x1f385;', '&#x1f3fb;', '&#x1f3fc;', '&#x1f3fd;', '&#x1f3fe;', '&#x1f3ff;', '&#x1f386;', '&#x1f387;', '&#x1f388;', '&#x1f389;', '&#x1f38a;', '&#x1f38b;', '&#x1f38c;', '&#x1f38d;', '&#x1f38e;', '&#x1f38f;', '&#x1f390;', '&#x1f391;', '&#x1f392;', '&#x1f393;', '&#x1f396;', '&#x1f397;', '&#x1f399;', '&#x1f39a;', '&#x1f39b;', '&#x1f39e;', '&#x1f39f;', '&#x1f3a0;', '&#x1f3a1;', '&#x1f3a2;', '&#x1f3a3;', '&#x1f3a4;', '&#x1f3a5;', '&#x1f3a6;', '&#x1f3a7;', '&#x1f3a8;', '&#x1f3a9;', '&#x1f3aa;', '&#x1f3ab;', '&#x1f3ac;', '&#x1f3ad;', '&#x1f3ae;', '&#x1f3af;', '&#x1f3b0;', '&#x1f3b1;', '&#x1f3b2;', '&#x1f3b3;', '&#x1f3b4;', '&#x1f3b5;', '&#x1f3b6;', '&#x1f3b7;', '&#x1f3b8;', '&#x1f3b9;', '&#x1f3ba;', '&#x1f3bb;', '&#x1f3bc;', '&#x1f3bd;', '&#x1f3be;', '&#x1f3bf;', '&#x1f3c0;', '&#x1f3c1;', '&#x1f3c2;', '&#x1f3c3;', '&#x200d;', '&#x2640;', '&#xfe0f;', '&#x2642;', '&#x1f3c4;', '&#x1f3c5;', '&#x1f3c6;', '&#x1f3c7;', '&#x1f3c8;', '&#x1f3c9;', '&#x1f3ca;', '&#x1f3cb;', '&#x1f3cc;', '&#x1f3cd;', '&#x1f3ce;', '&#x1f3cf;', '&#x1f3d0;', '&#x1f3d1;', '&#x1f3d2;', '&#x1f3d3;', '&#x1f3d4;', '&#x1f3d5;', '&#x1f3d6;', '&#x1f3d7;', '&#x1f3d8;', '&#x1f3d9;', '&#x1f3da;', '&#x1f3db;', '&#x1f3dc;', '&#x1f3dd;', '&#x1f3de;', '&#x1f3df;', '&#x1f3e0;', '&#x1f3e1;', '&#x1f3e2;', '&#x1f3e3;', '&#x1f3e4;', '&#x1f3e5;', '&#x1f3e6;', '&#x1f3e7;', '&#x1f3e8;', '&#x1f3e9;', '&#x1f3ea;', '&#x1f3eb;', '&#x1f3ec;', '&#x1f3ed;', '&#x1f3ee;', '&#x1f3ef;', '&#x1f3f0;', '&#x1f3f3;', '&#x1f3f4;', '&#x2620;', '&#xe0067;', '&#xe0062;', '&#xe0065;', '&#xe006e;', '&#xe007f;', '&#xe0073;', '&#xe0063;', '&#xe0074;', '&#xe0077;', '&#xe006c;', '&#x1f3f5;', '&#x1f3f7;', '&#x1f3f8;', '&#x1f3f9;', '&#x1f3fa;', '&#x1f400;', '&#x1f401;', '&#x1f402;', '&#x1f403;', '&#x1f404;', '&#x1f405;', '&#x1f406;', '&#x1f407;', '&#x1f408;', '&#x1f409;', '&#x1f40a;', '&#x1f40b;', '&#x1f40c;', '&#x1f40d;', '&#x1f40e;', '&#x1f40f;', '&#x1f410;', '&#x1f411;', '&#x1f412;', '&#x1f413;', '&#x1f414;', '&#x1f415;', '&#x1f9ba;', '&#x1f416;', '&#x1f417;', '&#x1f418;', '&#x1f419;', '&#x1f41a;', '&#x1f41b;', '&#x1f41c;', '&#x1f41d;', '&#x1f41e;', '&#x1f41f;', '&#x1f420;', '&#x1f421;', '&#x1f422;', '&#x1f423;', '&#x1f424;', '&#x1f425;', '&#x1f426;', '&#x1f427;', '&#x1f428;', '&#x1f429;', '&#x1f42a;', '&#x1f42b;', '&#x1f42c;', '&#x1f42d;', '&#x1f42e;', '&#x1f42f;', '&#x1f430;', '&#x1f431;', '&#x1f432;', '&#x1f433;', '&#x1f434;', '&#x1f435;', '&#x1f436;', '&#x1f437;', '&#x1f438;', '&#x1f439;', '&#x1f43a;', '&#x1f43b;', '&#x1f43c;', '&#x1f43d;', '&#x1f43e;', '&#x1f43f;', '&#x1f440;', '&#x1f441;', '&#x1f5e8;', '&#x1f442;', '&#x1f443;', '&#x1f444;', '&#x1f445;', '&#x1f446;', '&#x1f447;', '&#x1f448;', '&#x1f449;', '&#x1f44a;', '&#x1f44b;', '&#x1f44c;', '&#x1f44d;', '&#x1f44e;', '&#x1f44f;', '&#x1f450;', '&#x1f451;', '&#x1f452;', '&#x1f453;', '&#x1f454;', '&#x1f455;', '&#x1f456;', '&#x1f457;', '&#x1f458;', '&#x1f459;', '&#x1f45a;', '&#x1f45b;', '&#x1f45c;', '&#x1f45d;', '&#x1f45e;', '&#x1f45f;', '&#x1f460;', '&#x1f461;', '&#x1f462;', '&#x1f463;', '&#x1f464;', '&#x1f465;', '&#x1f466;', '&#x1f467;', '&#x1f468;', '&#x1f4bb;', '&#x1f4bc;', '&#x1f527;', '&#x1f52c;', '&#x1f680;', '&#x1f692;', '&#x1f9af;', '&#x1f9b0;', '&#x1f9b1;', '&#x1f9b2;', '&#x1f9b3;', '&#x1f9bc;', '&#x1f9bd;', '&#x2695;', '&#x2696;', '&#x2708;', '&#x1f91d;', '&#x1f469;', '&#x2764;', '&#x1f48b;', '&#x1f46a;', '&#x1f46b;', '&#x1f46c;', '&#x1f46d;', '&#x1f46e;', '&#x1f46f;', '&#x1f470;', '&#x1f471;', '&#x1f472;', '&#x1f473;', '&#x1f474;', '&#x1f475;', '&#x1f476;', '&#x1f477;', '&#x1f478;', '&#x1f479;', '&#x1f47a;', '&#x1f47b;', '&#x1f47c;', '&#x1f47d;', '&#x1f47e;', '&#x1f47f;', '&#x1f480;', '&#x1f481;', '&#x1f482;', '&#x1f483;', '&#x1f484;', '&#x1f485;', '&#x1f486;', '&#x1f487;', '&#x1f488;', '&#x1f489;', '&#x1f48a;', '&#x1f48c;', '&#x1f48d;', '&#x1f48e;', '&#x1f48f;', '&#x1f490;', '&#x1f491;', '&#x1f492;', '&#x1f493;', '&#x1f494;', '&#x1f495;', '&#x1f496;', '&#x1f497;', '&#x1f498;', '&#x1f499;', '&#x1f49a;', '&#x1f49b;', '&#x1f49c;', '&#x1f49d;', '&#x1f49e;', '&#x1f49f;', '&#x1f4a0;', '&#x1f4a1;', '&#x1f4a2;', '&#x1f4a3;', '&#x1f4a4;', '&#x1f4a5;', '&#x1f4a6;', '&#x1f4a7;', '&#x1f4a8;', '&#x1f4a9;', '&#x1f4aa;', '&#x1f4ab;', '&#x1f4ac;', '&#x1f4ad;', '&#x1f4ae;', '&#x1f4af;', '&#x1f4b0;', '&#x1f4b1;', '&#x1f4b2;', '&#x1f4b3;', '&#x1f4b4;', '&#x1f4b5;', '&#x1f4b6;', '&#x1f4b7;', '&#x1f4b8;', '&#x1f4b9;', '&#x1f4ba;', '&#x1f4bd;', '&#x1f4be;', '&#x1f4bf;', '&#x1f4c0;', '&#x1f4c1;', '&#x1f4c2;', '&#x1f4c3;', '&#x1f4c4;', '&#x1f4c5;', '&#x1f4c6;', '&#x1f4c7;', '&#x1f4c8;', '&#x1f4c9;', '&#x1f4ca;', '&#x1f4cb;', '&#x1f4cc;', '&#x1f4cd;', '&#x1f4ce;', '&#x1f4cf;', '&#x1f4d0;', '&#x1f4d1;', '&#x1f4d2;', '&#x1f4d3;', '&#x1f4d4;', '&#x1f4d5;', '&#x1f4d6;', '&#x1f4d7;', '&#x1f4d8;', '&#x1f4d9;', '&#x1f4da;', '&#x1f4db;', '&#x1f4dc;', '&#x1f4dd;', '&#x1f4de;', '&#x1f4df;', '&#x1f4e0;', '&#x1f4e1;', '&#x1f4e2;', '&#x1f4e3;', '&#x1f4e4;', '&#x1f4e5;', '&#x1f4e6;', '&#x1f4e7;', '&#x1f4e8;', '&#x1f4e9;', '&#x1f4ea;', '&#x1f4eb;', '&#x1f4ec;', '&#x1f4ed;', '&#x1f4ee;', '&#x1f4ef;', '&#x1f4f0;', '&#x1f4f1;', '&#x1f4f2;', '&#x1f4f3;', '&#x1f4f4;', '&#x1f4f5;', '&#x1f4f6;', '&#x1f4f7;', '&#x1f4f8;', '&#x1f4f9;', '&#x1f4fa;', '&#x1f4fb;', '&#x1f4fc;', '&#x1f4fd;', '&#x1f4ff;', '&#x1f500;', '&#x1f501;', '&#x1f502;', '&#x1f503;', '&#x1f504;', '&#x1f505;', '&#x1f506;', '&#x1f507;', '&#x1f508;', '&#x1f509;', '&#x1f50a;', '&#x1f50b;', '&#x1f50c;', '&#x1f50d;', '&#x1f50e;', '&#x1f50f;', '&#x1f510;', '&#x1f511;', '&#x1f512;', '&#x1f513;', '&#x1f514;', '&#x1f515;', '&#x1f516;', '&#x1f517;', '&#x1f518;', '&#x1f519;', '&#x1f51a;', '&#x1f51b;', '&#x1f51c;', '&#x1f51d;', '&#x1f51e;', '&#x1f51f;', '&#x1f520;', '&#x1f521;', '&#x1f522;', '&#x1f523;', '&#x1f524;', '&#x1f525;', '&#x1f526;', '&#x1f528;', '&#x1f529;', '&#x1f52a;', '&#x1f52b;', '&#x1f52d;', '&#x1f52e;', '&#x1f52f;', '&#x1f530;', '&#x1f531;', '&#x1f532;', '&#x1f533;', '&#x1f534;', '&#x1f535;', '&#x1f536;', '&#x1f537;', '&#x1f538;', '&#x1f539;', '&#x1f53a;', '&#x1f53b;', '&#x1f53c;', '&#x1f53d;', '&#x1f549;', '&#x1f54a;', '&#x1f54b;', '&#x1f54c;', '&#x1f54d;', '&#x1f54e;', '&#x1f550;', '&#x1f551;', '&#x1f552;', '&#x1f553;', '&#x1f554;', '&#x1f555;', '&#x1f556;', '&#x1f557;', '&#x1f558;', '&#x1f559;', '&#x1f55a;', '&#x1f55b;', '&#x1f55c;', '&#x1f55d;', '&#x1f55e;', '&#x1f55f;', '&#x1f560;', '&#x1f561;', '&#x1f562;', '&#x1f563;', '&#x1f564;', '&#x1f565;', '&#x1f566;', '&#x1f567;', '&#x1f56f;', '&#x1f570;', '&#x1f573;', '&#x1f574;', '&#x1f575;', '&#x1f576;', '&#x1f577;', '&#x1f578;', '&#x1f579;', '&#x1f57a;', '&#x1f587;', '&#x1f58a;', '&#x1f58b;', '&#x1f58c;', '&#x1f58d;', '&#x1f590;', '&#x1f595;', '&#x1f596;', '&#x1f5a4;', '&#x1f5a5;', '&#x1f5a8;', '&#x1f5b1;', '&#x1f5b2;', '&#x1f5bc;', '&#x1f5c2;', '&#x1f5c3;', '&#x1f5c4;', '&#x1f5d1;', '&#x1f5d2;', '&#x1f5d3;', '&#x1f5dc;', '&#x1f5dd;', '&#x1f5de;', '&#x1f5e1;', '&#x1f5e3;', '&#x1f5ef;', '&#x1f5f3;', '&#x1f5fa;', '&#x1f5fb;', '&#x1f5fc;', '&#x1f5fd;', '&#x1f5fe;', '&#x1f5ff;', '&#x1f600;', '&#x1f601;', '&#x1f602;', '&#x1f603;', '&#x1f604;', '&#x1f605;', '&#x1f606;', '&#x1f607;', '&#x1f608;', '&#x1f609;', '&#x1f60a;', '&#x1f60b;', '&#x1f60c;', '&#x1f60d;', '&#x1f60e;', '&#x1f60f;', '&#x1f610;', '&#x1f611;', '&#x1f612;', '&#x1f613;', '&#x1f614;', '&#x1f615;', '&#x1f616;', '&#x1f617;', '&#x1f618;', '&#x1f619;', '&#x1f61a;', '&#x1f61b;', '&#x1f61c;', '&#x1f61d;', '&#x1f61e;', '&#x1f61f;', '&#x1f620;', '&#x1f621;', '&#x1f622;', '&#x1f623;', '&#x1f624;', '&#x1f625;', '&#x1f626;', '&#x1f627;', '&#x1f628;', '&#x1f629;', '&#x1f62a;', '&#x1f62b;', '&#x1f62c;', '&#x1f62d;', '&#x1f62e;', '&#x1f62f;', '&#x1f630;', '&#x1f631;', '&#x1f632;', '&#x1f633;', '&#x1f634;', '&#x1f635;', '&#x1f636;', '&#x1f637;', '&#x1f638;', '&#x1f639;', '&#x1f63a;', '&#x1f63b;', '&#x1f63c;', '&#x1f63d;', '&#x1f63e;', '&#x1f63f;', '&#x1f640;', '&#x1f641;', '&#x1f642;', '&#x1f643;', '&#x1f644;', '&#x1f645;', '&#x1f646;', '&#x1f647;', '&#x1f648;', '&#x1f649;', '&#x1f64a;', '&#x1f64b;', '&#x1f64c;', '&#x1f64d;', '&#x1f64e;', '&#x1f64f;', '&#x1f681;', '&#x1f682;', '&#x1f683;', '&#x1f684;', '&#x1f685;', '&#x1f686;', '&#x1f687;', '&#x1f688;', '&#x1f689;', '&#x1f68a;', '&#x1f68b;', '&#x1f68c;', '&#x1f68d;', '&#x1f68e;', '&#x1f68f;', '&#x1f690;', '&#x1f691;', '&#x1f693;', '&#x1f694;', '&#x1f695;', '&#x1f696;', '&#x1f697;', '&#x1f698;', '&#x1f699;', '&#x1f69a;', '&#x1f69b;', '&#x1f69c;', '&#x1f69d;', '&#x1f69e;', '&#x1f69f;', '&#x1f6a0;', '&#x1f6a1;', '&#x1f6a2;', '&#x1f6a3;', '&#x1f6a4;', '&#x1f6a5;', '&#x1f6a6;', '&#x1f6a7;', '&#x1f6a8;', '&#x1f6a9;', '&#x1f6aa;', '&#x1f6ab;', '&#x1f6ac;', '&#x1f6ad;', '&#x1f6ae;', '&#x1f6af;', '&#x1f6b0;', '&#x1f6b1;', '&#x1f6b2;', '&#x1f6b3;', '&#x1f6b4;', '&#x1f6b5;', '&#x1f6b6;', '&#x1f6b7;', '&#x1f6b8;', '&#x1f6b9;', '&#x1f6ba;', '&#x1f6bb;', '&#x1f6bc;', '&#x1f6bd;', '&#x1f6be;', '&#x1f6bf;', '&#x1f6c0;', '&#x1f6c1;', '&#x1f6c2;', '&#x1f6c3;', '&#x1f6c4;', '&#x1f6c5;', '&#x1f6cb;', '&#x1f6cc;', '&#x1f6cd;', '&#x1f6ce;', '&#x1f6cf;', '&#x1f6d0;', '&#x1f6d1;', '&#x1f6d2;', '&#x1f6d5;', '&#x1f6e0;', '&#x1f6e1;', '&#x1f6e2;', '&#x1f6e3;', '&#x1f6e4;', '&#x1f6e5;', '&#x1f6e9;', '&#x1f6eb;', '&#x1f6ec;', '&#x1f6f0;', '&#x1f6f3;', '&#x1f6f4;', '&#x1f6f5;', '&#x1f6f6;', '&#x1f6f7;', '&#x1f6f8;', '&#x1f6f9;', '&#x1f6fa;', '&#x1f7e0;', '&#x1f7e1;', '&#x1f7e2;', '&#x1f7e3;', '&#x1f7e4;', '&#x1f7e5;', '&#x1f7e6;', '&#x1f7e7;', '&#x1f7e8;', '&#x1f7e9;', '&#x1f7ea;', '&#x1f7eb;', '&#x1f90d;', '&#x1f90e;', '&#x1f90f;', '&#x1f910;', '&#x1f911;', '&#x1f912;', '&#x1f913;', '&#x1f914;', '&#x1f915;', '&#x1f916;', '&#x1f917;', '&#x1f918;', '&#x1f919;', '&#x1f91a;', '&#x1f91b;', '&#x1f91c;', '&#x1f91e;', '&#x1f91f;', '&#x1f920;', '&#x1f921;', '&#x1f922;', '&#x1f923;', '&#x1f924;', '&#x1f925;', '&#x1f926;', '&#x1f927;', '&#x1f928;', '&#x1f929;', '&#x1f92a;', '&#x1f92b;', '&#x1f92c;', '&#x1f92d;', '&#x1f92e;', '&#x1f92f;', '&#x1f930;', '&#x1f931;', '&#x1f932;', '&#x1f933;', '&#x1f934;', '&#x1f935;', '&#x1f936;', '&#x1f937;', '&#x1f938;', '&#x1f939;', '&#x1f93a;', '&#x1f93c;', '&#x1f93d;', '&#x1f93e;', '&#x1f93f;', '&#x1f940;', '&#x1f941;', '&#x1f942;', '&#x1f943;', '&#x1f944;', '&#x1f945;', '&#x1f947;', '&#x1f948;', '&#x1f949;', '&#x1f94a;', '&#x1f94b;', '&#x1f94c;', '&#x1f94d;', '&#x1f94e;', '&#x1f94f;', '&#x1f950;', '&#x1f951;', '&#x1f952;', '&#x1f953;', '&#x1f954;', '&#x1f955;', '&#x1f956;', '&#x1f957;', '&#x1f958;', '&#x1f959;', '&#x1f95a;', '&#x1f95b;', '&#x1f95c;', '&#x1f95d;', '&#x1f95e;', '&#x1f95f;', '&#x1f960;', '&#x1f961;', '&#x1f962;', '&#x1f963;', '&#x1f964;', '&#x1f965;', '&#x1f966;', '&#x1f967;', '&#x1f968;', '&#x1f969;', '&#x1f96a;', '&#x1f96b;', '&#x1f96c;', '&#x1f96d;', '&#x1f96e;', '&#x1f96f;', '&#x1f970;', '&#x1f971;', '&#x1f973;', '&#x1f974;', '&#x1f975;', '&#x1f976;', '&#x1f97a;', '&#x1f97b;', '&#x1f97c;', '&#x1f97d;', '&#x1f97e;', '&#x1f97f;', '&#x1f980;', '&#x1f981;', '&#x1f982;', '&#x1f983;', '&#x1f984;', '&#x1f985;', '&#x1f986;', '&#x1f987;', '&#x1f988;', '&#x1f989;', '&#x1f98a;', '&#x1f98b;', '&#x1f98c;', '&#x1f98d;', '&#x1f98e;', '&#x1f98f;', '&#x1f990;', '&#x1f991;', '&#x1f992;', '&#x1f993;', '&#x1f994;', '&#x1f995;', '&#x1f996;', '&#x1f997;', '&#x1f998;', '&#x1f999;', '&#x1f99a;', '&#x1f99b;', '&#x1f99c;', '&#x1f99d;', '&#x1f99e;', '&#x1f99f;', '&#x1f9a0;', '&#x1f9a1;', '&#x1f9a2;', '&#x1f9a5;', '&#x1f9a6;', '&#x1f9a7;', '&#x1f9a8;', '&#x1f9a9;', '&#x1f9aa;', '&#x1f9ae;', '&#x1f9b4;', '&#x1f9b5;', '&#x1f9b6;', '&#x1f9b7;', '&#x1f9b8;', '&#x1f9b9;', '&#x1f9bb;', '&#x1f9be;', '&#x1f9bf;', '&#x1f9c0;', '&#x1f9c1;', '&#x1f9c2;', '&#x1f9c3;', '&#x1f9c4;', '&#x1f9c5;', '&#x1f9c6;', '&#x1f9c7;', '&#x1f9c8;', '&#x1f9c9;', '&#x1f9ca;', '&#x1f9cd;', '&#x1f9ce;', '&#x1f9cf;', '&#x1f9d0;', '&#x1f9d1;', '&#x1f9d2;', '&#x1f9d3;', '&#x1f9d4;', '&#x1f9d5;', '&#x1f9d6;', '&#x1f9d7;', '&#x1f9d8;', '&#x1f9d9;', '&#x1f9da;', '&#x1f9db;', '&#x1f9dc;', '&#x1f9dd;', '&#x1f9de;', '&#x1f9df;', '&#x1f9e0;', '&#x1f9e1;', '&#x1f9e2;', '&#x1f9e3;', '&#x1f9e4;', '&#x1f9e5;', '&#x1f9e6;', '&#x1f9e7;', '&#x1f9e8;', '&#x1f9e9;', '&#x1f9ea;', '&#x1f9eb;', '&#x1f9ec;', '&#x1f9ed;', '&#x1f9ee;', '&#x1f9ef;', '&#x1f9f0;', '&#x1f9f1;', '&#x1f9f2;', '&#x1f9f3;', '&#x1f9f4;', '&#x1f9f5;', '&#x1f9f6;', '&#x1f9f7;', '&#x1f9f8;', '&#x1f9f9;', '&#x1f9fa;', '&#x1f9fb;', '&#x1f9fc;', '&#x1f9fd;', '&#x1f9fe;', '&#x1f9ff;', '&#x1fa70;', '&#x1fa71;', '&#x1fa72;', '&#x1fa73;', '&#x1fa78;', '&#x1fa79;', '&#x1fa7a;', '&#x1fa80;', '&#x1fa81;', '&#x1fa82;', '&#x1fa90;', '&#x1fa91;', '&#x1fa92;', '&#x1fa93;', '&#x1fa94;', '&#x1fa95;', '&#x203c;', '&#x2049;', '&#x2122;', '&#x2139;', '&#x2194;', '&#x2195;', '&#x2196;', '&#x2197;', '&#x2198;', '&#x2199;', '&#x21a9;', '&#x21aa;', '&#x20e3;', '&#x231a;', '&#x231b;', '&#x2328;', '&#x23cf;', '&#x23e9;', '&#x23ea;', '&#x23eb;', '&#x23ec;', '&#x23ed;', '&#x23ee;', '&#x23ef;', '&#x23f0;', '&#x23f1;', '&#x23f2;', '&#x23f3;', '&#x23f8;', '&#x23f9;', '&#x23fa;', '&#x24c2;', '&#x25aa;', '&#x25ab;', '&#x25b6;', '&#x25c0;', '&#x25fb;', '&#x25fc;', '&#x25fd;', '&#x25fe;', '&#x2600;', '&#x2601;', '&#x2602;', '&#x2603;', '&#x2604;', '&#x260e;', '&#x2611;', '&#x2614;', '&#x2615;', '&#x2618;', '&#x261d;', '&#x2622;', '&#x2623;', '&#x2626;', '&#x262a;', '&#x262e;', '&#x262f;', '&#x2638;', '&#x2639;', '&#x263a;', '&#x2648;', '&#x2649;', '&#x264a;', '&#x264b;', '&#x264c;', '&#x264d;', '&#x264e;', '&#x264f;', '&#x2650;', '&#x2651;', '&#x2652;', '&#x2653;', '&#x265f;', '&#x2660;', '&#x2663;', '&#x2665;', '&#x2666;', '&#x2668;', '&#x267b;', '&#x267e;', '&#x267f;', '&#x2692;', '&#x2693;', '&#x2694;', '&#x2697;', '&#x2699;', '&#x269b;', '&#x269c;', '&#x26a0;', '&#x26a1;', '&#x26aa;', '&#x26ab;', '&#x26b0;', '&#x26b1;', '&#x26bd;', '&#x26be;', '&#x26c4;', '&#x26c5;', '&#x26c8;', '&#x26ce;', '&#x26cf;', '&#x26d1;', '&#x26d3;', '&#x26d4;', '&#x26e9;', '&#x26ea;', '&#x26f0;', '&#x26f1;', '&#x26f2;', '&#x26f3;', '&#x26f4;', '&#x26f5;', '&#x26f7;', '&#x26f8;', '&#x26f9;', '&#x26fa;', '&#x26fd;', '&#x2702;', '&#x2705;', '&#x2709;', '&#x270a;', '&#x270b;', '&#x270c;', '&#x270d;', '&#x270f;', '&#x2712;', '&#x2714;', '&#x2716;', '&#x271d;', '&#x2721;', '&#x2728;', '&#x2733;', '&#x2734;', '&#x2744;', '&#x2747;', '&#x274c;', '&#x274e;', '&#x2753;', '&#x2754;', '&#x2755;', '&#x2757;', '&#x2763;', '&#x2795;', '&#x2796;', '&#x2797;', '&#x27a1;', '&#x27b0;', '&#x27bf;', '&#x2934;', '&#x2935;', '&#x2b05;', '&#x2b06;', '&#x2b07;', '&#x2b1b;', '&#x2b1c;', '&#x2b50;', '&#x2b55;', '&#x3030;', '&#x303d;', '&#x3297;', '&#x3299;', '&#xe50a;' );

        return $partials;
    }

    public static function maybe_review_outdated($review) {
        if (is_string($review) || is_integer($review)) {
            $review = ReviewsModel::get($review);
            if (!empty($review)) {
                $review = $review[0];
            }
        }

        $outdated = false;

        $status = $review->status;

        // If review outdated or cancelled we can do next transfer for current user
        if ('outdated' === $status || 'cancelled' === $status) {
            return $review->id;
        }

        if ('pending' === $status || 'ready' === $status) {
            $selected_review_period = (int) get_option('wtsr_review_period', '30');
        } else {
            $selected_review_period = (int) get_option('wtsr_review_period', '30');
        }

        $period_to_second = (int) $selected_review_period * 60 * 60 * 24;
        $now = time();
        $created = strtotime($review->review_created);

        if (!empty($review->review_sent)) {
            $transfered = strtotime($review->review_sent);
        }

        if ('reviewed' === $status) {
            $timeout = $created + $period_to_second + (7 * 24 * 60 * 60);

            if ($timeout < $now) {
                return $review->id;
            }
        } elseif ('pending' === $status || 'ready' === $status) {
            $timeout = $created + $period_to_second;

            if ($timeout < $now) {
                $outdated = true;
            }
        } else {
            $timeout = $transfered + $period_to_second;

            if ($timeout < $now) {
                $outdated = true;
            }
        }

        if ($outdated) {
            $data = array(
                'id' => $review->id,
                'status' => 'outdated',
                'review_sent' => date('Y:m:d H:i:s'),
            );

            if ('pending' === $status || 'ready' === $status) {
                $data['status'] = 'cancelled';
            }

            $id = ReviewsModel::update($data);

            if ($id && 'transferred' === $status) {
                do_action('wtsr_review_status_changed', 'outdated', $review->id, $review->order_id);
            }

            return $id;
        }

        return false;
    }

    public static function check_if_product_reviews_reviewed_bg() {
        $result = Wtsr_Background_Review_Check::bg_process('check');
    }

    public static function maybe_product_page_review_reviewed($comment_ID, $post_id, $email) {
        $comment_reviewed = get_comment_meta( $comment_ID, 'wtsr_review_id', true );

        if (empty($comment_reviewed)) {
            $review = ReviewsModel::get_last_by_email($email);
        } else {
            $review = ReviewsModel::get_by_id($comment_reviewed);
        }

        if (!empty($review) && ('transferred' === $review["status"] || 'woo-sent' === $review["status"])) {
            $product = wc_get_product($post_id);

            if (!empty($product)) {
                $order_id = $review["order_id"];

                $order = wc_get_order( $order_id );

                if (!$order) {
                    return false;
                }

                $product_in_order = false;
                $order_items = self::get_order_items($order);

                foreach ($order_items as $product_data) {
                    if ($product_data['parent_id'] === (int)$post_id) {
                        $product_in_order = true;

                        break;
                    }
                }

                if ($product_in_order) {
                    $wtsr_send_woo_email_schedule_all = get_option(Wtsr_Cron::$schedule_option, array());

                    if (!empty($wtsr_send_woo_email_schedule_all[$review['id']])) {
                        unset($wtsr_send_woo_email_schedule_all[$review['id']]);

                        update_option(Wtsr_Cron::$schedule_option, $wtsr_send_woo_email_schedule_all);
                    }

                    $data = array(
                        'id' => $review['id'],
                        'status' => 'reviewed',
                        'review_sent' => gmdate( 'Y-m-d H:i:s', time() )
                    );

                    $id = ReviewsModel::update($data);

                    if ($id) {
                        do_action('wtsr_review_status_changed', 'reviewed', $review['id'], $review['order_id']);
                    }
                }
            }
        }
    }

    public static function check_if_product_reviews_reviewed($force = false) {
        $need_check = get_option('wtsr_to_check_product_reviews') || $force;

        if (!$need_check) return false;

        delete_option('wtsr_to_check_product_reviews');

        global $wpdb;

        $today_product_reviews = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE '%wtsr_product_reviews_%'", ARRAY_A );
        $merged_product_reviews = array();
        $ts_product_reviews = array();

        if (!empty($today_product_reviews) && !empty($today_product_reviews['option_value'])) {
            $product_reviews_decoded = json_decode($today_product_reviews['option_value'], true);

            if ($product_reviews_decoded) {
                $ts_product_reviews = TSManager::get_order_ids_from_product_reviews($product_reviews_decoded);
            }
        }

        if (!empty($ts_product_reviews)) {
            $merged_product_reviews[] = $ts_product_reviews;
        }

        if (!empty($merged_product_reviews)) {
            $merged_product_reviews = ReviewServiceManager::merge_order_ids_from_product_reviews($merged_product_reviews);
        }

        if (!empty($merged_product_reviews)) {
            $product_reviews_by_order_id = $merged_product_reviews;

            $transferred = ReviewsModel::get_not_reviewed('order_id');
            $reviewed = array();

            foreach ($transferred as $order_id => $review) {
                if (!empty($product_reviews_by_order_id[$order_id])) {
                    $reviewed[$order_id] = array(
                        'id' => $review['id'],
                        'email' => $review['email'],
                        'review_sent' => $product_reviews_by_order_id[$order_id][0]['date'],
                    );

                    $data = array(
                        'id' => $review['id'],
                        'status' => 'reviewed',
                        'review_sent' => $product_reviews_by_order_id[$order_id][0]['date'],
                    );

                    $id = ReviewsModel::update($data);

                    if ($id) {
                        do_action('wtsr_review_status_changed', 'reviewed', $review['id'], $review['order_id']);
                    }

                    $user_products = get_option('wtsr_reviewed_' . $review['email'], array());

                    foreach ($product_reviews_by_order_id[$order_id] as $_product_review) {
                        $user_products[] = $_product_review['sku'];
                    }

                    update_option('wtsr_reviewed_' . $review['email'], $user_products, false);
                } else {
                    $outdated = TSManager::maybe_review_outdated($review['id']);
                }
            }

            return $reviewed;
        }

        return false;
    }

    /**
     * Get Orders ID from json with product reviews got from TS API
     *
     * @param $products_reviews
     * @return array
     */
    public static function get_order_ids_from_product_reviews($products_reviews) {
        $order_ids = array();
        $order_ids_array = array();

        if (!empty($products_reviews['products']) || !empty($products_reviews['shop'])) {
            if (!empty($products_reviews['products'])) {
                $products = $products_reviews['products'];

                foreach ($products as $review) {
                    if (!empty($review['order']['orderReference'])) {
                        $sku = !empty($review['product']["sku"]) ? $review['product']["sku"] : '';
                        $creationDate = !empty($review['creationDate']) ? $review['creationDate'] : '';
                        $orderReference = $review['order']['orderReference'];
                        $order_ids_array[] = $orderReference;
                        $order_ids[$orderReference][] = array(
                            'date' => date('Y-m-d H:i:s', strtotime ($creationDate)),
                            'sku' => $sku,
                        );
                    }
                }
            }
            if (!empty($products_reviews['shop'])) {
                $shop = $products_reviews['shop'];

                foreach ($shop as $review) {
                    if (!empty($review['orderReference'])) {
                        $sku = 'shop_review';
                        $creationDate = !empty($review['creationDate']) ? $review['creationDate'] : '';
                        $orderReference = $review['orderReference'];
                        $order_ids_array[] = $orderReference;
                        $order_ids[$orderReference][] = array(
                            'date' => date('Y-m-d H:i:s', strtotime ($creationDate)),
                            'sku' => $sku,
                        );
                    }
                }
            }
        } elseif (!empty($products_reviews['response']['data']['shop']['products'])) {
            $products = $products_reviews['response']['data']['shop']['products'];

            foreach ($products as $product) {
                if (!empty($product['productReviews'])) {
                    $reviews = $product['productReviews'];

                    foreach ($reviews as $review) {
                        if (!empty($review['order']['orderReference'])) {
                            $creationDate = !empty($review['creationDate']) ? $review['creationDate'] : '';
                            $orderReference = $review['order']['orderReference'];

                            // if (!isset($order_ids[$orderReference])) {
                            $order_ids_array[] = $orderReference;
                            $order_ids[$orderReference][] = array(
                                'date' => date('Y-m-d H:i:s', strtotime ($creationDate)),
                                'sku' => $product["sku"],
                            );
                            // }
                        }
                    }
                }
            }
        }

        // return $order_ids_array;
        return $order_ids;
    }

    public static function get_api_review_request_body($order_id, $order_data, $order_items, $order_email) {
        $ts_credentials = TSManager::get_ts_credentials();
        $ts_id = $ts_credentials['ts_id'];
        $order_first_name = !empty($order_data['billing']['first_name']) ? $order_data['billing']['first_name'] : false;
        $order_last_name = !empty($order_data['billing']['last_name']) ? $order_data['billing']['last_name'] : false;
        $order_date = $order_data['date_created'];
        $order_date = $order_date->date('Y-m-d');
        $order_items_body_array = array();

        foreach ($order_items as $key => $item) {
            $name = $item['name'];
            $sku = $item['sku'];
            $url = $item['url'];
            $thumbnail = $item['thumbnail'];

            ob_start();
            ?>{
                "sku": "<?php echo $sku ?>",
                "name": "<?php echo $name ?>",
                "imageUrl": "<?php echo $thumbnail; ?>",
                "productUrl": "<?php echo $url; ?>"
            }<?php
            $order_item_body = ob_get_clean();
            $order_items_body_array[] = $order_item_body;
        }

        ob_start();
        ?>
{
    "tsId": "<?php echo $ts_id ?>",
    "order": {
        "orderDate": "<?php echo $order_date ?>",
        "orderReference": "<?php echo $order_id ?>",
        "products": [
            <?php echo implode(",", $order_items_body_array)?>

        ]
    },
    "consumer": {
        "firstname": "<?php echo $order_first_name ?>",
        "lastname": "<?php echo $order_last_name ?>",
        "contact": {
            "email": "<?php echo $order_email ?>"
        }
    },
    "sender": {
        "type": "ThirdParty"
    },
    "types": [
        {"key": "products"}
    ]
}<?php
        $ts_body = ob_get_clean();
        $ts_body_check = json_decode($ts_body);

        if (empty($ts_body_check)) {
            return false;
        }

        return $ts_body;
    }

    public static function get_review_request_body($order_id) {
        return ReviewServiceManager::get_api_review_request_body($order_id);
    }

    // TODO: In a case if we will need shop review request
    public static function get_review_shop_request_body($order_id) {
        $ts_credentials = TSManager::get_ts_credentials();
        $ts_id = $ts_credentials['ts_id'];

        if (empty($ts_id)) {
            return false;
        }

        $order = wc_get_order( $order_id );

        if (!$order) {
            return false;
        }

        $order_data = $order->get_data();

        $order_first_name = !empty($order_data['billing']['first_name']) ? $order_data['billing']['first_name'] : false;
        $order_last_name = !empty($order_data['billing']['last_name']) ? $order_data['billing']['last_name'] : false;
        $order_email = !empty($order_data['billing']['email']) ? $order_data['billing']['email'] : false;

        if (!$order_first_name || !$order_last_name || !$order_email) {
            return false;
        }

        $order_date = $order_data['date_created'];
        $order_date = $order_date->date('Y-m-d');

        $order_items = $order->get_items();
        $order_items_body_array = array();

        foreach ($order_items as $key => $item) {
            $product = $item->get_product();
            $product_id = $product->get_id();
            $product_data = $product->get_data();
            $product_type = $product->get_type();
            $product_sku = $product_data['sku'];
            $product_name = $product_data['name'];
            $product_url = get_permalink( $product_id );
            $product_thumbnail = get_the_post_thumbnail_url( $product_id );

            $name = $product_name;
            $sku = $product_sku;
            $url = $product_url;
            $thumbnail = $product_thumbnail;

            if ('variation' === $product_type) {
                $parent_product_id = $product->get_parent_id();
                $parent_product = wc_get_product( $parent_product_id );
                $parent_product_data = $parent_product->get_data();
                $parent_product_type = $parent_product->get_type();
                $parent_product_sku = $parent_product_data['sku'];
                $parent_product_name = $parent_product_data['name'];
                $parent_product_url = get_permalink( $parent_product_id );
                $parent_product_thumbnail = get_the_post_thumbnail_url( $parent_product_id );

                if (empty($name)) {
                    $name = $parent_product_name;
                }

                if (empty($sku)) {
                    $sku = $parent_product_sku;
                }

                if (empty($thumbnail)) {
                    $thumbnail = $parent_product_thumbnail;
                }
            }

            if (empty($sku)) {
                $sku = 'woo-' . $product_id;
            }

            $name = str_replace('"', '', $name);
            $name = str_replace('`', '', $name);
            $name = str_replace("'", '', $name);
            $name = trim($name);

            ob_start();
            ?>
            {
            "sku": "<?php echo $sku ?>",
            "name": "<?php echo $name ?>",
            "imageUrl": "<?php echo $thumbnail; ?>",
            "productUrl": "<?php echo $url; ?>"
            }<?php
            $order_item_body = ob_get_clean();
            $order_items_body_array[] = $order_item_body;
        }

        ob_start();
        ?>
        {
        "tsId": "<?php echo $ts_id ?>",
        "order": {
        "orderDate": "<?php echo $order_date ?>",
        "orderReference": "<?php echo $order_id ?>",
        "products": [
        <?php echo implode(",\r\n", $order_items_body_array)?>

        ]
        },
        "consumer": {
        "firstname": "<?php echo $order_first_name ?>",
        "lastname": "<?php echo $order_last_name ?>",
        "contact": {
        "email": "<?php echo $order_email ?>"
        }
        },
        "sender": {
        "type": "ThirdParty"
        },
        "types": [
        {
        "key": "shop"
        }
        ]
        }<?php
        $ts_body = ob_get_clean();
        $ts_body_check = json_decode($ts_body);

        if (empty($ts_body_check)) {
            return false;
        }

        return $ts_body;
    }

    public static function generate_dummy_orders($domain = false, $review_allowed = true, $created_via = 'checkout', $status = null, $fs = null, $ls = null) {
        $products_array = self::get_dummy_simple_products();
        $order_statuses = self::get_dummy_order_statuses();
        $order_payments = self::get_dummy_payments();
        $order_max = count($products_array) - 1;
        $order_rand = rand(1, $order_max);
        $person = self::get_dummy_person();
        $status_max = count($order_statuses) - 1;
        $status_rand = rand(0, $status_max);
        $payment_max = count($order_payments) - 1;
        $payment_rand = rand(0, $payment_max);
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        $person_email = $person['email'];

        if (empty($status)) {
            $status = $order_statuses[$status_rand];
        }

        if ($domain) {
            $person_email_array = explode('@', $person_email);
            $person_email = $person_email_array[0] . '@' . $domain;
        }

        $person_first_name = !empty($fs) ? $fs : $person['first_name'];
        $person_last_name = !empty($ls) ? $ls : $person['last_name'];


        $order_address = array(
            'first_name' => $person_first_name,
            'last_name'  => $person_last_name,
            'company'    => $person['company'],
            'email'      => $person_email,
            'phone'      => $person['phone'],
            'address_1'  => $person['street'],
            'address_2'  => '',
            'city'       => $person['city'],
            'state'      => $person['state'],
            'postcode'   => $person['postcode'],
            'country'    => $person['country']
        );

        $order = wc_create_order();

        for ($i = 0; $i < $order_rand; $i++) {
            $products_array = array_values($products_array);
            $products_count = count($products_array);

            $rand = rand(0, $products_count - 1);

            $_product = $products_array[$rand];

            unset($products_array[$rand]);

            $order->add_product( wc_get_product( $_product->get_id() ), rand(1, 10) );
        }

        $order->set_address( $order_address, 'billing' );
        $order->set_address( $order_address, 'shipping' );
        $order->set_payment_method( $payment_gateways[$order_payments[$payment_rand]] );
        $order->calculate_totals();
        $order->set_created_via( $created_via );
        $order->update_status($status, 'Lorem ipsum dolor sit amet, est at principes intellegat');

        update_post_meta( $order->get_id(), '_wtsr_dummy_order', 1 );

        if ($review_allowed) {
            update_post_meta( $order->get_id(), 'Trusted Shops Review', 'yes' );
        }

        do_action( 'woocommerce_checkout_order_processed', $order->get_id(), $person, $order );

        wp_reset_postdata();

        return $person;
    }

    public static function get_dummy_person() {
        $request = wp_remote_get('https://randomuser.me/api/?nat=us');
        $response = json_decode(wp_remote_retrieve_body( $request ), true);

        $fake_info = $response['results'][0];

        $person = array();

        $person['first_name'] = ucfirst($fake_info['name']['first']);
        $person['last_name']  = ucfirst($fake_info['name']['last']);
        $person['email']  = $fake_info['email'];
        $person['phone']  = $fake_info['phone'];
        $person['country']  = strtoupper($fake_info['nat']);
        $person['street']  = 'Addres Snt, 15';

        if (is_array($fake_info['location']['street'])) {
            $person_street = '';

            if (!empty($fake_info["location"]["street"]["name"])) {
                $person_street .= $fake_info["location"]["street"]["name"];
            } else {
                $person_street .= 'Addres Snt';
            }

            if (!empty($fake_info["location"]["street"]["number"])) {
                $person_street .= ', ' . $fake_info["location"]["street"]["number"];
            } else {
                $person_street .= ', 15';
            }
        } elseif (is_string($fake_info['location']['street'])) {
            $person_street = $fake_info['location']['street'];
        }

        if (!empty($person_street)) {
            $person['street']  = $person_street;
        }


        $person['city']  = ucwords($fake_info['location']['city']);
        $person['state']  = ucwords($fake_info['location']['state']);
        $person['postcode']  = $fake_info['location']['postcode'];
        $person['company']  = ucwords($fake_info['login']['username']) . '-' . ucwords($fake_info['login']['password']) . ' LTD';

        return $person;
    }

    public static function get_dummy_order_statuses() {
//        return array (
//            'pending',
//            'on-hold',
//            'processing',
//            'refunded',
//            'cancelled',
//            'processing',
//            'processing',
//            'processing',
//            'processing',
//            'processing',
//        );

        return array (
            'pending',
            'on-hold',
            'processing',
            'completed',
            'refunded',
            'cancelled',
            'completed',
            'processing',
            'completed',
            'processing',
            'completed',
            'completed',
            'processing',
            'completed',
            'processing',
            'completed',
            'completed',
            'processing',
            'completed',
            'failed',
        );
    }

    public static function get_dummy_payments() {
        return array (
            'bacs',
            'cheque',
            'cod',
            'paypal',
        );
    }

    public static function get_dummy_simple_products() {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1
        );

        $posts_array = get_posts( $args );

        $products_array = array();

        foreach ($posts_array as $post_item) {
            $product_object = wc_get_product($post_item->ID);

            //if ('simple' === $product_object->get_type() || 'variable' === $product_object->get_type()) {
            if ('simple' === $product_object->get_type()) {
                $products_array[] = $product_object;
            }
        }

        return $products_array;
    }

    public static function is_order_dummy($order_id) {
        $is_dummy = get_post_meta($order_id, '_wtsr_dummy_order', true);

        return $is_dummy;
    }

    public static function get_button_colors() {
        $wtsr_button_bg_color = get_option('wtsr_button_bg_color', '#A46497');
        $wtsr_button_text_color = get_option('wtsr_button_text_color', '#ffffff');

        return array(
            'bg_color' => $wtsr_button_bg_color,
            'text_color' => $wtsr_button_text_color,
        );
    }

    public static function get_hover_colors() {
        $wtsr_button_bg_color = get_option('wtsr_normal_color', '#555');
        $wtsr_button_text_color = get_option('wtsr_hover_color', '#A46497');

        return array(
            'normal' => $wtsr_button_bg_color,
            'hover' => $wtsr_button_text_color,
        );
    }

    public static function get_aiop_button_colors() {
        $wtsr_normal_button_bg_color = get_option('wtsr_normal_button_bg_color', '#A46497');
        $wtsr_normal_button_txt_color = get_option('wtsr_normal_button_txt_color', '#ffffff');
        $wtsr_hover_button_bg_color = get_option('wtsr_hover_button_bg_color', '#66405f');
        $wtsr_hover_button_txt_color = get_option('wtsr_hover_button_txt_color', '#ffffff');

        return array(
            'normal_bg' => $wtsr_normal_button_bg_color,
            'normal_txt' => $wtsr_normal_button_txt_color,
            'hover_bg' => $wtsr_hover_button_bg_color,
            'hover_txt' => $wtsr_hover_button_txt_color,
        );
    }

    public static function get_default_image_size() {
        $wtsr_image_size = get_option('wtsr_image_size', 'full');

        return $wtsr_image_size;
    }

    public static function get_all_image_sizes() {
        global $_wp_additional_image_sizes;

        $default_image_sizes = get_intermediate_image_sizes();

        foreach ( $default_image_sizes as $size ) {
            $image_sizes[ $size ][ 'width' ] = intval( get_option( "{$size}_size_w" ) );
            $image_sizes[ $size ][ 'height' ] = intval( get_option( "{$size}_size_h" ) );
            $image_sizes[ $size ][ 'crop' ] = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
        }

        if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
            $image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
        }

        return $image_sizes;
    }

    public static function is_wtsr_module_enabled() {
        global $wpdb;
        $wp2leads_modules = json_decode(get_option('wp2leads_module_maps'), true);

        if (!empty($wp2leads_modules['wtsr_review_request'])) {
            $table_name = $wpdb->prefix . 'wp2l_maps';

            foreach ($wp2leads_modules['wtsr_review_request'] as $map_id => $status) {
                $sql = "SELECT id FROM {$table_name} WHERE id = " . $map_id;
                $result = $wpdb->get_row($sql);

                if (empty($result)) {
                    unset($wp2leads_modules['wtsr_review_request'][$map_id]);

                    update_option('wp2leads_module_maps', json_encode($wp2leads_modules));
                }
            }

            return $wp2leads_modules['wtsr_review_request'];
        }

        return false;
    }

    public static function is_wtsr_map_exists() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp2l_maps';
        $sql = "SELECT id, name, mapping FROM {$table_name}";

        $result = $wpdb->get_results($sql, ARRAY_A);

        if (empty($result)) {
            return false;
        }

        $wtsr_maps = array();

        foreach ($result as $map) {
            $mapping = unserialize($map['mapping']);

            if (!empty($mapping['transferModule']) && 'wtsr_review_request' === $mapping['transferModule']) {
                $wtsr_maps[] = array(
                    'id' => $map['id'],
                    'name' => $map['name'],
                );
            }
        }

        if (empty($wtsr_maps)) {
            return false;
        }

        return $wtsr_maps;
    }

    public static function terminate_bg_generate_review($iteration = 0) {
        global $wpdb;
        $product_review_request_bg = $wpdb->get_results( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE '%wp_wtsr_review_request_batch%'", ARRAY_A );

        if (!empty($product_review_request_bg)) {
            $terminated = false;

            $sql = "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%wp_wtsr_review_request_batch%'";
            $wpdb->query($sql);
        }

        if ($iteration < 4) {
            $iteration++;
            self::terminate_bg_generate_review($iteration);
        }
    }

    public static function get_dummy_products() {
        $wtsr_image_size = self::get_default_image_size();
        return array(
            0 => array(
                'name' => __('Product 1 Title', 'more-better-reviews-for-woocommerce'),
                'content' => __('Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.', 'more-better-reviews-for-woocommerce'),
                'img' => wc_placeholder_img_src( $wtsr_image_size ),
                'img_full' => wc_placeholder_img_src( 'full' ),
                'img_thumbnail' => wc_placeholder_img_src( 'full' ),
            ),
            1 => array(
                'name' => __('Product 2 Title', 'more-better-reviews-for-woocommerce'),
                'content' => __('Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.', 'more-better-reviews-for-woocommerce'),
                'img' => wc_placeholder_img_src( $wtsr_image_size ),
                'img_full' => wc_placeholder_img_src( 'full' ),
                'img_thumbnail' => wc_placeholder_img_src( 'full' ),
            ),
            2 => array(
                'name' => __('Product 3 Title', 'more-better-reviews-for-woocommerce'),
                'content' => __('Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.', 'more-better-reviews-for-woocommerce'),
                'img' => wc_placeholder_img_src( $wtsr_image_size ),
                'img_full' => wc_placeholder_img_src( 'full' ),
                'img_thumbnail' => wc_placeholder_img_src( 'full' ),
            ),
            3 => array(
                'name' => __('Product 1 Title', 'more-better-reviews-for-woocommerce'),
                'content' => __('Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.', 'more-better-reviews-for-woocommerce'),
                'img' => wc_placeholder_img_src( $wtsr_image_size ),
                'img_full' => wc_placeholder_img_src( 'full' ),
                'img_thumbnail' => wc_placeholder_img_src( 'full' ),
            ),
            4 => array(
                'name' => __('Product 2 Title', 'more-better-reviews-for-woocommerce'),
                'content' => __('Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.', 'more-better-reviews-for-woocommerce'),
                'img' => wc_placeholder_img_src( $wtsr_image_size ),
                'img_full' => wc_placeholder_img_src( 'full' ),
                'img_thumbnail' => wc_placeholder_img_src( 'full' ),
            ),
            5 => array(
                'name' => __('Product 3 Title', 'more-better-reviews-for-woocommerce'),
                'content' => __('Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.', 'more-better-reviews-for-woocommerce'),
                'img' => wc_placeholder_img_src( $wtsr_image_size ),
                'img_full' => wc_placeholder_img_src( 'full' ),
                'img_thumbnail' => wc_placeholder_img_src( 'full' ),
            ),
            6 => array(
                'name' => __('Product 1 Title', 'more-better-reviews-for-woocommerce'),
                'content' => __('Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.', 'more-better-reviews-for-woocommerce'),
                'img' => wc_placeholder_img_src( $wtsr_image_size ),
                'img_full' => wc_placeholder_img_src( 'full' ),
                'img_thumbnail' => wc_placeholder_img_src( 'full' ),
            ),
            7 => array(
                'name' => __('Product 2 Title', 'more-better-reviews-for-woocommerce'),
                'content' => __('Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.', 'more-better-reviews-for-woocommerce'),
                'img' => wc_placeholder_img_src( $wtsr_image_size ),
                'img_full' => wc_placeholder_img_src( 'full' ),
                'img_thumbnail' => wc_placeholder_img_src( 'full' ),
            ),
            8 => array(
                'name' => __('Product 3 Title', 'more-better-reviews-for-woocommerce'),
                'content' => __('Neque integer, elementum eros. Sit explicabo arcu, in in arcu. Purus molestie nunc. Metus mus, erat sed. Tincidunt auctor ante, ante mi scelerisque, suspendisse aliquet. Ut conubia donec, id metus vestibulum. Leo in molestie, quam in per.', 'more-better-reviews-for-woocommerce'),
                'img' => wc_placeholder_img_src( $wtsr_image_size ),
                'img_full' => wc_placeholder_img_src( 'full' ),
                'img_thumbnail' => wc_placeholder_img_src( 'full' ),
            ),
        );
    }

    public static function is_any_orders_available() {
        global $wpdb;
        $filter_emails = get_option('wtsr_filter_email_domain', '');

        $sql = "
SELECT p.ID, p.post_status, p.post_type, pm.*, GROUP_CONCAT(DISTINCT pm.meta_value SEPARATOR ', ') AS meta
FROM {$wpdb->posts} AS p
LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
WHERE post_type = 'shop_order'
AND post_status NOT IN ('trash')
AND (
	(pm.meta_key IN ('_billing_email', '_created_via'))
)
GROUP BY p.ID
HAVING meta LIKE '%checkout%' AND meta NOT LIKE '%amazon%' AND meta NOT LIKE '%ebay%'";

        if (!empty($filter_emails && is_array($filter_emails))) {
            foreach ($filter_emails as $filter_email) {
                $sql .= " AND meta NOT LIKE '%{$filter_email}%'";
            }
        }

        $result = $wpdb->query($sql);

        return $result;
    }

    public static function get_email_send_via() {
        $required_plugins_wp2leads = Wtsr_Required_Plugins::get_required_plugins_wp2leads();
        $is_wp2leads_installed = Wtsr_Required_Plugins::is_plugin_installed( $required_plugins_wp2leads['slug'] ) && Wtsr_Required_Plugins::is_plugin_active( $required_plugins_wp2leads['slug'] );

        if ($is_wp2leads_installed) {
            $default_send_via = 'klick-tipp';
        } else {
            $default_send_via = 'woocommerce';
        }

        $wtsr_email_send_via = get_option('wtsr_email_send_via', $default_send_via);

        return $wtsr_email_send_via;
    }
}
