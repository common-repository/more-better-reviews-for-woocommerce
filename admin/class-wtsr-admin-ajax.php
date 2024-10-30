<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wtsr
 * @subpackage Wtsr/admin
 * @author     Tobias Conrad <tc@santegra.de>
 */
class Wtsr_Admin_Ajax {
    public function ts_enable_review_request() {
        $enable = sanitize_text_field( $_POST['enable'] );

        if ('yes' === $enable) {
            update_option('wtsr_ts_review_request_enabled', 1);

            $response = array('success' => 1, 'error' => 0, 'message' => __('Getting and tranfering review requests from Trusted Shops enabled', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        delete_option('wtsr_ts_review_request_enabled');

        $response = array('success' => 1, 'error' => 0, 'message' => __('Getting and tranfering review requests from Trusted Shops disabled', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function save_ts_credentials() {
        if (empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $params = array('message' => __('Cheating, hah?', 'more-better-reviews-for-woocommerce') );
            Wtsr_Admin_Ajax::error_response($params);
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = sanitize_text_field(trim($form_data['value']));
        }

        $validate_result = Wtsr_Settings::validate_integration_credentials($data);

        if (!empty($validate_result['error_array'])) {
            $error_message = implode(' ', $validate_result['error_array']);
            $params = array( 'message' => __('Error', 'more-better-reviews-for-woocommerce').': '.$error_message );
            Wtsr_Admin_Ajax::error_response($params);
        }

        Wtsr_Settings::update_integration_credentials($data);

        $params = array(
            'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Credentials saved', 'more-better-reviews-for-woocommerce'),
            'reload' => 1,
        );

        Wtsr_Admin_Ajax::success_response($params);
    }

    public function settings_save() {
        $settingsGroup = !empty($_POST['settingsGroup']) ? sanitize_text_field($_POST['settingsGroup']) : false;

        if (empty($settingsGroup)) {
            $params = array(
                'message' => __('Cheating, hah?', 'more-better-reviews-for-woocommerce')
            );

            Wtsr_Admin_Ajax::error_response($params);
        }

        switch ($settingsGroup) {
            case 'email_send_via':
                Wtsr_Settings::save_email_send_via();
                break;
        }
    }

    public function save_thankyou_settings() {

        if (empty($_POST['formData']) || !is_array($_POST['formData'])) {
            $params = array(
                'message' => __('Cheating, hah?', 'more-better-reviews-for-woocommerce')
            );

            Wtsr_Admin_Ajax::error_response($params);
        }

        $data = array();

        foreach ($_POST['formData'] as $form_data) {
            $data[sanitize_text_field($form_data['name'])] = $form_data['value'];
        }

        $result = Wtsr_Settings::save_thankyou_settings($data);

        if (!empty($result['errors'])) {
            $errors = implode(', ', $result['errors']);

            $params = array(
                'message' => __('Error', 'more-better-reviews-for-woocommerce') . ': ' . $errors,
            );

            Wtsr_Admin_Ajax::error_response($params);
        }

        $params = array(
            'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Settings saved.', 'more-better-reviews-for-woocommerce'),
            'reload' => 1,
        );

        Wtsr_Admin_Ajax::success_response($params);
    }

    public function test_spammyness() {
        $email = !empty($_POST['email']) ? sanitize_text_field($_POST['email']) : false;
        $review = !empty($_POST['review']) ? sanitize_text_field($_POST['review']) : false;
        $template = !empty($_POST['template']) ? sanitize_text_field($_POST['template']) : 'review_email';

        if (empty($email)) {
            $params = array('message' => __('Paste email address', 'more-better-reviews-for-woocommerce'));

            Wtsr_Admin_Ajax::error_response($params);
        }

        if (empty($review)) {
            $params = array('message' => __('No review request ID provided', 'more-better-reviews-for-woocommerce'));

            Wtsr_Admin_Ajax::error_response($params);
        }

        $review = new Wtsr_Review_Request($review);

        if ('review_email' === $template) {
            $mailer = WC()->mailer();
            $review_request_email = $mailer->emails['Wtsr_WC_Email_Review_Request'];
            $review_request_email->trigger( $review->get_order_id(), $email );
        } else {
            $mailer = WC()->mailer();
            $mail = $mailer->emails['Wtsr_WC_Email_Customer_Coupon'];
            $mail->trigger( $review->get_id(), $email );
        }

        $params = array('message' => __('Success', 'more-better-reviews-for-woocommerce'));
        Wtsr_Admin_Ajax::success_response($params);
    }

    public function send_review() {
        $review = !empty($_POST['review']) ? sanitize_text_field($_POST['review']) : false;

        if (empty($review)) {
            $params = array('message' => __('No review request ID provided', 'more-better-reviews-for-woocommerce'));

            Wtsr_Admin_Ajax::error_response($params);
        }

        $review = new Wtsr_Review_Request($review);

        $mailer = WC()->mailer();
        $review_request_email = $mailer->emails['Wtsr_WC_Email_Review_Request'];
        $review_request_email->trigger( $review->get_order_id());

        $params = array(
            'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Review request sent', 'more-better-reviews-for-woocommerce'),
            'reload' => 1,
        );
        Wtsr_Admin_Ajax::success_response($params);
    }

    public function send_selected_reviews() {
        $reviews_ids = !empty($_POST['reviews_ids']) ? sanitize_text_field( $_POST['reviews_ids'] ) : false;

        if (empty($reviews_ids)) {
            $params = array('message' => __('No reviews IDs', 'more-better-reviews-for-woocommerce'));
            Wtsr_Admin_Ajax::error_response($params);
        }

        $reviews_ids = json_decode(stripslashes($reviews_ids), true);
        $wtsr_send_woo_email_schedule_all = get_option(Wtsr_Cron::$schedule_option, array());
        $wtsr_send_woo_email_send_all = array();
        $now = time() - 100;

        foreach ($reviews_ids as $reviews_id) {
            $review = ReviewsModel::get_by_id($reviews_id);

            if (!empty($review)) {
                $review_schedule = array(
                    'email' => $review["email"],
                    'schedule' => array($now)
                );

                $wtsr_send_woo_email_send_all[$reviews_id] = $review_schedule;
            }
        }

        if (!empty($wtsr_send_woo_email_send_all)) {
            $result = Wtsr_Background_Woo_Email_Review::bg_process($wtsr_send_woo_email_send_all);
        }

        if (!empty($result)) {
            $params = array(
                'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Review request sending started in background', 'more-better-reviews-for-woocommerce'),
                'reload' => 1,
            );
            Wtsr_Admin_Ajax::success_response($params);
        } else {
            $params = array('message' => __('Review requests can not be sent right now', 'more-better-reviews-for-woocommerce'));
            Wtsr_Admin_Ajax::error_response($params);
        }

    }

    public function get_ts_reviews() {
        if (TSManager::is_credentials_empty()) {
            $params = array('message' => __('Error', 'more-better-reviews-for-woocommerce') . ': ' . __('No Trusted Shops credentials', 'more-better-reviews-for-woocommerce'), );

            Wtsr_Admin_Ajax::error_response($params);
        }

        $api = new TSApi('restricted');
        $response = $api->get_products_reviews();

        if (is_wp_error($response)) {
            $params = array('message' => __('Reviews can not be imported from Trusted Shops, please check your Trusted Shops credentials', 'more-better-reviews-for-woocommerce'));
            Wtsr_Admin_Ajax::error_response($params);
        }

        $response_shop = $api->get_shop_reviews();

        if (is_wp_error($response_shop)) {
            $params = array('message' => __('Reviews can not be imported from Trusted Shops, please check your Trusted Shops credentials', 'more-better-reviews-for-woocommerce'));
            Wtsr_Admin_Ajax::error_response($params);
        }

        if (
            empty($response["data"]["shop"]['products']) &&
            empty($response["data"]["shop"]['productReviews']) &&
            empty($response_shop["data"]["shop"]["reviews"])
        ) {
            $params = array('message' => __('No reviews for import', 'more-better-reviews-for-woocommerce'), );

            Wtsr_Admin_Ajax::success_response($params);
        }

        $shop = $response["data"]["shop"];
        $products_by_sku = array();
        $products_by_uuid = array();

        if (!empty($response_shop["data"]["shop"]["reviews"])) {
            $shop_reviews = $response_shop["data"]["shop"]["reviews"];

            foreach ($shop_reviews as $shop_review) {
                $shop_uuid = 'shop_review';

                if (empty($products_by_uuid[$shop_uuid])) {
                    $products_by_uuid[$shop_uuid] = array(
                        'sku' => '',
                        'name' => __('Shop reviews', 'more-better-reviews-for-woocommerce'),
                        'imageUrl' => '',
                        'productUrl' => '',
                        'uuid' => $shop_uuid,
                        'productReviews' => array()
                    );
                }

                $reviewer = !empty($shop_review["reviewer"]) ? $shop_review["reviewer"] : array();
                $reviewer['email'] = !empty($shop_review["consumerEmail"]) ? $shop_review["consumerEmail"] : '';

                $products_by_uuid[$shop_uuid]['productReviews'][] = array(
                    'creationDate' => $shop_review["creationDate"],
                    'comment' => $shop_review["comment"],
                    'criteria' => $shop_review["criteria"],
                    'mark' => (int) $shop_review["mark"],
                    'reviewer' => $reviewer,
                    'uuid' => $shop_review["UID"],
                );
            }
        }

        if (!empty($response["data"]["shop"]['products'])) {
            $products = $response["data"]["shop"]['products'];

            foreach ($products as $product) {
                unset($product['qualityIndicators']);
                $products_by_sku[$product['sku']] = $product;
                $products_by_uuid[$product['uuid']] = $product;
            }
        } elseif (!empty($response["data"]["shop"]['productReviews'])) {
            foreach ($response["data"]["shop"]['productReviews'] as $product_review) {
                if (!empty($product_review["product"])) {
                    $product_uuid = $product_review["product"]["uuid"];
                    $product_review_uuid = $product_review["UID"];

                    if (empty($products_by_uuid[$product_uuid])) {
                        $products_by_uuid[$product_review["product"]["uuid"]] = array(
                            'sku' => $product_review["product"]["sku"],
                            'name' => $product_review["product"]["name"],
                            'imageUrl' => $product_review["product"]["imageUrl"],
                            'productUrl' => $product_review["product"]["url"],
                            'uuid' => $product_review["product"]["uuid"],
                            'productReviews' => array(),
                        );
                    }

                    unset($product_review['product']);
                    unset($product_review['UID']);
                    $product_review['uuid'] = $product_review_uuid;
                    $products_by_uuid[$product_uuid]['productReviews'][] = $product_review;
                }
            }
        }

        $import_product_reviews = get_option('wtsr_ts_product_reviews_import', array());
        $import_product_reviews['products'] = $products_by_uuid;
        $import_product_reviews['last_update'] = current_time( 'mysql');

        update_option('wtsr_ts_product_reviews_import', $import_product_reviews);

        $params = array(
            'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Trusted Shops reviews loaded', 'more-better-reviews-for-woocommerce'),
            'url' => get_admin_url(null, 'admin.php?page=wp2leads-wtsr&tab=ts-reviews'),
        );

        Wtsr_Admin_Ajax::success_response($params);
    }

    public function map_ts_reviews() {
        $form_data = $_POST['formData'];

        $ts_to_wc_map = array();

        foreach ($form_data as $i => $form_data_item) {
            if (false !== strpos($form_data_item['name'], 'review_map_to_')) {
                $uuid_array = explode('__', $form_data_item['name']);

                if (2 === count($uuid_array)) {
                    $uuid = $uuid_array[1];
                    $ts_to_wc_map[$uuid] = $form_data_item['value'];
                }

                unset($form_data[$i]);
            }
        }

        $import_product_reviews = get_option('wtsr_ts_product_reviews_import', array());
        $import_product_reviews['mapping'] = $ts_to_wc_map;
        update_option('wtsr_ts_product_reviews_import', $import_product_reviews);

        $params = array(
            'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Settings saved', 'more-better-reviews-for-woocommerce'),
            'url' => get_admin_url(null, 'admin.php?page=wp2leads-wtsr&tab=ts-reviews'),
        );
        Wtsr_Admin_Ajax::success_response($params);
    }

