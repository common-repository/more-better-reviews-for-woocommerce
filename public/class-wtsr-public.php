<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since      0.0.1
 *
 * @package    Wtsr
 * @subpackage Wtsr/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wtsr
 * @subpackage Wtsr/public
 * @author     Tobias Conrad <tc@santegra.de>
 */
class Wtsr_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {
        global $woo_all_in_one_page_template;

        if (!empty($woo_all_in_one_page_template)) {
            wp_enqueue_style( 'more-better-reviews-for-woocommerce', plugin_dir_url( __FILE__ ) . 'css/wtsr-public.css', array(), $this->version . time(), 'all' );
        }
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'scrollTo', plugin_dir_url( __FILE__ ) . 'js/jquery.scrollTo.min.js', array( 'jquery' ), '2.1.2', true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wtsr-public.js', array( 'jquery' ), $this->version . time(), true );
        wp_localize_script( $this->plugin_name, 'wtsrJsObj', array('ajaxurl'       => admin_url( 'admin-ajax.php' )));
	}

	public function add_shortcodes() {
        add_shortcode( 'wtsr_custom_woo_review_page', array( 'Wtsr_Public', 'custom_woo_review_page' ) );
    }

    public function manage_cookies() {
	    // if all in one page

        $review_id = false;
        $review_status = '';

        if ( !empty($_GET['id']) ) {
            $id = sanitize_text_field($_GET['id']);

            $id_array = explode('_', $id);
            $order_id = $id_array[0];
            $review = ReviewsModel::get_by_order_id($order_id);

            if (!empty($review)) {
                $review_id = $review[0]->id;
                $review_status = $review[0]->status;
            }
        } elseif (!empty($_GET['ts_review'])) {
            $review = ReviewsModel::get_by_id($_GET['ts_review']);
            if (!empty($review)) {
                $review_id = $_GET['ts_review'];
                $review_status = $review['status'];
            }
        }

        if (!empty($review_id)) {
            if ('transferred' === $review_status || 'woo-sent' === $review_status) {
                setcookie('wtsr_review_id', $review_id, time() + (24 * 60 * 60), '/');
            } else {
                setcookie('wtsr_review_id', '', time() - 3600, '/');
            }
        } elseif(!empty($_COOKIE['wtsr_review_id'])) {
            $review = ReviewsModel::get_by_id($_COOKIE['wtsr_review_id']);
            $review_status = $review['status'];

            if (!empty($review)) {
                if ('transferred' === $review_status || 'woo-sent' === $review_status) {

                } else {
                    setcookie('wtsr_review_id', '', time() - 3600, '/');
                }
            }
        }
    }

    public function fake_page_redirect() {
	    if (!empty($_GET['id'])) {
            $id = sanitize_text_field($_GET['id']);

            $id_array = explode('_', $id);
            $order_id = $id_array[0];
            $review = ReviewsModel::get_by_order_id($order_id);

            if (!empty($review)) {
                $wtsr_all_reviews_page = get_option('wtsr_all_reviews_page', false);

                if (!empty($wtsr_all_reviews_page) && is_page($wtsr_all_reviews_page)) {
                    $page_url = get_permalink($wtsr_all_reviews_page);
                }
            }
        }

    }

    public function add_rewrite_rules() {
        flush_rewrite_rules();

        add_rewrite_rule('^wtsr_tracking_pixel/(.*)/([^/]*)/?', 'index.php?wtsr_tracking_pixel=1&wtsr_review_id=$matches[1]&wtsr_countdown_period=$matches[2]', 'top');
        add_rewrite_rule('^wtsr_tracking_pixel/(.*)/?', 'index.php?wtsr_tracking_pixel=1&wtsr_review_id=$matches[1]', 'top');
        add_rewrite_tag( '%wtsr_tracking_pixel%', '(.*)' );
        add_rewrite_tag( '%wtsr_review_id%', '(.*)' );
        add_rewrite_tag( '%wtsr_countdown_period%', '(.*)' );
    }

    public function display_all_in_one_review_page_title($title) {
        global $woo_all_in_one_page;

        if (!empty($woo_all_in_one_page)) {
            $title['title'] = __('Shop review page', 'more-better-reviews-for-woocommerce');
        }

	    return $title;
    }

    public function show_admin_bar($show_admin_bar) {
        global $woo_all_in_one_page_template;

        if (!empty($woo_all_in_one_page_template)) {
            return false;
        }

        return $show_admin_bar;
    }

    public function template_redirect() {
        $wtsr_tracking_pixel = get_query_var( 'wtsr_tracking_pixel' );

        if (!empty($wtsr_tracking_pixel)) {
            $wtsr_review_id = get_query_var( 'wtsr_review_id' );
            $wtsr_countdown_period = get_query_var( 'wtsr_countdown_period' );

            if (empty($wtsr_review_id) || empty($wtsr_countdown_period)) {
                return;
            }

            $review = ReviewsModel::get_by_id($wtsr_review_id);

            if (empty($review)) {
                return;
            }

            $thankyou_settings = Wtsr_Settings::get_thankyou_settings();

            $countdown_enabled = ReviewsModel::get_meta($wtsr_review_id, 'coupon_generate_countdown', true);

            if (empty($countdown_enabled)) {
                $delay = (int) $wtsr_countdown_period * 60 * 60;
                $now = time() + $delay;

                ReviewsModel::update_meta($wtsr_review_id, 'coupon_generate_countdown', $now);

                if (!empty($thankyou_settings['coupon_countdown_period_reset'])) {
                    $reset_delay = (int) $thankyou_settings['coupon_countdown_period_reset'] * 60 * 60;
                    $reset = $now + $reset_delay;
                    ReviewsModel::update_meta($wtsr_review_id, 'coupon_generate_reset', $reset);
                }
            } else {
                $countdown_reset = ReviewsModel::get_meta($wtsr_review_id, 'coupon_generate_reset', true);

                if (!empty($countdown_reset) && (int) $countdown_reset < time()) {
                    $delay = (int) $wtsr_countdown_period * 60 * 60;
                    $now = time() + $delay;

                    ReviewsModel::update_meta($wtsr_review_id, 'coupon_generate_countdown', $now);

                    if (!empty($thankyou_settings['coupon_countdown_period_reset'])) {
                        $reset_delay = (int) $thankyou_settings['coupon_countdown_period_reset'] * 60 * 60;
                        $reset = $now + $reset_delay;
                        ReviewsModel::update_meta($wtsr_review_id, 'coupon_generate_reset', $reset);
                    } else {
                        ReviewsModel::delete_meta($wtsr_review_id, 'coupon_generate_reset');
                    }
                }

            }

            ReviewsModel::update_meta($wtsr_review_id, 'email_opened', 1);

            $pixel = plugin_dir_path( WTSR_PLUGIN_FILE ) . 'public/img/1x1.png';
            $image = imagecreatefrompng($pixel);
            header('Content-Type: image/png');

            imagepng($image);
            imagedestroy($image);
            exit();
        } else {
            global $woo_all_in_one_page;
            global $woo_all_in_one_page_template;
            $woo_all_in_one_page = false;
            $woo_all_in_one_page_template = false;

            $wtsr_all_reviews_page = get_option('wtsr_all_reviews_page', false);

            if (!empty($wtsr_all_reviews_page) && is_page($wtsr_all_reviews_page)) {
                $woo_all_in_one_page_template = true;
                wtsr_show_template('wtsr-public-review-page.php');
                exit();
            } elseif ( !empty($_GET['id']) ) {
                $id = sanitize_text_field($_GET['id']);

                $id_array = explode('_', $id);
                $order_id = $id_array[0];
                $review = ReviewsModel::get_by_order_id($order_id);

                if (!empty($review)) {
                    $order_id = $review[0]->order_id;
                    $order_email = $review[0]->email;
                    $order = wc_get_order( $order_id );

                    if (!empty($order)) {
                        $is_verified = $id_array[1] === md5($order_email);

                        if ($is_verified) {
                            status_header( 200 );

                            if (!empty($wtsr_all_reviews_page) && 'publish' === get_post_status( $wtsr_all_reviews_page )) {
                                $page_url = get_permalink($wtsr_all_reviews_page);

                                $page_url .= '?id=' . $id;

                                if (!empty($_GET["rating"])) {
                                    $page_url .= '&rating=' . sanitize_text_field($_GET["rating"]);
                                }

                                if (!empty($_GET["product_id"])) {
                                    $page_url .= '&product_id=' . sanitize_text_field($_GET["product_id"]);
                                }

                                wp_redirect($page_url, 301);
                                exit;
                            }

                            $woo_all_in_one_page = true;
                            $woo_all_in_one_page_template = true;

                            wtsr_show_template('wtsr-public-review-page.php');
                            exit();
                        }
                    }
                }
            } elseif (!empty($_GET['sample'])) {
                $current_user = wp_get_current_user();
                $current_user_email = md5($current_user->user_email);
                $sample_user_email = $_GET['sample'];

                if ($current_user_email === $sample_user_email) {
                    $woo_all_in_one_page_template = true;
                    wtsr_show_template('wtsr-public-review-page.php');
                    exit();
                }
            }
        }
    }

    function maybe_apply_coupon() {
        if ( $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        if ( ! isset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ) ) {
            return;
        }

        if (empty($_GET['wtsr_coupon_code'])) {
            return;
        }

        $coupon_code = sanitize_text_field($_GET['wtsr_coupon_code']);

        if ( ! WC()->cart->has_discount( $coupon_code ) ) {
            $applied = WC()->cart->add_discount( $coupon_code );

            if ( ! $applied ) {
                if ( ! WC()->session->has_session() ) {
                    WC()->session->set_customer_session_cookie( true );
                }
            } else {
                if ( ! WC()->session->has_session() ) {
                    WC()->session->set_customer_session_cookie( true );
                }
            }
        }

        wp_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );

        exit;
    }

    public function first_review_created($review_id, $order_id) {
	    return true;
    }

    public function review_created($review_id, $order_id) {
	    return true;
    }

    public function ajax_review_submit() {
        $textarea_min_length = get_option('wtsr_all_reviews_page_reviews_min', 0);
        $order_id = !empty($_POST['order_id']) ? sanitize_text_field( $_POST['order_id'] ) : false;
        $product_id = !empty($_POST['product_id']) ? sanitize_text_field( $_POST['product_id'] ) : false;
        $review_id = !empty($_POST['review_id']) ? sanitize_text_field( $_POST['review_id'] ) : false;
        $rating = !empty($_POST['rating']) ? sanitize_text_field( $_POST['rating'] ) : false;
        $email = !empty($_POST['email']) ? sanitize_email( $_POST['email'] ) : false;
        $comment = !empty($_POST['comment']) ? sanitize_textarea_field( $_POST['comment'] ) : '';
        $dummy = !empty($_POST['dummy']);

        if (empty($comment) && !empty($textarea_min_length)) {
            $message = __('Error', 'more-better-reviews-for-woocommerce');
            $message .= ': ' . __("Comment field can't be empty", 'more-better-reviews-for-woocommerce');

            $response = array('success' => 0, 'error' => 1, 'message' => $message);

            echo json_encode($response);
            wp_die();
        }

        if (!$dummy) {
            $comment_array = array(
                'comment_post_ID'      => $product_id, // <=== The product ID where the review will show up
                'comment_content'      => $comment,
                'comment_date'         => date('Y-m-d H:i:s'),
            );
            $order = wc_get_order( $order_id );
            $order_data = $order->get_data();
            $order_first_name = ucfirst(trim($order->get_billing_first_name()));
            $order_last_name = ucfirst(trim($order->get_billing_last_name()));
            $comment_author = '';

            if (!empty($order_first_name)) {
                $comment_author .= $order_first_name;

                if (!empty($order_last_name)) {
                    $comment_author .= ' ' . substr($order_last_name, 0, 1) . '.';
                }
            }

            $user = get_user_by( 'email', $email );

            $comment_array['comment_author'] = $comment_author;
            $comment_array['comment_author_email'] = $email;
            $comment_array['comment_type'] = 'review';
            $comment_array['comment_author_url'] = '';

            if (!empty($user)) {
                $user_id = $user->ID;

                $comment_array['user_id'] = $user_id;
            }

            // $comment_id = wp_insert_comment( $comment_array );
            $comment_id = wp_new_comment( $comment_array, true );

            if (is_wp_error($comment_id)) {
                $error_string = $comment_id->get_error_message();

                $message = __('Error', 'more-better-reviews-for-woocommerce');
                $message .= ': ' . $error_string;
                $response = array('success' => 0, 'error' => 1, 'message' => $message);

                echo json_encode($response);
                wp_die();
            }

            add_comment_meta( $comment_id, 'wtsr_review_id', $review_id, true );

            $reviewed = get_option('wtsr_reviewed_by_review_id_' . $review_id, array());

            if (empty($reviewed)) {
                do_action('wtsr_first_review_created', $review_id, $order_id);
            }

            $reviewed[$product_id] = array($comment_id, $rating, $comment);

            update_option('wtsr_reviewed_by_review_id_' . $review_id, $reviewed);

            // update_comment_meta( $comment_id, 'rating', $rating );
            do_action('wtsr_review_created', $review_id, $order_id);
        }

        $review_rating = (int) $rating;

        if (1 === $review_rating) {
            $review_rating_label = __('Poor', 'more-better-reviews-for-woocommerce');
        } elseif (2 === $review_rating) {
            $review_rating_label = __('Fair', 'more-better-reviews-for-woocommerce');
        } elseif (3 === $review_rating) {
            $review_rating_label = __('Good', 'more-better-reviews-for-woocommerce');
        } elseif (4 === $review_rating) {
            $review_rating_label = __('Very good', 'more-better-reviews-for-woocommerce');
        } elseif (5 === $review_rating) {
            $review_rating_label = __('Excellent', 'more-better-reviews-for-woocommerce');
        }

        ob_start();
        ?>
        <p style="margin-bottom:0;margin-top:0;">
            <?php echo __('Your rating', 'more-better-reviews-for-woocommerce'); ?>: <strong class="rating_label"><?php echo $review_rating_label; ?></strong>
        </p>

        <div class="comment-form-comment-text" style="margin-bottom:0;margin-top:0;">
            <label><?php echo __('Your review', 'more-better-reviews-for-woocommerce'); ?>:</label>

            <div class="text-holder">
                <?php echo wpautop($comment); ?>
            </div>
        </div>

        <div class="thankyou_message">
            <?php echo __('Thankyou for review!', 'more-better-reviews-for-woocommerce'); ?>
        </div>
        <?php
        $review_content = ob_get_clean();

        $message = __('Success', 'more-better-reviews-for-woocommerce');
        $message .= ': ' . __('Your review saved!', 'more-better-reviews-for-woocommerce');
        $response = array('success' => 1, 'error' => 0, 'message' => $message, 'review_comment' => $review_content);

        echo json_encode($response);
        wp_die();
    }

    public function woocommerce_review_display_meta() {
        global $comment;
        $verified = wc_review_is_from_verified_owner( $comment->comment_ID );

        if ( '0' !== $comment->comment_approved && !$verified ) {
            $wtsr_ts_uuid = get_comment_meta($comment->comment_ID, 'wtsr_ts_uuid', true);

            if (empty($wtsr_ts_uuid)) return '';

            $ts_credentials = TSManager::get_ts_credentials();

            if (!empty($ts_credentials["ts_id"])) {
                $proved_by_ts_text = '<a href="https://www.trustedshops.de/bewertung/info_'.$ts_credentials["ts_id"].'.html" target="_blank">'.__( 'proved by Trusted Shops', 'more-better-reviews-for-woocommerce' ).'</a>';
            } else {
                $proved_by_ts_text = __( 'proved by Trusted Shops', 'more-better-reviews-for-woocommerce' );
            }
            ?>
            <p class="wtsr_ts_meta" style="margin-top: 3px; margin-bottom: 3px;">
                (<em class="woocommerce-review__verified verified">
                    <?php _e( 'verified owner', 'woocommerce' ); ?>,
                    <?php echo $proved_by_ts_text; ?>,
                </em>)
            </p>
            <?php
        }
    }

    static public function custom_woo_review_page() {
	    return wtsr_get_template('shortcodes/woo_review_page.php');
    }
}
