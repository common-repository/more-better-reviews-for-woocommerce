<?php
class Wtsr_Template {
    public static function preview_email_html_template($review) {
        if ('woocommerce' === TSManager::get_email_send_via()) {
            $order_id = $review->order_id;

            add_filter('wtsr_max_email_template_length', 'wtsr_unlim_email_template_length');
            $wtsr_email_template_editor = Wtsr_Template::get_review_request_html($order_id, false);
            remove_filter('wtsr_max_email_template_length', 'wtsr_unlim_email_template_length');

            echo wpautop($wtsr_email_template_editor);
        } else {
            echo wpautop($review->review_message);
        }
    }

    public static function get_email_plain_template($order_id, $review_link) {
        $order = wc_get_order( $order_id );
        if (!$order) return '';

        $order_items = TSManager::get_order_items($order);

        if (empty($order_items)) return '';
        $order_data = $order->get_data();

        $table_template = '';
        $length = 0;

        foreach ($order_items as $key => $item) {
            $name = $item['name'];
            $post_content = $item['product_description'];

            $star_ratings = self::get_stars_html($item, $order_data, ReviewsModel::get_by('order_id', $order_id), false);

            ob_start();
            echo '-------------------------------------' . PHP_EOL; ?>
    <?php echo ">> " . $name . " <<" . PHP_EOL;
    echo !empty($post_content) ? $post_content . PHP_EOL : '';
    echo '-------------------------------------' . PHP_EOL; ?>
    <?php echo __('Your rating', 'more-better-reviews-for-woocommerce') . ':' . PHP_EOL . PHP_EOL;
    echo implode('', $star_ratings); ?>
<?php
            echo PHP_EOL;
            $table_template_part = ob_get_clean();

            $table_template_length = strlen($table_template) + strlen($table_template_part);
            $max_length = TSManager::get_max_email_template_length() - $length;

            if ($max_length > $table_template_length) {
                $table_template .= $table_template_part;
            }
        }

        return $table_template;
    }

    /**
     * @param $order_id
     * @param bool $is_live
     * @param array $args
     *
     * @return string
     */
    public static function get_review_request_html($order_id, $is_live = true, $args = array()) {
        $order = wc_get_order( $order_id );
        if (empty($order)) return '';
        $review = ReviewsModel::get_by('order_id', $order_id);
        if (empty($review)) return '';

        $length = 0;
        $wtsr_image_size = TSManager::get_default_image_size();
        $order_data = $order->get_data();
        $html_template = get_option('wtsr_email_template', TSManager::get_default_email_template_editor());
        $html_template = self::replace_review_request_shortcodes($html_template, $order_data);
        $coupon_countdown_pixel = empty($is_live) ? '' : Wtsr_Template::get_coupon_countdown_pixel($order_id);
        $html_template .= $coupon_countdown_pixel;
        $length = strlen($html_template) + $length;

        if (false !== strpos($html_template, '{single_review}')) {
            $order_items_table = Wtsr_Template::get_single_review_html($order_data, $review, $length);
            $html_template = str_replace( '{single_review}', $order_items_table, $html_template );
        }

        $length = strlen($html_template) + $length;

        if (false !== strpos($html_template, '{order_items_table_one_col}')) {
            $order_items_table = Wtsr_Template::get_order_items_table_one_col_html($order_data, $review, $length);
            $html_template = str_replace( '{order_items_table_one_col}', $order_items_table, $html_template );
        }

        $length = strlen($html_template) + $length;

        if (false !== strpos($html_template, '{order_items_table}')) {
            if ('no_image_template' !== $wtsr_image_size)
                  $order_items_table = Wtsr_Template::get_order_items_table_two_col_html($order_data, $review, $length);
            else  $order_items_table = Wtsr_Template::get_order_items_table_one_col_html($order_data, $review, $length);

            $html_template = str_replace( '{order_items_table}', $order_items_table, $html_template );
        }

        return $html_template;
    }

