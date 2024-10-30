<?php
$sample = !empty($_GET['sample']) ? sanitize_text_field($_GET['sample']) : false;
$order_id = !empty($_GET['order_id']) ? sanitize_text_field($_GET['order_id']) : false;
$id = !empty($_GET['id']) ? sanitize_text_field($_GET['id']) : false;
$review_product_id = !empty($_GET['product_id']) ? sanitize_text_field($_GET['product_id']) : false;
$review_link_rating = !empty($_GET['rating']) ? sanitize_text_field($_GET['rating']) : false;
$wtsr_hover_colors = TSManager::get_hover_colors();
$wtsr_aiop_button_colors = TSManager::get_aiop_button_colors();
$wtsr_all_reviews_page_product_link = get_option('wtsr_all_reviews_page_product_link', 'no');

$wtsr_all_reviews_page_description = get_option('wtsr_all_reviews_page_description', false);
$wtsr_all_reviews_page_reviews_title = get_option('wtsr_all_reviews_page_reviews_title');

if ( false === $wtsr_all_reviews_page_reviews_title ) {
    $wtsr_all_reviews_page_reviews_title = __( 'Review now!', 'more-better-reviews-for-woocommerce' );
}

if (!$wtsr_all_reviews_page_description) {
    $wtsr_all_reviews_page_description = 'yes';
}

if (!$sample && !$order_id && !$id) {
    wtsr_show_template('shortcodes/woo_review_page_empty.php');
    return;
}

$textarea_min_length = get_option('wtsr_all_reviews_page_reviews_min', 50);
$textarea_max_length = get_option('wtsr_all_reviews_page_reviews_max', 500);

$textarea_placeholder = get_option('wtsr_all_reviews_page_comment_placeholder', false);

if (false === $textarea_placeholder) {
    ob_start();
    ?><?php echo __( 'Describe your experiences with the product here', 'more-better-reviews-for-woocommerce' );
    echo PHP_EOL . PHP_EOL . __( 'Why did you choose this product?', 'more-better-reviews-for-woocommerce' );
    echo PHP_EOL . __( 'What did you like in particular?', 'more-better-reviews-for-woocommerce' );
    echo PHP_EOL . __( 'Would you recommend this product?', 'more-better-reviews-for-woocommerce' ); ?><?php
    $textarea_placeholder = ob_get_clean();
}

$two_col = true;
?>
<style>
    .wtsr_order_item img {
        max-width: 100% !important;
        height: auto !important;
    }
    .wtsr_order_item_header {
        color: <?php echo $wtsr_hover_colors['normal'] ?>;
        transition: all 0.3s;
    }
    .wtsr_order_item_header:hover {
        color: <?php echo $wtsr_hover_colors['hover'] ?>
    }
    .wtsr_order_items .wtsr_order_item_header .wtsr_order_item_header_btn {
        color: <?php echo $wtsr_hover_colors['normal'] ?>;
    }
    .wtsr_order_items .wtsr_order_item_header:hover .wtsr_order_item_header_btn {
        color: <?php echo $wtsr_hover_colors['hover'] ?>;
    }
    .wtsr_order_item_body .comment-form-submit{
        display: block !important;
        float: none !important;
    }
    .wtsr_order_item_body .comment-form-submit .wtsr_review_submit,
    .wtsr_order_item_body .comment-form-submit .wtsr_review_submit_dummy {
        color: <?php echo $wtsr_aiop_button_colors['normal_txt'] ?>;
        background-color: <?php echo $wtsr_aiop_button_colors['normal_bg'] ?>;
        border: none;
        padding: 10px 15px;
    }
    .wtsr_order_item_body .comment-form-submit .wtsr_review_submit:hover,
    .wtsr_order_item_body .comment-form-submit .wtsr_review_submit_dummy:hover {
        color: <?php echo $wtsr_aiop_button_colors['hover_txt'] ?>;
        background-color: <?php echo $wtsr_aiop_button_colors['hover_bg'] ?>;
    }
    .wtsr_order_item_body .comment-form-submit .wtsr_review_submit.disabled,
    .wtsr_order_item_body .comment-form-submit .wtsr_review_submit_dummy.disabled,
    .wtsr_order_item_body .comment-form-submit .wtsr_review_submit.disabled:hover,
    .wtsr_order_item_body .comment-form-submit .wtsr_review_submit_dummy.disabled:hover {
        color: <?php echo $wtsr_aiop_button_colors['normal_txt'] ?>;
        background-color: <?php echo $wtsr_aiop_button_colors['normal_bg'] ?>;
        opacity: 0.5;
        cursor: default;
    }
