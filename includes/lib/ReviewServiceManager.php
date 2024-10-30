<?php

class ReviewServiceManager {
    public static $trustedshops_manager = 'TSManager';

    public static function init() {
        require_once plugin_dir_path( WTSR_PLUGIN_FILE ) . 'includes/lib/TSManager.php';

        require_once plugin_dir_path( WTSR_PLUGIN_FILE ) . 'includes/lib/TSApi.php';
    }

    private static function get_manager($service) {
        $manager = self::$trustedshops_manager;

        return $manager;
    }

    public static function get_api_review_request_body($order_id, $service = 'trustedshops') {
        $manager = self::get_manager($service);

        if ($manager::is_credentials_empty()) return false;

        $order = wc_get_order( $order_id );
        if (!$order) return false;

        $order_items = self::get_order_items($order);
        if (empty($order_items)) return false;

        $order_data = $order->get_data();
        $order_email = !empty($order_data['billing']['email']) ? $order_data['billing']['email'] : false;

        if (!$order_email) return false;

        return $manager::get_api_review_request_body($order_id, $order_data, $order_items, $order_email);
    }

    public static function get_order_items($order, $both = false) {
        if (is_array($order)) {
            $order_data = $order;
            $order_items = $order_data['line_items'];
        } else {
            $order_data = $order->get_data();
            $order_items = $order->get_items();
        }

        $order_email = $order_data['billing']['email'];
        $review_variations = get_option('wtsr_review_variations', 'no');
        $image_size = TSManager::get_default_image_size();
        $show_parent = $review_variations === 'no' ? true : false;
        $items_array = array();
        $ids_array = array();
        $skus_array = array();

        foreach ($order_items as $key => $item) {
            $product = $item->get_product();

            if (empty($product)) {
                continue;
            }
            $product_id = $product->get_id();
            $product_data = $product->get_data();
            $product_type = $product->get_type();
            $product_sku = $product_data['sku'];
            $product_slug = $product_data['slug'];
            $product_name = $product_data['name'];
            $product_url = get_permalink( $product_id );
            $product_thumbnail = get_the_post_thumbnail_url( $product_id, $image_size );

            $id = $product_id;
            $parent_id = $product_id;
            $name = $product_name;
            $sku = $product_sku;
            $slug = $product_slug;
            $url = $product_url;
            $thumbnail = $product_thumbnail;
            $product_description = get_post($product_id)->post_content;
            $type = $product_type;

            if ('variation' === $product_type) {
                $parent_product_id = $product->get_parent_id();
                $parent_product = wc_get_product( $parent_product_id );
                $parent_product_data = $parent_product->get_data();
                $parent_product_type = $parent_product->get_type();
                $parent_product_sku = $parent_product_data['sku'];
                $parent_product_slug = $parent_product_data['slug'];
                $parent_product_name = $parent_product_data['name'];
                $parent_product_url = get_permalink( $parent_product_id );
                $parent_product_thumbnail = get_the_post_thumbnail_url( $parent_product_id, $image_size );

                $parent_id = $parent_product_id;

                if (empty($id) || $show_parent) {
                    $id = $parent_product_id;
                }

                if (empty($name) || $show_parent) {
                    $name = $parent_product_name;
                }

                if (empty($sku) || $show_parent) {
                    $sku = $parent_product_sku;
                }

                if (empty($slug) || $show_parent) {
                    $slug = $parent_product_slug;
                }

                if (empty($url) || $show_parent) {
                    $url = $parent_product_url;
                }

                if (empty($thumbnail) || $show_parent) {
                    $thumbnail = $parent_product_thumbnail;
                }

                if (empty($product_description) || $show_parent) {
                    $product_description = get_post($parent_product_id)->post_content;
                }

                if (empty($type) || $show_parent) {
                    $type = $parent_product_type;
                }
            }

            if (!empty(trim($product_description))) {
                $product_description = strip_tags($product_description);
                $product_description = strip_shortcodes($product_description);
                $product_description = trim($product_description);
                $product_description = substr($product_description, 0, strrpos(substr($product_description, 0, 250), ' '));
                $product_description = $product_description . ' ... ';
            }

            if (!empty(trim($name))) {
                $name = TSManager::remove_wp_emoji($name);
                $name = str_replace('"', '', $name);
                $name = str_replace('`', '', $name);
                $name = str_replace("'", '', $name);
                $name = trim($name);
            }

            if (empty(trim($name)) && !empty($slug)) {
                $name = ucwords(str_replace('-', ' ', $slug));
            }

//            if (empty(trim($sku)) && !empty($slug)) {
//                $sku = $slug;
//            }

            if (empty(trim($sku)) && !empty($id)) {
                $sku = $id;
            }

            if (!in_array($id, $ids_array) && !in_array($sku, $skus_array)) {
                $review_open = 'open' === get_post($parent_id)->comment_status;

                if (!$review_open) {
                    $is_product_url_exists = TSManager::is_star_rating_type_exists('wtsr_product_url');

                    if (!$is_product_url_exists) {
                        $review_open = true;
                    }
                }

                if (!empty($sku) && !empty($name) && !empty($review_open)) {
                    $ids_array[] = $id;
                    $skus_array[] = $sku;

                    $items_array[] = array(
                        'id' => $id,
                        'parent_id' => $parent_id,
                        'name' => $name,
                        'sku' => $sku,
                        'slug' => $slug,
                        'url' => $url,
                        'thumbnail' => $thumbnail,
                        'product_description' => $product_description,
                        'type' => $type,
                    );
                }
            }
        }

        return $items_array;
    }