    /**
     * {single_review} - stars and buttons only one review
     *
     * @param $order
     * @param $review
     * @param $length
     *
     * @return string
     */
    public static function get_single_review_html($order, $review, $length) {
        $order_id = $order['id'];
        $order_items = TSManager::get_order_items($order);
        if (empty($order_items)) return '';

        $template = '';
        $item = $order_items[0];
        $star_ratings = self::get_stars_html($item, $order, $review);

        ob_start();
        ?><div class="items_table"><div style="padding:5px 0 25px 0;"><div style="text-align:left;"><?php echo implode('', $star_ratings); ?></div></div></div><?php
        $template_part = ob_get_clean();

        $table_template_length = strlen($template) + strlen($template_part);
        $max_length = TSManager::get_max_email_template_length() - $length;
        if ($max_length > $table_template_length) $template .= $template_part;

        return $template;
    }

    /**
     * {order_items_table_one_col} - products list in order (better for mobile devices view)
     *
     * @param $order
     * @param $review
     * @param $length
     *
     * @return string
     */
    public static function get_order_items_table_one_col_html($order, $review, $length) {
        $order_id = $order['id'];
        $order_items = TSManager::get_order_items($order);
        if (empty($order_items)) return '';

        $table_template = '';
        $table_template_length = 0;

        $wtsr_image_size = TSManager::get_default_image_size();

        foreach ($order_items as $key => $item) {
            $id = $item['id'];
            $parent_id = !empty($item['parent_id']) ? $item['parent_id'] : $id;
            $slug = $item['slug'];
            $name = $item['name'];
            $post_content = $item['product_description'];
            $thumbnail = !empty($item['thumbnail']) ? $item['thumbnail'] : wc_placeholder_img_src( $wtsr_image_size );
            $star_ratings = self::get_stars_html($item, $order, $review);
            ob_start();
            if ('no_image_template' !== $wtsr_image_size) {
                ?><div class="items_table" style="max-width:800px;"><div style="text-align:left;"><img src="<?php echo $thumbnail; ?>" alt="<?php echo !empty($name) ? $name : $slug; ?>" style="max-width:100%;border:1px solid #eee;">
</div><?php
            } else {
                ?><div class="items_table" style="max-width:800px;"><?php
            }
            ?><div><div style="padding:5px 10px;"><?php
                    if (!empty($name)) {
                        ?><h4 style="margin-top:5px; margin-bottom: 5px;"><?php echo $name; ?></h4><?php
                    }
                    ?><p style="margin-top:5px; margin-bottom:5px;"><?php echo $post_content; ?></p></div><div>
                    <div style="padding:5px 10px 25px 10px;">
                        <p style="text-align:left;font-weight:bold;margin-top:0;"><?php _e('Your rating', 'more-better-reviews-for-woocommerce') ?></p>
                        <div style="text-align:left;"><?php echo implode('', $star_ratings); ?></div>
                    </div>
                </div>
            </div></div><?php
            $table_template_part = ob_get_clean();

            $table_template_length = strlen($table_template) + strlen($table_template_part);
            $max_length = TSManager::get_max_email_template_length() - $length;

            if ($max_length > $table_template_length) {
                $table_template .= $table_template_part;
            }
        }

        return $table_template;
    }

