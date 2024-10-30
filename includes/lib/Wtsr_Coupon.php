<?php
/**
 * Class Wtsr_Coupon
 *
 * Library for generating coupons after review
 */

class Wtsr_Coupon {
    public static function generate_sample_coupon() {
        $code = Wtsr_Coupon::generate_unique_code();
        $code = 'WTSRCODE';
        $wtsr_thankyou_settings = Wtsr_Settings::get_thankyou_settings();
        $coupon_description = $wtsr_thankyou_settings["coupon_description"];

        $coupon_description = str_replace( '{coupon_code}', strtolower($code), $coupon_description );
        $coupon_description = str_replace( '{coupon_url}', get_permalink( wc_get_page_id( 'shop' ) ) . '?wtsr_coupon_code=' . $code, $coupon_description );

        if (!empty($wtsr_thankyou_settings["coupon_expiration"])) {
            $date_expires = ((int) $wtsr_thankyou_settings["coupon_expiration"] * 60 * 60) + time();
            $expiry_date = date('Y-m-d', $date_expires);
            $coupon_expiration = $wtsr_thankyou_settings["coupon_expiration"];
        }

        if (!empty($date_expires)) {
            $datetime = new WC_DateTime( "@{$date_expires}", new DateTimeZone( 'UTC' ) );

            if ( get_option( 'timezone_string' ) ) {
                $datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );
            } else {
                $datetime->set_utc_offset( wc_timezone_offset() );
            }

            $date_expires = wc_format_datetime($datetime);
            $hours_expires = $wtsr_thankyou_settings["coupon_expiration"];
        } else {
            $date_expires = '';
            $hours_expires = '';
        }

        $coupon_description = str_replace( '{coupon_date_time_expires}', $date_expires , $coupon_description );
        $coupon_description = str_replace( '{coupon_hours}', $hours_expires , $coupon_description );

        // TODO - WTSR Change description
        return array(
            'code' => $code,
            'description' => $coupon_description
        );
    }

    public static function generate_coupon($data = array()) {
        // Generate unique coupon code
        $code = Wtsr_Coupon::generate_unique_code();

        $coupon = array(
            'post_title' => $code,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'shop_coupon',
        );

        if (!empty($data['post_excerpt'])) {
            $coupon['post_excerpt'] = $data['post_excerpt'];

            $coupon['post_excerpt'] = str_replace( '{coupon_code}', strtolower($code), $coupon['post_excerpt'] );
            $coupon['post_excerpt'] = str_replace( '{coupon_url}', get_permalink( wc_get_page_id( 'shop' ) ) . '?wtsr_coupon_code=' . $code, $coupon['post_excerpt'] );

            if (!empty($data["date_expires"])) {
                $datetime = new WC_DateTime( "@{$data["date_expires"]}", new DateTimeZone( 'UTC' ) );

                if ( get_option( 'timezone_string' ) ) {
                    $datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );
                } else {
                    $datetime->set_utc_offset( wc_timezone_offset() );
                }

                $date_expires = wc_format_datetime($datetime);
                $hours_expires = $data["coupon_expiration"];
            } else {
                $date_expires = '';
                $hours_expires = '';
            }

            $coupon['post_excerpt'] = str_replace( '{coupon_date_time_expires}', $date_expires , $coupon['post_excerpt'] );
            $coupon['post_excerpt'] = str_replace( '{coupon_hours}', $hours_expires , $coupon['post_excerpt'] );

            unset($data['post_excerpt']);
        }

        $new_coupon_id = wp_insert_post( $coupon );
        $data = Wtsr_Coupon::get_coupon_settings($data);


        // Write the $data values into postmeta table
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                update_post_meta( $new_coupon_id, $key, $value );
            }
        }

        return $new_coupon_id;
    }

    public static function generate_unique_code($prefix = '', $n = 8) {
        global $wpdb;
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        $exists = true;

        while($exists) {
            for ($i = 0; $i < $n; $i++) {
                $index = rand(0, strlen($characters) - 1);
                $randomString .= $characters[$index];
            }

            $code = $prefix . $randomString;

            $sql = "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' ORDER BY post_date DESC LIMIT 1;";
            $sql_prepared = $wpdb->prepare( $sql , $code );
            $coupon_id = $wpdb->get_var( $sql_prepared );

            if ( empty( $coupon_id ) ) {
                $exists = false;
            }
        }

        return $code;
    }

    public static function get_coupon_settings($data) {
        $settings = array(
            'discount_type'              => 'percent', // percent, fixed_cart
            'coupon_amount'              => '', // value
            'individual_use'             => 'yes',
            'product_ids'                => '', // 2359,1258,2598
            'exclude_product_ids'        => '',
            'usage_limit'                => '',
            'usage_limit_per_user'       => '1',
            'limit_usage_to_x_items'     => '',
            'usage_count'                => '',
            'expiry_date'                => '',
            'date_expires'                => '', // YYYY-MM-DD
            'free_shipping'              => 'no',
            'product_categories'         => array(),
            'exclude_product_categories' => array(),
            'exclude_sale_items'         => 'no',
            'minimum_amount'             => '',
            'maximum_amount'             => '',
            'customer_email'             => '' // array()
        );

        return array_merge($settings, $data);
    }

    public static function is_coupon_generation_enabled($review_id) {
        $countdown_enabled = ReviewsModel::get_meta($review_id, 'coupon_generate_countdown', true);

        if (!empty($countdown_enabled) && time() > (int) $countdown_enabled) {
            return false;
        }

        $wtsr_thankyou_settings = Wtsr_Settings::get_thankyou_settings();

        if (
            'none' === $wtsr_thankyou_settings["discount_type"] ||
            empty($wtsr_thankyou_settings["coupon_amount"]) ||
            empty(trim(wp_unslash($wtsr_thankyou_settings["coupon_description"])))
        ) {
            return false;
        }

        return true;
    }
}