    public static function delete_ts_reviews() {
        $published_reviews = Wtsr_Wc_Review::get_ts_reviews_uuid();

        if (!empty($published_reviews)) {
            $comments_id = array();

            foreach ($published_reviews as $published_review) {
                $comments_id[] = $published_review['comment_id'];

                wp_delete_comment( $published_review['comment_id'], true );
            }
        }

        $import_product_reviews = delete_option('wtsr_ts_product_reviews_import');

        $params = array(
            'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Data deleted', 'more-better-reviews-for-woocommerce'),
            'url' => get_admin_url(null, 'admin.php?page=wp2leads-wtsr&tab=ts-reviews'),
        );
        Wtsr_Admin_Ajax::success_response($params);
    }

    public static function import_ts_reviews() {
        $import_product_reviews = get_option('wtsr_ts_product_reviews_import', array());

        if (empty($import_product_reviews['mapping']) || empty($import_product_reviews['products'])) {
            $params = array(
                'message' => __('No reviews for import', 'more-better-reviews-for-woocommerce'),
            );
            Wtsr_Admin_Ajax::success_response($params);
        }

        $mapping = $import_product_reviews['mapping'];
        $products = $import_product_reviews['products'];
        $count = 0;

        if (!class_exists('Wtsr_Wc_Review')) {
            include_once plugin_dir_path( WTSR_PLUGIN_FILE ) . 'includes/lib/Wtsr_Wc_Review.php';
        }

        foreach ($mapping as $uuid => $product_id) {
            if (!empty($products[$uuid]['productReviews'])) {
                $reviews = $products[$uuid]['productReviews'];

                foreach ($reviews as $review) {
                    if (!empty($review['reviewer']['email'])) {
                        $email = $review["reviewer"]["email"];
                        $email_array = explode('@', $email);
                        $author = $email_array[0];
                        $review_date = !empty($review['creationDate']) ? strtotime($review['creationDate']) : false;

                        if (!empty($review['reviewer']['profile']['firstname'])) {
                            $author = $review['reviewer']['profile']['firstname'];

                            if (!empty($review['reviewer']['profile']['lastname'])) {
                                $author .= ' ' . $review['reviewer']['profile']['lastname'];
                            }
                        }

                        $commentdata = array(
                            'post_ID' => $product_id,
                            'content' => !empty($review["comment"]) ? sanitize_textarea_field($review["comment"]) : '',
                            'author_email' => $email,
                            'author' => $author,
                            'meta' => array(
                                'rating' => $review["mark"],
                                'wtsr_ts_uuid' => $review["uuid"],
                            )
                        );

                        if (!empty($review_date)) {
                            $commentdata['comment_date'] = date('Y-m-d H:i:s', $review_date);
                        }

                        $comment_id = Wtsr_Wc_Review::add_new_review($commentdata);
                        if (!empty($comment_id)) $count++;
                    }
                }
            }
        }

        $params = array(
            'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Reviews imported', 'more-better-reviews-for-woocommerce'),
            'url' => get_admin_url(null, 'admin.php?page=wp2leads-wtsr&tab=ts-reviews&import=success'),
            // 'reload' => 1,
        );
        Wtsr_Admin_Ajax::success_response($params);
    }

    public static function error_response($params = array()) {
        $response = array('success' => 0, 'error' => 1);

        if (!empty($params)) {
            $response = array_merge($response, $params);
        }

        echo json_encode($response);
        wp_die();
    }

    public static function success_response($params = array()) {
        $response = array('success' => 1, 'error' => 0);

        if (!empty($params) && is_array($params)) {
            $response = array_merge($response, $params);
        }

        echo json_encode($response);
        wp_die();
    }

    public function woocommerce_only_mode_enable() {
        ReviewServiceManager::activate_woocommerce_only_mode();

        $response = array('success' => 1, 'error' => 0, 'message' => __('WooCommerce only mode enabled', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function woocommerce_reviews_enable() {
        update_option( 'woocommerce_enable_reviews', 'yes' );

        delete_transient('wtsr_dismiss_woocommerce_reviews_disabled_globally_warning');

        $response = array('success' => 1, 'error' => 0, 'message' => __('WooCommerce sent your review requests active', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function woocommerce_reviews_per_product_enable() {
        global $wpdb;

        $result = $wpdb->update( $wpdb->posts,
            array( 'comment_status' => 'open' ),
            array( 'post_type' => 'product' )
        );


        if (empty($result)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __("We can't enable reviews for this products", 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        delete_transient('wtsr_dismiss_woocommerce_reviews_disabled_per_product_warning');

        $response = array('success' => 1, 'error' => 0, 'message' => __($result . ' products reviews enabled', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function dissmiss_notice() {
        $dismiss = sanitize_text_field( $_POST['dismiss'] );
        $dismiss_slug = sanitize_text_field( $_POST['dismissSlug'] );

        if (empty($dismiss) || empty($dismiss_slug)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Something went wrong', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        delete_transient('wtsr_dismiss_' . $dismiss_slug);

        if ('dismiss-week' === $dismiss) {
            set_transient('wtsr_dismiss_' . $dismiss_slug, 1, WEEK_IN_SECONDS);
        } else {
            set_transient('wtsr_dismiss_' . $dismiss_slug, 1);
        }

        $response = array('success' => 1, 'error' => 0, 'message' => __('Dismissed', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function wtsr_generate_review() {
        $order_id = sanitize_text_field( $_POST['order_id'] );

        if (empty($order_id)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No order ID', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        $result = TSManager::create_new_review_item($order_id, true);

        if ($result) {
            $response = array('success' => 1, 'error' => 0, 'message' => __('Review request generated', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 0, 'error' => 1, 'message' => __('Review request could not be created for this order', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();

    }

    public function generate_selected_reviews() {
        $order_ids = !empty($_POST['reviews_ids']) ? sanitize_text_field( $_POST['reviews_ids'] ) : false;

        if (empty($order_ids)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No order IDs', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        $order_ids = json_decode(stripslashes($order_ids), true);

        $license_version = Wtsr_License::get_license_version();

        if ('free' === $license_version) {
            $orders_count = count($order_ids);
            $limit_allowed = TSManager::get_limit_allowed();

            if (empty($limit_allowed)) {
                $response = array('success' => 0, 'error' => 1, 'message' => __('You have exceeded your Pro Version limit', 'more-better-reviews-for-woocommerce'));

                echo json_encode($response);
                wp_die();
            }

            $order_ids = array_slice($order_ids, 0, $limit_allowed, true);
        }

        $result = Wtsr_Background_Review_Request::bg_process($order_ids);

        $response = array('success' => 1, 'error' => 0, 'reload' => 1, 'message' => count($order_ids) . __(' review requests started to generate in background', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function all_reviews_page_create() {
        $title = !empty($_POST['title']) ? sanitize_text_field( $_POST['title'] ) : __('Shop review page', 'more-better-reviews-for-woocommerce');

        if (empty($title)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Page title is required', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        // '<!-- wp:shortcode -->[' . apply_filters( 'woocommerce_cart_shortcode_tag', 'woocommerce_cart' ) . ']<!-- /wp:shortcode -->'

        $post_id = wp_insert_post(  wp_slash( array(
            'post_title'   => $title,
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_author'   => wp_get_current_user(),
            'post_content'  => '<!-- wp:shortcode -->[wtsr_custom_woo_review_page]<!-- /wp:shortcode -->'
        ) ) );

        if ($post_id) {
            update_option('wtsr_all_reviews_page', $post_id);
        }

        $response = array('success' => 1, 'error' => 0, 'message' => __('Page created successfuly', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function all_reviews_page_colors_save() {
        $normal = !empty($_POST['normal']) ? sanitize_text_field( $_POST['normal'] ) : false;
        $hover = !empty($_POST['hover']) ? sanitize_text_field( $_POST['hover'] ) : false;

        $normal_button_bg = !empty($_POST['normal_button_bg']) ? sanitize_text_field( $_POST['normal_button_bg'] ) : false;
        $normal_button_txt = !empty($_POST['normal_button_txt']) ? sanitize_text_field( $_POST['normal_button_txt'] ) : false;
        $hover_button_bg = !empty($_POST['hover_button_bg']) ? sanitize_text_field( $_POST['hover_button_bg'] ) : false;
        $hover_button_txt = !empty($_POST['hover_button_txt']) ? sanitize_text_field( $_POST['hover_button_txt'] ) : false;
        $wtsr_all_reviews_page_reviews_title = !empty($_POST['wtsr_all_reviews_page_reviews_title']) ? sanitize_text_field( $_POST['wtsr_all_reviews_page_reviews_title'] ) : '';

        $uploaded_image = !empty($_POST['uploaded_image']) ? sanitize_text_field( $_POST['uploaded_image'] ) : '';
        $wtsr_all_reviews_page_description = !empty($_POST['wtsr_all_reviews_page_description']) ? sanitize_text_field( $_POST['wtsr_all_reviews_page_description'] ) : '';
        $wtsr_all_reviews_page_product_link = !empty($_POST['wtsr_all_reviews_page_product_link']) ? sanitize_text_field( $_POST['wtsr_all_reviews_page_product_link'] ) : '';

        $wtsr_all_reviews_page_footer_template_editor = !empty($_POST['all_reviews_page_footer_template_editor']) ? $_POST['all_reviews_page_footer_template_editor'] : '';

        $wtsr_all_reviews_page_reviews_min = isset($_POST['wtsr_all_reviews_page_reviews_min']) ? sanitize_text_field( $_POST['wtsr_all_reviews_page_reviews_min']) : 50;
        $wtsr_all_reviews_page_comment_placeholder = !empty($_POST['wtsr_all_reviews_page_comment_placeholder']) ? sanitize_textarea_field( $_POST['wtsr_all_reviews_page_comment_placeholder']) : '';

        if (empty($normal) || empty($hover) || empty($normal_button_bg) || empty($normal_button_txt) || empty($hover_button_bg) || empty($hover_button_txt) ) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Please, select colors for all reviews page', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        update_option('wtsr_normal_color', $normal);
        update_option('wtsr_hover_color', $hover);

        update_option('wtsr_normal_button_bg_color', $normal_button_bg);
        update_option('wtsr_normal_button_txt_color', $normal_button_txt);
        update_option('wtsr_hover_button_bg_color', $hover_button_bg);
        update_option('wtsr_hover_button_txt_color', $hover_button_txt);
        update_option('wtsr_uploaded_image', $uploaded_image);
        update_option('wtsr_all_reviews_page_description', $wtsr_all_reviews_page_description);
        update_option('wtsr_all_reviews_page_product_link', $wtsr_all_reviews_page_product_link);
        update_option('wtsr_all_reviews_page_reviews_min', $wtsr_all_reviews_page_reviews_min);
        update_option('wtsr_all_reviews_page_comment_placeholder', $wtsr_all_reviews_page_comment_placeholder);
        update_option('wtsr_all_reviews_page_footer_template_editor', wp_kses_post( $wtsr_all_reviews_page_footer_template_editor));

        if (empty($wtsr_all_reviews_page_reviews_title)) {
            update_option('wtsr_all_reviews_page_reviews_title', '');
        } else {
            update_option('wtsr_all_reviews_page_reviews_title', $wtsr_all_reviews_page_reviews_title);
        }

        $response = array('success' => 1, 'error' => 0, 'message' => __('Settings saved successfuly', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function wtsr_delete_review() {
        $review_id = sanitize_text_field( $_POST['review_id'] );

        if (empty($review_id)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No review ID', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        $result = ReviewsModel::delete($review_id);

        if (empty($result)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Nothing was deleted', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 1, 'error' => 0, 'message' => __('Review deleted', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function delete_selected_reviews() {
        $reviews_ids = !empty($_POST['reviews_ids']) ? sanitize_text_field( $_POST['reviews_ids'] ) : false;

        if (empty($reviews_ids)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('No reviews IDs', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        $reviews_ids = json_decode(stripslashes($reviews_ids), true);

        $count = 0;

        foreach ($reviews_ids as $review_id) {
            $result = ReviewsModel::delete($review_id);

            if (!empty($result)) {
                $count++;
            }
        }

        if (empty($count)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Nothing was deleted', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 1, 'error' => 0, 'message' => $count . __(' review(s) deleted', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function generate_dummy_orders() {
        $domain = !empty($_POST['domain']) ? sanitize_text_field( $_POST['domain'] ) : false;
        $number = !empty($_POST['number']) ? sanitize_text_field( $_POST['number'] ) : 10;

        if (empty($domain)) {
            $params = array('message' => __('Input email domain', 'more-better-reviews-for-woocommerce'));
            Wtsr_Admin_Ajax::error_response($params);
        }

        $domain_array = explode('@', $domain);

        if (!empty($domain_array)) {
            $count = count($domain_array);

            if (2 === $count) {
                $site = $domain_array[1];
            } else {
                $site = $domain_array[0];
            }
        }

        $selected_order_status = get_option('wtsr_order_status', 'wc-completed');
        $status = str_replace('wc-', '', $selected_order_status);

        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);
        $order = TSManager::generate_dummy_orders($site, true, 'checkout', $status);

        $params = array(
            'message' => __('Success', 'more-better-reviews-for-woocommerce') . ': ' . __('Orders generated', 'more-better-reviews-for-woocommerce'),
            'reload' => 1,
        );
        Wtsr_Admin_Ajax::success_response($params);
    }

    public function delete_all_reviews() {
        $result = ReviewsModel::deleteAll();

        if (empty($result)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Nothing was deleted', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        $response = array('success' => 1, 'error' => 0, 'message' => $result . __(' review(s) deleted', 'more-better-reviews-for-woocommerce'));

        echo json_encode($response);
        wp_die();
    }

    public function remove_license() {
        $license_email = isset($_POST['licenseEmail']) ? sanitize_email( $_POST['licenseEmail'] ) : '';
        $license_key = isset($_POST['licenseKey']) ? sanitize_text_field( $_POST['licenseKey'] ) : '';
        $license_site = isset($_POST['licenseSite']) ? sanitize_text_field( $_POST['licenseSite'] ) : '';

        if (empty($license_email) || empty($license_key)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Error: Please fill in license email and license key', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        if (empty($license_site)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Error: Please select site to remove', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        $result = Wtsr_License::remove_license($license_email, $license_key, $license_site);

        $response = array('success' => $result['success'], 'error' => $result['error'], 'message' => $result['message']);

        echo json_encode($response);
        wp_die();
    }

    public function license_action() {
        $license_email = isset($_POST['licenseEmail']) ? sanitize_email( $_POST['licenseEmail'] ) : '';
        $license_key = isset($_POST['licenseKey']) ? sanitize_text_field( $_POST['licenseKey'] ) : '';
        $license_action = isset($_POST['licenseAction']) ? sanitize_text_field( $_POST['licenseAction'] ) : '';

        if (empty($license_action) || !in_array($license_action, array('activate', 'deactivate', 'close-license'))) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Error: This action is not allowed', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        if ('close-license' === $license_action) {
            $is_wizard = Wtsr_Wizard::is_wizard();
            $result = Wtsr_License::close_license();
            $license_info = Wtsr_License::get_lecense_info();

            if ($is_wizard && 'free' !== $license_info) {
                $wizard_step = get_option('wtsr_wizard_step', false);

                update_option('wtsr_wizard_step', 'generate_reviews');
            }

            $response = array('success' => $result['success'], 'error' => $result['error'], 'message' => $result['message']);

            echo json_encode($response);
            wp_die();
        }

        if (empty($license_email) || empty($license_key)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Error: Please fill in license email and license key', 'more-better-reviews-for-woocommerce'));

            echo json_encode($response);
            wp_die();
        }

        if ('activate' === $license_action) {
            $result = Wtsr_License::activate_license($license_email, $license_key);
            $response = array('success' => $result['success'], 'error' => $result['error'], 'message' => $result['message']);

            echo json_encode($response);
            wp_die();
        }

        if ('deactivate' === $license_action) {
            $result = Wtsr_License::deactivate_license($license_email, $license_key);
            $response = array('success' => $result['success'], 'error' => $result['error'], 'message' => $result['message']);

            echo json_encode($response);
            wp_die();
        }
    }

    public function install_plugin() {
        $plugin = isset($_POST['plugin']) ? $_POST['plugin'] : '';

        if (empty($plugin)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Error', 'wp2leads-wtsr') . ': ' . __('No plugin selected to install', 'wp2leads-wtsr'));
            echo json_encode($response);

            wp_die();
        }

        if (!in_array($plugin, array('wp2leads', 'woocommerce'))) {
            echo 'error';
            echo '&&&&&';
            $response = array('success' => 0, 'error' => 1, 'message' => __('Error', 'wp2leads-wtsr') . ': ' . __('Plugin you are trying to install is not in the required list', 'wp2leads-wtsr'));
            echo json_encode($response);

            wp_die();
        }

        if ('wp2leads' === $plugin) {
            $required_plugin = Wtsr_Required_Plugins::get_required_plugins_wp2leads();
        } else {
            $required_plugin = Wtsr_Required_Plugins::get_required_plugins_woocommerce();
        }


        $result = Wtsr_Required_Plugins::install_and_activate_plugin($plugin);

        if (!$result) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Error', 'wp2leads-wtsr') . ': ' . __('Plugin could not be installed manually', 'wp2leads-wtsr'));

            echo 'error';
            echo '&&&&&';
            echo json_encode($response);
            wp_die();
        }

        $plugin_name = $required_plugin['label'];

        $response = array('success' => 1, 'error' => 0, 'message' => __('Success', 'wp2leads-wtsr') . ': ' . $plugin_name . __(' installed and activated', 'wp2leads-wtsr'));

        echo 'success';
        echo '&&&&&';
        echo json_encode($response);
        wp_die();
    }

    public function wizard_go_to_step() {
        $step = isset($_POST['step']) ? $_POST['step'] : '';

        if (empty($step)) {
            $response = array('success' => 0, 'error' => 1, 'message' => __('Error', 'wp2leads-wtsr') . ': ' . __('No step selected', 'wp2leads-wtsr'));
            echo json_encode($response);

            wp_die();
        }

        if ('complete_wizard' === $step) {
            $complete_wizard = Wtsr_Wizard::complete_wizard();

            $response = array(
                'success' => 1,
                'error' => 0,
                'message' => __('Success', 'wp2leads-wtsr'),
                'redirect' => $complete_wizard
            );
            echo json_encode($response);
            wp_die();
        }

        $result = update_option('wtsr_wizard_step', $step);

        $response = array('success' => 1, 'error' => 0, 'message' => __('Success', 'wp2leads-wtsr'));
        echo json_encode($response);
        wp_die();
    }

    public function activate_wizard() {
        $activate = Wtsr_Wizard::activate_wizard();

        $response = array('success' => 1, 'error' => 0, 'message' => __('Success', 'wp2leads-wtsr'), 'redirect' => get_admin_url(null, 'admin.php?page=wp2leads-wtsr&tab=settings'));
        echo json_encode($response);
        wp_die();
    }

    public function cancel_schedule() {
        $review = !empty($_POST['review']) ? sanitize_text_field($_POST['review']) : false;
        $schedule = !empty($_POST['schedule']) ? sanitize_text_field($_POST['schedule']) : false;

        $result = Wtsr_Cron::remove_woo_email_schedule($review, $schedule);

        if ($result) {
            $params = array(
                'message' => __('Please use only numbers devided with ":" for WooCommerce Email Delay.', 'more-better-reviews-for-woocommerce')
            );

            Wtsr_Admin_Ajax::success_response($params);
        } else {
            $params = array(
                'message' => __('Please use only numbers devided with ":" for WooCommerce Email Delay.', 'more-better-reviews-for-woocommerce')
            );

            Wtsr_Admin_Ajax::error_response($params);
        }
    }
}