    /**
     * {order_items_table} - products list in order (better for desktop view)
     *
     * @param $order
     * @param $review
     * @param $length
     *
     * @return string
     */
    public static function get_order_items_table_two_col_html($order, $review, $length) {
        $order_id = $order['id'];
        $order_items = TSManager::get_order_items($order);
        if (empty($order_items)) return '';

        $table_template = '';
        $table_template_length = 0;
        $max_length = TSManager::get_max_email_template_length() - $length;

        $wtsr_image_size = TSManager::get_default_image_size();

        ob_start();
        ?><div class="items_table_header" style="max-width:800px;">
        <div style="width:50%;float:left;">
            <p style="text-align:center;font-weight:bold;"><?php _e('Image', 'more-better-reviews-for-woocommerce') ?></p>
        </div><div style="width:50%;float:left;">
            <p style="text-align:center;font-weight:bold;"><?php _e('Description', 'more-better-reviews-for-woocommerce') ?></p>
        </div><div style="clear:both;"></div>
        </div><?php
        $table_template_header = ob_get_clean();
        $table_template .= $table_template_header;

        $table_template_length = strlen($table_template);

        if ($max_length <= $table_template_length) return '';

        foreach ($order_items as $key => $item) {
            $id = $item['id'];
            $parent_id = !empty($item['parent_id']) ? $item['parent_id'] : $id;
            $slug = $item['slug'];
            $name = $item['name'];
            $post_content = $item['product_description'];
            $thumbnail = $item['thumbnail'];

            if (!$thumbnail) {
                $thumbnail = wc_placeholder_img_src( $wtsr_image_size );
            }

            $star_ratings = self::get_stars_html($item, $order, $review);

            ob_start();
            if ('no_image_template' !== $wtsr_image_size) {
                ?><div class="items_table" style="max-width:800px;"><div style="width:50%;float:left;text-align:center;"><img
                        src="<?php echo $thumbnail; ?>" alt="<?php echo !empty($name) ? $name : $slug; ?>" style="max-width:100%;border:1px solid #eee;"></div><div
                style="width:50%;float:left;">
                <?php
            } else {
                ?><div class="items_table" style="max-width:800px;"><div style="width:100%;float:left;">
                <?php
            }
            ?><div style="padding:5px 10px;"><?php
                    if (!empty($name)) {
                        ?><h4 style="margin-top:5px; margin-bottom: 5px;"><?php echo $name; ?></h4><?php
                    }
                    ?><p style="margin-top:5px; margin-bottom:5px;"><?php echo $post_content; ?></p></div><div>
                    <div style="padding:5px 10px 25px 10px;">
                        <p style="text-align:center;font-weight:bold;margin-top:0;"><?php _e('Your rating', 'more-better-reviews-for-woocommerce') ?></p>
                        <div style="text-align:center;"><?php echo implode('', $star_ratings); ?></div>
                    </div>
                </div>
            </div><div style="clear:both;"></div>
            </div><?php
            $table_template_part = ob_get_clean();

            $table_template_length = strlen($table_template) + strlen($table_template_part);
            $max_length = TSManager::get_max_email_template_length() - $length;

            if ($max_length > $table_template_length) {
                $table_template .= $table_template_part;
            }
        }

        return $table_template;
    }