</style>
<?php

if (!empty($order_id)) {
    $review = ReviewsModel::get_by_order_id($order_id);
} elseif (!empty($id)) {
    $id_array = explode('_', $id);
    $order_id = $id_array[0];
    $review = ReviewsModel::get_by_order_id($order_id);
} elseif (!empty($sample)) {
    $current_user = wp_get_current_user();
    $current_user_email = $current_user->user_email;

    if (md5($current_user_email) !== $sample) {
        wtsr_show_template('shortcodes/woo_review_page_empty.php');
        return;
    } else {
        wtsr_show_template('shortcodes/woo_review_page_sample.php');
        return;
    }
}

if (empty($review)) {
    wtsr_show_template('shortcodes/woo_review_page_empty.php');
    return;
}

// var_dump($review);
$review_id = $review[0]->id;
$order_id = $review[0]->order_id;
$order_email = $review[0]->email;
$order = wc_get_order( $order_id );
$order_items = TSManager::get_order_items($order);

if (!empty($id) && !empty($id_array[1])) {
    $is_verified = $id_array[1] === md5($order_email);

    if (!$is_verified) {
        wtsr_show_template('shortcodes/woo_review_page_empty.php');
        return;
    }
}

if (!empty($wtsr_all_reviews_page_reviews_title)) {
    ?>
    <h2 style="text-align: center;"><?php echo $wtsr_all_reviews_page_reviews_title; ?></h2>
    <?php
}

