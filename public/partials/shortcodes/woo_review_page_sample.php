<?php
$dummy_products = TSManager::get_dummy_products();
$wtsr_all_reviews_page_reviews_title = get_option('wtsr_all_reviews_page_reviews_title');
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

if ( false === $wtsr_all_reviews_page_reviews_title ) {
    $wtsr_all_reviews_page_reviews_title = __( 'Review now!', 'more-better-reviews-for-woocommerce' );
}

if (!empty($wtsr_all_reviews_page_reviews_title)) {
    ?>
    <h2 style="text-align: center;"><?php echo $wtsr_all_reviews_page_reviews_title; ?></h2>
    <?php
}
?>

<div id="wtsr_order_items" class="wtsr_order_items">
    <?php
    $i = 1;
    foreach ($dummy_products as $index => $dummy_product) {
        $open_class = '';

        if (3 === $i) {
            $open_class = ' wtsr_order_item_open';
        }
        ?>
        <div id="wtsr_order_item_<?php echo $index + 1 ?>" class="wtsr_order_item<?php echo $open_class ?>">
            <div class="wtsr_order_item_header">
                <div class="wtsr_order_item_header_img">
                    <img src="<?php echo $dummy_product['img_thumbnail'] ?>" alt="">
                </div>

                <div class="wtsr_order_item_header_title">
                    <?php echo $dummy_product['name'] ?>
                    <?php
                    if (3 === $i) {
                        ?>
                        <div class="wtsr_order_item_header_rating"><?php echo __('Your rating', 'more-better-reviews-for-woocommerce'); ?>: <strong class="rating_label"><?php echo __('Very good', 'more-better-reviews-for-woocommerce'); ?></strong></div>
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
                        <img src="<?php echo $dummy_product['img_full'] ?>" alt="">
                    </div>

                    <div class="wtsr_order_item_body_desc">
                        <h4>
                            <a href="#" target="_blank">
                                <?php echo $dummy_product['name'] ?>
                            </a>
                        </h4>

                        <div class="wtsr_order_item_ratings">
                            <p class="wtsr_rating-star" style="margin-bottom:0;margin-top:10px;">
                                <input
                                    type="radio" name="wtsr_rating" value="5" title="<?php echo __('Excellent', 'more-better-reviews-for-woocommerce'); ?>"
                                ><span class="wtsr_star"></span><input
                                    type="radio" name="wtsr_rating" value="4" title="<?php echo __('Very good', 'more-better-reviews-for-woocommerce'); ?>"<?php echo 3 === $i ? ' checked' : ''; ?>
                                ><span class="wtsr_star"></span><input
                                    type="radio" name="wtsr_rating" value="3" title="<?php echo __('Good', 'more-better-reviews-for-woocommerce'); ?>"
                                ><span class="wtsr_star"></span><input
                                    type="radio" name="wtsr_rating" value="2" title="<?php echo __('Fair', 'more-better-reviews-for-woocommerce'); ?>"
                                ><span class="wtsr_star"></span><input
                                    type="radio" name="wtsr_rating" value="1" title="<?php echo __('Poor', 'more-better-reviews-for-woocommerce'); ?>"
                                ><span class="wtsr_star"></span>
                            </p>

                            <div class="wtsr_order_item_comment"<?php echo 3 === $i ? ' style="display:block;"' : ''; ?>>
                                <p style="margin-bottom:0;margin-top:0;">
                                    <?php echo __('Your rating', 'more-better-reviews-for-woocommerce'); ?>: <strong class="rating_label"><?php echo 3 === $i ? __('Very good', 'more-better-reviews-for-woocommerce') : ''; ?></strong>
                                </p>

                                <p class="comment-form-comment" style="margin-bottom:0;margin-top:0;">
                                    <label for="wtsr_comment_<?php echo $index + 1 ?>"><?php echo __('Your review', 'more-better-reviews-for-woocommerce'); ?>&nbsp;<span class="required">(*)</span>:</label>
                                    <textarea
                                            id="wtsr_comment_<?php echo $index + 1 ?>"
                                            class="wtsr_comment"
                                            name="wtsr_comment" cols="45" rows="6"
                                            required=""
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
                                    <button class="wtsr_review_submit_dummy<?php echo !empty($textarea_min_length) ? ' disabled' : ''; ?>"<?php echo !empty($textarea_min_length) ? ' disabled' : ''; ?>>
                                        <?php _e( 'Submit review', 'more-better-reviews-for-woocommerce' ) ?>
                                    </button>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $i++;
    }
    ?>
</div>

<?php
$thankyou_settings = Wtsr_Settings::get_thankyou_settings();

if ($pixel_expiration = Wtsr_Settings::is_pixel_enabled()) {
    // Sample countdouw
    $cd = __('d', 'more-better-reviews-for-woocommerce');
    $ch = __('h', 'more-better-reviews-for-woocommerce');
    $cm = __('m', 'more-better-reviews-for-woocommerce');
    $cs = __('s', 'more-better-reviews-for-woocommerce');
    $cfinished = "<p>" . __('Your countdown finished! No Coupon for reviewing at the moment.', 'more-better-reviews-for-woocommerce') . "</p>";
    $cholder = '<span id="wtsr_countdown"></span>';
    $ctext = sprintf( __( "Don't miss a chance to get your discount coupon! <strong>%s</strong>", 'more-better-reviews-for-woocommerce' ), $cholder );
    $countdown_enabled = time() + ((int) $pixel_expiration * 60 * 60);
    $date_countdown_gmt = date('M j, Y H:i:s', $countdown_enabled);
    $date_countdown = get_date_from_gmt( $date_countdown_gmt, $format = 'M j, Y H:i:s' );
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
}