    public static function get_stars_html($item, $order, $review, $is_html = true) {
        $wtsr_rating_links = get_option('wtsr_rating_links', ReviewsModel::get_default_rating_links());
        $wtsr_custom_rating_links = $wtsr_rating_links['custom_link'];
        unset($wtsr_rating_links['custom_link']);
        $wtsr_rating_links_reverse = array_reverse($wtsr_rating_links);
        $star_review_title = TSManager::get_star_review_title();

        $is_woocommerce_reviews_disabled_globally = TSManager::is_woocommerce_reviews_disabled_globally();
        $is_ts_mode = TSManager::is_review_mode_enabled();

        $wtsr_all_reviews_page = get_option('wtsr_all_reviews_page', true);

        $id = $item['id'];
        $parent_id = !empty($item['parent_id']) ? $item['parent_id'] : $id;
        $slug = $item['slug'];
        $name = $item['name'];

        $order_id = $order['id'];
        $order_email = !empty($order['billing']['email']) ? $order['billing']['email'] : '';
        $order_first_name = !empty($order['billing']['first_name']) ? $order['billing']['first_name'] : '';
        $order_last_name = !empty($order['billing']['last_name']) ? $order['billing']['last_name'] : '';

        $plugin_url = untrailingslashit( plugins_url( '/', WTSR_PLUGIN_FILE ) );
        $button_colors = TSManager::get_button_colors();

        $ts_link = !empty($review["review_meta"]["trustedshops_review_link"][0]) ? $review["review_meta"]["trustedshops_review_link"][0] : (!empty($review["review_link"]) ? $review["review_link"] : '');

        $star_ratings = array();

        foreach ($wtsr_rating_links_reverse as $star => $rating_link) {
            if (defined('WTSR_PRESENTATION') && WTSR_PRESENTATION) {
                $rating_link = 'wtsr_ts_review_link';
            } else {
                $rating_link = 'wtsr_all_reviews_page';

                if ((!$is_ts_mode || empty($ts_link)) && 'wtsr_ts_review_link' === $rating_link) {
                    if ($wtsr_all_reviews_page)     $rating_link = 'wtsr_all_reviews_page';
                    else                            $rating_link = 'wtsr_product_url';
                }

                if ($is_woocommerce_reviews_disabled_globally && ('wtsr_product_url' === $rating_link || 'wtsr_all_reviews_page' === $rating_link)) {
                    $rating_link = 'wtsr_custom_link';
                }
            }

            $link = '';

            if ($wtsr_all_reviews_page && 'wtsr_all_reviews_page' === $rating_link) {
                if (true === $wtsr_all_reviews_page || empty(get_post($wtsr_all_reviews_page)))
                        $link = $link = home_url( '/all-in-one-woo-review-page' );
                else    $link = get_permalink( $wtsr_all_reviews_page );

                $link .= '?id=' . $order_id . '_' . md5($order_email) . '&rating=' . $star . '&product_id=' . $id;
            } elseif ('wtsr_ts_review_link' === $rating_link) {
                $link = $ts_link;
            } elseif ('wtsr_product_url' === $rating_link) {
                $link = get_permalink( $parent_id );
                $link_parse = parse_url($link);
                $query = "ts_review={$review['id']}&ts_order_id={$order_id}&ts_rating={$star}";

                if (!empty($link_parse['query']))   $link .= '&' . $query;
                else                                $link .= '?' . $query;
            } elseif ('wtsr_custom_link' === $rating_link) {
                $link = $wtsr_custom_rating_links[$star];
                $link = str_replace('{order_number}', $order_id, $link);
                $link = str_replace('{order_number_base64}', base64_encode($order_id), $link);
                $link = str_replace('{product_id}', $id, $link);
                $link = str_replace('{product_slug}', $slug, $link);
                $link = str_replace('{product_title}', $name, $link);
                $link = str_replace('{customer_fn}', $order_first_name, $link);
                $link = str_replace('{customer_ln}', $order_last_name, $link);
                $link = str_replace('{customer_email}', $order_email, $link);
                $link = str_replace('{customer_email_base64}', base64_encode($order_email), $link);
            }

            if ($is_html) {
            ob_start();
            ?><div style="margin-bottom:15px;"><a
                    href="<?php echo $link; ?>"
                    target="_blank"
                    title="<?php echo $star_review_title[$star] ?>"
            ><img src="<?php echo $plugin_url . '/admin/img/'.$star.'.png'; ?>" alt="<?php echo $star_review_title[$star] ?>" style="vertical-align:sub;"></a> <a
                class="<?php echo $star; ?>_rating"
                href="<?php echo $link; ?>"
                target="_blank"
                title="<?php echo $star_review_title[$star] ?>"
                style="display:inline-block!important;width:200px!important;max-width:100%!important;background-color:<?php echo $button_colors['bg_color']; ?>!important;color:<?php echo $button_colors['text_color']; ?>!important;text-decoration:none!important;line-height: 16px!important;font-size: 16px!important;padding:8px 0!important;border-radius:20px!important;text-align:center!important;margin-right:10px!important;"
            ><?php echo $star_review_title[$star] ?></a></div><?php
            $star_item = ob_get_clean();
            } else {
                ob_start();
                ?>
    <?php echo $star_review_title[$star] . ":" . PHP_EOL ?>
    <?php echo $link . PHP_EOL; ?><?php
                $star_item = ob_get_clean();
            }

            $star_ratings[] = $star_item;
        }

        return $star_ratings;
    }