if (!empty($order_items)) {
    $reviewed = get_option('wtsr_reviewed_by_review_id_' . $review_id, array());
    $already_reviewed = false;
    $review_product_exists = false;

    if (!empty($review_product_id)) {
        foreach ($order_items as $order_item) {
            $product_id = $order_item['id'];

            if ((int) $product_id === (int) $review_product_id) {
                $review_product_exists = true;

                if (!empty($review_product_id) && !empty($reviewed[$review_product_id])) {
                    $already_reviewed = true;
                }

                break;
            }
        }
    }

    $scroll_to = '';

    if ($review_product_exists && !$already_reviewed) {
        $scroll_to = ' data-scroll-to="'.$review_product_id.'"';
    }
    ?>
    <div id="wtsr_order_items" class="wtsr_order_items"<?php echo $scroll_to; ?>>
        <?php
        $i = 0;
        foreach ($order_items as $order_item) {
            $product_id = $order_item['id'];
            $is_reviewed = !empty($reviewed[$product_id]);
            $open_class = '';
            $already_opened = false;

            $comment_id = '';
            $review_rating = '';
            $review_comment = '';

            if ($is_reviewed) {
                $comment_id = $reviewed[$product_id][0];
                $review_rating = (int) $reviewed[$product_id][1];
                $review_comment = $reviewed[$product_id][2];

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
            } else {
                if ($review_product_exists && !$already_reviewed) {
                    if ((int) $product_id === (int) $review_product_id) {
                        $open_class = ' wtsr_order_item_open';
                        $already_opened = true;
                    }
                } else {
                    $i++;

                    if (1 === $i && !$already_opened) {
                        $open_class = ' wtsr_order_item_open';
                        $already_opened = true;
                    }
                }

            }
            ?>
            <div id="wtsr_order_item_<?php echo $order_item['id'] ?>" class="wtsr_order_item<?php echo $open_class ?><?php echo $is_reviewed ? ' wtsr_reviewed' : ''; ?>">
                <div class="wtsr_order_item_header">
                    <div class="wtsr_order_item_header_img">
                        <?php
                        $img = get_the_post_thumbnail_url( $order_item['id'], 'full');
                        if (!empty($img)) {
                            ?>
                            <img src="<?php echo $img ?>" alt="<?php echo $order_item['name'] ?>">
                            <?php
                        } else {
                            $thumbnail = wc_placeholder_img_src( 'full' );
                            ?>
                            <img src="<?php echo $thumbnail ?>" alt="<?php echo $order_item['name'] ?>">
                            <?php
                        }
                        ?>
                    </div>
                    <div class="wtsr_order_item_header_title">
                        <?php echo $order_item['name'] ?>
                        <?php
                        if ($is_reviewed) {
                            ?>
                            <div class="wtsr_order_item_header_rating"><?php echo __('Your rating', 'more-better-reviews-for-woocommerce'); ?>: <strong class="rating_label"><?php echo $review_rating_label; ?></strong></div>
                            <?php
                        } else {
                            ?>
                            <div class="wtsr_order_item_header_rating"><?php echo __('Your rating', 'more-better-reviews-for-woocommerce'); ?>: <strong class="rating_label"><?php echo __('pending', 'more-better-reviews-for-woocommerce'); ?></strong></div>
                            <?php
                        }
                        ?>
                    </div>
                    <button class="wtsr_order_item_header_btn" type="button">&#10010;</button>
                </div>

                <div class="wtsr_order_item_body">
                    <div class="wtsr_order_item_body_group">
                        <div class="wtsr_order_item_body_img">
                            <?php
                            if ( 'yes' === $wtsr_all_reviews_page_product_link ) {
                                ?>
                                <a href="<?php echo get_permalink( $order_item['id'] ) ?>" target="_blank">
                                    <?php
                                    $img = get_the_post_thumbnail_url( $order_item['id'], 'full');
                                    if (!empty($img)) {
                                        ?>
                                        <img src="<?php echo $img ?>" alt="<?php echo $order_item['name'] ?>">
                                        <?php
                                    } else {
                                        $thumbnail = wc_placeholder_img_src( 'full' );
                                        ?>
                                        <img src="<?php echo $thumbnail ?>" alt="<?php echo $order_item['name'] ?>">
                                        <?php
                                    }
                                    ?>
                                </a>
                                <?php
                            } else {
                                $img = get_the_post_thumbnail_url( $order_item['id'], 'full');
                                if (!empty($img)) {
                                    ?>
                                    <img src="<?php echo $img ?>" alt="">
                                    <?php
                                } else {
                                    $thumbnail = wc_placeholder_img_src( 'full' );
                                    ?>
                                    <img src="<?php echo $thumbnail ?>" alt="">
                                    <?php
                                }
                            }
                            ?>
                        </div>

                        <div class="wtsr_order_item_body_desc">
                            <h4>
                                <?php
                                if ('yes' === $wtsr_all_reviews_page_product_link) {
                                    ?>
                                    <a href="<?php echo get_permalink( $order_item['id'] ) ?>" target="_blank">
                                        <?php echo $order_item['name'] ?>
                                    </a>
                                    <?php
                                } else {
                                    ?>
                                    <?php echo $order_item['name'] ?>
                                    <?php
                                }
                                ?>
                            </h4>

                            <?php
                            if ('yes' === $wtsr_all_reviews_page_description) {
                                ?>
                                <div class="wtsr_order_item_content">
                                    <?php echo wpautop($order_item['product_description']); ?>
                                </div>
                                <?php
                            }

                            if ($two_col) {
                                if ($is_reviewed) {
                                    ?>
                                    <div class="wtsr_order_item_ratings">
                                        <p style="margin-bottom:0;margin-top:0;">
                                            <?php echo __('Your rating', 'more-better-reviews-for-woocommerce'); ?>: <strong class="rating_label"><?php echo $review_rating_label; ?></strong>
                                        </p>

                                        <div class="comment-form-comment-text" style="margin-bottom:0;margin-top:0;">
                                            <label><?php echo __('Your review', 'more-better-reviews-for-woocommerce'); ?>:</label>

                                            <div class="text-holder">
                                                <?php echo wpautop($review_comment); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                } else {
                                    $checked = '';
                                    if ($review_product_exists && !$already_reviewed && $review_link_rating && (int) $product_id === (int) $review_product_id) {
                                        $checked = ' checked';

                                        if ($review_link_rating) {
                                            switch ($review_link_rating) {
                                                case 'five_star':
                                                    $review_link_rating = 5;
                                                    break;
                                                case 'four_star':
                                                    $review_link_rating = 4;
                                                    break;
                                                case 'three_star':
                                                    $review_link_rating = 3;
                                                    break;
                                                case 'two_star':
                                                    $review_link_rating = 2;
                                                    break;
                                                case 'one_star':
                                                    $review_link_rating = 1;
                                                    break;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="wtsr_order_item_ratings">
                                        <p class="wtsr_rating-star" style="margin-bottom:0;margin-top:10px;">
                                            <input
                                                    type="radio" name="wtsr_rating" value="5" title="<?php echo __('Excellent', 'more-better-reviews-for-woocommerce'); ?>"
                                                <?php echo (!empty($checked) && 5 === $review_link_rating ? $checked : ''); ?>
                                            ><span class="wtsr_star"></span><input
                                                    type="radio" name="wtsr_rating" value="4" title="<?php echo __('Very good', 'more-better-reviews-for-woocommerce'); ?>"
                                                <?php echo (!empty($checked) && 4 === $review_link_rating ? $checked : ''); ?>
                                            ><span class="wtsr_star"></span><input
                                                    type="radio" name="wtsr_rating" value="3" title="<?php echo __('Good', 'more-better-reviews-for-woocommerce'); ?>"
                                                <?php echo (!empty($checked) && 3 === $review_link_rating ? $checked : ''); ?>
                                            ><span class="wtsr_star"></span><input
                                                    type="radio" name="wtsr_rating" value="2" title="<?php echo __('Fair', 'more-better-reviews-for-woocommerce'); ?>"
                                                <?php echo (!empty($checked) && 2 === $review_link_rating ? $checked : ''); ?>
                                            ><span class="wtsr_star"></span><input
                                                    type="radio" name="wtsr_rating" value="1" title="<?php echo __('Poor', 'more-better-reviews-for-woocommerce'); ?>"
                                                <?php echo (!empty($checked) && 1 === $review_link_rating ? $checked : ''); ?>
                                            ><span class="wtsr_star"></span>
                                        </p>

                                        <div class="wtsr_order_item_comment">
                                            <p style="margin-bottom:0;margin-top:0;">
                                                <?php echo __('Your rating', 'more-better-reviews-for-woocommerce'); ?>: <strong class="rating_label"></strong>
                                            </p>

                                            <p class="comment-form-comment" style="margin-bottom:0;margin-top:0;">
                                                <label for="wtsr_comment_<?php echo $order_item['id']; ?>"><?php echo __('Your review', 'more-better-reviews-for-woocommerce'); ?>&nbsp;<span class="required">(*)</span>:</label>
                                                <textarea
                                                        id="wtsr_comment_<?php echo $order_item['id']; ?>"
                                                        class="wtsr_comment"
                                                        name="wtsr_comment" cols="45" rows="6" required=""
                                                        placeholder="<?php echo $textarea_placeholder; ?>"
                                                        <?php echo !empty($textarea_min_length) ? ' data-min-length="'.$textarea_min_length.'"' : ''; ?>
                                                ></textarea>
                                                <?php
                                                if (!empty($textarea_min_length)) {
                                                    ?>
                                                    <small class="warning-text" style="font-size:13px;"><?php echo __('Min comment length', 'more-better-reviews-for-woocommerce'); ?>:
                                                        <strong><?php echo $textarea_min_length; ?></strong></small>

                                                    - <small style="font-size:13px;"><?php echo __('Comment length', 'more-better-reviews-for-woocommerce'); ?>:
                                                        <strong class="wtsr_comment_length">0</strong></small>
                                                    <?php
                                                }
                                                ?>
                                            </p>

                                            <p class="comment-form-submit" style="margin-bottom:0;margin-top:10px;">
                                                <button class="wtsr_review_submit<?php echo !empty($textarea_min_length) ? ' disabled' : ''; ?>" data-product-id="<?php echo $order_item['id']; ?>" data-email="<?php echo $order_email; ?>" data-review-id="<?php echo $review_id; ?>" data-order-id="<?php echo $order_id; ?>"<?php echo !empty($textarea_min_length) ? ' disabled' : ''; ?>>
                                                    <?php _e( 'Submit review', 'more-better-reviews-for-woocommerce' ) ?>
                                                </button>
                                            </p>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                    if (!$two_col) {
                        ?>
                        <div class="wtsr_order_item_ratings">
                            <p class="wtsr_rating-star">
                                <input type="radio" name="wtsr_rating" value="5" title="<?php echo __('Excellent', 'more-better-reviews-for-woocommerce'); ?>"><span class="wtsr_star"></span><input
                                        type="radio" name="wtsr_rating" value="4" title="<?php echo __('Very good', 'more-better-reviews-for-woocommerce'); ?>"><span class="wtsr_star"></span><input
                                        type="radio" name="wtsr_rating" value="3" title="<?php echo __('Good', 'more-better-reviews-for-woocommerce'); ?>"><span class="wtsr_star"></span><input
                                        type="radio" name="wtsr_rating" value="2" title="<?php echo __('Fair', 'more-better-reviews-for-woocommerce'); ?>"><span class="wtsr_star"></span><input
                                        type="radio" name="rating" value="1" title="<?php echo __('Poor', 'more-better-reviews-for-woocommerce'); ?>"><span class="wtsr_star"></span>
                            </p>

                            <div class="wtsr_order_item_comment">
                                <p>
                                    <?php echo __('Your rating', 'more-better-reviews-for-woocommerce'); ?>: <strong class="rating_label"></strong>
                                </p>

                                <p class="comment-form-comment">
                                    <label for="wtsr_comment_<?php echo $order_item['id']; ?>"><?php echo __('Your review', 'more-better-reviews-for-woocommerce'); ?>&nbsp;<span class="required">(*)</span>:</label>
                                    <textarea id="wtsr_comment_<?php echo $order_item['id']; ?>" class="wtsr_comment" name="wtsr_comment" cols="45" rows="4" required=""></textarea>
                                </p>

                                <p class="comment-form-submit">
                                    <button class="wtsr_review_submit disabled" data-product-id="<?php echo $order_item['id']; ?>" data-email="<?php echo $order_email; ?>" data-review-id="<?php echo $review_id; ?>" data-order-id="<?php echo $order_id; ?>" disabled>
                                        <?php _e( 'Submit review', 'more-better-reviews-for-woocommerce' ) ?>
                                    </button>
                                </p>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
    $countdown_enabled = ReviewsModel::get_meta($review_id, 'coupon_generate_countdown', true);
    $coupon_countdown_period = ReviewsModel::get_meta($review_id, 'coupon_countdown_period', true);
    $thankyou_settings = Wtsr_Settings::get_thankyou_settings();

    if (true ) {
        if (empty($countdown_enabled)) {

            if (!empty($coupon_countdown_period)) {
                $delay = (int) $coupon_countdown_period * 60 * 60;
                $now = time() + $delay;

                ReviewsModel::update_meta($review_id, 'coupon_generate_countdown', $now);

                if (!empty($thankyou_settings['coupon_countdown_period_reset'])) {
                    $reset_delay = (int) $thankyou_settings['coupon_countdown_period_reset'] * 60 * 60;
                    $reset = $now + $reset_delay;
                    ReviewsModel::update_meta($review_id, 'coupon_generate_reset', $reset);
                }

                $countdown_enabled = ReviewsModel::get_meta($review_id, 'coupon_generate_countdown', true);
            }
        } else {
            $countdown_reset = ReviewsModel::get_meta($review_id, 'coupon_generate_reset', true);

            if (!empty($countdown_reset) && (int) $countdown_reset < time()) {
                $delay = (int) $coupon_countdown_period * 60 * 60;
                $now = time() + $delay;

                ReviewsModel::update_meta($review_id, 'coupon_generate_countdown', $now);

                if (!empty($thankyou_settings['coupon_countdown_period_reset'])) {
                    $reset_delay = (int) $thankyou_settings['coupon_countdown_period_reset'] * 60 * 60;
                    $reset = $now + $reset_delay;
                    ReviewsModel::update_meta($review_id, 'coupon_generate_reset', $reset);
                } else {
                    ReviewsModel::delete_meta($review_id, 'coupon_generate_reset');
                }
            }

            $countdown_enabled = ReviewsModel::get_meta($review_id, 'coupon_generate_countdown', true);
        }
    }

    if (!empty($countdown_enabled)) {
        ?>
        <style>
            body.wtsr_all_reviews_page_template {
                padding-bottom: 35px;
            }

            #wtsr_all_reviews_page_countdown_wrapper {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                background-color: #fff;
                z-index: 1200;
                -webkit-box-shadow: 0px -1px 2px 0px rgba(0,0,0,0.1);
                -moz-box-shadow: 0px -1px 2px 0px rgba(0,0,0,0.1);
                box-shadow: 0px -1px 2px 0px rgba(0,0,0,0.1);
            }

            #wtsr_all_reviews_page_countdown_wrapper p {
                line-height: 1.2;
                margin: 10px 0;
                font-size: 17px;
                text-align: center;
            }
        </style>
        <?php
    }

    if ($countdown_enabled) {
        $time_left = $countdown_enabled - time();
        if (0 < $time_left) {
            $cd = __('d', 'more-better-reviews-for-woocommerce');
            $ch = __('h', 'more-better-reviews-for-woocommerce');
            $cm = __('m', 'more-better-reviews-for-woocommerce');
            $cs = __('s', 'more-better-reviews-for-woocommerce');
            $cfinished = "<p>" . __('Your countdown finished! No Coupon for reviewing at the moment.', 'more-better-reviews-for-woocommerce') . "</p>";

            $date_countdown_gmt = date('M j, Y H:i:s', $countdown_enabled);
            $date_countdown = get_date_from_gmt( $date_countdown_gmt, $format = 'M j, Y H:i:s' );
            $days = floor($time_left / (24 * 60 * 60));
            $cholder = '<span id="wtsr_countdown"></span>';
            $ctext = sprintf( __( "Don't miss a chance to get your discount coupon! <strong>%s</strong>", 'more-better-reviews-for-woocommerce' ), $cholder )
            ?>
            <div id="wtsr_all_reviews_page_countdown_wrapper">
                <p>
                    <?php echo $ctext; ?>
                </p>
            </div>

            <script>
                // Set the date we're counting down to
                var countDownDate = new Date("<?php echo $date_countdown; ?>").getTime();

                countdownCoupon(countDownDate);

                // Update the count down every 1 second
                var x = setInterval(function() {

                    var left = countdownCoupon(countDownDate);

                    // If the count down is finished, write some text
                    if (left < 0) {
                        clearInterval(x);
                        document.getElementById("wtsr_all_reviews_page_countdown_wrapper").innerHTML = "<?php echo $cfinished; ?>";
                    }
                }, 1000);

                function countdownCoupon(countDownDate) {
                    // Get today's date and time
                    var now = new Date().getTime();

                    // Find the distance between now and the count down date
                    var distance = countDownDate - now;

                    // Time calculations for days, hours, minutes and seconds
                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Display the result in the element with id="demo"
                    document.getElementById("wtsr_countdown").innerHTML = days + "<?php echo $cd; ?> " + hours + "<?php echo $ch; ?> "
                        + minutes + "<?php echo $cm; ?> " + seconds + "<?php echo $cs; ?> ";

                        return distance;
                }
            </script>
            <?php
        } else {
            ?>

            <?php
        }
    }
}
?>
