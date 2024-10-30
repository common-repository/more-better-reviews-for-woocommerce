<?php
/**
 * Modules for transfering data
 *
 * @package Wp2Leads
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Wtsr_Transfer_Review_Request {
    private static $key = 'wtsr_review_request';

    private static $required_column = 'wp2lwtsr_reviews.order_id';

    public static function transfer_init() {
        if ('woocommerce' !== TSManager::get_email_send_via()) {
            add_action( 'wtsr_new_review_item_created', 'Wtsr_Transfer_Review_Request::review_item_created', 20, 2 );
            add_action( 'wtsr_review_status_changed', 'Wtsr_Transfer_Review_Request::review_status_changed', 20, 3 );
        }
    }

    public static function get_label() {
        return __('Better Reviews for WooCommerce: New review request created', 'more-better-reviews-for-woocommerce');
    }

    public static function get_description() {
        return __('This module will transfer user data once new review request will be created');
    }

    public static function get_required_column() {
        return self::$required_column;
    }

    public static function get_instruction() {
        ob_start();
        ?>
        <p><?php _e('This module is created for Better Reviews for WooCommerce maps.', 'more-better-reviews-for-woocommerce') ?></p>
        <p><?php _e('Once new review will be request created user data will be transfered to KT account.', 'more-better-reviews-for-woocommerce') ?></p>
        <p><?php _e('Requirement: <strong>wp2lwtsr_reviews.order_id</strong> column within selected data.', 'more-better-reviews-for-woocommerce') ?></p>
        <?php

        return ob_get_clean();
    }

    public static function review_item_created( $review_id, $order_id ) {
        $is_ts_review_request_enabled = get_option('wtsr_ts_review_request_enabled');

        if (!$is_ts_review_request_enabled) {
            return true;
        }

        $review = ReviewsModel::get($review_id);

        if (!empty($review)) {
            $review = $review[0];
        }

        $status = $review->status;

        if ('ready' === $status) {
            $id = $order_id;

            self::transfer($id);
        }
    }

    public static function review_status_changed( $status, $id, $order_id ) {
        $is_ts_review_request_enabled = get_option('wtsr_ts_review_request_enabled');

        if (!$is_ts_review_request_enabled) {
            return true;
        }

        if ( 'outdated' === $status || 'reviewed' === $status ) {
            $send_email_via = TSManager::get_email_send_via();

            if ('klick-tipp' === $send_email_via) {
                self::transfer($order_id);
            }
        }
    }

    public static function transfer($id) {
        $existed_modules_map = Wp2leads_Transfer_Modules::get_modules_map();

        $condition = array(
            'tableColumn' => 'wp2lwtsr_reviews.order_id',
            'conditions' => array(
                0 => array(
                    'operator' => 'like',
                    'string' => (string) $id
                )
            )
        );

        if (!empty($existed_modules_map[self::$key])) {
            foreach ($existed_modules_map[self::$key] as $map_id => $status) {
                $result = Wp2leads_Background_Module_Transfer::module_transfer_bg($map_id, $condition);
            }
        }
    }
}

function wtsr_transfer_review_request_module($transfer_modules) {
    $transfer_modules['wtsr_review_request'] = 'Wtsr_Transfer_Review_Request';

    return $transfer_modules;
}

add_filter('wp2leads_transfer_modules', 'wtsr_transfer_review_request_module');