    /**
     * @param $template
     * @param $order_data
     *
     * @return mixed|string|string[]
     */
    private static function replace_review_request_shortcodes($template, $order_data) {
        $order_id = $order_data['id'];
        $customer_fn = !empty($order_data['billing']['first_name']) ? $order_data['billing']['first_name'] : '';
        $customer_ln = !empty($order_data['billing']['last_name']) ? $order_data['billing']['last_name'] : '';
        $order_date = $order_data['date_created']->date(get_option( 'date_format' ));

        // Change Placeholder {order_number}
        if (false !== strpos($template, '{order_number}')) {
            $template = str_replace( '{order_number}', $order_id, $template );
        }

        // Change Placeholder {customer_fn}
        if (false !== strpos($template, '{customer_fn}')) {
            $template = str_replace( '{customer_fn}', $customer_fn, $template );
        }

        // Change Placeholder {customer_ln}
        if (false !== strpos($template, '{customer_ln}')) {
            $template = str_replace( '{customer_ln}', $customer_ln, $template );
        }

        // Change Placeholder {order_date}
        if (false !== strpos($template, '{order_date}')) {
            $template = str_replace( '{order_date}', $order_date, $template );
        }

        if (false !== strpos($template, '{coupon_countdown_description}')) {
            $coupon_countdown_description = Wtsr_Template::get_coupon_countdown_description();
            $template = str_replace( '{coupon_countdown_description}', $coupon_countdown_description, $template );
        }

        return $template;
    }

    public static function get_email_thankyou_html_template($review_id, $is_live = true) {
        $wtsr_thankyou_settings = Wtsr_Settings::get_thankyou_settings();

        if (
            'no' === $wtsr_thankyou_settings["thankyou_enabled"] ||
            '' === trim($wtsr_thankyou_settings["thankyou_template"])
        ) {
            return '';
        }

        // Check if review exists
        if (empty($review = ReviewsModel::get_by_id($review_id))) return false;

        // Check if order exists
        if (empty($order = wc_get_order( $review['order_id'] ))) return false;

        // Check if email exists
        if (empty($email = $review['email'])) return false;

        $thankyou_template = wp_unslash($wtsr_thankyou_settings["thankyou_template"]);

        $coupon_id = false;
        $coupon_description = '';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'lib/Wtsr_Coupon.php';

        if ($is_live) {
            if (Wtsr_Coupon::is_coupon_generation_enabled($review_id)) $coupon_id = wtsr_generate_coupon($review_id, $email);

            if (!empty($coupon_id)) {
                $coupon = new WC_Coupon( $coupon_id );
                $coupon_description = wpautop($coupon->get_description());
            }
        } else {
            $sample_coupon = Wtsr_Coupon::generate_sample_coupon();
            $coupon_description = $sample_coupon['description'];
        }

        if (false !== strpos($thankyou_template, '{coupon_description}')) {
            $thankyou_template = str_replace( '{coupon_description}', $coupon_description, $thankyou_template );
        }

        return $thankyou_template;
    }

    public static function get_coupon_countdown_pixel($order_id, $show = false) {
        $review = ReviewsModel::get_by_order_id($order_id);
        $pixel_expiration = Wtsr_Settings::is_pixel_enabled();

        if (empty($review) || empty($pixel_expiration)) {
            $pixel = '';
        } else {
            $review_id = $review[0]->id;

            $link = get_site_url(null, '/') . 'wtsr_tracking_pixel/' . $review_id . '/' . $pixel_expiration;

            ob_start();
            ?>
            <img src="<?php echo $link; ?>" alt="">
            <?php
            $pixel = ob_get_clean();
        }

        if (empty($show)) {
            return $pixel;
        }

        echo $pixel;
    }

    public static function get_coupon_countdown_description() {
        $thankyou_settings = Wtsr_Settings::get_thankyou_settings();

        if (
            'no' === $thankyou_settings['thankyou_enabled'] ||
            'none' === $thankyou_settings['discount_type'] ||
            '' === $thankyou_settings['coupon_amount'] ||
            'no' === $thankyou_settings['coupon_countdown'] ||
            '' === $thankyou_settings['coupon_countdown_period'] ||
            empty($thankyou_settings['coupon_countdown_description'])
        ) {
            return '';
        }

        $description = wp_unslash($thankyou_settings['coupon_countdown_description']);

        if (false !== strpos($description, '{coupon_countdown_period}')) {
            $description = str_replace('{coupon_countdown_period}', $thankyou_settings['coupon_countdown_period'], $description);
        }

        return $description;
    }
}