    public static function is_woocommerce_only_mode() {
        $woocommerce_only_mode_enabled = get_option('wtsr_woocommerce_only_mode_enabled', false);

        if ($woocommerce_only_mode_enabled) {
            delete_option('wtsr_ts_mode_enabled');
            return true;
        }

        $check_ts_credentials_empty = TSManager::is_credentials_empty();

        if ($check_ts_credentials_empty) {
            self::activate_woocommerce_only_mode();
            return true;
        }

        if (!$check_ts_credentials_empty) {

        }

        return false;
    }

    public static function activate_woocommerce_only_mode() {
        delete_option('wtsr_ts_mode_enabled');
        update_option('wtsr_woocommerce_only_mode_enabled', 1);
    }

    public static function deactivate_woocommerce_only_mode() {
        // TODO - Deprecated
        delete_option('wtsr_woocommerce_only_mode_enabled');
    }

    public static function merge_order_ids_from_product_reviews($service_reviews = array()) {
        if (empty($service_reviews)) return $service_reviews;

        $return_array = array();

        foreach ($service_reviews as $service_review) {
            foreach ($service_review as $order_id => $reviews) {
                if (empty($return_array[$order_id])) {
                    $return_array[$order_id] = $reviews;
                } else {
                    $return_array[$order_id] = array_merge($return_array[$order_id], $reviews);
                }
            }
        }

        return $return_array;
    }

    public static function check_product_reviews() {
        $is_ts_mode = TSManager::is_review_mode_enabled();
        $is_woocommerce_only_mode = !$is_ts_mode;

        if (!$is_woocommerce_only_mode) {
            global $wpdb;
            $hour = 12;
            $today              = date('Ymd', strtotime($hour . ':00:00'));

            $today_product_reviews = get_option('wtsr_product_reviews_' . $today);

            if (empty(get_option('wtsr_updated_product_reviews_2_6_0'))) {
                $today_product_reviews = false;

                update_option('wtsr_updated_product_reviews_2_6_0', 1);
            }

            if (
                (false === $today_product_reviews && $is_ts_mode)
            ) {
                $api = new TSApi('restricted');

                if (defined( 'WTSR_TEST_MODE' ) && WTSR_TEST_MODE) {
                    $today_product_reviews = $api->get_product_reviews();
                } else {
                    $response_product = $api->get_product_reviews_restricted();
                    $response_shop = $api->get_shop_reviews_restricted();

                    $today_product_reviews = json_encode(array(
                        'products' => $response_product,
                        'shop' => $response_shop,
                    ));
                }

                if (!empty($today_product_reviews)) {
                    $need_check = true;
                    $previous_product_reviews = $wpdb->get_row( "SELECT option_value FROM $wpdb->options WHERE option_name LIKE '%wtsr_product_reviews_%'", ARRAY_A );

                    if (!empty($previous_product_reviews)) {
                        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%wtsr_product_reviews_%'" );
                        $previous_product_reviews = $previous_product_reviews['option_value'];

                        $is_equal = strcmp($today_product_reviews, $previous_product_reviews);

                        if (0 === $is_equal) {
                            $need_check = false;
                        }
                    }

                    update_option('wtsr_product_reviews_' . $today, $today_product_reviews, false);
                }

                if ($need_check) {
                    update_option('wtsr_to_check_product_reviews', 1);
                }

                TSManager::check_if_product_reviews_reviewed_bg();
            } else {
                if (get_option('wtsr_to_check_product_reviews')) {
                    TSManager::check_if_product_reviews_reviewed_bg();
                }
            }
        } else {
            $is_last_check = get_transient('wtsr_product_reviews_last_check');

            if (empty($is_last_check)) {
                $reviews = ReviewsModel::get_sent_not_reviewed(false, OBJECT, 'id, order_id, email, status, review_created, review_sent');

                if (!empty($reviews)) {
                    foreach ($reviews as $review) {
                        $maybe_outdated = TSManager::maybe_review_outdated($review);
                    }
                }

                set_transient( 'wtsr_product_reviews_last_check', 1, 60 * 60 * 2 );
            }
        }
    }
}

ReviewServiceManager::init();